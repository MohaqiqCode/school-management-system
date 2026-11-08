<?php
namespace App\Models;

/**
 * Class Model
 * مدیریت کلاس‌ها
 */
class ClassModel extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'branch_id',
        'class_name',
        'class_level',
        'class_code',
        'description',
        'capacity',
        'is_active',
    ];
    
    protected $casts = [
        'class_level' => 'int',
        'capacity' => 'int',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'branch_name',
        'section_count',
        'student_count',
        'subject_count',
        'occupancy_rate',
    ];
    
    /**
     * Get classes by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM sections WHERE class_id = c.id AND is_active = 1) as section_count,
                       (SELECT COUNT(*) FROM students WHERE current_class_id = c.id AND is_active = 1) as student_count
                FROM classes c 
                WHERE c.branch_id = ? AND c.is_active = 1
                ORDER BY c.class_level";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get sections for class
     */
    public function getSections($classId)
    {
        $sql = "SELECT sec.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as class_teacher_name,
                       (SELECT COUNT(*) FROM students WHERE current_section_id = sec.id AND is_active = 1) as student_count
                FROM sections sec 
                LEFT JOIN users u ON sec.class_teacher_id = u.id
                WHERE sec.class_id = ? AND sec.is_active = 1
                ORDER BY sec.section_name";
        
        return $this->db->select($sql, [$classId]);
    }
    
    /**
     * Get subjects for class
     */
    public function getSubjects($classId)
    {
        $sql = "SELECT s.*, cs.teacher_id, cs.periods_per_week,
                       CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                FROM class_subjects cs 
                JOIN subjects s ON cs.subject_id = s.id 
                LEFT JOIN teachers t ON cs.teacher_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE cs.class_id = ? AND cs.is_active = 1
                ORDER BY s.subject_name";
        
        return $this->db->select($sql, [$classId]);
    }
    
    /**
     * Get students in class
     */
    public function getStudents($classId)
    {
        $sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo, sec.section_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                WHERE s.current_class_id = ? AND s.is_active = 1
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$classId]);
    }
    
    /**
     * Get next class for promotion
     */
    public function getNextClass($currentClassId)
    {
        $current = $this->find($currentClassId);
        
        if (!$current) return null;
        
        $sql = "SELECT * FROM classes 
                WHERE branch_id = ? AND class_level = ? + 1 AND is_active = 1
                LIMIT 1";
        
        $result = $this->db->selectOne($sql, [$current->branch_id, $current->class_level]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    // Accessors
    public function appendBranchNameAttribute()
    {
        return $this->attributes['branch_name'] ?? null;
    }
    
    public function appendSectionCountAttribute()
    {
        return $this->attributes['section_count'] ?? 0;
    }
    
    public function appendStudentCountAttribute()
    {
        return $this->attributes['student_count'] ?? 0;
    }
    
    public function appendSubjectCountAttribute()
    {
        $classId = $this->attributes['id'] ?? null;
        if (!$classId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM class_subjects WHERE class_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$classId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendOccupancyRateAttribute()
    {
        $students = $this->student_count ?? 0;
        $capacity = $this->attributes['capacity'] ?? 1;
        
        return $capacity > 0 ? round(($students / $capacity) * 100, 2) : 0;
    }
}