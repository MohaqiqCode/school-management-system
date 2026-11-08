<?php
namespace App\Models;

/**
 * Role-Permission Pivot Model
 * مدیریت ارتباط نقش‌ها و مجوزها
 */
class RolePermission extends Model
{
    protected $table = 'role_permission';
    protected $timestamps = false;
    
    /**
     * Assign permissions to role
     */
    public function assignToRole($roleId, array $permissionIds)
    {
        $this->db->beginTransaction();
        
        try {
            // Remove existing
            $this->db->delete('role_permission', 'role_id = ?', [$roleId]);
            
            // Add new permissions
            foreach ($permissionIds as $permissionId) {
                $this->db->insert('role_permission', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Remove all permissions from role
     */
    public function removeFromRole($roleId)
    {
        return $this->db->delete('role_permission', 'role_id = ?', [$roleId]);
    }
    
    /**
     * Get role permissions
     */
    public function getRolePermissions($roleId)
    {
        $sql = "SELECT p.* FROM permissions p 
                JOIN role_permission rp ON p.id = rp.permission_id 
                WHERE rp.role_id = ?
                ORDER BY p.module, p.name";
        
        return $this->db->select($sql, [$roleId]);
    }
    
    /**
     * Get permission IDs for role
     */
    public function getRolePermissionIds($roleId)
    {
        $sql = "SELECT permission_id FROM role_permission WHERE role_id = ?";
        return $this->db->selectColumn($sql, [$roleId], 0);
    }
    
    /**
     * Check if role has permission
     */
    public function roleHasPermission($roleId, $permissionId)
    {
        $sql = "SELECT COUNT(*) as total FROM role_permission 
                WHERE role_id = ? AND permission_id = ?";
        $result = $this->db->selectOne($sql, [$roleId, $permissionId]);
        return ($result['total'] ?? 0) > 0;
    }
    
    /**
     * Clone permissions from one role to another
     */
    public function clonePermissions($fromRoleId, $toRoleId)
    {
        $permissions = $this->getRolePermissionIds($fromRoleId);
        return $this->assignToRole($toRoleId, $permissions);
    }
}