<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class AuthController extends Controller
{
    public function loginForm()
    {
        if ($this->session->has('user_id')) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Generate CSRF token
        if (!$this->session->has('csrf_token')) {
            $this->session->set('csrf_token', Helper::generateToken());
        }
        
        $this->view('auth/login', [
            'title' => 'ورود به سیستم مدیریت مدرسه',
            'csrf_token' => $this->session->get('csrf_token')
        ]);
    }
    
    public function login()
    {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->session->flash('error', 'توکن امنیتی نامعتبر است');
            $this->redirect('/login');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validate input
        $errors = [];
        if (empty($email)) {
            $errors['email'] = 'ایمیل الزامی است';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'فرمت ایمیل نامعتبر است';
        }
        if (empty($password)) {
            $errors['password'] = 'رمز عبور الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', ['email' => $email]);
            $this->redirect('/login');
            return;
        }
        
        // Check login attempts (rate limiting)
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $attempts = $this->getLoginAttempts($email, $ipAddress);
        
        if ($attempts >= 5) {
            $this->session->flash('error', 'تعداد تلاش‌های ناموفق زیاد است. لطفاً 15 دقیقه دیگر تلاش کنید');
            $this->redirect('/login');
            return;
        }
        
        // Find user
        $user = $this->db->selectOne(
            "SELECT u.*, r.name as role_name, r.display_name as role_display_name,
                    b.name as branch_name, b.id as branch_id
             FROM users u 
             INNER JOIN roles r ON u.role_id = r.id 
             INNER JOIN branches b ON u.branch_id = b.id 
             WHERE u.email = ? AND u.deleted_at IS NULL",
            [$email]
        );
        
        if (!$user) {
            $this->logFailedAttempt($email, $ipAddress, 'User not found');
            $this->session->flash('error', 'ایمیل یا رمز عبور اشتباه است');
            $this->session->flash('old', ['email' => $email]);
            $this->redirect('/login');
            return;
        }
        
        if (!$user['is_active']) {
            $this->logFailedAttempt($email, $ipAddress, 'Account deactivated');
            $this->session->flash('error', 'حساب کاربری شما غیرفعال شده است. با مدیر سیستم تماس بگیرید');
            $this->redirect('/login');
            return;
        }
        
        if (!Helper::verifyPassword($password, $user['password'])) {
            $this->logFailedAttempt($email, $ipAddress, 'Invalid password');
            $this->session->flash('error', 'ایمیل یا رمز عبور اشتباه است');
            $this->session->flash('old', ['email' => $email]);
            $this->redirect('/login');
            return;
        }
        
        // Check email verification if required
        if (!$user['email_verified_at'] && $this->config['require_email_verification'] ?? false) {
            $this->session->flash('warning', 'لطفاً ابتدا ایمیل خود را تأیید کنید');
            $this->redirect('/login');
            return;
        }
        
        // Clear login attempts
        $this->clearLoginAttempts($email, $ipAddress);
        
        // Regenerate session ID for security
        $this->session->regenerate();
        
        // Set session data
        $this->session->set('user_id', $user['id']);
        $this->session->set('user_code', $user['user_code']);
        $this->session->set('user_email', $user['email']);
        $this->session->set('user_name', $user['first_name'] . ' ' . $user['last_name']);
        $this->session->set('user_role', $user['role_name']);
        $this->session->set('user_role_display', $user['role_display_name']);
        $this->session->set('branch_id', $user['branch_id']);
        $this->session->set('branch_name', $user['branch_name']);
        $this->session->set('role_id', $user['role_id']);
        $this->session->set('profile_photo', $user['profile_photo']);
        
        // Handle remember me functionality
        if ($remember) {
            $token = Helper::generateToken();
            $refreshToken = Helper::generateToken();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            $this->db->insert('jwt_tokens', [
                'user_id' => $user['id'],
                'token' => $token,
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresAt,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            setcookie('remember_token', $token, [
                'expires' => strtotime('+30 days'),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        
        // Update last login information
        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ipAddress,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);
        
        // Create user session record
        $this->db->insert('user_sessions', [
            'user_id' => $user['id'],
            'session_id' => session_id(),
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'last_activity' => time(),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log successful login
        $this->logActivity($user['id'], 'login', 'ورود موفق به سیستم', 'authentication');
        
        // Redirect to dashboard
        $this->redirect('/dashboard');
    }
    
    public function logout()
    {
        $userId = $this->session->get('user_id');
        
        if ($userId) {
            // Log logout activity
            $this->logActivity($userId, 'logout', 'خروج از سیستم', 'authentication');
            
            // Clear remember token if exists
            if (isset($_COOKIE['remember_token'])) {
                $this->db->delete('jwt_tokens', 'user_id = ?', [$userId]);
                setcookie('remember_token', '', time() - 3600, '/');
            }
            
            // Remove user session
            $this->db->delete('user_sessions', 'user_id = ? AND session_id = ?', [
                $userId, 
                session_id()
            ]);
        }
        
        // Destroy session
        $this->session->destroy();
        
        $this->redirect('/login');
    }
    
    public function forgotPasswordForm()
    {
        if ($this->session->has('user_id')) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->view('auth/forgot_password', [
            'title' => 'فراموشی رمز عبور'
        ]);
    }
    
    public function forgotPassword()
    {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->flash('error', 'لطفاً یک ایمیل معتبر وارد کنید');
            $this->redirect('/forgot-password');
            return;
        }
        
        // Find user
        $user = $this->db->selectOne(
            "SELECT id, email, first_name, last_name FROM users WHERE email = ? AND is_active = 1 AND deleted_at IS NULL",
            [$email]
        );
        
        if (!$user) {
            // Don't reveal if email exists or not (security best practice)
            $this->session->flash('success', 'اگر ایمیل وارد شده در سیستم ثبت شده باشد، لینک بازیابی به آن ارسال خواهد شد');
            $this->redirect('/login');
            return;
        }
        
        // Generate reset token
        $token = Helper::generateToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Invalidate old reset tokens
        $this->db->update('jwt_tokens', 
            ['is_revoked' => 1], 
            'user_id = ? AND is_revoked = 0', 
            [$user['id']]
        );
        
        // Create new token
        $this->db->insert('jwt_tokens', [
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => $expiry,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Send reset email
        $resetLink = $this->config['app_url'] . '/reset-password/' . $token;
        $emailSent = $this->sendPasswordResetEmail($user['email'], $user['first_name'], $resetLink);
        
        if ($emailSent) {
            $this->logActivity($user['id'], 'password_reset_request', 'درخواست بازیابی رمز عبور', 'authentication');
        }
        
        $this->session->flash('success', 'اگر ایمیل وارد شده در سیستم ثبت شده باشد، لینک بازیابی به آن ارسال خواهد شد');
        $this->redirect('/login');
    }
    
    public function resetPasswordForm($token)
    {
        if (empty($token)) {
            $this->session->flash('error', 'لینک بازیابی نامعتبر است');
            $this->redirect('/login');
            return;
        }
        
        // Validate token
        $tokenData = $this->db->selectOne(
            "SELECT jt.*, u.email, u.first_name 
             FROM jwt_tokens jt 
             JOIN users u ON jt.user_id = u.id 
             WHERE jt.token = ? AND jt.expires_at > NOW() AND jt.is_revoked = 0",
            [$token]
        );
        
        if (!$tokenData) {
            $this->session->flash('error', 'لینک بازیابی منقضی شده یا نامعتبر است');
            $this->redirect('/login');
            return;
        }
        
        $this->view('auth/reset_password', [
            'title' => 'بازیابی رمز عبور',
            'token' => $token,
            'email' => $tokenData['email']
        ]);
    }
    
    public function resetPassword()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        
        // Validate inputs
        $errors = [];
        
        if (empty($token)) {
            $this->session->flash('error', 'لینک بازیابی نامعتبر است');
            $this->redirect('/login');
            return;
        }
        
        if (empty($password)) {
            $errors['password'] = 'رمز عبور الزامی است';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'رمز عبور باید حداقل 8 کاراکتر باشد';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'رمز عبور باید حداقل یک حرف بزرگ داشته باشد';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'رمز عبور باید حداقل یک حرف کوچک داشته باشد';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'رمز عبور باید حداقل یک عدد داشته باشد';
        }
        
        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'رمز عبور و تکرار آن مطابقت ندارند';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect('/reset-password/' . $token);
            return;
        }
        
        // Validate token again
        $tokenData = $this->db->selectOne(
            "SELECT * FROM jwt_tokens WHERE token = ? AND expires_at > NOW() AND is_revoked = 0",
            [$token]
        );
        
        if (!$tokenData) {
            $this->session->flash('error', 'لینک بازیابی منقضی شده یا نامعتبر است');
            $this->redirect('/login');
            return;
        }
        
        // Update password
        $hashedPassword = Helper::encryptPassword($password);
        $this->db->update('users', [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$tokenData['user_id']]);
        
        // Revoke token
        $this->db->update('jwt_tokens', ['is_revoked' => 1], 'id = ?', [$tokenData['id']]);
        
        // Revoke all sessions for this user (force re-login)
        $this->db->delete('user_sessions', 'user_id = ?', [$tokenData['user_id']]);
        
        // Log activity
        $this->logActivity($tokenData['user_id'], 'password_reset', 'بازیابی موفق رمز عبور', 'authentication');
        
        $this->session->flash('success', 'رمز عبور با موفقیت تغییر کرد. لطفاً با رمز جدید وارد شوید');
        $this->redirect('/login');
    }
    
    public function changePasswordForm()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        $this->view('auth/change_password', [
            'title' => 'تغییر رمز عبور',
            'user' => $user
        ]);
    }
    
    public function changePassword()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors['current_password'] = 'رمز عبور فعلی الزامی است';
        }
        
        if (empty($newPassword)) {
            $errors['new_password'] = 'رمز عبور جدید الزامی است';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'رمز عبور جدید باید حداقل 8 کاراکتر باشد';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'رمز عبور جدید و تکرار آن مطابقت ندارند';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect('/change-password');
            return;
        }
        
        // Verify current password
        $userData = $this->db->selectOne("SELECT password FROM users WHERE id = ?", [$user['id']]);
        
        if (!Helper::verifyPassword($currentPassword, $userData['password'])) {
            $this->session->flash('error', 'رمز عبور فعلی اشتباه است');
            $this->redirect('/change-password');
            return;
        }
        
        // Update password
        $hashedPassword = Helper::encryptPassword($newPassword);
        $this->db->update('users', [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);
        
        // Log activity
        $this->logActivity($user['id'], 'password_change', 'تغییر رمز عبور', 'authentication');
        
        $this->session->flash('success', 'رمز عبور با موفقیت تغییر کرد');
        $this->redirect('/profile');
    }
    
    private function getLoginAttempts($email, $ipAddress)
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(*) as attempts 
             FROM audit_logs 
             WHERE (description LIKE ? OR description LIKE ?)
             AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            ["%Failed login: {$email}%", "%Failed login from IP: {$ipAddress}%"]
        );
        
        return $result['attempts'] ?? 0;
    }
    
    private function logFailedAttempt($email, $ipAddress, $reason)
    {
        $this->db->insert('audit_logs', [
            'user_id' => null,
            'action' => 'login_failed',
            'module' => 'authentication',
            'description' => "Failed login: {$email} from IP: {$ipAddress} - Reason: {$reason}",
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function clearLoginAttempts($email, $ipAddress)
    {
        // We don't delete logs, just the rate limiting is time-based
    }
    
    private function logActivity($userId, $action, $description, $module)
    {
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function sendPasswordResetEmail($email, $firstName, $resetLink)
    {
        try {
            $subject = "بازیابی رمز عبور - سیستم مدیریت مدرسه";
            
            $message = "
            <html>
            <head>
                <title>بازیابی رمز عبور</title>
            </head>
            <body>
                <div style='font-family: Tahoma, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2>سلام {$firstName} عزیز</h2>
                    <p>درخواست بازیابی رمز عبور برای حساب کاربری شما ثبت شده است.</p>
                    <p>برای تنظیم رمز عبور جدید، روی لینک زیر کلیک کنید:</p>
                    <p>
                        <a href='{$resetLink}' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                            تنظیم رمز عبور جدید
                        </a>
                    </p>
                    <p>این لینک تا 1 ساعت معتبر است.</p>
                    <p>اگر شما این درخواست را نداده‌اید، لطفاً این ایمیل را نادیده بگیرید.</p>
                    <hr>
                    <p style='color: #666; font-size: 12px;'>این یک ایمیل خودکار است. لطفاً به آن پاسخ ندهید.</p>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $this->config['email']['from_name'] . " <" . $this->config['email']['from_address'] . ">\r\n";
            
            return mail($email, $subject, $message, $headers);
        } catch (\Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }
}