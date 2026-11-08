<?php
namespace App\Controllers;

use App\Core\Controller;

class AttendanceController extends Controller
{
    public function studentIndex()
    {
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');
        $branchId = $this->session->get('branch_id');
        
        $classes = $this->db->select(
            "SELECT id, class_name FROM classes WHERE branch_id = ? AND is_active = 1 ORDER BY class_level",
            [$branchId]
        );
        
        $attendanceData = [];
        
        if ($classId && $sectionId) {
            $attendanceData = $this->db->select(
                "SELECT sa.*, s.student_code, u.first_name, u.last_name, u.profile_photo
                 FROM student_attendance sa 
                 JOIN students s ON sa.student_id = s.id 
                 JOIN users u ON s.user_id = u.id 
                 WHERE sa.class_id = ? AND sa.section_id = ? AND sa.attendance_date = ?
                 ORDER BY u.last_name, u.first_name",
                [$classId, $sectionId, $date]
            );
        }
        
        $this->view('attendance/student_index', [
            'title' => 'حضور و غیاب دانش‌آموزان',
            'classes' => $classes,
            'attendanceData' => $attendanceData,
            'selectedClass' => $classId,
            'selectedSection' => $sectionId,
            'selectedDate' => $date
        ]);
    }
    
    public function takeStudentAttendance()
    {
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');
        
        if (!$classId || !$sectionId) {
            $this->session->flash('error', 'لطفاً کلاس و بخش را انتخاب کنید');
            $this->redirect('/attendance/students');
            return;
        }
        
        // Get students
        $students = $this->db->select(
            "SELECT s.id, s.student_code, u.first_name, u.last_name, u.profile_photo,
                    COALESCE(sa.status, '') as current_status
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = ?
             WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
             ORDER BY u.last_name, u.first_name",
            [$date, $classId, $sectionId]
        );
        
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$classId]);
        $section = $this->db->selectOne("SELECT * FROM sections WHERE id = ?", [$sectionId]);
        
        $this->view('attendance/take_student', [
            'title' => 'ثبت حضور و غیاب',
            'students' => $students,
            'class' => $class,
            'section' => $section,
            'date' => $date
        ]);
    }
    
    public function storeStudentAttendance()
    {
        $attendanceData = $_POST['attendance'] ?? [];
        $classId = $_POST['class_id'] ?? '';
        $sectionId = $_POST['section_id'] ?? '';
        $date = $_POST['attendance_date'] ?? date('Y-m-d');
        $markedBy = $this->session->get('user_id');
        
        if (empty($attendanceData) || !$classId || !$sectionId) {
            $this->session->flash('error', 'داده‌های حضور و غیاب نامعتبر است');
            $this->redirect('/attendance/students/take?class_id=' . $classId . '&section_id=' . $sectionId . '&date=' . $date);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($attendanceData as $studentId => $status) {
                if (!in_array($status, ['present', 'absent', 'late', 'half_day', 'excused'])) {
                    continue;
                }
                
                // Check if attendance already exists
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
                    'check_in_time' => $status == 'present' ? date('H:i:s') : null,
                    'marked_by' => $markedBy,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    $this->db->update('student_attendance', $data, 'id = ?', [$existing['id']]);
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('student_attendance', $data);
                }
            }
            
            $this->db->commit();
            
            $this->logActivity('attendance', 'create', "حضور و غیاب برای کلاس {$classId} بخش {$sectionId} در تاریخ {$date} ثبت شد");
            
            $this->session->flash('success', 'حضور و غیاب با موفقیت ثبت شد');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در ثبت حضور و غیاب: ' . $e->getMessage());
        }
        
        $this->redirect('/attendance/students?class_id=' . $classId . '&section_id=' . $sectionId . '&date=' . $date);
    }
    
    public function teacherIndex()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $branchId = $this->session->get('branch_id');
        
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name, t.teacher_code,
                    COALESCE(ta.status, '') as attendance_status,
                    ta.check_in_time, ta.check_out_time
             FROM teachers t 
             JOIN users u ON t.user_id = u.id 
             LEFT JOIN teacher_attendance ta ON t.id = ta.teacher_id AND ta.attendance_date = ?
             WHERE u.branch_id = ? AND u.is_active = 1
             ORDER BY u.last_name, u.first_name",
            [$date, $branchId]
        );
        
        $this->view('attendance/teacher_index', [
            'title' => 'حضور و غیاب معلمان',
            'teachers' => $teachers,
            'date' => $date
        ]);
    }
    
    public function storeTeacherAttendance()
    {
        $teacherId = $_POST['teacher_id'] ?? '';
        $status = $_POST['status'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $checkIn = $_POST['check_in_time'] ?? null;
        $checkOut = $_POST['check_out_time'] ?? null;
        $remarks = $_POST['remarks'] ?? '';
        $markedBy = $this->session->get('user_id');
        
        if (!$teacherId || !$status) {
            $this->json(['success' => false, 'message' => 'داده‌های نامعتبر']);
            return;
        }
        
        $existing = $this->db->selectOne(
            "SELECT id FROM teacher_attendance WHERE teacher_id = ? AND attendance_date = ?",
            [$teacherId, $date]
        );
        
        $data = [
            'teacher_id' => $teacherId,
            'attendance_date' => $date,
            'status' => $status,
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'remarks' => $remarks,
            'marked_by' => $markedBy,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->update('teacher_attendance', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('teacher_attendance', $data);
        }
        
        $this->logActivity('attendance', 'update', "حضور و غیاب معلم {$teacherId} ثبت شد");
        
        $this->json(['success' => true, 'message' => 'حضور و غیاب با موفقیت ثبت شد']);
    }
    
    public function monthlyReport()
    {
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        
        if (!$classId || !$sectionId) {
            $this->session->flash('error', 'لطفاً کلاس و بخش را انتخاب کنید');
            $this->redirect('/attendance/students');
            return;
        }
        
        $report = $this->db->select(
            "SELECT s.id, s.student_code, u.first_name, u.last_name,
                    COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN sa.status = 'late' THEN 1 END) as late_days,
                    COUNT(CASE WHEN sa.status = 'excused' THEN 1 END) as excused_days,
                    COUNT(CASE WHEN sa.status = 'half_day' THEN 1 END) as half_days,
                    COUNT(*) as total_days,
                    ROUND((COUNT(CASE WHEN sa.status IN ('present', 'late') THEN 1 END) / COUNT(*)) * 100, 2) as attendance_percentage
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                 AND MONTH(sa.attendance_date) = ? AND YEAR(sa.attendance_date) = ?
             WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
             GROUP BY s.id
             ORDER BY u.last_name, u.first_name",
            [$month, $year, $classId, $sectionId]
        );
        
        $this->view('attendance/monthly_report', [
            'title' => 'گزارش ماهانه حضور و غیاب',
            'report' => $report,
            'month' => $month,
            'year' => $year
        ]);
    }
    
    private function logActivity($module, $action, $description)
    {
        $userId = $this->session->get('user_id');
        
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}