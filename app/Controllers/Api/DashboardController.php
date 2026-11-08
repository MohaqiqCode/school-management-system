<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * GET /api/v1/dashboard/stats
     */
    public function stats()
    {
        $user = $this->getCurrentUser();
        $branchId = $this->session->get('branch_id');
        $role = $user['role_name'] ?? '';

        $stats = [];

        switch ($role) {
            case 'super_admin':
                $stats = $this->getSuperAdminStats();
                break;
            case 'branch_admin':
            case 'principal':
                $stats = $this->getBranchStats($branchId);
                break;
            case 'teacher':
                $stats = $this->getTeacherStats($user['id']);
                break;
            case 'student':
                $stats = $this->getStudentStats($user['id']);
                break;
            default:
                $stats = $this->getGeneralStats($branchId);
        }

        $this->json(['success' => true, 'data' => $stats]);
    }

    private function getSuperAdminStats()
    {
        return [
            'total_branches' => $this->db->selectOne("SELECT COUNT(*) as count FROM branches WHERE is_active = 1")['count'] ?? 0,
            'total_students' => $this->db->selectOne("SELECT COUNT(*) as count FROM students s JOIN users u ON s.user_id = u.id WHERE s.is_active = 1 AND u.is_active = 1")['count'] ?? 0,
            'total_teachers' => $this->db->selectOne("SELECT COUNT(*) as count FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1")['count'] ?? 0,
            'total_employees' => $this->db->selectOne("SELECT COUNT(*) as count FROM employees e JOIN users u ON e.user_id = u.id WHERE u.is_active = 1")['count'] ?? 0,
            'monthly_revenue' => $this->db->selectOne("SELECT COALESCE(SUM(amount),0) as total FROM fee_payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())")['total'] ?? 0,
            'pending_fees' => $this->db->selectOne("SELECT COALESCE(SUM(balance_amount),0) as total FROM student_fees WHERE status IN ('pending','partial','overdue')")['total'] ?? 0,
            'active_classes' => $this->db->selectOne("SELECT COUNT(*) as count FROM classes WHERE is_active = 1")['count'] ?? 0,
            'today_attendance_rate' => $this->calculateAttendanceRate(),
            'active_borrowings' => $this->db->selectOne("SELECT COUNT(*) as count FROM book_borrowings WHERE status = 'borrowed'")['count'] ?? 0,
            'overdue_books' => $this->db->selectOne("SELECT COUNT(*) as count FROM book_borrowings WHERE status = 'overdue' OR (status = 'borrowed' AND due_date < CURDATE())")['count'] ?? 0,
        ];
    }

    private function getBranchStats($branchId)
    {
        return [
            'total_students' => $this->db->selectOne("SELECT COUNT(*) as count FROM students WHERE branch_id = ? AND is_active = 1", [$branchId])['count'] ?? 0,
            'total_teachers' => $this->db->selectOne("SELECT COUNT(*) as count FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = ? AND u.is_active = 1", [$branchId])['count'] ?? 0,
            'total_employees' => $this->db->selectOne("SELECT COUNT(*) as count FROM employees e JOIN users u ON e.user_id = u.id WHERE u.branch_id = ? AND u.is_active = 1", [$branchId])['count'] ?? 0,
            'total_classes' => $this->db->selectOne("SELECT COUNT(*) as count FROM classes WHERE branch_id = ? AND is_active = 1", [$branchId])['count'] ?? 0,
            'monthly_revenue' => $this->db->selectOne("SELECT COALESCE(SUM(fp.amount),0) as total FROM fee_payments fp JOIN student_fees sf ON fp.student_fee_id = sf.id JOIN students s ON sf.student_id = s.id WHERE s.branch_id = ? AND MONTH(fp.payment_date) = MONTH(CURDATE())", [$branchId])['total'] ?? 0,
            'today_attendance' => $this->getTodayAttendance($branchId),
        ];
    }

    private function getTeacherStats($userId)
    {
        $teacher = $this->db->selectOne("SELECT id FROM teachers WHERE user_id = ?", [$userId]);
        if (!$teacher) return [];

        $teacherId = $teacher['id'];

        return [
            'assigned_classes' => $this->db->selectOne("SELECT COUNT(DISTINCT class_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1", [$teacherId])['count'] ?? 0,
            'assigned_subjects' => $this->db->selectOne("SELECT COUNT(DISTINCT subject_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1", [$teacherId])['count'] ?? 0,
            'total_students' => $this->db->selectOne("SELECT COUNT(DISTINCT s.id) as count FROM class_subjects cs JOIN sections sec ON cs.class_id = sec.class_id JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1 WHERE cs.teacher_id = ? AND cs.is_active = 1", [$teacherId])['count'] ?? 0,
            'today_periods' => $this->db->selectOne("SELECT COUNT(*) as count FROM timetables WHERE teacher_id = ? AND day_of_week = DAYNAME(CURDATE())", [$teacherId])['count'] ?? 0,
        ];
    }

    private function getStudentStats($userId)
    {
        $student = $this->db->selectOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
        if (!$student) return [];

        $studentId = $student['id'];

        return [
            'attendance_rate' => $this->getStudentAttendanceRate($studentId),
            'pending_fees' => $this->db->selectOne("SELECT COALESCE(SUM(balance_amount),0) as total FROM student_fees WHERE student_id = ? AND status != 'paid'", [$studentId])['total'] ?? 0,
            'upcoming_exams' => $this->db->selectOne("SELECT COUNT(*) as count FROM exams WHERE class_id = (SELECT current_class_id FROM students WHERE id = ?) AND exam_date >= CURDATE() AND status = 'scheduled'", [$studentId])['count'] ?? 0,
        ];
    }

    private function getGeneralStats($branchId)
    {
        return [
            'total_students' => $this->db->selectOne("SELECT COUNT(*) as count FROM students WHERE branch_id = ? AND is_active = 1", [$branchId])['count'] ?? 0,
            'total_teachers' => $this->db->selectOne("SELECT COUNT(*) as count FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = ?", [$branchId])['count'] ?? 0,
        ];
    }

    /**
     * Get revenue chart data
     * GET /api/v1/dashboard/charts/revenue
     */
    public function revenueChart()
    {
        $branchId = $this->session->get('branch_id');

        $data = $this->db->select(
            "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month,
                    DATE_FORMAT(payment_date, '%M %Y') as month_name,
                    SUM(amount) as total
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month",
            [$branchId]
        );

        $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get attendance chart data
     * GET /api/v1/dashboard/charts/attendance
     */
    public function attendanceChart()
    {
        $branchId = $this->session->get('branch_id');

        $data = $this->db->select(
            "SELECT attendance_date as date,
                    COUNT(*) as total,
                    COUNT(CASE WHEN status IN ('present','late') THEN 1 END) as present,
                    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent
             FROM student_attendance sa 
             JOIN students s ON sa.student_id = s.id 
             WHERE s.branch_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY attendance_date
             ORDER BY attendance_date",
            [$branchId]
        );

        $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get recent activities
     * GET /api/v1/dashboard/recent-activities
     */
    public function recentActivities()
    {
        $activities = $this->db->select(
            "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM audit_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             ORDER BY al.created_at DESC 
             LIMIT 20"
        );

        $this->json(['success' => true, 'data' => $activities]);
    }

    private function calculateAttendanceRate()
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(CASE WHEN status IN ('present','late') THEN 1 END) as present, COUNT(*) as total
             FROM student_attendance WHERE attendance_date = CURDATE()"
        );
        return ($result['total'] ?? 0) > 0 ? round(($result['present'] / $result['total']) * 100, 2) : 0;
    }

    private function getTodayAttendance($branchId)
    {
        return $this->db->select(
            "SELECT c.class_name,
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(CASE WHEN sa.status IN ('present','late') THEN 1 END) as present,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent
             FROM classes c JOIN sections sec ON c.id = sec.class_id 
             JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1
             LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = CURDATE()
             WHERE c.branch_id = ? GROUP BY c.id ORDER BY c.class_level",
            [$branchId]
        );
    }

    private function getStudentAttendanceRate($studentId)
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(CASE WHEN status IN ('present','late') THEN 1 END) as present, COUNT(*) as total
             FROM student_attendance WHERE student_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
            [$studentId]
        );
        return ($result['total'] ?? 0) > 0 ? round(($result['present'] / $result['total']) * 100, 2) : 0;
    }
}