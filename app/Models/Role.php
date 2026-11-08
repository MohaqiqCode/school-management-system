<?php
namespace App\Models;

/**
 * Role Model
 * مدیریت نقش‌های کاربری
 */
class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system_role',
        'can_be_deleted',
        'can_be_modified',
        'color',
        'icon',
    ];
    
    protected $casts = [
        'is_system_role' => 'bool',
        'can_be_deleted' => 'bool',
        'can_be_modified' => 'bool',
    ];
    
    protected $appends = [
        'user_count',
        'permission_count',
    ];
    
    /**
     * Get role with permissions
     */
    public function getWithPermissions($id)
    {
        $role = $this->find($id);
        
        if ($role) {
            $role->attributes['permissions'] = $this->getPermissions($id);
        }
        
        return $role;
    }
    
    /**
     * Get permissions for role
     */
    public function getPermissions($roleId)
    {
        $sql = "SELECT p.* FROM permissions p 
                JOIN role_permission rp ON p.id = rp.permission_id 
                WHERE rp.role_id = ?
                ORDER BY p.module, p.name";
        
        return $this->db->select($sql, [$roleId]);
    }
    
    /**
     * Assign permissions to role
     */
    public function assignPermissions($roleId, array $permissionIds)
    {
        $this->db->beginTransaction();
        
        try {
            // Remove existing permissions
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
     * Get all roles with user count
     */
    public function getAllWithUserCount()
    {
        $sql = "SELECT r.*, 
                       (SELECT COUNT(*) FROM users WHERE role_id = r.id AND deleted_at IS NULL) as user_count
                FROM roles r 
                ORDER BY r.display_name";
        
        return $this->hydrate($this->db->select($sql));
    }
    
    /**
     * Get system roles only
     */
    public function getSystemRoles()
    {
        $sql = "SELECT * FROM roles WHERE is_system_role = 1 ORDER BY display_name";
        return $this->hydrate($this->db->select($sql));
    }
    
    /**
     * Get role by name
     */
    public function findByName($name)
    {
        $sql = "SELECT * FROM roles WHERE name = ?";
        $result = $this->db->selectOne($sql, [$name]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    // Accessors
    public function appendUserCountAttribute()
    {
        $roleId = $this->attributes['id'] ?? null;
        
        if (!$roleId) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as total FROM users WHERE role_id = ? AND deleted_at IS NULL";
        $result = $this->db->selectOne($sql, [$roleId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendPermissionCountAttribute()
    {
        $roleId = $this->attributes['id'] ?? null;
        
        if (!$roleId) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as total FROM role_permission WHERE role_id = ?";
        $result = $this->db->selectOne($sql, [$roleId]);
        return (int)($result['total'] ?? 0);
    }
}