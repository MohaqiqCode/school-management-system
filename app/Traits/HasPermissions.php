<?php
namespace App\Traits;

use App\Core\Application;

/**
 * HasPermissions Trait
 * 
 * تریت مدیریت مجوزهای دسترسی
 * قابل استفاده در Model ها و Controller ها
 * 
 * Usage:
 *   class User extends Model {
 *       use HasPermissions;
 *   }
 * 
 *   $user->hasPermission('view_students');
 *   $user->hasRole('admin');
 *   $user->hasAnyPermission(['view_students', 'edit_students']);
 *   $user->hasAllPermissions(['view_students', 'edit_students']);
 */
trait HasPermissions
{
    /**
     * @var array|null Cached permissions
     */
    protected $cachedPermissions = null;

    /**
     * @var array|null Cached roles
     */
    protected $cachedRoles = null;

    /**
     * Check if user has a specific permission
     * 
     * @param string $permission Permission name
     * @return bool
     */
    public function hasPermission($permission)
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->getAllPermissions();

        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any of the given permissions
     * 
     * @param array $permissions Array of permission names
     * @return bool
     */
    public function hasAnyPermission(array $permissions)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     * 
     * @param array $permissions Array of permission names
     * @return bool
     */
    public function hasAllPermissions(array $permissions)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has a specific role
     * 
     * @param string|array $roles Role name(s)
     * @return bool
     */
    public function hasRole($roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $userRole = $this->getRoleName();

        return in_array($userRole, $roles);
    }

    /**
     * Check if user has any of the given roles
     * 
     * @param array $roles Array of role names
     * @return bool
     */
    public function hasAnyRole(array $roles)
    {
        $userRole = $this->getRoleName();
        return in_array($userRole, $roles);
    }

    /**
     * Check if user is super admin
     * 
     * @return bool
     */
    public function isSuperAdmin()
    {
        $roleName = $this->getRoleName();
        return $roleName === 'super_admin';
    }

    /**
     * Check if user is branch admin
     * 
     * @return bool
     */
    public function isBranchAdmin()
    {
        return $this->hasRole('branch_admin');
    }

    /**
     * Check if user is teacher
     * 
     * @return bool
     */
    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    /**
     * Check if user is student
     * 
     * @return bool
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is parent
     * 
     * @return bool
     */
    public function isParent()
    {
        return $this->hasRole('parent');
    }

    /**
     * Check if user is accountant
     * 
     * @return bool
     */
    public function isAccountant()
    {
        return $this->hasRole('accountant');
    }

    /**
     * Get user's role name
     * 
     * @return string|null
     */
    public function getRoleName()
    {
        // Try to get from attributes
        if (isset($this->attributes['role_name'])) {
            return $this->attributes['role_name'];
        }

        // Try to get from role_id
        $roleId = $this->getRoleId();
        if (!$roleId) return null;

        // Get from cache or database
        $roles = $this->getCachedRoles();
        return $roles[$roleId] ?? null;
    }

    /**
     * Get user's role display name
     * 
     * @return string|null
     */
    public function getRoleDisplayName()
    {
        if (isset($this->attributes['role_display'])) {
            return $this->attributes['role_display'];
        }

        if (isset($this->attributes['role_display_name'])) {
            return $this->attributes['role_display_name'];
        }

        return null;
    }

    /**
     * Get user's role ID
     * 
     * @return int|null
     */
    public function getRoleId()
    {
        if (isset($this->attributes['role_id'])) {
            return $this->attributes['role_id'];
        }

        // Try to get from database
        if (isset($this->attributes['id'])) {
            $db = Application::getInstance()->getDB();
            $result = $db->selectOne(
                "SELECT role_id FROM users WHERE id = ?",
                [$this->attributes['id']]
            );
            return $result['role_id'] ?? null;
        }

        return null;
    }

