<?php
namespace App\Controllers;

use App\Core\Controller;

class ReportController extends Controller
{
    public function index()
    {
        $this->view('reports/index', [
            'title' => 'گزارش‌های سیستم'
        ]);
    }
    
    public function studentReport()
    {
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $statusFilter = $_GET['status'] ?? 'active';
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
        
        if ($statusFilter === 'active') {
            $sql .= " AND s.is_active = 1";
        } elseif ($statusFilter === 'inactive') {
            $sql .= " AND s.is_active = 0";
        }
        
        $sql .= " ORDER BY c.class_level, u.last_name, u.first_name";
        $students = $this->db->select($sql, $params);
        
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE branch_id = ? AND is_active = 1 ORDER BY class_level", [$branchId]);
        
        // Statistics
        $stats = [
            'total' => count($students),
            'male' => count(array_filter($students, fn($s) => $s['gender'] === 'male')),
            'female' => count(array_filter($students, fn($s) => $s['gender'] === 'female')),
        ];
        
        $this->view('reports/students', [
            'title' => 'گزارش دانش‌آموزان',
            'students' => $students,
            'classes' => $classes,
            'stats' => $stats,
            'classFilter' => $classId,
            'sectionFilter' => $sectionId,
            'statusFilter' => $statusFilter
        ]);
    }
    
