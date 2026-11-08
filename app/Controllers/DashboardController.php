<?php
namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        switch ($user['role_name']) {
            case 'super_admin':
                return $this->superAdminDashboard($user);
            case 'branch_admin':
                return $this->branchAdminDashboard($user);
            case 'principal':
                return $this->principalDashboard($user);
            case 'academic_coordinator':
                return $this->academicCoordinatorDashboard($user);
            case 'accountant':
                return $this->accountantDashboard($user);
            case 'teacher':
                return $this->teacherDashboard($user);
            case 'librarian':
                return $this->librarianDashboard($user);
            case 'transport_manager':
                return $this->transportManagerDashboard($user);
            case 'parent':
                return $this->parentDashboard($user);
            case 'student':
                return $this->studentDashboard($user);
            default:
                return $this->defaultDashboard($user);
        }
    }
    
    private function superAdminDashboard($user)
    {
        // Get comprehensive statistics
        $stats = [
            'total_branches' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM branches WHERE is_active = 1"
            )['count'],
            'total_students' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM students s 
                 JOIN users u ON s.user_id = u.id 
                 WHERE s.is_active = 1 AND u.is_active = 1"
            )['count'],
            'total_teachers' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM teachers t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE u.is_active = 1"
            )['count'],
            'total_employees' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM employees e 
                 JOIN users u ON e.user_id = u.id 
                 WHERE u.is_active = 1"
            )['count'],
            'total_parents' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM parents p 
                 JOIN users u ON p.user_id = u.id 
                 WHERE u.is_active = 1"
            )['count'],
            'active_classes' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM classes WHERE is_active = 1"
            )['count'],
            'monthly_revenue' => $this->db->selectOne(
                "SELECT COALESCE(SUM(amount), 0) as total 
                 FROM fee_payments 
                 WHERE MONTH(payment_date) = MONTH(CURDATE()) 
                 AND YEAR(payment_date) = YEAR(CURDATE())"
            )['total'],
            'pending_fees' => $this->db->selectOne(
                "SELECT COALESCE(SUM(balance_amount), 0) as total 
                 FROM student_fees 
                 WHERE status IN ('pending', 'partial', 'overdue')"
            )['total'],
            'today_attendance_percentage' => $this->calculateTodayAttendancePercentage(),
            'active_borrowings' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM book_borrowings WHERE status = 'borrowed'"
            )['count'],
            'overdue_books' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM book_borrowings 
                 WHERE status = 'overdue' OR (status = 'borrowed' AND due_date < CURDATE())"
            )['count']
        ];
        
        // Get branch-wise student distribution
        $branchStudents = $this->db->select(
            "SELECT b.id, b.name, b.branch_code, 
                    COUNT(s.id) as student_count,
                    COALESCE(SUM(fp.amount), 0) as revenue
             FROM branches b 
             LEFT JOIN students s ON b.id = s.branch_id AND s.is_active = 1 
             LEFT JOIN student_fees sf ON s.id = sf.student_id
             LEFT JOIN fee_payments fp ON sf.id = fp.student_fee_id 
                 AND MONTH(fp.payment_date) = MONTH(CURDATE())
             WHERE b.is_active = 1 
             GROUP BY b.id 
             ORDER BY student_count DESC"
        );
        
        // Monthly revenue trend (last 12 months)
        $monthlyRevenue = $this->db->select(
            "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, 
                    DATE_FORMAT(payment_date, '%M %Y') as month_name,
                    SUM(amount) as total 
             FROM fee_payments 
             WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
             GROUP BY month 
             ORDER BY month"
        );
        
        // Expense vs Revenue
        $expenseVsRevenue = $this->db->select(
            "SELECT 'Revenue' as type, COALESCE(SUM(amount), 0) as total 
             FROM fee_payments 
             WHERE MONTH(payment_date) = MONTH(CURDATE())
             UNION ALL
             SELECT 'Expenses' as type, COALESCE(SUM(amount), 0) as total 
             FROM expenses 
             WHERE MONTH(expense_date) = MONTH(CURDATE()) AND status = 'paid'"
        );
        
        // Recent activities
        $recentActivities = $this->db->select(
            "SELECT al.*, 
                    COALESCE(u.first_name, 'System') as user_first_name,
                    COALESCE(u.last_name, '') as user_last_name,
                    b.name as branch_name
             FROM audit_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             LEFT JOIN branches b ON u.branch_id = b.id
             ORDER BY al.created_at DESC 
             LIMIT 20"
        );
        
        // Student enrollment trend
        $enrollmentTrend = $this->db->select(
            "SELECT DATE_FORMAT(admission_date, '%Y-%m') as month,
                    COUNT(*) as count
             FROM students 
             WHERE admission_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month"
        );
        
        // Top performing classes (by average marks)
        $topClasses = $this->db->select(
            "SELECT c.class_name, 
                    AVG(er.marks_obtained) as avg_marks,
                    COUNT(DISTINCT er.student_id) as student_count
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN classes c ON e.class_id = c.id
             WHERE e.exam_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY c.id 
             ORDER BY avg_marks DESC 
             LIMIT 10"
        );
        
        // System health
        $systemHealth = [
            'total_users' => $this->db->selectOne("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL")['count'],
            'active_sessions' => $this->db->selectOne("SELECT COUNT(*) as count FROM user_sessions WHERE expires_at > NOW()")['count'],
            'storage_used' => $this->calculateStorageUsed(),
            'database_size' => $this->getDatabaseSize()
        ];
        
        $this->view('dashboard/super_admin', compact(
            'stats', 'branchStudents', 'monthlyRevenue', 'expenseVsRevenue',
            'recentActivities', 'enrollmentTrend', 'topClasses', 'systemHealth', 'user'
        ));
    }
    
    private function branchAdminDashboard($user)
    {
        $branchId = $user['branch_id'];
        
        // Branch-specific statistics
        $stats = [
            'total_students' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM students 
                 WHERE branch_id = ? AND is_active = 1", 
                [$branchId]
            )['count'],
            'total_teachers' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM teachers t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE u.branch_id = ? AND u.is_active = 1", 
                [$branchId]
            )['count'],
            'total_employees' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM employees e 
                 JOIN users u ON e.user_id = u.id 
                 WHERE u.branch_id = ? AND u.is_active = 1", 
                [$branchId]
            )['count'],
            'total_classes' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM classes 
                 WHERE branch_id = ? AND is_active = 1", 
                [$branchId]
            )['count'],
            'fee_collection_rate' => $this->calculateFeeCollectionRate($branchId),
            'attendance_rate' => $this->calculateBranchAttendanceRate($branchId),
            'monthly_revenue' => $this->db->selectOne(
                "SELECT COALESCE(SUM(fp.amount), 0) as total 
                 FROM fee_payments fp 
                 JOIN student_fees sf ON fp.student_fee_id = sf.id 
                 JOIN students s ON sf.student_id = s.id 
                 WHERE s.branch_id = ? 
                 AND MONTH(fp.payment_date) = MONTH(CURDATE()) 
                 AND YEAR(fp.payment_date) = YEAR(CURDATE())", 
                [$branchId]
            )['total']
        ];
        
        // Class-wise student distribution
        $classDistribution = $this->db->select(
            "SELECT c.id, c.class_name, c.class_level,
                    COUNT(s.id) as student_count,
                    c.capacity,
                    ROUND((COUNT(s.id) / c.capacity) * 100, 2) as occupancy_rate
             FROM classes c 
             LEFT JOIN students s ON c.id = s.current_class_id AND s.is_active = 1 
             WHERE c.branch_id = ? AND c.is_active = 1 
             GROUP BY c.id 
             ORDER BY c.class_level", 
            [$branchId]
        );
        
        // Daily attendance for today
        $todayAttendance = $this->db->select(
            "SELECT c.class_name,
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN sa.status = 'late' THEN 1 END) as late,
                    COUNT(CASE WHEN sa.status = 'excused' THEN 1 END) as excused
             FROM classes c
             JOIN sections sec ON c.id = sec.class_id
             JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1
             LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = CURDATE()
             WHERE c.branch_id = ?
             GROUP BY c.id
             ORDER BY c.class_level", 
            [$branchId]
        );
        
        // Recent fee collections
        $recentPayments = $this->db->select(
            "SELECT fp.*, u.first_name as student_first_name, u.last_name as student_last_name,
                    fc.category_name, c.class_name
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             JOIN classes c ON s.current_class_id = c.id
             WHERE s.branch_id = ? 
             ORDER BY fp.payment_date DESC 
             LIMIT 10", 
            [$branchId]
        );
        
        // Upcoming exams
        $upcomingExams = $this->db->select(
            "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name,
                    t.user_id as teacher_user_id,
                    ut.first_name as teacher_first_name, ut.last_name as teacher_last_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             LEFT JOIN teachers t ON e.invigilator_id = t.id
             LEFT JOIN users ut ON t.user_id = ut.id
             WHERE c.branch_id = ? 
             AND e.exam_date >= CURDATE() 
             AND e.status = 'scheduled' 
             ORDER BY e.exam_date 
             LIMIT 5", 
            [$branchId]
        );
        
        $this->view('dashboard/branch_admin', compact(
            'stats', 'classDistribution', 'todayAttendance', 
            'recentPayments', 'upcomingExams', 'user'
        ));
    }
    
    private function principalDashboard($user)
    {
        $branchId = $user['branch_id'];
        
        // Similar to branch admin but with more academic focus
        $stats = [
            'total_students' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM students WHERE branch_id = ? AND is_active = 1", 
                [$branchId]
            )['count'],
            'total_teachers' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM teachers t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE u.branch_id = ? AND u.is_active = 1", 
                [$branchId]
            )['count'],
            'attendance_rate' => $this->calculateBranchAttendanceRate($branchId),
            'academic_performance' => $this->calculateAcademicPerformance($branchId)
        ];
        
        // Teacher performance metrics
        $teacherPerformance = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name, d.name as department_name,
                    COUNT(DISTINCT cs.class_id) as classes_assigned,
                    COUNT(DISTINCT cs.subject_id) as subjects_assigned,
                    AVG(er.marks_obtained) as avg_student_marks
             FROM teachers t 
             JOIN users u ON t.user_id = u.id 
             LEFT JOIN departments d ON t.department_id = d.id
             LEFT JOIN class_subjects cs ON t.id = cs.teacher_id AND cs.is_active = 1
             LEFT JOIN exams e ON cs.subject_id = e.subject_id 
             LEFT JOIN exam_results er ON e.id = er.exam_id
             WHERE u.branch_id = ? AND u.is_active = 1
             GROUP BY t.id
             ORDER BY avg_student_marks DESC", 
            [$branchId]
        );
        
        $this->view('dashboard/principal', compact('stats', 'teacherPerformance', 'user'));
    }
    
    private function academicCoordinatorDashboard($user)
    {
        $branchId = $user['branch_id'];
        
        // Academic focused statistics
        $stats = [
            'active_academic_year' => $this->getCurrentAcademicYear($branchId),
            'total_classes' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM classes WHERE branch_id = ? AND is_active = 1", 
                [$branchId]
            )['count'],
            'total_subjects' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM subjects s 
                 JOIN departments d ON s.department_id = d.id 
                 WHERE d.branch_id = ? AND s.is_active = 1", 
                [$branchId]
            )['count'],
            'total_timetable_entries' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM timetables tt 
                 JOIN sections sec ON tt.section_id = sec.id 
                 JOIN classes c ON sec.class_id = c.id 
                 WHERE c.branch_id = ?", 
                [$branchId]
            )['count']
        ];
        
        // Timetable conflicts
        $timetableConflicts = $this->detectTimetableConflicts($branchId);
        
        $this->view('dashboard/academic_coordinator', compact('stats', 'timetableConflicts', 'user'));
    }
    
    private function accountantDashboard($user)
    {
        $branchId = $user['branch_id'];
        
        // Financial statistics
        $stats = [
            'total_revenue_today' => $this->db->selectOne(
                "SELECT COALESCE(SUM(fp.amount), 0) as total 
                 FROM fee_payments fp 
                 JOIN student_fees sf ON fp.student_fee_id = sf.id 
                 JOIN students s ON sf.student_id = s.id 
                 WHERE s.branch_id = ? AND DATE(fp.payment_date) = CURDATE()", 
                [$branchId]
            )['total'],
            'total_revenue_month' => $this->db->selectOne(
                "SELECT COALESCE(SUM(fp.amount), 0) as total 
                 FROM fee_payments fp 
                 JOIN student_fees sf ON fp.student_fee_id = sf.id 
                 JOIN students s ON sf.student_id = s.id 
                 WHERE s.branch_id = ? 
                 AND MONTH(fp.payment_date) = MONTH(CURDATE()) 
                 AND YEAR(fp.payment_date) = YEAR(CURDATE())", 
                [$branchId]
            )['total'],
            'pending_fees' => $this->db->selectOne(
                "SELECT COALESCE(SUM(sf.balance_amount), 0) as total 
                 FROM student_fees sf 
                 JOIN students s ON sf.student_id = s.id 
                 WHERE s.branch_id = ? AND sf.status IN ('pending', 'partial', 'overdue')", 
                [$branchId]
            )['total'],
            'overdue_fees' => $this->db->selectOne(
                "SELECT COALESCE(SUM(sf.balance_amount), 0) as total 
                 FROM student_fees sf 
                 JOIN students s ON sf.student_id = s.id 
                 WHERE s.branch_id = ? AND sf.status = 'overdue'", 
                [$branchId]
            )['total'],
            'total_expenses_month' => $this->db->selectOne(
                "SELECT COALESCE(SUM(amount), 0) as total 
                 FROM expenses 
                 WHERE branch_id = ? 
                 AND MONTH(expense_date) = MONTH(CURDATE()) 
                 AND YEAR(expense_date) = YEAR(CURDATE())
                 AND status = 'paid'", 
                [$branchId]
            )['total']
        ];
        
        // Fee collection by class
        $feeCollectionByClass = $this->db->select(
            "SELECT c.class_name,
                    COUNT(DISTINCT sf.student_id) as total_students,
                    COALESCE(SUM(sf.net_payable), 0) as total_fees,
                    COALESCE(SUM(sf.paid_amount), 0) as collected,
                    COALESCE(SUM(sf.balance_amount), 0) as pending,
                    ROUND((COALESCE(SUM(sf.paid_amount), 0) / COALESCE(SUM(sf.net_payable), 1)) * 100, 2) as collection_rate
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN classes c ON s.current_class_id = c.id 
             WHERE s.branch_id = ? AND sf.status != 'waived'
             GROUP BY c.id
             ORDER BY collection_rate", 
            [$branchId]
        );
        
        // Daily collection trend
        $dailyCollection = $this->db->select(
            "SELECT DATE(payment_date) as date, SUM(amount) as total 
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? 
             AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY date 
             ORDER BY date", 
            [$branchId]
        );
        
        $this->view('dashboard/accountant', compact(
            'stats', 'feeCollectionByClass', 'dailyCollection', 'user'
        ));
    }
    
    private function teacherDashboard($user)
    {
        // Get teacher ID
        $teacher = $this->db->selectOne(
            "SELECT id, department_id FROM teachers WHERE user_id = ?", 
            [$user['id']]
        );
        
        if (!$teacher) {
            $this->session->flash('error', 'اطلاعات معلم یافت نشد');
            $this->redirect('/profile');
            return;
        }
        
        $teacherId = $teacher['id'];
        
        // Teacher statistics
        $stats = [
            'assigned_classes' => $this->db->selectOne(
                "SELECT COUNT(DISTINCT cs.class_id) as count 
                 FROM class_subjects cs 
                 WHERE cs.teacher_id = ? AND cs.is_active = 1", 
                [$teacherId]
            )['count'],
            'assigned_subjects' => $this->db->selectOne(
                "SELECT COUNT(DISTINCT cs.subject_id) as count 
                 FROM class_subjects cs 
                 WHERE cs.teacher_id = ? AND cs.is_active = 1", 
                [$teacherId]
            )['count'],
            'total_students' => $this->db->selectOne(
                "SELECT COUNT(DISTINCT s.id) as count 
                 FROM class_subjects cs 
                 JOIN sections sec ON cs.class_id = sec.class_id
                 JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1
                 WHERE cs.teacher_id = ? AND cs.is_active = 1", 
                [$teacherId]
            )['count'],
            'today_periods' => $this->db->selectOne(
                "SELECT COUNT(*) as count 
                 FROM timetables 
                 WHERE teacher_id = ? AND day_of_week = DAYNAME(CURDATE())", 
                [$teacherId]
            )['count']
        ];
        
        // Today's timetable
        $todayTimetable = $this->db->select(
            "SELECT tt.*, sec.section_name, s.subject_name, 
                    c.class_name, c.class_level,
                    sem.semester_name
             FROM timetables tt 
             JOIN sections sec ON tt.section_id = sec.id 
             JOIN subjects s ON tt.subject_id = s.id 
             JOIN classes c ON sec.class_id = c.id 
             JOIN semesters sem ON tt.semester_id = sem.id
             WHERE tt.teacher_id = ? 
             AND tt.day_of_week = DAYNAME(CURDATE()) 
             ORDER BY tt.start_time", 
            [$teacherId]
        );
        
        // Recent attendance summary
        $attendanceSummary = $this->db->select(
            "SELECT c.class_name, sec.section_name,
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(CASE WHEN sa.status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN sa.status = 'absent' THEN 1 END) as absent
             FROM class_subjects cs 
             JOIN classes c ON cs.class_id = c.id 
             JOIN sections sec ON c.id = sec.class_id
             JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1
             LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                 AND sa.attendance_date = CURDATE()
             WHERE cs.teacher_id = ? AND cs.is_active = 1
             GROUP BY sec.id", 
            [$teacherId]
        );
        
        // Upcoming exams to invigilate
        $upcomingExams = $this->db->select(
            "SELECT e.*, et.name as exam_type, s.subject_name, 
                    c.class_name, sec.section_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             LEFT JOIN sections sec ON c.id = sec.class_id
             WHERE e.invigilator_id = ? 
             AND e.exam_date >= CURDATE() 
             AND e.status = 'scheduled'
             ORDER BY e.exam_date 
             LIMIT 5", 
            [$teacherId]
        );
        
        $this->view('dashboard/teacher', compact(
            'stats', 'todayTimetable', 'attendanceSummary', 
            'upcomingExams', 'user', 'teacher'
        ));
    }
    
    private function librarianDashboard($user)
    {
        $branchId = $user['branch_id'];
        
        $stats = [
            'total_books' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM library_books WHERE branch_id = ? AND is_active = 1", 
                [$branchId]
            )['count'],
            'available_books' => $this->db->selectOne(
                "SELECT SUM(available_copies) as count FROM library_books WHERE branch_id = ?", 
                [$branchId]
            )['count'],
            'books_borrowed_today' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM book_borrowings 
                 WHERE borrow_date = CURDATE()", 
                []
            )['count'],
            'overdue_books' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM book_borrowings 
                 WHERE status = 'overdue' AND due_date < CURDATE()", 
                []
            )['count'],
            'total_fines' => $this->db->selectOne(
                "SELECT COALESCE(SUM(fine_amount), 0) as total FROM book_borrowings 
                 WHERE fine_paid = 0", 
                []
            )['total']
        ];
        
        // Recent borrowings
        $recentBorrowings = $this->db->select(
            "SELECT bb.*, lb.title as book_title, lb.author,
                    u.first_name, u.last_name
             FROM book_borrowings bb 
             JOIN library_books lb ON bb.book_id = lb.id 
             JOIN users u ON bb.user_id = u.id
             WHERE lb.branch_id = ?
             ORDER BY bb.borrow_date DESC 
             LIMIT 10", 
            [$branchId]
        );
        
        // Popular books
        $popularBooks = $this->db->select(
            "SELECT lb.id, lb.title, lb.author, lb.isbn,
                    COUNT(bb.id) as borrow_count
             FROM library_books lb 
             LEFT JOIN book_borrowings bb ON lb.id = bb.book_id
             WHERE lb.branch_id = ? AND lb.is_active = 1
             GROUP BY lb.id
             ORDER BY borrow_count DESC
             LIMIT 10", 
            [$branchId]
        );
        
        $this->view('dashboard/librarian', compact(
            'stats', 'recentBorrowings', 'popularBooks', 'user'
        ));
    }
    
    private function transportManagerDashboard($user)
    {
        $branchId = $user['branch_id'];
        
        $stats = [
            'total_vehicles' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM vehicles WHERE branch_id = ? AND is_active = 1", 
                [$branchId]
            )['count'],
            'total_routes' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM transport_routes WHERE branch_id = ? AND is_active = 1", 
                [$branchId]
            )['count'],
            'total_drivers' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM drivers d 
                 JOIN employees e ON d.employee_id = e.id
                 JOIN users u ON e.user_id = u.id
                 WHERE u.branch_id = ? AND d.is_active = 1", 
                [$branchId]
            )['count'],
            'students_using_transport' => $this->db->selectOne(
                "SELECT COUNT(*) as count FROM student_transport st 
                 JOIN students s ON st.student_id = s.id 
                 WHERE s.branch_id = ? AND st.is_active = 1", 
                [$branchId]
            )['count']
        ];
        
        // Route utilization
        $routeUtilization = $this->db->select(
            "SELECT tr.id, tr.route_name, tr.route_code,
                    v.vehicle_number,
                    COUNT(st.id) as students_count
             FROM transport_routes tr 
             LEFT JOIN vehicles v ON tr.vehicle_id = v.id
             LEFT JOIN student_transport st ON tr.id = st.route_id AND st.is_active = 1
             WHERE tr.branch_id = ? AND tr.is_active = 1
             GROUP BY tr.id
             ORDER BY students_count DESC", 
            [$branchId]
        );
        
        $this->view('dashboard/transport_manager', compact(
            'stats', 'routeUtilization', 'user'
        ));
    }
    
    private function parentDashboard($user)
    {
        // Get parent ID
        $parent = $this->db->selectOne(
            "SELECT id FROM parents WHERE user_id = ?", 
            [$user['id']]
        );
        
        if (!$parent) {
            $this->session->flash('error', 'اطلاعات والدین یافت نشد');
            $this->redirect('/profile');
            return;
        }
        
        // Get children
        $children = $this->db->select(
            "SELECT s.*, u.first_name, u.last_name, u.profile_photo,
                    c.class_name, c.class_level,
                    sec.section_name
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             JOIN student_parent sp ON s.id = sp.student_id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id
             WHERE sp.parent_id = ? AND s.is_active = 1
             ORDER BY c.class_level, u.first_name", 
            [$parent['id']]
        );
        
        // Get each child's attendance and results
        $childrenData = [];
        foreach ($children as $child) {
            $attendance = $this->getStudentAttendanceStats($child['id']);
            $results = $this->getStudentLatestResults($child['id']);
            $fees = $this->getStudentFeeStatus($child['id']);
            
            $childrenData[] = [
                'child' => $child,
                'attendance' => $attendance,
                'results' => $results,
                'fees' => $fees
            ];
        }
        
        $this->view('dashboard/parent', compact('childrenData', 'user', 'parent'));
    }
    
    private function studentDashboard($user)
    {
        // Get student ID
        $student = $this->db->selectOne(
            "SELECT s.*, c.class_name, sec.section_name
             FROM students s 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id
             WHERE s.user_id = ? AND s.is_active = 1", 
            [$user['id']]
        );
        
        if (!$student) {
            $this->session->flash('error', 'اطلاعات دانش‌آموز یافت نشد');
            $this->redirect('/profile');
            return;
        }
        
        // Attendance statistics
        $attendanceStats = $this->getStudentAttendanceStats($student['id']);
        
        // Recent results
        $recentResults = $this->getStudentLatestResults($student['id'], 10);
        
        // Today's timetable
        $todayTimetable = $this->db->select(
            "SELECT tt.*, s.subject_name
             FROM timetables tt 
             JOIN subjects s ON tt.subject_id = s.id 
             WHERE tt.section_id = ? 
             AND tt.day_of_week = DAYNAME(CURDATE()) 
             ORDER BY tt.start_time", 
            [$student['current_section_id']]
        );
        
        // Fee status
        $feeStatus = $this->getStudentFeeStatus($student['id']);
        
        // Upcoming exams
        $upcomingExams = $this->db->select(
            "SELECT e.*, et.name as exam_type, s.subject_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             WHERE e.class_id = ? 
             AND e.exam_date >= CURDATE() 
             AND e.status = 'scheduled'
             ORDER BY e.exam_date 
             LIMIT 5", 
            [$student['current_class_id']]
        );
        
        // Notifications
        $notifications = $this->db->select(
            "SELECT n.*, nr.read_at
             FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id 
                 AND nr.user_id = ?
             WHERE (n.recipient_type = 'all')
             OR (n.recipient_type = 'class' AND n.recipient_id = ?)
             OR (n.recipient_type = 'section' AND n.recipient_id = ?)
             OR (n.recipient_type = 'individual' AND n.recipient_id = ?)
             ORDER BY n.created_at DESC 
             LIMIT 10", 
            [$user['id'], $student['current_class_id'], 
             $student['current_section_id'], $user['id']]
        );
        
        $this->view('dashboard/student', compact(
            'student', 'attendanceStats', 'recentResults', 
            'todayTimetable', 'feeStatus', 'upcomingExams', 
            'notifications', 'user'
        ));
    }
    
    private function defaultDashboard($user)
    {
        $this->view('dashboard/default', [
            'title' => 'داشبورد',
            'user' => $user
        ]);
    }
    
    // Helper methods
    private function calculateTodayAttendancePercentage()
    {
        $result = $this->db->selectOne(
            "SELECT 
                COUNT(CASE WHEN sa.status IN ('present', 'late') THEN 1 END) as present_count,
                COUNT(*) as total_count
             FROM student_attendance sa 
             WHERE sa.attendance_date = CURDATE()"
        );
        
        if ($result['total_count'] > 0) {
            return round(($result['present_count'] / $result['total_count']) * 100, 2);
        }
        
        return 0;
    }
    
    private function calculateFeeCollectionRate($branchId)
    {
        $result = $this->db->selectOne(
            "SELECT 
                COALESCE(SUM(sf.paid_amount), 0) as collected,
                COALESCE(SUM(sf.net_payable), 0) as total
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND sf.status != 'waived'", 
            [$branchId]
        );
        
        if ($result['total'] > 0) {
            return round(($result['collected'] / $result['total']) * 100, 2);
        }
        
        return 0;
    }
    
    private function calculateBranchAttendanceRate($branchId)
    {
        $result = $this->db->selectOne(
            "SELECT 
                COUNT(CASE WHEN sa.status IN ('present', 'late') THEN 1 END) as present_count,
                COUNT(*) as total_count
             FROM student_attendance sa 
             JOIN students s ON sa.student_id = s.id 
             WHERE s.branch_id = ? 
             AND sa.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", 
            [$branchId]
        );
        
        if ($result['total_count'] > 0) {
            return round(($result['present_count'] / $result['total_count']) * 100, 2);
        }
        
        return 0;
    }
    
    private function calculateAcademicPerformance($branchId)
    {
        $result = $this->db->selectOne(
            "SELECT AVG(er.marks_obtained) as avg_marks
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN classes c ON e.class_id = c.id 
             WHERE c.branch_id = ? 
             AND e.exam_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)", 
            [$branchId]
        );
        
        return round($result['avg_marks'] ?? 0, 2);
    }
    
    private function getCurrentAcademicYear($branchId)
    {
        return $this->db->selectOne(
            "SELECT * FROM academic_years 
             WHERE branch_id = ? AND is_current = 1 AND status = 'active'", 
            [$branchId]
        );
    }
    
    private function detectTimetableConflicts($branchId)
    {
        return $this->db->select(
            "SELECT tt1.id as conflict1_id, tt2.id as conflict2_id,
                    tt1.day_of_week, tt1.start_time, tt1.end_time,
                    t1.first_name as teacher1_name, t1.last_name as teacher1_last,
                    t2.first_name as teacher2_name, t2.last_name as teacher2_last,
                    sec1.section_name as section1, sec2.section_name as section2
             FROM timetables tt1 
             JOIN timetables tt2 ON tt1.id < tt2.id 
                 AND tt1.day_of_week = tt2.day_of_week
                 AND ((tt1.start_time BETWEEN tt2.start_time AND tt2.end_time)
                     OR (tt2.start_time BETWEEN tt1.start_time AND tt1.end_time))
             JOIN sections sec1 ON tt1.section_id = sec1.id 
             JOIN sections sec2 ON tt2.section_id = sec2.id 
             JOIN classes c1 ON sec1.class_id = c1.id 
             JOIN classes c2 ON sec2.class_id = c2.id 
             JOIN users t1 ON tt1.teacher_id = t1.id 
             JOIN users t2 ON tt2.teacher_id = t2.id 
             WHERE c1.branch_id = ? AND c2.branch_id = ?
             AND (tt1.teacher_id = tt2.teacher_id OR tt1.section_id = tt2.section_id)", 
            [$branchId, $branchId]
        );
    }
    
    private function calculateStorageUsed()
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        $size = 0;
        
        if (is_dir($uploadDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadDir)) as $file) {
                $size += $file->getSize();
            }
        }
        
        return round($size / 1024 / 1024, 2); // Convert to MB
    }
    
    private function getDatabaseSize()
    {
        $result = $this->db->selectOne(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size 
             FROM information_schema.tables 
             WHERE table_schema = 'school_management_oltp'"
        );
        
        return $result['size'] ?? 0;
    }
    
    private function getStudentAttendanceStats($studentId)
    {
        $total = $this->db->selectOne(
            "SELECT COUNT(*) as count 
             FROM student_attendance 
             WHERE student_id = ? 
             AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", 
            [$studentId]
        );
        
        $present = $this->db->selectOne(
            "SELECT COUNT(*) as count 
             FROM student_attendance 
             WHERE student_id = ? 
             AND status IN ('present', 'late')
             AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", 
            [$studentId]
        );
        
        $absent = $this->db->selectOne(
            "SELECT COUNT(*) as count 
             FROM student_attendance 
             WHERE student_id = ? 
             AND status = 'absent'
             AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", 
            [$studentId]
        );
        
        return [
            'total' => $total['count'],
            'present' => $present['count'],
            'absent' => $absent['count'],
            'percentage' => $total['count'] > 0 ? 
                round(($present['count'] / $total['count']) * 100, 2) : 0
        ];
    }
    
    private function getStudentLatestResults($studentId, $limit = 10)
    {
        return $this->db->select(
            "SELECT er.*, s.subject_name, e.exam_date, et.name as exam_type,
                    e.total_marks, e.passing_marks,
                    CASE 
                        WHEN er.marks_obtained >= e.passing_marks THEN 'Pass'
                        ELSE 'Fail'
                    END as result_status
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN exam_types et ON e.exam_type_id = et.id 
             WHERE er.student_id = ? 
             ORDER BY e.exam_date DESC 
             LIMIT ?", 
            [$studentId, $limit]
        );
    }
    
    private function getStudentFeeStatus($studentId)
    {
        return $this->db->select(
            "SELECT sf.*, fc.category_name, fs.due_day
             FROM student_fees sf 
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             WHERE sf.student_id = ? 
             AND sf.status != 'paid'
             ORDER BY sf.due_date", 
            [$studentId]
        );
    }
}