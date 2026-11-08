<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class AttendanceController extends Controller
{
    /**
     * Get student attendance for a date
     * GET /api/v1/attendance/students
     */
    public function studentIndex()
    {
        $classId = $this->request->get('class_id');
        $sectionId = $this->request->get('section_id');
        $date = $this->request->get('date', date('Y-m-d'));
        
        if (!$classId || !$sectionId) {
            $this->json(['success' => false, 'message' => 'کلاس و بخش الزامی است'], 422);
            return;
        }
        
        $attendance = $this->db->select(
            "SELECT sa.*, s.student_code, u.first_name, u.last_name, u.profile_photo
             FROM student_attendance sa 
             JOIN students s ON sa.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             WHERE sa.class_id = ? AND sa.section_id = ? AND sa.attendance_date = ?
             ORDER BY u.last_name, u.first_name",
            [$classId, $sectionId, $date]
        );
        
        $summary = $this->db->selectOne(
            "SELECT COUNT(*) as total,
                    COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN status = 'late' THEN 1 END) as late,
                    COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused
             FROM student_attendance 
             WHERE class_id = ? AND section_id = ? AND attendance_date = ?",
            [$classId, $sectionId, $date]
        );
        
        $this->json([
            'success' => true,
            'data' => [
                'attendance' => $attendance,
                'summary' => $summary,
                'date' => $date
            ]
        ]);
    }
    
    /**
     * Store student attendance
     * POST /api/v1/attendance/students
     */
    public function storeStudent()
    {
        $data = $this->request->getJson();
        
        $studentId = $data['student_id'] ?? '';
        $classId = $data['class_id'] ?? '';
        $sectionId = $data['section_id'] ?? '';
        $date = $data['date'] ?? date('Y-m-d');
        $status = $data['status'] ?? 'present';
        $remarks = $data['remarks'] ?? '';
        
        if (!$studentId || !$classId || !$sectionId) {
            $this->json(['success' => false, 'message' => 'اطلاعات ناقص است'], 422);
            return;
        }
        
        if (!in_array($status, ['present', 'absent', 'late', 'half_day', 'excused'])) {
            $this->json(['success' => false, 'message' => 'وضعیت نامعتبر است'], 422);
            return;
        }
        
        $existing = $this->db->selectOne(
            "SELECT id FROM student_attendance WHERE student_id = ? AND attendance_date = ?",
            [$studentId, $date]
        );
        
        $record = [
            'student_id' => $studentId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'attendance_date' => $date,
            'status' => $status,
            'check_in_time' => $status === 'present' ? date('H:i:s') : null,
            'remarks' => $remarks,
            'marked_by' => $this->session->get('user_id')
        ];
        
        if ($existing) {
            $record['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update('student_attendance', $record, 'id = ?', [$existing['id']]);
        } else {
            $record['created_at'] = date('Y-m-d H:i:s');
            $record['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('student_attendance', $record);
        }
        
        $this->json(['success' => true, 'message' => 'حضور و غیاب ثبت شد']);
    }
    
    /**
     * Bulk store student attendance
     * POST /api/v1/attendance/students/bulk
     */
    public function bulkStoreStudent()
    {
        $data = $this->request->getJson();
        
        $classId = $data['class_id'] ?? '';
        $sectionId = $data['section_id'] ?? '';
        $date = $data['date'] ?? date('Y-m-d');
        $attendanceData = $data['attendance'] ?? [];
        
        if (!$classId || !$sectionId || empty($attendanceData)) {
            $this->json(['success' => false, 'message' => 'اطلاعات ناقص است'], 422);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($attendanceData as $studentId => $status) {
                if (!in_array($status, ['present', 'absent', 'late', 'half_day', 'excused'])) continue;
                
                $existing = $this->db->selectOne(
                    "SELECT id FROM student_attendance WHERE student_id = ? AND attendance_date = ?",
                    [$studentId, $date]
                );
                
                $record = [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'attendance_date' => $date,
                    'status' => $status,
                    'marked_by' => $this->session->get('user_id')
                ];
                
                if ($existing) {
                    $record['updated_at'] = date('Y-m-d H:i:s');
                    $this->db->update('student_attendance', $record, 'id = ?', [$existing['id']]);
                } else {
                    $record['created_at'] = date('Y-m-d H:i:s');
                    $record['updated_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('student_attendance', $record);
                }
            }
            
            $this->db->commit();
            $this->json(['success' => true, 'message' => 'حضور و غیاب گروهی ثبت شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get student attendance report
     * GET /api/v1/attendance/students/report
     */
    public function studentReport()
    {
        $classId = $this->request->get('class_id');
        $sectionId = $this->request->get('section_id');
        $month = $this->request->get('month', date('m'));
        $year = $this->request->get('year', date('Y'));
        
        if (!$classId || !$sectionId) {
            $this->json(['success' => false, 'message' => 'کلاس و بخش الزامی است'], 422);
            return;
        }
        
        $report = $this->db->select(
            "SELECT s.id, s.student_code, u.first_name, u.last_name,
                    COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN sa.status = 'late' THEN 1 END) as late_days,
                    COUNT(CASE WHEN sa.status = 'excused' THEN 1 END) as excused_days,
                    COUNT(*) as total_days,
                    ROUND((COUNT(CASE WHEN sa.status IN ('present','late') THEN 1 END) / COUNT(*)) * 100, 2) as attendance_percentage
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                 AND MONTH(sa.attendance_date) = ? AND YEAR(sa.attendance_date) = ?
             WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
             GROUP BY s.id
             ORDER BY u.last_name, u.first_name",
            [$month, $year, $classId, $sectionId]
        );
        
        $this->json(['success' => true, 'data' => $report]);
    }
    
    /**
     * Get teacher attendance
     * GET /api/v1/attendance/teachers
     */
    public function teacherIndex()
    {
        $date = $this->request->get('date', date('Y-m-d'));
        $branchId = $this->session->get('branch_id');
        
        $teachers = $this->db->select(
            "SELECT t.id, t.teacher_code, u.first_name, u.last_name,
                    COALESCE(ta.status, '') as attendance_status,
                    ta.check_in_time, ta.check_out_time, ta.remarks
             FROM teachers t 
             JOIN users u ON t.user_id = u.id 
             LEFT JOIN teacher_attendance ta ON t.id = ta.teacher_id AND ta.attendance_date = ?
             WHERE u.branch_id = ? AND u.is_active = 1
             ORDER BY u.last_name, u.first_name",
            [$date, $branchId]
        );
        
        $this->json(['success' => true, 'data' => $teachers]);
    }
    
    /**
     * Store teacher attendance
     * POST /api/v1/attendance/teachers
     */
    public function storeTeacher()
    {
        $data = $this->request->getJson();
        
        $teacherId = $data['teacher_id'] ?? '';
        $date = $data['date'] ?? date('Y-m-d');
        $status = $data['status'] ?? 'present';
        $checkIn = $data['check_in_time'] ?? null;
        $checkOut = $data['check_out_time'] ?? null;
        $remarks = $data['remarks'] ?? '';
        
        if (!$teacherId) {
            $this->json(['success' => false, 'message' => 'شناسه معلم الزامی است'], 422);
            return;
        }
        
        $existing = $this->db->selectOne(
            "SELECT id FROM teacher_attendance WHERE teacher_id = ? AND attendance_date = ?",
            [$teacherId, $date]
        );
        
        $record = [
            'teacher_id' => $teacherId,
            'attendance_date' => $date,
            'status' => $status,
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'remarks' => $remarks,
            'marked_by' => $this->session->get('user_id')
        ];
        
        if ($existing) {
            $this->db->update('teacher_attendance', $record, 'id = ?', [$existing['id']]);
        } else {
            $record['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('teacher_attendance', $record);
        }
        
        $this->json(['success' => true, 'message' => 'حضور معلم ثبت شد']);
    }
    
    /**
     * Bulk store teacher attendance
     * POST /api/v1/attendance/teachers/bulk
     */
    public function bulkStoreTeacher()
    {
        $data = $this->request->getJson();
        $date = $data['date'] ?? date('Y-m-d');
        $attendanceData = $data['attendance'] ?? [];
        
        if (empty($attendanceData)) {
            $this->json(['success' => false, 'message' => 'داده‌ای برای ثبت وجود ندارد'], 422);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($attendanceData as $teacherId => $status) {
                $existing = $this->db->selectOne(
                    "SELECT id FROM teacher_attendance WHERE teacher_id = ? AND attendance_date = ?",
                    [$teacherId, $date]
                );
                
                $record = [
                    'teacher_id' => $teacherId,
                    'attendance_date' => $date,
                    'status' => $status,
                    'marked_by' => $this->session->get('user_id')
                ];
                
                if ($existing) {
                    $this->db->update('teacher_attendance', $record, 'id = ?', [$existing['id']]);
                } else {
                    $record['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('teacher_attendance', $record);
                }
            }
            
            $this->db->commit();
            $this->json(['success' => true, 'message' => 'حضور معلمان ثبت شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()], 500);
        }
    }
}