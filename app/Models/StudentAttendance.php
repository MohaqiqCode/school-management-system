<?php
namespace App\Models;

/**
 * Student Attendance Model
 * مدیریت حضور و غیاب دانش‌آموزان
 */
class StudentAttendance extends Model
{
    protected $table = 'student_attendance';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'attendance_date',
        'status',
        'check_in_time',
        'check_out_time',
        'remarks',
        'marked_by',
    ];
    
    protected $casts = [
        'attendance_date' => 'date',
    ];
    
    protected $appends = [
        'student_name',
        'status_text',
    ];
    
    /**
     * Get daily attendance for a section
     */
    public function getDailyAttendance($classId, $sectionId, $date)
    {
        $sql = "SELECT sa.*, s.student_code, u.first_name, u.last_name, u.profile_photo
                FROM student_attendance sa 
                JOIN students s ON sa.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                WHERE sa.class_id = ? AND sa.section_id = ? AND sa.attendance_date = ?
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$classId, $sectionId, $date]);
    }
    
    /**
     * Get students without attendance for a date
     */
    public function getStudentsWithoutAttendance($sectionId, $date)
    {
        $sql = "SELECT s.id, s.student_code, u.first_name, u.last_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.current_section_id = ? AND s.is_active = 1
                AND s.id NOT IN (
                    SELECT student_id FROM student_attendance WHERE attendance_date = ?
                )
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$sectionId, $date]);
    }
    
    /**
     * Bulk mark attendance
     */
    public function bulkMark($classId, $sectionId, $date, array $attendanceData, $markedBy)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($attendanceData as $studentId => $status) {
                if (!in_array($status, ['present', 'absent', 'late', 'half_day', 'excused'])) {
                    continue;
                }
                
                $existing = $this->db->selectOne(
                    "SELECT id FROM student_attendance WHERE student_id = ? AND attendance_date = ?",
                    [$studentId, $date]
                );
                
                $data = [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'attendance_date' => $date,
                    'status' => $status,
                    'check_in_time' => $status === 'present' ? date('H:i:s') : null,
                    'marked_by' => $markedBy,
                ];
                
                if ($existing) {
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $this->db->update('student_attendance', $data, 'id = ?', [$existing['id']]);
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('student_attendance', $data);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get monthly report
     */
    public function getMonthlyReport($classId, $sectionId, $month, $year)
    {
        $sql = "SELECT s.id, s.student_code, u.first_name, u.last_name,
                       COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present_days,
                       COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent_days,
                       COUNT(CASE WHEN sa.status = 'late' THEN 1 END) as late_days,
                       COUNT(CASE WHEN sa.status = 'excused' THEN 1 END) as excused_days,
                       COUNT(*) as total_days,
                       ROUND((COUNT(CASE WHEN sa.status IN ('present', 'late') THEN 1 END) / COUNT(*)) * 100, 2) as attendance_percentage
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                    AND MONTH(sa.attendance_date) = ? AND YEAR(sa.attendance_date) = ?
                WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
                GROUP BY s.id
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$month, $year, $classId, $sectionId]);
    }
    
    /**
     * Get student attendance statistics
     */
    public function getStudentStats($studentId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? date('Y-m-01');
        $endDate = $endDate ?? date('Y-m-t');
        
        $sql = "SELECT COUNT(*) as total,
                       COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) as present,
                       COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                       COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused,
                       COUNT(CASE WHEN status = 'late' THEN 1 END) as late,
                       ROUND((COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) / COUNT(*)) * 100, 2) as percentage
                FROM student_attendance 
                WHERE student_id = ? AND attendance_date BETWEEN ? AND ?";
        
        return $this->db->selectOne($sql, [$studentId, $startDate, $endDate]);
    }
    
    /**
     * Get class attendance summary for a date
     */
    public function getClassSummary($classId, $date)
    {
        $sql = "SELECT COUNT(*) as total,
                       COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
                       COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                       COUNT(CASE WHEN status = 'late' THEN 1 END) as late,
                       COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused
                FROM student_attendance 
                WHERE class_id = ? AND attendance_date = ?";
        
        return $this->db->selectOne($sql, [$classId, $date]);
    }
    
    // Accessors
    public function appendStudentNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendStatusTextAttribute()
    {
        $statuses = [
            'present' => 'حاضر',
            'absent' => 'غایب',
            'late' => 'تأخیر',
            'half_day' => 'نیمه وقت',
            'excused' => 'غیبت موجه',
            'holiday' => 'تعطیل',
        ];
        
        $status = $this->attributes['status'] ?? '';
        return $statuses[$status] ?? $status;
    }
}