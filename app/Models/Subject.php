<?php
namespace App\Models;

/**
 * Subject Model
 * مدیریت دروس
 */
class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'department_id',
        'subject_code',
        'subject_name',
        'description',
        'credit_hours',
        'is_core',
        'is_active',
    ];
    
    protected $casts = [
        'credit_hours' => 'float',
        'is_core' => 'bool',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'department_name',
        'class_count',
    ];
    
    /**
     * Get subjects by department
     */
    public function getByDepartment($departmentId)
    {
        $sql = "SELECT s.*, d.name as department_name,
                       (SELECT COUNT(*) FROM class_subjects WHERE subject_id = s.id AND is_active = 1) as class_count
                FROM subjects s 
                JOIN departments d ON s.department_id = d.id 
                WHERE s.department_id = ?
                ORDER BY s.subject_name";
        
        return $this->hydrate($this->db->select($sql, [$departmentId]));
    }
    
    /**
     * Get subjects for class
     */
    public function getByClass($classId)
    {
        $sql = "SELECT s.* FROM subjects s 
                JOIN class_subjects cs ON s.id = cs.subject_id 
                WHERE cs.class_id = ? AND cs.is_active = 1 AND s.is_active = 1
                ORDER BY s.subject_name";
        
        return $this->hydrate($this->db->select($sql, [$classId]));
    }
    
    /**
     * Get subject by code
     */
    public function findByCode($code)
    {
        $sql = "SELECT * FROM subjects WHERE subject_code = ?";
        $result = $this->db->selectOne($sql, [$code]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get core subjects
     */
    public function getCoreSubjects()
    {
        $sql = "SELECT s.*, d.name as department_name
                FROM subjects s 
                JOIN departments d ON s.department_id = d.id 
                WHERE s.is_core = 1 AND s.is_active = 1
                ORDER BY s.subject_name";
        
        return $this->hydrate($this->db->select($sql));
    }
    
    // Accessors
    public function appendDepartmentNameAttribute()
    {
        return $this->attributes['department_name'] ?? null;
    }
    
    public function appendClassCountAttribute()
    {
        return $this->attributes['class_count'] ?? 0;
    }
}