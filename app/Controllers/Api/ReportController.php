<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class ReportController extends Controller
{
    /**
     * Student report
     * GET /api/v1/reports/students
     */
    public function students()
    {
        $classId = $this->request->get('class_id', '');
        $sectionId = $this->request->get('section_id', '');
        $status = $this->request->get('status', 'active');
        $branchId = $this->session->get('branch_id');

        $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.gender,
                       c.class_name, sec.section_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                WHERE s.branch_id = ? AND u.deleted_at IS NULL";

        $params = [$branchId];

        if ($classId) {
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
        }

        if ($sectionId) {
            $sql .= " AND s.current_section_id = ?";
            $params[] = $sectionId;
        }

        if ($status === 'active') {
            $sql .= " AND s.is_active = 1";
        } elseif ($status === 'inactive') {
            $sql .= " AND s.is_active = 0";
        }

        $sql .= " ORDER BY c.class_level, u.last_name, u.first_name";
        $students = $this->db->select($sql, $params);

        $this->json(['success' => true, 'data' => $students, 'total' => count($students)]);
    }

    /**
     * Attendance report
     * GET /api/v1/reports/attendance
     */
    public function attendance()
    {
        $classId = $this->request->get('class_id', '');
        $sectionId = $this->request->get('section_id', '');
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-t'));

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
                 AND sa.attendance_date BETWEEN ? AND ?
             WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
             GROUP BY s.id
             ORDER BY u.last_name, u.first_name",
            [$startDate, $endDate, $classId, $sectionId]
        );

        $this->json(['success' => true, 'data' => $report]);
    }

    /**
     * Financial report
     * GET /api/v1/reports/financial
     */
    public function financial()
    {
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-t'));
        $branchId = $this->session->get('branch_id');

        $dailyCollection = $this->db->select(
            "SELECT DATE(fp.payment_date) as date,
                    SUM(fp.amount) as total,
                    COUNT(*) as count
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?
             GROUP BY DATE(fp.payment_date)
             ORDER BY date",
            [$branchId, $startDate, $endDate]
        );

        $byClass = $this->db->select(
            "SELECT c.class_name,
                    COUNT(DISTINCT sf.student_id) as student_count,
                    COALESCE(SUM(sf.net_payable),0) as total_fees,
                    COALESCE(SUM(sf.paid_amount),0) as collected,
                    COALESCE(SUM(sf.balance_amount),0) as outstanding
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN classes c ON s.current_class_id = c.id 
             WHERE s.branch_id = ? AND sf.status != 'waived'
             GROUP BY c.id
             ORDER BY c.class_level",
            [$branchId]
        );

        $expenses = $this->db->select(
            "SELECT expense_category,
                    COUNT(*) as count,
                    COALESCE(SUM(amount),0) as total
             FROM expenses 
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND status = 'paid'
             GROUP BY expense_category",
            [$branchId, $startDate, $endDate]
        );

        $totals = $this->db->selectOne(
            "SELECT 
                (SELECT COALESCE(SUM(amount),0) FROM fee_payments fp 
                 JOIN student_fees sf ON fp.student_fee_id = sf.id 
                 JOIN students s ON sf.student_id = s.id 
                 WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?) as total_revenue,
                (SELECT COALESCE(SUM(amount),0) FROM expenses 
                 WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND status = 'paid') as total_expenses",
            [$branchId, $startDate, $endDate, $branchId, $startDate, $endDate]
        );

        $this->json([
            'success' => true,
            'data' => [
                'daily_collection' => $dailyCollection,
                'by_class' => $byClass,
                'expenses' => $expenses,
                'totals' => $totals,
                'net_income' => ($totals['total_revenue'] ?? 0) - ($totals['total_expenses'] ?? 0)
            ]
        ]);
    }

    /**
     * Exam report
     * GET /api/v1/reports/exams
     */
    public function exams()
    {
        $examId = $this->request->get('exam_id', '');
        $classId = $this->request->get('class_id', '');

        if ($examId) {
            $results = $this->db->select(
                "SELECT er.*, s.student_code, u.first_name, u.last_name,
                        e.total_marks, e.passing_marks
                 FROM exam_results er 
                 JOIN students s ON er.student_id = s.id 
                 JOIN users u ON s.user_id = u.id 
                 JOIN exams e ON er.exam_id = e.id 
                 WHERE er.exam_id = ?
                 ORDER BY er.marks_obtained DESC",
                [$examId]
            );

            $stats = $this->db->selectOne(
                "SELECT COUNT(*) as total,
                        COUNT(CASE WHEN er.marks_obtained >= e.passing_marks THEN 1 END) as passed,
                        ROUND(AVG(er.marks_obtained), 2) as average,
                        MAX(er.marks_obtained) as highest,
                        ROUND((COUNT(CASE WHEN er.marks_obtained >= e.passing_marks THEN 1 END) / COUNT(*)) * 100, 2) as pass_rate
                 FROM exam_results er JOIN exams e ON er.exam_id = e.id WHERE er.exam_id = ?",
                [$examId]
            );

            $this->json(['success' => true, 'data' => ['results' => $results, 'statistics' => $stats]]);
            return;
        }

        // List all completed exams
        $exams = $this->db->select(
            "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name,
                    (SELECT COUNT(*) FROM exam_results WHERE exam_id = e.id) as result_count
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             WHERE e.status = 'completed'
             ORDER BY e.exam_date DESC"
        );

        $this->json(['success' => true, 'data' => $exams]);
    }

    /**
     * Payroll report
     * GET /api/v1/reports/payroll
     */
    public function payroll()
    {
        $month = $this->request->get('month', date('m'));
        $year = $this->request->get('year', date('Y'));
        $branchId = $this->session->get('branch_id');

        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $payrolls = $this->db->select(
            "SELECT p.*, u.first_name, u.last_name,
                    ss.salary_grade
             FROM payroll p 
             JOIN users u ON p.user_id = u.id 
             JOIN salary_structures ss ON p.salary_structure_id = ss.id 
             WHERE p.pay_period_start = ? AND p.pay_period_end = ?",
            [$startDate, $endDate]
        );

        $summary = $this->db->selectOne(
            "SELECT COUNT(*) as total_employees,
                    COALESCE(SUM(gross_salary),0) as total_gross,
                    COALESCE(SUM(deductions),0) as total_deductions,
                    COALESCE(SUM(net_salary),0) as total_net
             FROM payroll 
             WHERE pay_period_start = ? AND pay_period_end = ?",
            [$startDate, $endDate]
        );

        $this->json(['success' => true, 'data' => ['payrolls' => $payrolls, 'summary' => $summary]]);
    }

    /**
     * Library report
     * GET /api/v1/reports/library
     */
    public function library()
    {
        $stats = $this->db->selectOne(
            "SELECT 
                (SELECT COUNT(*) FROM library_books WHERE is_active = 1) as total_books,
                (SELECT COALESCE(SUM(available_copies),0) FROM library_books WHERE is_active = 1) as available,
                (SELECT COUNT(*) FROM book_borrowings WHERE status = 'borrowed') as active_borrowings,
                (SELECT COUNT(*) FROM book_borrowings WHERE status = 'overdue' OR (status = 'borrowed' AND due_date < CURDATE())) as overdue"
        );

        $popular = $this->db->select(
            "SELECT lb.title, lb.author, lb.isbn, COUNT(bb.id) as borrow_count
             FROM library_books lb 
             LEFT JOIN book_borrowings bb ON lb.id = bb.book_id 
             WHERE lb.is_active = 1
             GROUP BY lb.id
             ORDER BY borrow_count DESC
             LIMIT 10"
        );

        $this->json(['success' => true, 'data' => ['stats' => $stats, 'popular_books' => $popular]]);
    }

    /**
     * Export data
     * GET /api/v1/reports/export/{type}
     */
    public function export($type)
    {
        $format = $this->request->get('format', 'csv');

        switch ($type) {
            case 'students':
                $data = $this->exportStudents();
                $filename = 'students_report';
                break;
            case 'attendance':
                $data = $this->exportAttendance();
                $filename = 'attendance_report';
                break;
            case 'financial':
                $data = $this->exportFinancial();
                $filename = 'financial_report';
                break;
            case 'exams':
                $data = $this->exportExams();
                $filename = 'exam_report';
                break;
            default:
                $this->json(['success' => false, 'message' => 'نوع گزارش نامعتبر است'], 400);
                return;
        }

        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'داده‌ای برای خروجی وجود ندارد'], 404);
            return;
        }

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
            $output = fopen('php://output', 'w');
            fprintf($output, "\xEF\xBB\xBF"); // BOM for UTF-8
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) fputcsv($output, $row);
            fclose($output);
            exit;
        }

        $this->json(['success' => true, 'data' => $data]);
    }

    private function exportStudents()
    {
        $branchId = $this->session->get('branch_id');
        return $this->db->select(
            "SELECT s.student_code, s.admission_number, u.first_name, u.last_name,
                    u.email, u.phone, u.gender, c.class_name, sec.section_name, s.admission_date
             FROM students s JOIN users u ON s.user_id = u.id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id 
             WHERE s.branch_id = ? AND s.is_active = 1",
            [$branchId]
        );
    }

    private function exportAttendance()
    {
        $classId = $this->request->get('class_id');
        $sectionId = $this->request->get('section_id');
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-t'));

        return $this->db->select(
            "SELECT s.student_code, u.first_name, u.last_name,
                    COUNT(CASE WHEN sa.status IN ('present','late') THEN 1 END) as present,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent,
                    COUNT(*) as total
             FROM students s JOIN users u ON s.user_id = u.id 
             LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date BETWEEN ? AND ?
             WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
             GROUP BY s.id",
            [$startDate, $endDate, $classId, $sectionId]
        );
    }

    private function exportFinancial()
    {
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-t'));
        $branchId = $this->session->get('branch_id');

        return $this->db->select(
            "SELECT fp.payment_number, fp.amount, fp.payment_method,
                    DATE(fp.payment_date) as date, u.first_name, u.last_name
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?
             ORDER BY fp.payment_date",
            [$branchId, $startDate, $endDate]
        );
    }

    private function exportExams()
    {
        $examId = $this->request->get('exam_id');
        return $this->db->select(
            "SELECT s.student_code, u.first_name, u.last_name,
                    er.marks_obtained, er.grade,
                    CASE WHEN er.marks_obtained >= e.passing_marks THEN 'Pass' ELSE 'Fail' END as result
             FROM exam_results er 
             JOIN students s ON er.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             JOIN exams e ON er.exam_id = e.id 
             WHERE er.exam_id = ?
             ORDER BY er.marks_obtained DESC",
            [$examId]
        );
    }
}