    /**
     * Get all permissions for this user
     * 
     * @return array Array of permission names
     */
    public function getAllPermissions()
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }

        $roleId = $this->getRoleId();
        if (!$roleId) {
            $this->cachedPermissions = [];
            return [];
        }

        $db = Application::getInstance()->getDB();

        $permissions = $db->select(
            "SELECT p.name 
             FROM permissions p 
             JOIN role_permission rp ON p.id = rp.permission_id 
             WHERE rp.role_id = ?
             ORDER BY p.module, p.name",
            [$roleId]
        );

        $this->cachedPermissions = array_column($permissions, 'name');

        return $this->cachedPermissions;
    }

    /**
     * Get permissions grouped by module
     * 
     * @return array
     */
    public function getPermissionsGrouped()
    {
        $roleId = $this->getRoleId();
        if (!$roleId) return [];

        $db = Application::getInstance()->getDB();

        $permissions = $db->select(
            "SELECT p.name, p.display_name, p.module
             FROM permissions p 
             JOIN role_permission rp ON p.id = rp.permission_id 
             WHERE rp.role_id = ?
             ORDER BY p.module, p.name",
            [$roleId]
        );

        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }

        return $grouped;
    }

    /**
     * Check if user can access a specific module
     * 
     * @param string $module Module name
     * @return bool
     */
    public function canAccessModule($module)
    {
        $permissions = $this->getAllPermissions();
        
        foreach ($permissions as $permission) {
            if (strpos($permission, $module . '_') === 0 || strpos($permission, 'view_' . $module) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can create records
     * 
     * @param string $module Module name
     * @return bool
     */
    public function canCreate($module)
    {
        return $this->hasPermission('create_' . $module);
    }

    /**
     * Check if user can edit records
     * 
     * @param string $module Module name
     * @return bool
     */
    public function canEdit($module)
    {
        return $this->hasPermission('edit_' . $module);
    }

    /**
     * Check if user can delete records
     * 
     * @param string $module Module name
     * @return bool
     */
    public function canDelete($module)
    {
        return $this->hasPermission('delete_' . $module);
    }

    /**
     * Check if user can view records
     * 
     * @param string $module Module name
     * @return bool
     */
    public function canView($module)
    {
        return $this->hasPermission('view_' . $module);
    }

    /**
     * Check if user can export records
     * 
     * @param string $module Module name
     * @return bool
     */
    public function canExport($module)
    {
        return $this->hasPermission('export_' . $module);
    }

    /**
     * Get user's branch ID
     * 
     * @return int|null
     */
    public function getBranchId()
    {
        if (isset($this->attributes['branch_id'])) {
            return $this->attributes['branch_id'];
        }

        if (isset($this->attributes['id'])) {
            $db = Application::getInstance()->getDB();
            $result = $db->selectOne(
                "SELECT branch_id FROM users WHERE id = ?",
                [$this->attributes['id']]
            );
            return $result['branch_id'] ?? null;
        }

        return null;
    }

    /**
     * Check if user belongs to a specific branch
     * 
     * @param int $branchId Branch ID
     * @return bool
     */
    public function belongsToBranch($branchId)
    {
        if ($this->isSuperAdmin()) return true;
        return $this->getBranchId() === (int)$branchId;
    }

    /**
     * Scope query to current user's branch
     * 
     * @param string $query SQL query
     * @param string $tableAlias Table alias (default: main table)
     * @return string Modified query
     */
    public function scopeToBranch($query, $tableAlias = '')
    {
        if ($this->isSuperAdmin()) return $query;

        $branchId = $this->getBranchId();
        if (!$branchId) return $query;

        $column = $tableAlias ? "{$tableAlias}.branch_id" : 'branch_id';
        return $query . " AND {$column} = {$branchId}";
    }

    /**
     * Assign a role to user
     * 
     * @param string|int $role Role name or ID
     * @return bool
     */
    public function assignRole($role)
    {
        $userId = $this->attributes['id'] ?? null;
        if (!$userId) return false;

        $db = Application::getInstance()->getDB();

        if (is_numeric($role)) {
            $roleId = $role;
        } else {
            $roleData = $db->selectOne("SELECT id FROM roles WHERE name = ?", [$role]);
            if (!$roleData) return false;
            $roleId = $roleData['id'];
        }

        $db->update('users', [
            'role_id' => $roleId,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);

        // Clear cache
        $this->cachedPermissions = null;
        $this->cachedRoles = null;
        $this->attributes['role_id'] = $roleId;

        return true;
    }

    /**
     * Give permission to user's role
     * 
     * @param string $permission Permission name
     * @return bool
     */
    public function givePermission($permission)
    {
        $roleId = $this->getRoleId();
        if (!$roleId) return false;

        $db = Application::getInstance()->getDB();

        $permData = $db->selectOne("SELECT id FROM permissions WHERE name = ?", [$permission]);
        if (!$permData) return false;

        // Check if already assigned
        $existing = $db->selectOne(
            "SELECT 1 FROM role_permission WHERE role_id = ? AND permission_id = ?",
            [$roleId, $permData['id']]
        );

        if ($existing) return true;

        $db->insert('role_permission', [
            'role_id' => $roleId,
            'permission_id' => $permData['id']
        ]);

        // Clear cache
        $this->cachedPermissions = null;

        return true;
    }

    /**
     * Revoke permission from user's role
     * 
     * @param string $permission Permission name
     * @return bool
     */
    public function revokePermission($permission)
    {
        $roleId = $this->getRoleId();
        if (!$roleId) return false;

        $db = Application::getInstance()->getDB();

        $permData = $db->selectOne("SELECT id FROM permissions WHERE name = ?", [$permission]);
        if (!$permData) return false;

        $db->delete('role_permission', 'role_id = ? AND permission_id = ?', [
            $roleId, $permData['id']
        ]);

        // Clear cache
        $this->cachedPermissions = null;

        return true;
    }

    /**
     * Sync permissions for user's role
     * 
     * @param array $permissions Array of permission names
     * @return bool
     */
    public function syncPermissions(array $permissions)
    {
        $roleId = $this->getRoleId();
        if (!$roleId) return false;

        $db = Application::getInstance()->getDB();

        $db->beginTransaction();
        try {
            // Remove all existing permissions
            $db->delete('role_permission', 'role_id = ?', [$roleId]);

            // Add new permissions
            foreach ($permissions as $permission) {
                $permData = $db->selectOne("SELECT id FROM permissions WHERE name = ?", [$permission]);
                if ($permData) {
                    $db->insert('role_permission', [
                        'role_id' => $roleId,
                        'permission_id' => $permData['id']
                    ]);
                }
            }

            $db->commit();
            $this->cachedPermissions = null;
            return true;

        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Get cached roles
     * 
     * @return array
     */
    private function getCachedRoles()
    {
        if ($this->cachedRoles !== null) {
            return $this->cachedRoles;
        }

        $db = Application::getInstance()->getDB();
        $roles = $db->select("SELECT id, name FROM roles");

        $this->cachedRoles = [];
        foreach ($roles as $role) {
            $this->cachedRoles[$role['id']] = $role['name'];
        }

        return $this->cachedRoles;
    }

    /**
     * Clear permission cache
     */
    public function clearPermissionCache()
    {
        $this->cachedPermissions = null;
        $this->cachedRoles = null;
    }
}