    public function attendanceReport()
    {
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $branchId = $this->session->get('branch_id');
        
        $report = [];
        
        if ($classId && $sectionId) {
            $report = $this->db->select(
                "SELECT s.id, s.student_code, u.first_name, u.last_name,
                        COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present_days,
                        COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent_days,
                        COUNT(CASE WHEN sa.status = 'late' THEN 1 END) as late_days,
                        COUNT(CASE WHEN sa.status = 'excused' THEN 1 END) as excused_days,
                        COUNT(*) as total_days,
                        ROUND((COUNT(CASE WHEN sa.status IN ('present', 'late') THEN 1 END) / NULLIF(COUNT(*), 0)) * 100, 2) as attendance_percentage
                 FROM students s 
                 JOIN users u ON s.user_id = u.id 
                 LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                     AND sa.attendance_date BETWEEN ? AND ?
                 WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
                 GROUP BY s.id
                 ORDER BY u.last_name, u.first_name",
                [$startDate, $endDate, $classId, $sectionId]
            );
        }
        
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE branch_id = ? AND is_active = 1 ORDER BY class_level", [$branchId]);
        
        $this->view('reports/attendance', [
            'title' => 'گزارش حضور و غیاب',
            'report' => $report,
            'classes' => $classes,
            'classFilter' => $classId,
            'sectionFilter' => $sectionId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    public function financialReport()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $branchId = $this->session->get('branch_id');
        
        // Fee collection summary
        $feeSummary = $this->db->select(
            "SELECT DATE(fp.payment_date) as date,
                    SUM(fp.amount) as total_collected,
                    COUNT(*) as transaction_count,
                    fp.payment_method
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?
             GROUP BY DATE(fp.payment_date), fp.payment_method
             ORDER BY date",
            [$branchId, $startDate, $endDate]
        );
        
        // Outstanding fees
        $outstandingFees = $this->db->select(
            "SELECT c.class_name,
                    COUNT(DISTINCT sf.student_id) as student_count,
                    COALESCE(SUM(sf.balance_amount), 0) as total_outstanding
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN classes c ON s.current_class_id = c.id 
             WHERE s.branch_id = ? AND sf.status IN ('pending', 'partial', 'overdue')
             GROUP BY c.id
             ORDER BY total_outstanding DESC",
            [$branchId]
        );
        
        // Expense summary
        $expenseSummary = $this->db->select(
            "SELECT expense_category,
                    SUM(amount) as total,
                    COUNT(*) as count
             FROM expenses 
             WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND status = 'paid'
             GROUP BY expense_category",
            [$branchId, $startDate, $endDate]
        );
        
        // Totals
        $totals = [
            'total_collected' => array_sum(array_column($feeSummary, 'total_collected')),
            'total_outstanding' => array_sum(array_column($outstandingFees, 'total_outstanding')),
            'total_expenses' => array_sum(array_column($expenseSummary, 'total'))
        ];
        
        $totals['net_income'] = $totals['total_collected'] - $totals['total_expenses'];
        
        $this->view('reports/financial', [
            'title' => 'گزارش مالی',
            'feeSummary' => $feeSummary,
            'outstandingFees' => $outstandingFees,
            'expenseSummary' => $expenseSummary,
            'totals' => $totals,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    public function examReport()
    {
        $examId = $_GET['exam_id'] ?? '';
        
        $results = [];
        $statistics = [];
        
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
            
            // Calculate statistics
            $marks = array_column($results, 'marks_obtained');
            $statistics = [
                'total_students' => count($results),
                'highest' => !empty($marks) ? max($marks) : 0,
                'lowest' => !empty($marks) ? min($marks) : 0,
                'average' => !empty($marks) ? round(array_sum($marks) / count($marks), 2) : 0,
                'passed' => count(array_filter($results, fn($r) => $r['marks_obtained'] >= $r['passing_marks'])),
                'failed' => count(array_filter($results, fn($r) => $r['marks_obtained'] < $r['passing_marks'] && !$r['is_absent'])),
                'absent' => count(array_filter($results, fn($r) => $r['is_absent']))
            ];
            
            $statistics['pass_percentage'] = $statistics['total_students'] > 0 
                ? round(($statistics['passed'] / $statistics['total_students']) * 100, 2) 
                : 0;
        }
        
        $exams = $this->db->select(
            "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             WHERE e.status = 'completed'
             ORDER BY e.exam_date DESC"
        );
        
        $this->view('reports/exam', [
            'title' => 'گزارش آزمون‌ها',
            'results' => $results,
            'statistics' => $statistics,
            'exams' => $exams,
            'selectedExam' => $examId
        ]);
    }
    
    public function export($type)
    {
        switch ($type) {
            case 'students':
                $data = $this->exportStudents();
                $filename = 'students_report_' . date('Y-m-d') . '.csv';
                break;
            case 'attendance':
                $data = $this->exportAttendance();
                $filename = 'attendance_report_' . date('Y-m-d') . '.csv';
                break;
            case 'financial':
                $data = $this->exportFinancial();
                $filename = 'financial_report_' . date('Y-m-d') . '.csv';
                break;
            case 'exam':
                $data = $this->exportExamResults();
                $filename = 'exam_report_' . date('Y-m-d') . '.csv';
                break;
            default:
                $this->session->flash('error', 'نوع گزارش نامعتبر است');
                $this->redirect('/reports');
                return;
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    private function exportStudents()
    {
        $classId = $_GET['class_id'] ?? '';
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT s.student_code, s.admission_number, u.first_name, u.last_name,
                       u.email, u.phone, u.gender, c.class_name, sec.section_name,
                       s.admission_date
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                WHERE s.branch_id = ? AND s.is_active = 1";
        
        $params = [$branchId];
        
        if ($classId) {
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
        }
        
        return $this->db->select($sql, $params);
    }
    
    private function exportAttendance()
    {
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        return $this->db->select(
            "SELECT s.student_code, u.first_name, u.last_name,
                    COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN sa.status = 'late' THEN 1 END) as late,
                    COUNT(*) as total,
                    ROUND((COUNT(CASE WHEN sa.status IN ('present', 'late') THEN 1 END) / COUNT(*)) * 100, 2) as percentage
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date BETWEEN ? AND ?
             WHERE s.current_class_id = ? AND s.current_section_id = ? AND s.is_active = 1
             GROUP BY s.id",
            [$startDate, $endDate, $classId, $sectionId]
        );
    }
    
    private function exportFinancial()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $branchId = $this->session->get('branch_id');
        
        return $this->db->select(
            "SELECT fp.payment_number, fp.amount, fp.payment_method,
                    DATE(fp.payment_date) as date, u.first_name, u.last_name,
                    fc.category_name
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?
             ORDER BY fp.payment_date",
            [$branchId, $startDate, $endDate]
        );
    }
    
    private function exportExamResults()
    {
        $examId = $_GET['exam_id'] ?? '';
        
        return $this->db->select(
            "SELECT s.student_code, u.first_name, u.last_name,
                    er.marks_obtained, er.grade, er.grade_point,
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