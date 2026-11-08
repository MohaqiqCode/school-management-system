<?php
namespace App\Middleware;

use App\Core\Middleware;

class Auth extends Middleware
{
    public function handle()
    {
        // Check if user is logged in
        if (!$this->session->has('user_id')) {
            // Check for remember token
            if (isset($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                
                $tokenData = $this->db->selectOne(
                    "SELECT * FROM jwt_tokens WHERE token = ? AND expires_at > NOW() AND is_revoked = 0",
                    [$token]
                );
                
                if ($tokenData) {
                    $user = $this->db->selectOne(
                        "SELECT u.*, r.name as role_name 
                         FROM users u 
                         JOIN roles r ON u.role_id = r.id 
                         WHERE u.id = ? AND u.is_active = 1",
                        [$tokenData['user_id']]
                    );
                    
                    if ($user) {
                        $this->session->set('user_id', $user['id']);
                        $this->session->set('user_email', $user['email']);
                        $this->session->set('user_name', $user['first_name'] . ' ' . $user['last_name']);
                        $this->session->set('user_role', $user['role_name']);
                        $this->session->set('branch_id', $user['branch_id']);
                        
                        return true;
                    }
                }
                
                setcookie('remember_token', '', time() - 3600, '/');
            }
            
            $this->redirectToLogin();
            return false;
        }
        
        // Check if user is still active
        $user = $this->db->selectOne(
            "SELECT is_active FROM users WHERE id = ?",
            [$this->session->get('user_id')]
        );
        
        if (!$user || !$user['is_active']) {
            $this->session->destroy();
            $this->redirectToLogin();
            return false;
        }
        
        // Update session last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    private function redirectToLogin()
    {
        header('Location: /login');
        exit;
    }
}