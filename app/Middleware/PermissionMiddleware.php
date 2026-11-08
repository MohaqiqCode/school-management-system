<?php
namespace App\Middleware;

/**
 * Permission Middleware
 * بررسی مجوزهای دسترسی
 * 
 * Usage: Permission:view_students
 * Usage: Permission:create_students,edit_students
 */
class PermissionMiddleware extends Middleware
{
    /**
     * Handle permission check
     * 
     * @param string ...$permissions
     * @return bool
     */
    public function handle(...$permissions)
    {
        // Ensure user is authenticated
        if (!$this->session->has('user_id')) {
            $this->abort(401, 'لطفاً ابتدا وارد سیستم شوید');
            return false;
        }
        
        // If no permissions specified, allow access
        if (empty($permissions)) {
            return true;
        }
        
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->abort(401, 'کاربر یافت نشد');
            return false;
        }
        
        // Super admin bypasses all permission checks
        if ($user['role_name'] === 'super_admin') {
            return true;
        }
        
        // Check if user has at least one of the required permissions
        $hasPermission = false;
        
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }
        
        if (!$hasPermission) {
            // Log unauthorized access attempt
            $this->log('permission_denied', 
                "User {$user['id']} attempted to access with required permissions: " . implode(', ', $permissions)
            );
            
            if ($this->request->wantsJson()) {
                $this->response->json([
                    'success' => false,
                    'message' => 'شما مجوز دسترسی به این بخش را ندارید',
                    'required_permissions' => $permissions
                ], 403);
            } else {
                $this->session->flash('error', 'شما مجوز دسترسی به این بخش را ندارید');
                $this->session->flash('required_permissions', $permissions);
                $this->response->redirect('/dashboard');
            }
            
            return false;
        }
        
        return true;
    }
}