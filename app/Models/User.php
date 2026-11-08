<?php
namespace App\Models;

use App\Helpers\Helper;

/**
 * User Model
 * مدیریت کاربران سیستم
 */
class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_code',
        'branch_id',
        'role_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'alternate_phone',
        'gender',
        'date_of_birth',
        'profile_photo',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'settings',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'is_active' => 'bool',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'settings' => 'json',
    ];
    
    protected $softDelete = true;
    
    protected $appends = [
        'full_name',
        'role_name',
        'branch_name',
    ];
    
    /**
     * Get user with role and branch details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT u.*, r.name as role_name, r.display_name as role_display,
                       b.name as branch_name, b.branch_code
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE u.id = ? AND u.deleted_at IS NULL";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE email = ? AND deleted_at IS NULL";
        $result = $this->db->selectOne($sql, [$email]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Find user by code
     */
    public function findByCode($code)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE user_code = ? AND deleted_at IS NULL";
        $result = $this->db->selectOne($sql, [$code]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get users by role
     */
    public function getByRole($roleId)
    {
        $sql = "SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.role_id = ? AND u.is_active = 1 AND u.deleted_at IS NULL
                ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, [$roleId]));
    }
    
    /**
     * Get users by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.branch_id = ? AND u.is_active = 1 AND u.deleted_at IS NULL
                ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Search users
     */
    public function search($query, $limit = 20)
    {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.deleted_at IS NULL 
                AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.user_code LIKE ?)
                ORDER BY u.last_name, u.first_name
                LIMIT ?";
        
        return $this->hydrate(
            $this->db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit])
        );
    }
    
    /**
     * Get active users count
     */
    public function getActiveCount()
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}` WHERE is_active = 1 AND deleted_at IS NULL";
        $result = $this->db->selectOne($sql);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }
        
        $newStatus = $user->is_active ? 0 : 1;
        
        $this->db->update($this->table, [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], "{$this->primaryKey} = ?", [$id]);
        
        return $newStatus;
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $password)
    {
        return $this->db->update($this->table, [
            'password' => Helper::encryptPassword($password),
            'updated_at' => date('Y-m-d H:i:s')
        ], "{$this->primaryKey} = ?", [$id]);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Get permissions for user
     */
    public function getPermissions()
    {
        $roleId = $this->attributes['role_id'] ?? null;
        
        if (!$roleId) {
            return [];
        }
        
        $sql = "SELECT p.name, p.display_name, p.module 
                FROM permissions p 
                JOIN role_permission rp ON p.id = rp.permission_id 
                WHERE rp.role_id = ?";
        
        return $this->db->select($sql, [$roleId]);
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($permission)
    {
        $roleId = $this->attributes['role_id'] ?? null;
        $roleName = $this->attributes['role_name'] ?? null;
        
        if (!$roleId) {
            return false;
        }
        
        // Super admin has all permissions
        if ($roleName === 'super_admin') {
            return true;
        }
        
        $sql = "SELECT COUNT(*) as count 
                FROM role_permission rp 
                JOIN permissions p ON rp.permission_id = p.id 
                WHERE rp.role_id = ? AND p.name = ?";
        
        $result = $this->db->selectOne($sql, [$roleId, $permission]);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($id)
    {
        return $this->db->update($this->table, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ], "{$this->primaryKey} = ?", [$id]);
    }
    
    // Accessors
    public function getFullNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendRoleNameAttribute()
    {
        return $this->attributes['role_name'] ?? null;
    }
    
    public function appendBranchNameAttribute()
    {
        return $this->attributes['branch_name'] ?? null;
    }
    
    public function getAvatarAttribute()
    {
        $photo = $this->attributes['profile_photo'] ?? null;
        return $photo ?: '/assets/images/default-avatar.png';
    }
}