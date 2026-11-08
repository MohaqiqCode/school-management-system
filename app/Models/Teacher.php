<?php
namespace App\Models;

/**
 * Teacher Model
 * مدیریت معلمان
 */
class Teacher extends Model
{
    protected $table = 'teachers';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',
        'teacher_code',
        'employee_id',
        'department_id',
        'qualification',
        'specialization',
        'teaching_experience_years',
        'joining_date',
        'contract_type',
        'salary_grade',
        'bank_name',
        'bank_account_number',
        'tax_id',
    ];
    
    protected $casts = [
        'teaching_experience_years' => 'float',
        'joining_date' => 'date',
    ];
    
    protected $appends = [
        'full_name',
        'department_name',
        'class_count',
        'subject_count',
    ];
    
    /**
     * Get teacher with full details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.gender, u.date_of_birth, u.address,
                       d.name as department_name,
                       b.name as branch_name
                FROM teachers t 
                JOIN users u ON t.user_id = u.id 
                LEFT JOIN departments d ON t.department_id = d.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE t.id = ? AND u.deleted_at IS NULL";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get teacher by user ID
     */
    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM teachers WHERE user_id = ?";
        $result = $this->db->selectOne($sql, [$userId]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get assigned classes
     */
    public function getAssignedClasses($teacherId)
    {
        $sql = "SELECT cs.*, c.class_name, c.class_level, s.subject_name, s.subject_code,
                       ay.year_name
                FROM class_subjects cs 
                JOIN classes c ON cs.class_id = c.id 
                JOIN subjects s ON cs.subject_id = s.id 
                JOIN academic_years ay ON cs.academic_year_id = ay.id 
                WHERE cs.teacher_id = ? AND cs.is_active = 1
                ORDER BY c.class_level, s.subject_name";
        
        return $this->db->select($sql, [$teacherId]);
    }
    
    /**
     * Get teacher timetable
     */
    public function getTimetable($teacherId)
    {
        $sql = "SELECT tt.*, sec.section_name, s.subject_name, c.class_name,
                       sem.semester_name
                FROM timetables tt 
                JOIN sections sec ON tt.section_id = sec.id 
                JOIN subjects s ON tt.subject_id = s.id 
                JOIN classes c ON sec.class_id = c.id 
                JOIN semesters sem ON tt.semester_id = sem.id
                WHERE tt.teacher_id = ?
                ORDER BY FIELD(tt.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), 
                         tt.start_time";
        
        return $this->db->select($sql, [$teacherId]);
    }
    
    /**
     * Get teacher workload
     */
    public function getWorkload($teacherId)
    {
        $weeklyPeriods = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM timetables WHERE teacher_id = ?",
            [$teacherId]
        )['count'] ?? 0;
        
        $classCount = $this->db->selectOne(
            "SELECT COUNT(DISTINCT class_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1",
            [$teacherId]
        )['count'] ?? 0;
        
        $subjectCount = $this->db->selectOne(
            "SELECT COUNT(DISTINCT subject_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1",
            [$teacherId]
        )['count'] ?? 0;
        
        $studentCount = $this->db->selectOne(
            "SELECT COUNT(DISTINCT s.id) as count 
             FROM class_subjects cs 
             JOIN sections sec ON cs.class_id = sec.class_id
             JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1
             WHERE cs.teacher_id = ? AND cs.is_active = 1",
            [$teacherId]
        )['count'] ?? 0;
        
        return [
            'weekly_periods' => (int)$weeklyPeriods,
            'class_count' => (int)$classCount,
            'subject_count' => (int)$subjectCount,
            'student_count' => (int)$studentCount,
            'utilization_percentage' => min(round(($weeklyPeriods / 40) * 100, 2), 100),
        ];
    }
    
    /**
     * Get attendance summary
     */
    public function getAttendanceSummary($teacherId, $month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $sql = "SELECT COUNT(*) as total,
                       COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
                       COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                       COUNT(CASE WHEN status = 'on_leave' THEN 1 END) as on_leave
                FROM teacher_attendance 
                WHERE teacher_id = ? 
                AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?";
        
        return $this->db->selectOne($sql, [$teacherId, $month, $year]);
    }
    
    // Accessors
    public function getFullNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendDepartmentNameAttribute()
    {
        return $this->attributes['department_name'] ?? null;
    }
    
    public function appendClassCountAttribute()
    {
        $teacherId = $this->attributes['id'] ?? null;
        if (!$teacherId) return 0;
        
        $sql = "SELECT COUNT(DISTINCT class_id) as total FROM class_subjects WHERE teacher_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$teacherId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendSubjectCountAttribute()
    {
        $teacherId = $this->attributes['id'] ?? null;
        if (!$teacherId) return 0;
        
        $sql = "SELECT COUNT(DISTINCT subject_id) as total FROM class_subjects WHERE teacher_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$teacherId]);
        return (int)($result['total'] ?? 0);
    }
}