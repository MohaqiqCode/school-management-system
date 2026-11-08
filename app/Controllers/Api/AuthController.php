<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Helpers\Helper;
use App\Helpers\SecurityHelper;

class AuthController extends Controller
{
    /**
     * Login user and return JWT token
     * POST /api/v1/auth/login
     */
    public function login()
    {
        $data = $this->request->getJson();
        
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        // Validate
        if (empty($email) || empty($password)) {
            $this->json([
                'success' => false,
                'message' => 'ایمیل و رمز عبور الزامی است',
                'error_code' => 'VALIDATION_ERROR'
            ], 422);
            return;
        }
        
        // Rate limiting
        $ipKey = 'login_attempt_' . $this->request->getIpAddress();
        $attempts = $this->session->get($ipKey, ['count' => 0, 'time' => time()]);
        
        if (time() - $attempts['time'] < 900 && $attempts['count'] >= 5) {
            $this->json([
                'success' => false,
                'message' => 'تعداد تلاش‌های ناموفق زیاد است. ۱۵ دقیقه دیگر تلاش کنید',
                'error_code' => 'RATE_LIMIT'
            ], 429);
            return;
        }
        
        // Find user
        $user = $this->db->selectOne(
            "SELECT u.*, r.name as role_name, r.display_name as role_display,
                    b.name as branch_name
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE u.email = ? AND u.deleted_at IS NULL",
            [$email]
        );
        
        if (!$user || !Helper::verifyPassword($password, $user['password'])) {
            // Increment attempts
            $attempts['count']++;
            $attempts['time'] = time();
            $this->session->set($ipKey, $attempts);
            
            $this->json([
                'success' => false,
                'message' => 'ایمیل یا رمز عبور اشتباه است',
                'error_code' => 'INVALID_CREDENTIALS'
            ], 401);
            return;
        }
        
        if (!$user['is_active']) {
            $this->json([
                'success' => false,
                'message' => 'حساب کاربری شما غیرفعال شده است',
                'error_code' => 'ACCOUNT_INACTIVE'
            ], 403);
            return;
        }
        
        // Clear attempts
        $this->session->remove($ipKey);
        
        // Generate JWT token
        $secret = $this->config['security']['jwt_secret'] ?? 'default-secret-key';
        $expiry = $this->config['security']['jwt_expiry'] ?? 86400;
        
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role_name'],
            'branch_id' => $user['branch_id'],
            'iat' => time(),
            'exp' => time() + $expiry
        ];
        
        $token = SecurityHelper::generateJwt($payload, $secret);
        $refreshToken = SecurityHelper::generateJwt(
            array_merge($payload, ['exp' => time() + ($this->config['security']['refresh_token_expiry'] ?? 604800), 'type' => 'refresh']),
            $secret
        );
        
        // Save token to database
        $this->db->insert('jwt_tokens', [
            'user_id' => $user['id'],
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => date('Y-m-d H:i:s', time() + $expiry),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update last login
        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->request->getIpAddress()
        ], 'id = ?', [$user['id']]);
        
        // Log activity
        $this->logActivity('authentication', 'api_login', "API Login: {$user['email']}");
        
