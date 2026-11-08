<?php
namespace App\Middleware;

use App\Core\Middleware;

class Permission extends Middleware
{
    private $permission;
    
    public function __construct($permission = null)
    {
        parent::__construct();
        $this->permission = $permission;
    }
    
    public function handle()
    {
        if (!$this->session->has('user_id')) {
            return false;
        }
        
        $userId = $this->session->get('user_id');
        $userRole = $this->session->get('user_role');
        
        // Super admin has all permissions
        if ($userRole === 'super_admin') {
            return true;
        }
        
        // Check specific permission
        $user = $this->db->selectOne(
            "SELECT role_id FROM users WHERE id = ?",
            [$userId]
        );
        
        $hasPermission = $this->db->selectOne(
            "SELECT COUNT(*) as count 
             FROM role_permission rp 
             JOIN permissions p ON rp.permission_id = p.id 
             WHERE rp.role_id = ? AND p.name = ?",
            [$user['role_id'], $this->permission]
        );
        
        if ($hasPermission['count'] > 0) {
            return true;
        }
        
        // Unauthorized
        http_response_code(403);
        echo "403 Forbidden - You do not have permission to access this resource.";
        exit;
    }
}