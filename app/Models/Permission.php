<?php
namespace App\Models;

/**
 * Permission Model
 * مدیریت مجوزهای دسترسی
 */
class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'display_name',
        'module',
        'description',
    ];
    
    protected $appends = [
        'role_count',
        'module_label',
    ];
    
    /**
     * Get all permissions grouped by module
     */
    public function getAllGrouped()
    {
        $sql = "SELECT * FROM permissions ORDER BY module, name";
        $permissions = $this->db->select($sql);
        
        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }
        
        return $grouped;
    }
    
    /**
     * Get permissions by module
     */
    public function getByModule($module)
    {
        $sql = "SELECT * FROM permissions WHERE module = ? ORDER BY name";
        return $this->hydrate($this->db->select($sql, [$module]));
    }
    
    /**
     * Get all modules
     */
    public function getModules()
    {
        $sql = "SELECT DISTINCT module FROM permissions ORDER BY module";
        return $this->db->selectColumn($sql);
    }
    
    /**
     * Get permissions with role assignment status
     */
    public function getWithRoleStatus($roleId)
    {
        $sql = "SELECT p.*, 
                       CASE WHEN rp.role_id IS NOT NULL THEN 1 ELSE 0 END as assigned
                FROM permissions p 
                LEFT JOIN role_permission rp ON p.id = rp.permission_id AND rp.role_id = ?
                ORDER BY p.module, p.name";
        
        return $this->db->select($sql, [$roleId]);
    }
    
    /**
     * Get permission by name
     */
    public function findByName($name)
    {
        $sql = "SELECT * FROM permissions WHERE name = ?";
        $result = $this->db->selectOne($sql, [$name]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    // Accessors
    public function appendRoleCountAttribute()
    {
        $permId = $this->attributes['id'] ?? null;
        if (!$permId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM role_permission WHERE permission_id = ?";
        $result = $this->db->selectOne($sql, [$permId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendModuleLabelAttribute()
    {
        $modules = [
            'dashboard' => 'داشبورد',
            'branches' => 'شعب',
            'users' => 'کاربران',
            'roles' => 'نقش‌ها',
            'departments' => 'دپارتمان‌ها',
            'academic' => 'امور آموزشی',
            'classes' => 'کلاس‌ها',
            'subjects' => 'دروس',
            'timetable' => 'برنامه هفتگی',
            'students' => 'دانش‌آموزان',
            'parents' => 'والدین',
            'teachers' => 'معلمان',
            'employees' => 'کارمندان',
            'attendance' => 'حضور و غیاب',
            'examinations' => 'آزمون‌ها',
            'finance' => 'امور مالی',
            'payroll' => 'حقوق و دستمزد',
            'library' => 'کتابخانه',
            'transport' => 'حمل و نقل',
            'inventory' => 'انبار',
            'notifications' => 'اعلان‌ها',
            'reports' => 'گزارش‌ها',
            'audit' => 'ممیزی',
            'settings' => 'تنظیمات',
        ];
        
        $module = $this->attributes['module'] ?? '';
        return $modules[$module] ?? $module;
    }
}