        $this->json([
            'success' => true,
            'message' => 'ورود موفق',
            'data' => [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiry,
                'user' => [
                    'id' => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role_name'],
                    'role_display' => $user['role_display'],
                    'branch_name' => $user['branch_name'],
                    'profile_photo' => $user['profile_photo']
                ]
            ]
        ]);
    }
    
    /**
     * Register new user
     * POST /api/v1/auth/register
     */
    public function register()
    {
        $data = $this->request->getJson();
        
        // Validate required fields
        $errors = [];
        
        if (empty($data['first_name'])) $errors['first_name'] = 'نام الزامی است';
        if (empty($data['last_name'])) $errors['last_name'] = 'نام خانوادگی الزامی است';
        if (empty($data['email'])) $errors['email'] = 'ایمیل الزامی است';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'ایمیل نامعتبر است';
        if (empty($data['password'])) $errors['password'] = 'رمز عبور الزامی است';
        elseif (strlen($data['password']) < 8) $errors['password'] = 'رمز عبور باید حداقل ۸ کاراکتر باشد';
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'خطای اعتبارسنجی', 'errors' => $errors], 422);
            return;
        }
        
        // Check unique email
        $existing = $this->db->selectOne("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL", [$data['email']]);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'این ایمیل قبلاً ثبت شده است'], 422);
            return;
        }
        
        // Create user
        $userCode = Helper::generateCode('USR');
        $hashedPassword = Helper::encryptPassword($data['password']);
        
        // Get default role (student or parent)
        $defaultRole = $this->db->selectOne("SELECT id FROM roles WHERE name = ? LIMIT 1", [$data['role'] ?? 'student']);
        $roleId = $defaultRole['id'] ?? 10;
        
        $userId = $this->db->insert('users', [
            'user_code' => $userCode,
            'branch_id' => $data['branch_id'] ?? 1,
            'role_id' => $roleId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? 'other',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('authentication', 'api_register', "API Register: {$data['email']}");
        
        $this->json([
            'success' => true,
            'message' => 'ثبت نام با موفقیت انجام شد',
            'data' => ['user_id' => $userId]
        ], 201);
    }
    
    /**
     * Forgot password
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword()
    {
        $data = $this->request->getJson();
        $email = $data['email'] ?? '';
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'ایمیل معتبر وارد کنید'], 422);
            return;
        }
        
        $user = $this->db->selectOne("SELECT id, first_name, email FROM users WHERE email = ? AND is_active = 1 AND deleted_at IS NULL", [$email]);
        
        if ($user) {
            $token = Helper::generateToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->db->insert('jwt_tokens', [
                'user_id' => $user['id'],
                'token' => $token,
                'expires_at' => $expiry,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Send email (implementation depends on mail service)
            // $this->sendPasswordResetEmail($user['email'], $user['first_name'], $token);
        }
        
        // Always return success to prevent email enumeration
        $this->json([
            'success' => true,
            'message' => 'اگر ایمیل وارد شده در سیستم ثبت شده باشد، لینک بازیابی به آن ارسال خواهد شد'
        ]);
    }
    
    /**
     * Reset password
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword()
    {
        $data = $this->request->getJson();
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';
        $passwordConfirmation = $data['password_confirmation'] ?? '';
        
        if (empty($token) || empty($password)) {
            $this->json(['success' => false, 'message' => 'توکن و رمز عبور جدید الزامی است'], 422);
            return;
        }
        
        if ($password !== $passwordConfirmation) {
            $this->json(['success' => false, 'message' => 'رمز عبور و تکرار آن مطابقت ندارند'], 422);
            return;
        }
        
        if (strlen($password) < 8) {
            $this->json(['success' => false, 'message' => 'رمز عبور باید حداقل ۸ کاراکتر باشد'], 422);
            return;
        }
        
        $tokenData = $this->db->selectOne(
            "SELECT * FROM jwt_tokens WHERE token = ? AND expires_at > NOW() AND is_revoked = 0",
            [$token]
        );
        
        if (!$tokenData) {
            $this->json(['success' => false, 'message' => 'توکن نامعتبر یا منقضی شده است'], 400);
            return;
        }
        
        $hashedPassword = Helper::encryptPassword($password);
        $this->db->update('users', ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$tokenData['user_id']]);
        $this->db->update('jwt_tokens', ['is_revoked' => 1], 'id = ?', [$tokenData['id']]);
        
        $this->logActivity('authentication', 'api_password_reset', "Password reset for user {$tokenData['user_id']}");
        
        $this->json(['success' => true, 'message' => 'رمز عبور با موفقیت بازنشانی شد']);
    }
    
    /**
     * Refresh JWT token
     * POST /api/v1/auth/refresh-token
     */
    public function refreshToken()
    {
        $data = $this->request->getJson();
        $refreshToken = $data['refresh_token'] ?? '';
        
        if (empty($refreshToken)) {
            $this->json(['success' => false, 'message' => 'رفرش توکن الزامی است'], 422);
            return;
        }
        
        $secret = $this->config['security']['jwt_secret'] ?? 'default-secret-key';
        $payload = SecurityHelper::verifyJwt($refreshToken, $secret);
        
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            $this->json(['success' => false, 'message' => 'رفرش توکن نامعتبر است'], 401);
            return;
        }
        
        $expiry = $this->config['security']['jwt_expiry'] ?? 86400;
        
        $newPayload = [
            'user_id' => $payload['user_id'],
            'email' => $payload['email'] ?? '',
            'role' => $payload['role'] ?? '',
            'branch_id' => $payload['branch_id'] ?? null,
            'iat' => time(),
            'exp' => time() + $expiry
        ];
        
        $newToken = SecurityHelper::generateJwt($newPayload, $secret);
        
        $this->json([
            'success' => true,
            'data' => [
                'token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiry
            ]
        ]);
    }
    
    /**
     * Get current authenticated user
     * GET /api/v1/auth/me
     */
    public function me()
    {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $user = $this->db->selectOne(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                    u.gender, u.date_of_birth, u.is_active,
                    r.name as role_name, r.display_name as role_display,
                    b.name as branch_name
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE u.id = ? AND u.deleted_at IS NULL",
            [$userId]
        );
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        // Get additional details based on role
        $additional = null;
        switch ($user['role_name']) {
            case 'student':
                $additional = $this->db->selectOne("SELECT student_code, current_class_id FROM students WHERE user_id = ?", [$userId]);
                break;
            case 'teacher':
                $additional = $this->db->selectOne("SELECT teacher_code, department_id FROM teachers WHERE user_id = ?", [$userId]);
                break;
        }
        
        $this->json([
            'success' => true,
            'data' => array_merge($user, ['additional' => $additional])
        ]);
    }
    
    /**
     * Logout
     * POST /api/v1/auth/logout
     */
    public function logout()
    {
        $userId = $this->session->get('user_id');
        
        if ($userId) {
            // Revoke all tokens for user
            $this->db->update('jwt_tokens', ['is_revoked' => 1], 'user_id = ? AND is_revoked = 0', [$userId]);
            $this->logActivity('authentication', 'api_logout', "API Logout user {$userId}");
        }
        
        $this->session->destroy();
        
        $this->json(['success' => true, 'message' => 'خروج موفق']);
    }
    
    private function logActivity($module, $action, $description)
    {
        try {
            $this->db->insert('audit_logs', [
                'user_id' => $this->session->get('user_id'),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => $this->request->getIpAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {}
    }
}