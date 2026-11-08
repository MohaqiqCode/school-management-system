<?php
namespace App\Services;

use App\Core\Application;

/**
 * Report Service
 * سرویس تولید گزارش‌های پیشرفته
 */
class ReportService
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = Application::getInstance()->getDB();
        $this->config = Application::getInstance()->getConfig();
    }

    /**
     * Generate student report
     */
    public function studentReport($filters = [])
    {
        $branchId = $filters['branch_id'] ?? null;
        $classId = $filters['class_id'] ?? null;
        $sectionId = $filters['section_id'] ?? null;
        $status = $filters['status'] ?? 'active';

        $sql = "SELECT s.student_code, s.admission_number, s.admission_date,
                       u.first_name, u.last_name, u.email, u.phone, u.gender,
                       u.date_of_birth, u.address, u.city,
                       c.class_name, c.class_level,
                       sec.section_name,
                       b.name as branch_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                JOIN branches b ON s.branch_id = b.id 
                WHERE u.deleted_at IS NULL";

        $params = [];

        if ($branchId) { $sql .= " AND s.branch_id = ?"; $params[] = $branchId; }
        if ($classId) { $sql .= " AND s.current_class_id = ?"; $params[] = $classId; }
        if ($sectionId) { $sql .= " AND s.current_section_id = ?"; $params[] = $sectionId; }
        if ($status === 'active') { $sql .= " AND s.is_active = 1"; }
        elseif ($status === 'inactive') { $sql .= " AND s.is_active = 0"; }

        $sql .= " ORDER BY c.class_level, u.last_name, u.first_name";

        return $this->db->select($sql, $params);
    }

    /**
     * Generate attendance report
     */
    public function attendanceReport($filters = [])
    {
        $classId = $filters['class_id'] ?? null;
        $sectionId = $filters['section_id'] ?? null;
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-t');

        return $this->db->select(
            "SELECT s.student_code, u.first_name, u.last_name,
                    COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN sa.status = 'late' THEN 1 END) as late,
                    COUNT(CASE WHEN sa.status = 'excused' THEN 1 END) as excused,
                    COUNT(CASE WHEN sa.status = 'half_day' THEN 1 END) as half_day,
                    COUNT(*) as total,
                    ROUND((COUNT(CASE WHEN sa.status IN ('present','late') THEN 1 END) / COUNT(*)) * 100, 2) as percentage
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

    /**
     * Generate financial report
     */
    public function financialReport($filters = [])
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-t');
        $branchId = $filters['branch_id'] ?? null;

        // Fee collection summary
        $feeCollection = $this->db->select(
            "SELECT DATE(fp.payment_date) as date,
                    COUNT(*) as transaction_count,
                    SUM(fp.amount) as total_collected,
                    fp.payment_method
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE DATE(fp.payment_date) BETWEEN ? AND ?" .
             ($branchId ? " AND s.branch_id = ?" : "") . "
             GROUP BY DATE(fp.payment_date), fp.payment_method
             ORDER BY date",
            $branchId ? [$startDate, $endDate, $branchId] : [$startDate, $endDate]
        );

        // Outstanding fees by class
        $outstanding = $this->db->select(
            "SELECT c.class_name, c.class_level,
                    COUNT(DISTINCT sf.student_id) as student_count,
                    COALESCE(SUM(sf.balance_amount), 0) as total_outstanding,
                    COUNT(CASE WHEN sf.status = 'overdue' THEN 1 END) as overdue_count
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN classes c ON s.current_class_id = c.id 
             WHERE sf.status IN ('pending', 'partial', 'overdue')" .
             ($branchId ? " AND s.branch_id = ?" : "") . "
             GROUP BY c.id
             ORDER BY total_outstanding DESC",
            $branchId ? [$branchId] : []
        );

        // Expenses by category
        $expenses = $this->db->select(
            "SELECT expense_category,
                    COUNT(*) as count,
                    SUM(amount) as total
             FROM expenses 
             WHERE expense_date BETWEEN ? AND ? AND status = 'paid'" .
             ($branchId ? " AND branch_id = ?" : "") . "
             GROUP BY expense_category
             ORDER BY total DESC",
            $branchId ? [$startDate, $endDate, $branchId] : [$startDate, $endDate]
        );

        // Totals
        $totals = [
            'total_collected' => array_sum(array_column($feeCollection, 'total_collected')),
            'total_outstanding' => array_sum(array_column($outstanding, 'total_outstanding')),
            'total_expenses' => array_sum(array_column($expenses, 'total')),
            'collection_rate' => 0,
        ];

        $totalFees = $totals['total_collected'] + $totals['total_outstanding'];
        if ($totalFees > 0) {
            $totals['collection_rate'] = round(($totals['total_collected'] / $totalFees) * 100, 2);
        }
        $totals['net_income'] = $totals['total_collected'] - $totals['total_expenses'];

        return [
            'fee_collection' => $feeCollection,
            'outstanding' => $outstanding,
            'expenses' => $expenses,
            'totals' => $totals
        ];
    }

    /**
     * Generate exam performance report
     */
    public function examReport($filters = [])
    {
        $examId = $filters['exam_id'] ?? null;
        $classId = $filters['class_id'] ?? null;
        $academicYearId = $filters['academic_year_id'] ?? null;

        if ($examId) {
            return $this->singleExamReport($examId);
        }

        $sql = "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name,
                       (SELECT COUNT(*) FROM exam_results WHERE exam_id = e.id) as student_count
                FROM exams e 
                JOIN exam_types et ON e.exam_type_id = et.id 
                JOIN subjects s ON e.subject_id = s.id 
                JOIN classes c ON e.class_id = c.id 
                WHERE e.status = 'completed'";

        $params = [];

        if ($classId) { $sql .= " AND e.class_id = ?"; $params[] = $classId; }
        if ($academicYearId) { $sql .= " AND e.academic_year_id = ?"; $params[] = $academicYearId; }

        $sql .= " ORDER BY e.exam_date DESC";
        $exams = $this->db->select($sql, $params);

        // Add statistics to each exam
        foreach ($exams as &$exam) {
            $stats = $this->db->selectOne(
                "SELECT ROUND(AVG(marks_obtained), 2) as average,
                        COUNT(CASE WHEN marks_obtained >= e.passing_marks THEN 1 END) as passed,
                        COUNT(CASE WHEN marks_obtained < e.passing_marks THEN 1 END) as failed,
                        ROUND((COUNT(CASE WHEN marks_obtained >= e.passing_marks THEN 1 END) / COUNT(*)) * 100, 2) as pass_percentage
                 FROM exam_results er JOIN exams e ON er.exam_id = e.id WHERE er.exam_id = ?",
                [$exam['id']]
            );
            $exam['statistics'] = $stats;
        }

        return $exams;
    }

    /**
     * Generate single exam detailed report
     */
    private function singleExamReport($examId)
    {
        $exam = $this->db->selectOne(
            "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             WHERE e.id = ?",
            [$examId]
        );

        if (!$exam) return null;

        $results = $this->db->select(
            "SELECT er.*, s.student_code, u.first_name, u.last_name
             FROM exam_results er 
             JOIN students s ON er.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             WHERE er.exam_id = ?
             ORDER BY er.marks_obtained DESC",
            [$examId]
        );

        $statistics = $this->db->selectOne(
            "SELECT COUNT(*) as total,
                    COUNT(CASE WHEN marks_obtained >= ? THEN 1 END) as passed,
                    COUNT(CASE WHEN marks_obtained < ? AND is_absent = 0 THEN 1 END) as failed,
                    COUNT(CASE WHEN is_absent = 1 THEN 1 END) as absent,
                    ROUND(AVG(CASE WHEN is_absent = 0 THEN marks_obtained END), 2) as average,
                    MAX(marks_obtained) as highest,
                    MIN(CASE WHEN is_absent = 0 THEN marks_obtained END) as lowest,
                    ROUND(STDDEV(CASE WHEN is_absent = 0 THEN marks_obtained END), 2) as std_dev
             FROM exam_results WHERE exam_id = ?",
            [$exam['passing_marks'], $exam['passing_marks'], $examId]
        );

        return [
            'exam' => $exam,
            'results' => $results,
            'statistics' => $statistics
        ];
    }

    /**
     * Generate payroll report
     */
    public function payrollReport($filters = [])
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-t');
        $branchId = $filters['branch_id'] ?? null;

        $sql = "SELECT p.*, u.first_name, u.last_name, u.email,
                       ss.salary_grade, ss.basic_salary as grade_basic,
                       b.name as branch_name
                FROM payroll p 
                JOIN users u ON p.user_id = u.id 
                JOIN salary_structures ss ON p.salary_structure_id = ss.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE p.pay_period_start = ? AND p.pay_period_end = ?";

        $params = [$startDate, $endDate];

        if ($branchId) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " ORDER BY u.last_name, u.first_name";
        $payrolls = $this->db->select($sql, $params);

        $summary = $this->db->selectOne(
            "SELECT COUNT(*) as total_employees,
                    COALESCE(SUM(basic_salary), 0) as total_basic,
                    COALESCE(SUM(allowances), 0) as total_allowances,
                    COALESCE(SUM(deductions), 0) as total_deductions,
                    COALESCE(SUM(gross_salary), 0) as total_gross,
                    COALESCE(SUM(net_salary), 0) as total_net,
                    COALESCE(AVG(net_salary), 0) as avg_net
             FROM payroll WHERE pay_period_start = ? AND pay_period_end = ?",
            [$startDate, $endDate]
        );

        return [
            'payrolls' => $payrolls,
            'summary' => $summary
        ];
    }

    /**
     * Generate library report
     */
    public function libraryReport($filters = [])
    {
        $branchId = $filters['branch_id'] ?? null;

        $stats = $this->db->selectOne(
            "SELECT 
                (SELECT COUNT(*) FROM library_books WHERE is_active = 1) as total_books,
                (SELECT COALESCE(SUM(total_copies), 0) FROM library_books WHERE is_active = 1) as total_copies,
                (SELECT COALESCE(SUM(available_copies), 0) FROM library_books WHERE is_active = 1) as available_copies,
                (SELECT COUNT(*) FROM book_borrowings WHERE status = 'borrowed') as active_borrowings,
                (SELECT COUNT(*) FROM book_borrowings WHERE status = 'overdue' OR (status = 'borrowed' AND due_date < CURDATE())) as overdue,
                (SELECT COALESCE(SUM(fine_amount), 0) FROM book_borrowings WHERE fine_paid = 0) as unpaid_fines"
        );

        $popularBooks = $this->db->select(
            "SELECT lb.id, lb.title, lb.author, lb.isbn, lb.category,
                    COUNT(bb.id) as borrow_count,
                    lb.available_copies
             FROM library_books lb 
             LEFT JOIN book_borrowings bb ON lb.id = bb.book_id 
             WHERE lb.is_active = 1
             GROUP BY lb.id
             ORDER BY borrow_count DESC
             LIMIT 20"
        );

        $monthlyBorrowings = $this->db->select(
            "SELECT DATE_FORMAT(borrow_date, '%Y-%m') as month,
                    COUNT(*) as count
             FROM book_borrowings 
             WHERE borrow_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month"
        );

        return [
            'stats' => $stats,
            'popular_books' => $popularBooks,
            'monthly_borrowings' => $monthlyBorrowings
        ];
    }

    /**
     * Generate transport report
     */
    public function transportReport($filters = [])
    {
        $branchId = $filters['branch_id'] ?? null;

        $sql = "SELECT tr.id, tr.route_name, tr.route_code,
                       v.vehicle_number, v.capacity,
                       COUNT(st.id) as allocated_students,
                       COALESCE(SUM(st.monthly_fee), 0) as total_monthly_fee,
                       ROUND((COUNT(st.id) / NULLIF(v.capacity, 0)) * 100, 2) as utilization_rate
                FROM transport_routes tr 
                LEFT JOIN vehicles v ON tr.vehicle_id = v.id
                LEFT JOIN student_transport st ON tr.id = st.route_id AND st.is_active = 1";

        $params = [];
        if ($branchId) {
            $sql .= " WHERE tr.branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " GROUP BY tr.id ORDER BY utilization_rate DESC";

        return $this->db->select($sql, $params);
    }

    /**
     * Generate inventory report
     */
    public function inventoryReport($filters = [])
    {
        $branchId = $filters['branch_id'] ?? null;

        $summary = $this->db->selectOne(
            "SELECT COUNT(*) as total_items,
                    COALESCE(SUM(quantity_in_stock), 0) as total_quantity,
                    COALESCE(SUM(quantity_in_stock * unit_price), 0) as total_value,
                    COUNT(CASE WHEN quantity_in_stock <= reorder_level THEN 1 END) as low_stock,
                    COUNT(CASE WHEN quantity_in_stock = 0 THEN 1 END) as out_of_stock
             FROM inventory_items WHERE is_active = 1" .
             ($branchId ? " AND branch_id = ?" : ""),
            $branchId ? [$branchId] : []
        );

        $byCategory = $this->db->select(
            "SELECT ic.category_name,
                    COUNT(ii.id) as item_count,
                    COALESCE(SUM(ii.quantity_in_stock), 0) as total_quantity,
                    COALESCE(SUM(ii.quantity_in_stock * ii.unit_price), 0) as total_value
             FROM inventory_categories ic 
             LEFT JOIN inventory_items ii ON ic.id = ii.category_id AND ii.is_active = 1
             WHERE 1=1" .
             ($branchId ? " AND ic.branch_id = ?" : "") . "
             GROUP BY ic.id
             ORDER BY total_value DESC",
            $branchId ? [$branchId] : []
        );

        return [
            'summary' => $summary,
            'by_category' => $byCategory
        ];
    }

    /**
     * Export data to CSV
     */
    public function exportToCsv($data, $filename)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, "\xEF\xBB\xBF"); // BOM for UTF-8

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export data to PDF
     */
    public function exportToPdf($data, $title, $filename)
    {
        // This would require a PDF library like TCPDF or DomPDF
        // Placeholder for PDF export logic
        return ['success' => false, 'message' => 'PDF export requires additional library'];
    }

    /**
     * Export data to Excel
     */
    public function exportToExcel($data, $filename)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.xlsx"');

        // Simple CSV-based Excel export (can be enhanced with PhpSpreadsheet)
        $output = fopen('php://output', 'w');
        fprintf($output, "\xEF\xBB\xBF");

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}