<?php
namespace App\Middleware;

/**
 * Role Middleware
 * بررسی نقش کاربر
 * 
 * Usage: Role:admin,principal
 * Usage: Role:teacher
 */
class RoleMiddleware extends Middleware
{
    /**
     * Handle role check
     * 
     * @param string ...$roles
     * @return bool
     */
    public function handle(...$roles)
    {
        // Ensure user is authenticated
        if (!$this->session->has('user_id')) {
            $this->abort(401, 'لطفاً ابتدا وارد سیستم شوید');
            return false;
        }
        
        if (empty($roles)) {
            return true;
        }
        
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->abort(401, 'کاربر یافت نشد');
            return false;
        }
        
        // Super admin has access to everything
        if ($user['role_name'] === 'super_admin') {
            return true;
        }
        
        // Check if user has one of the required roles
        if (!in_array($user['role_name'], $roles)) {
            $this->log('role_denied', 
                "User {$user['id']} with role '{$user['role_name']}' attempted to access with required roles: " . implode(', ', $roles)
            );
            
            if ($this->request->wantsJson()) {
                $this->response->json([
                    'success' => false,
                    'message' => 'نقش شما اجازه دسترسی به این بخش را ندارد',
                    'your_role' => $user['role_name'],
                    'required_roles' => $roles
                ], 403);
            } else {
                $this->session->flash('error', 'شما دسترسی به این بخش را ندارید');
                $this->response->redirect('/dashboard');
            }
            
            return false;
        }
        
        return true;
    }
}