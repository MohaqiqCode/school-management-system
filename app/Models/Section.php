<?php
namespace App\Models;

/**
 * Section Model
 * مدیریت بخش‌های کلاس
 */
class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'class_id',
        'section_name',
        'section_code',
        'capacity',
        'class_teacher_id',
        'room_number',
        'is_active',
    ];
    
    protected $casts = [
        'capacity' => 'int',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'class_name',
        'class_teacher_name',
        'student_count',
        'occupancy_rate',
    ];
    
    /**
     * Get sections by class
     */
    public function getByClass($classId)
    {
        $sql = "SELECT sec.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as class_teacher_name,
                       (SELECT COUNT(*) FROM students WHERE current_section_id = sec.id AND is_active = 1) as student_count
                FROM sections sec 
                LEFT JOIN teachers t ON sec.class_teacher_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE sec.class_id = ? AND sec.is_active = 1
                ORDER BY sec.section_name";
        
        return $this->hydrate($this->db->select($sql, [$classId]));
    }
    
    /**
     * Get students in section
     */
    public function getStudents($sectionId)
    {
        $sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.current_section_id = ? AND s.is_active = 1
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$sectionId]);
    }
    
    /**
     * Get timetable for section
     */
    public function getTimetable($sectionId)
    {
        $sql = "SELECT tt.*, s.subject_name, 
                       CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                FROM timetables tt 
                JOIN subjects s ON tt.subject_id = s.id 
                JOIN teachers t ON tt.teacher_id = t.id
                JOIN users u ON t.user_id = u.id
                WHERE tt.section_id = ?
                ORDER BY FIELD(tt.day_of_week, 'Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'), 
                         tt.start_time";
        
        return $this->db->select($sql, [$sectionId]);
    }
    
    /**
     * Assign class teacher
     */
    public function assignTeacher($sectionId, $teacherId)
    {
        return $this->db->update('sections', [
            'class_teacher_id' => $teacherId,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$sectionId]);
    }
    
    // Accessors
    public function appendClassNameAttribute()
    {
        return $this->attributes['class_name'] ?? null;
    }
    
    public function appendClassTeacherNameAttribute()
    {
        return $this->attributes['class_teacher_name'] ?? 'تعیین نشده';
    }
    
    public function appendStudentCountAttribute()
    {
        return $this->attributes['student_count'] ?? 0;
    }
    
    public function appendOccupancyRateAttribute()
    {
        $students = $this->student_count ?? 0;
        $capacity = $this->attributes['capacity'] ?? 1;
        
        return $capacity > 0 ? round(($students / $capacity) * 100, 2) : 0;
    }
}