<?php
namespace App\Models;

/**
 * Department Model
 * مدیریت دپارتمان‌ها
 */
class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'branch_id',
        'department_code',
        'name',
        'description',
        'head_teacher_id',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'branch_name',
        'head_teacher_name',
        'teacher_count',
        'subject_count',
    ];
    
    /**
     * Get department with details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT d.*, b.name as branch_name,
                       CONCAT(u.first_name, ' ', u.last_name) as head_teacher_name,
                       (SELECT COUNT(*) FROM teachers WHERE department_id = d.id) as teacher_count,
                       (SELECT COUNT(*) FROM subjects WHERE department_id = d.id AND is_active = 1) as subject_count
                FROM departments d 
                JOIN branches b ON d.branch_id = b.id 
                LEFT JOIN teachers t ON d.head_teacher_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE d.id = ?";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get departments by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT d.*, b.name as branch_name,
                       (SELECT COUNT(*) FROM teachers WHERE department_id = d.id) as teacher_count
                FROM departments d 
                JOIN branches b ON d.branch_id = b.id 
                WHERE d.branch_id = ? AND d.is_active = 1
                ORDER BY d.name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get teachers in department
     */
    public function getTeachers($departmentId)
    {
        $sql = "SELECT t.*, u.first_name, u.last_name, u.email
                FROM teachers t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.department_id = ? AND u.is_active = 1
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$departmentId]);
    }
    
    /**
     * Get subjects in department
     */
    public function getSubjects($departmentId)
    {
        $sql = "SELECT s.* FROM subjects s 
                WHERE s.department_id = ? AND s.is_active = 1
                ORDER BY s.subject_name";
        
        return $this->db->select($sql, [$departmentId]);
    }
    
    /**
     * Assign head teacher
     */
    public function assignHead($departmentId, $teacherId)
    {
        return $this->db->update('departments', [
            'head_teacher_id' => $teacherId,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$departmentId]);
    }
    
    // Accessors
    public function appendBranchNameAttribute()
    {
        return $this->attributes['branch_name'] ?? null;
    }
    
    public function appendHeadTeacherNameAttribute()
    {
        return $this->attributes['head_teacher_name'] ?? 'تعیین نشده';
    }
    
    public function appendTeacherCountAttribute()
    {
        $deptId = $this->attributes['id'] ?? null;
        if (!$deptId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM teachers WHERE department_id = ?";
        $result = $this->db->selectOne($sql, [$deptId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendSubjectCountAttribute()
    {
        $deptId = $this->attributes['id'] ?? null;
        if (!$deptId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM subjects WHERE department_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$deptId]);
        return (int)($result['total'] ?? 0);
    }
}