<?php
namespace App\Models;

/**
 * Teacher Attendance Model
 * مدیریت حضور و غیاب معلمان
 */
class TeacherAttendance extends Model
{
    protected $table = 'teacher_attendance';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'teacher_id',
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
        'teacher_name',
        'status_text',
    ];
    
    /**
     * Get daily attendance for teachers
     */
    public function getDailyAttendance($branchId, $date)
    {
        $sql = "SELECT ta.*, t.teacher_code, u.first_name, u.last_name
                FROM teacher_attendance ta 
                JOIN teachers t ON ta.teacher_id = t.id 
                JOIN users u ON t.user_id = u.id 
                WHERE ta.attendance_date = ? AND u.branch_id = ?
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$date, $branchId]);
    }
    
    /**
     * Get teachers without attendance for a date
     */
    public function getTeachersWithoutAttendance($branchId, $date)
    {
        $sql = "SELECT t.id, t.teacher_code, u.first_name, u.last_name
                FROM teachers t 
                JOIN users u ON t.user_id = u.id 
                WHERE u.branch_id = ? AND u.is_active = 1
                AND t.id NOT IN (
                    SELECT teacher_id FROM teacher_attendance WHERE attendance_date = ?
                )
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$branchId, $date]);
    }
    
    /**
     * Bulk mark teacher attendance
     */
    public function bulkMark($date, array $attendanceData, $markedBy)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($attendanceData as $teacherId => $data) {
                $existing = $this->db->selectOne(
                    "SELECT id FROM teacher_attendance WHERE teacher_id = ? AND attendance_date = ?",
                    [$teacherId, $date]
                );
                
                $record = [
                    'teacher_id' => $teacherId,
                    'attendance_date' => $date,
                    'status' => $data['status'] ?? 'present',
                    'check_in_time' => $data['check_in_time'] ?? null,
                    'check_out_time' => $data['check_out_time'] ?? null,
                    'remarks' => $data['remarks'] ?? '',
                    'marked_by' => $markedBy,
                ];
                
                if ($existing) {
                    $this->db->update('teacher_attendance', $record, 'id = ?', [$existing['id']]);
                } else {
                    $record['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('teacher_attendance', $record);
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
    public function getMonthlyReport($teacherId, $month, $year)
    {
        $sql = "SELECT COUNT(*) as total_days,
                       COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
                       COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
                       COUNT(CASE WHEN status = 'on_leave' THEN 1 END) as leave_days,
                       COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days,
                       ROUND((COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) / COUNT(*)) * 100, 2) as attendance_percentage
                FROM teacher_attendance 
                WHERE teacher_id = ? 
                AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?";
        
        return $this->db->selectOne($sql, [$teacherId, $month, $year]);
    }
    
    // Accessors
    public function appendTeacherNameAttribute()
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
            'on_leave' => 'مرخصی',
        ];
        
        $status = $this->attributes['status'] ?? '';
        return $statuses[$status] ?? $status;
    }
}