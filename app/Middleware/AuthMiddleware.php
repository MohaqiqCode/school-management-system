<?php
namespace App\Middleware;

/**
 * Authentication Middleware
 * بررسی احراز هویت کاربر
 */
class AuthMiddleware extends Middleware
{
    /**
     * Handle authentication check
     * 
     * @return bool
     */
    public function handle()
    {
        // Check if user is logged in via session
        if ($this->session->has('user_id')) {
            return $this->validateSession();
        }
        
        // Check for remember token in cookie
        if (isset($_COOKIE['remember_token'])) {
            return $this->handleRememberToken();
        }
        
        // Check for API token
        if ($this->request->header('Authorization')) {
            return $this->handleApiToken();
        }
        
        // Not authenticated
        $this->abort(401, 'لطفاً ابتدا وارد سیستم شوید');
        return false;
    }
    
    /**
     * Validate current session
     * 
     * @return bool
     */
    private function validateSession()
    {
        $userId = $this->session->get('user_id');
        
        // Check if user still exists and is active
        $user = $this->db->selectOne(
            "SELECT id, is_active FROM users WHERE id = ? AND deleted_at IS NULL",
            [$userId]
        );
        
        if (!$user) {
            $this->session->destroy();
            $this->abort(401, 'کاربر یافت نشد');
            return false;
        }
        
        if (!$user['is_active']) {
            $this->session->destroy();
            $this->abort(403, 'حساب کاربری شما غیرفعال شده است');
            return false;
        }
        
        // Check session expiry
        $lastActivity = $this->session->get('last_activity', 0);
        $sessionLifetime = $this->config['session']['lifetime'] ?? 120;
        
        if (time() - $lastActivity > $sessionLifetime * 60) {
            $this->session->destroy();
            $this->abort(401, 'نشست شما منقضی شده است');
            return false;
        }
        
        // Update last activity
        $this->session->set('last_activity', time());
        
        // Check if user's branch is still active
        $branchId = $this->session->get('branch_id');
        if ($branchId) {
            $branch = $this->db->selectOne(
                "SELECT is_active FROM branches WHERE id = ?",
                [$branchId]
            );
            
            if (!$branch || !$branch['is_active']) {
                $this->session->flash('warning', 'شعبه شما غیرفعال شده است');
            }
        }
        
        return true;
    }
    
    /**
     * Handle remember me token
     * 
     * @return bool
     */
    private function handleRememberToken()
    {
        $token = $_COOKIE['remember_token'];
        
        $tokenData = $this->db->selectOne(
            "SELECT jt.*, u.id as user_id, u.first_name, u.last_name, u.email,
                    r.name as role_name, u.branch_id, b.name as branch_name
             FROM jwt_tokens jt 
             JOIN users u ON jt.user_id = u.id 
             JOIN roles r ON u.role_id = r.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE jt.token = ? AND jt.expires_at > NOW() AND jt.is_revoked = 0
             AND u.is_active = 1 AND u.deleted_at IS NULL",
            [$token]
        );
        
        if (!$tokenData) {
            // Invalid or expired token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
            $this->abort(401, 'لطفاً مجدداً وارد شوید');
            return false;
        }
        
        // Regenerate session
        $this->session->regenerate();
        
        // Set session data
        $this->session->set('user_id', $tokenData['user_id']);
        $this->session->set('user_email', $tokenData['email']);
        $this->session->set('user_name', $tokenData['first_name'] . ' ' . $tokenData['last_name']);
        $this->session->set('user_role', $tokenData['role_name']);
        $this->session->set('branch_id', $tokenData['branch_id']);
        $this->session->set('branch_name', $tokenData['branch_name']);
        $this->session->set('last_activity', time());
        
        // Create new session record
        $this->db->insert('user_sessions', [
            'user_id' => $tokenData['user_id'],
            'session_id' => session_id(),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $this->request->getUserAgent(),
            'last_activity' => time(),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update last login
        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->getClientIp()
        ], 'id = ?', [$tokenData['user_id']]);
        
        return true;
    }
    
    /**
     * Handle API token authentication
     * 
     * @return bool
     */
    private function handleApiToken()
    {
        $authHeader = $this->request->header('Authorization');
        $token = '';
        
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
        }
        
        if (empty($token)) {
            $this->response->json([
                'success' => false,
                'message' => 'Invalid authorization token'
            ], 401);
            return false;
        }
        
        // Verify JWT token
        $payload = $this->verifyJwtToken($token);
        
        if (!$payload) {
            $this->response->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
            return false;
        }
        
        // Set user data from token payload
        $this->session->set('user_id', $payload['user_id']);
        $this->session->set('user_role', $payload['role'] ?? '');
        $this->session->set('branch_id', $payload['branch_id'] ?? null);
        $this->session->set('last_activity', time());
        
        return true;
    }
    
    /**
     * Verify JWT token
     * 
     * @param string $token
     * @return array|false
     */
    private function verifyJwtToken($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        $secret = $this->config['security']['jwt_secret'] ?? 'default-secret-key';
        
        $validSignature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $secret, true)
        );
        
        if (!hash_equals($validSignature, $signature)) {
            return false;
        }
        
        $payload = json_decode($this->base64UrlDecode($payload), true);
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}