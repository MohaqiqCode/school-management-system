<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class BranchController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        
        $sql = "SELECT b.*, 
                       (SELECT COUNT(*) FROM students WHERE branch_id = b.id AND is_active = 1) as student_count,
                       (SELECT COUNT(*) FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = b.id AND u.is_active = 1) as teacher_count,
                       (SELECT COUNT(*) FROM employees e JOIN users u ON e.user_id = u.id WHERE u.branch_id = b.id AND u.is_active = 1) as employee_count,
                       (SELECT COALESCE(SUM(fp.amount), 0) FROM fee_payments fp 
                        JOIN student_fees sf ON fp.student_fee_id = sf.id 
                        JOIN students s ON sf.student_id = s.id 
                        WHERE s.branch_id = b.id AND MONTH(fp.payment_date) = MONTH(CURDATE()) AND YEAR(fp.payment_date) = YEAR(CURDATE())) as monthly_revenue
                FROM branches b 
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (b.name LIKE ? OR b.branch_code LIKE ? OR b.city LIKE ? OR b.email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $sql .= " ORDER BY b.name ASC";
        
        // Pagination
        $countSql = "SELECT COUNT(*) as total FROM branches b WHERE 1=1";
        if ($search) {
            $countSql .= " AND (b.name LIKE ? OR b.branch_code LIKE ? OR b.city LIKE ? OR b.email LIKE ?)";
        }
        
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        $branches = $this->db->select($sql, $params);
        
        $this->view('branches/index', [
            'title' => 'مدیریت شعب',
            'branches' => $branches,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search
        ]);
    }
    
    public function create()
    {
        $this->view('branches/create', [
            'title' => 'ایجاد شعبه جدید'
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // CSRF Protection
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->session->flash('error', 'توکن امنیتی نامعتبر است');
            $this->redirect('/branches/create');
            return;
        }
        
        // Validate inputs
        $errors = $this->validateBranchData($data);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/branches/create');
            return;
        }
        
        // Check unique branch code
        $existingBranch = $this->db->selectOne(
            "SELECT id FROM branches WHERE branch_code = ?",
            [$data['branch_code']]
        );
        
        if ($existingBranch) {
            $this->session->flash('errors', ['branch_code' => 'کد شعبه قبلاً ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect('/branches/create');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create branch
            $branchId = $this->db->insert('branches', [
                'branch_code' => strtoupper($data['branch_code']),
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'Iran',
                'postal_code' => $data['postal_code'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'principal_name' => $data['principal_name'] ?? null,
                'established_date' => $data['established_date'] ?? null,
                'total_capacity' => $data['total_capacity'] ?? 500,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logoPath = $this->uploadLogo($_FILES['logo'], $branchId);
                if ($logoPath) {
                    $this->db->update('branches', ['logo_path' => $logoPath], 'id = ?', [$branchId]);
                }
            }
            
            // Create default departments for this branch
            $this->createDefaultDepartments($branchId);
            
            $this->db->commit();
            
            // Log activity
            $this->logActivity('branches', 'create', "شعبه جدید '{$data['name']}' با کد {$data['branch_code']} ایجاد شد");
            
            $this->session->flash('success', 'شعبه جدید با موفقیت ایجاد شد');
            $this->redirect('/branches');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در ایجاد شعبه: ' . $e->getMessage());
            $this->session->flash('old', $data);
            $this->redirect('/branches/create');
        }
    }
    
    public function show($id)
    {
        $branch = $this->getBranchWithStats($id);
        
        if (!$branch) {
            $this->session->flash('error', 'شعبه مورد نظر یافت نشد');
            $this->redirect('/branches');
            return;
        }
        
        // Get detailed statistics
        $stats = $this->getBranchDetailedStats($id);
        
        // Get recent students
        $recentStudents = $this->db->select(
            "SELECT s.*, u.first_name, u.last_name, u.profile_photo, c.class_name
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             WHERE s.branch_id = ? 
             ORDER BY s.created_at DESC 
             LIMIT 10",
            [$id]
        );
        
        // Get class distribution
        $classDistribution = $this->db->select(
            "SELECT c.class_name, c.class_level,
                    COUNT(s.id) as student_count,
                    c.capacity,
                    ROUND((COUNT(s.id) / c.capacity) * 100, 2) as occupancy_rate
             FROM classes c 
             LEFT JOIN students s ON c.id = s.current_class_id AND s.is_active = 1 
             WHERE c.branch_id = ? AND c.is_active = 1 
             GROUP BY c.id 
             ORDER BY c.class_level",
            [$id]
        );
        
        // Get monthly revenue
        $monthlyRevenue = $this->db->select(
            "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month,
                    DATE_FORMAT(payment_date, '%M %Y') as month_name,
                    SUM(amount) as total,
                    COUNT(*) as transaction_count
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? 
             AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY month 
             ORDER BY month",
            [$id]
        );
        
        $this->view('branches/show', [
            'title' => 'جزئیات شعبه',
            'branch' => $branch,
            'stats' => $stats,
            'recentStudents' => $recentStudents,
            'classDistribution' => $classDistribution,
            'monthlyRevenue' => $monthlyRevenue
        ]);
    }
    
    public function edit($id)
    {
        $branch = $this->db->selectOne("SELECT * FROM branches WHERE id = ?", [$id]);
        
        if (!$branch) {
            $this->session->flash('error', 'شعبه مورد نظر یافت نشد');
            $this->redirect('/branches');
            return;
        }
        
        $this->view('branches/edit', [
            'title' => 'ویرایش شعبه',
            'branch' => $branch
        ]);
    }
    
    public function update($id)
    {
        $branch = $this->db->selectOne("SELECT * FROM branches WHERE id = ?", [$id]);
        
        if (!$branch) {
            $this->session->flash('error', 'شعبه مورد نظر یافت نشد');
            $this->redirect('/branches');
            return;
        }
        
        $data = $_POST;
        
        // CSRF Protection
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->session->flash('error', 'توکن امنیتی نامعتبر است');
            $this->redirect("/branches/{$id}/edit");
            return;
        }
        
        // Validate
        $errors = $this->validateBranchData($data, $id);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect("/branches/{$id}/edit");
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update branch
            $updateData = [
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'Iran',
                'postal_code' => $data['postal_code'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'principal_name' => $data['principal_name'] ?? null,
                'established_date' => $data['established_date'] ?? null,
                'total_capacity' => $data['total_capacity'] ?? 500,
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->update('branches', $updateData, 'id = ?', [$id]);
            
            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                // Delete old logo if exists
                if ($branch['logo_path']) {
                    $this->deleteFile($branch['logo_path']);
                }
                
                $logoPath = $this->uploadLogo($_FILES['logo'], $id);
                if ($logoPath) {
                    $this->db->update('branches', ['logo_path' => $logoPath], 'id = ?', [$id]);
                }
            }
            
            // Handle logo deletion
            if (isset($data['delete_logo']) && $data['delete_logo'] == 1) {
                if ($branch['logo_path']) {
                    $this->deleteFile($branch['logo_path']);
                }
                $this->db->update('branches', ['logo_path' => null], 'id = ?', [$id]);
            }
            
            $this->db->commit();
            
            $this->logActivity('branches', 'update', "شعبه '{$data['name']}' به‌روزرسانی شد");
            
            $this->session->flash('success', 'اطلاعات شعبه با موفقیت به‌روزرسانی شد');
            $this->redirect('/branches');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در به‌روزرسانی شعبه: ' . $e->getMessage());
            $this->redirect("/branches/{$id}/edit");
        }
    }
    
    public function delete($id)
    {
        $branch = $this->db->selectOne("SELECT * FROM branches WHERE id = ?", [$id]);
        
        if (!$branch) {
            $this->json(['success' => false, 'message' => 'شعبه مورد نظر یافت نشد'], 404);
            return;
        }
        
        // Check if branch has associated data
        $associations = $this->checkBranchAssociations($id);
        
        if (!empty($associations)) {
            $message = 'این شعبه دارای موارد زیر است و قابل حذف نیست: ' . implode('، ', $associations);
            $this->json(['success' => false, 'message' => $message]);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Delete logo file
            if ($branch['logo_path']) {
                $this->deleteFile($branch['logo_path']);
            }
            
            // Delete branch
            $this->db->delete('branches', 'id = ?', [$id]);
            
            $this->db->commit();
            
            $this->logActivity('branches', 'delete', "شعبه '{$branch['name']}' حذف شد");
            
            $this->json(['success' => true, 'message' => 'شعبه با موفقیت حذف شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در حذف شعبه: ' . $e->getMessage()]);
        }
    }
    
    public function settings($id)
    {
        $branch = $this->db->selectOne("SELECT * FROM branches WHERE id = ?", [$id]);
        
        if (!$branch) {
            $this->session->flash('error', 'شعبه مورد نظر یافت نشد');
            $this->redirect('/branches');
            return;
        }
        
        // Get grading scales for this branch
        $gradingScales = $this->db->select(
            "SELECT * FROM grading_scales WHERE branch_id = ? ORDER BY min_percentage DESC",
            [$id]
        );
        
        // Get fee categories for this branch
        $feeCategories = $this->db->select(
            "SELECT * FROM fee_categories WHERE branch_id = ? ORDER BY category_name",
            [$id]
        );
        
        $this->view('branches/settings', [
            'title' => 'تنظیمات شعبه',
            'branch' => $branch,
            'gradingScales' => $gradingScales,
            'feeCategories' => $feeCategories
        ]);
    }
    
    public function updateSettings($id)
    {
        $branch = $this->db->selectOne("SELECT * FROM branches WHERE id = ?", [$id]);
        
        if (!$branch) {
            $this->session->flash('error', 'شعبه مورد نظر یافت نشد');
            $this->redirect('/branches');
            return;
        }
        
        $data = $_POST;
        
        try {
            $this->db->beginTransaction();
            
            // Update grading scales
            if (isset($data['grades']) && is_array($data['grades'])) {
                // Delete old scales
                $this->db->delete('grading_scales', 'branch_id = ?', [$id]);
                
                // Insert new scales
                foreach ($data['grades'] as $grade) {
                    if (!empty($grade['grade_name']) && isset($grade['min_percentage']) && isset($grade['max_percentage'])) {
                        $this->db->insert('grading_scales', [
                            'branch_id' => $id,
                            'grade_name' => $grade['grade_name'],
                            'min_percentage' => $grade['min_percentage'],
                            'max_percentage' => $grade['max_percentage'],
                            'grade_point' => $grade['grade_point'] ?? 0,
                            'description' => $grade['description'] ?? '',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            // Update fee categories
            if (isset($data['fee_categories']) && is_array($data['fee_categories'])) {
                foreach ($data['fee_categories'] as $category) {
                    if (!empty($category['category_name'])) {
                        if (!empty($category['id'])) {
                            // Update existing
                            $this->db->update('fee_categories', [
                                'category_name' => $category['category_name'],
                                'description' => $category['description'] ?? '',
                                'is_recurring' => isset($category['is_recurring']) ? 1 : 0,
                                'frequency' => $category['frequency'] ?? 'monthly'
                            ], 'id = ? AND branch_id = ?', [$category['id'], $id]);
                        } else {
                            // Create new
                            $this->db->insert('fee_categories', [
                                'branch_id' => $id,
                                'category_name' => $category['category_name'],
                                'description' => $category['description'] ?? '',
                                'is_recurring' => isset($category['is_recurring']) ? 1 : 0,
                                'frequency' => $category['frequency'] ?? 'monthly',
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }
            
            $this->db->commit();
            
            $this->logActivity('branches', 'settings_update', "تنظیمات شعبه '{$branch['name']}' به‌روزرسانی شد");
            
            $this->session->flash('success', 'تنظیمات شعبه با موفقیت به‌روزرسانی شد');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در به‌روزرسانی تنظیمات: ' . $e->getMessage());
        }
        
        $this->redirect("/branches/{$id}/settings");
    }
    
    private function validateBranchData($data, $branchId = null)
    {
        $errors = [];
        
        if (empty($data['branch_code'])) {
            $errors['branch_code'] = 'کد شعبه الزامی است';
        } elseif (!preg_match('/^[A-Za-z0-9\-_]+$/', $data['branch_code'])) {
            $errors['branch_code'] = 'کد شعبه فقط می‌تواند شامل حروف، اعداد، خط تیره و زیرخط باشد';
        } elseif (strlen($data['branch_code']) > 20) {
            $errors['branch_code'] = 'کد شعبه نباید بیشتر از 20 کاراکتر باشد';
        }
        
        if (empty($data['name'])) {
            $errors['name'] = 'نام شعبه الزامی است';
        } elseif (strlen($data['name']) > 200) {
            $errors['name'] = 'نام شعبه نباید بیشتر از 200 کاراکتر باشد';
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'فرمت ایمیل نامعتبر است';
        }
        
        if (!empty($data['phone']) && !preg_match('/^[0-9\-\(\)\/\+\s]*$/', $data['phone'])) {
            $errors['phone'] = 'فرمت شماره تماس نامعتبر است';
        }
        
        if (!empty($data['total_capacity']) && (!is_numeric($data['total_capacity']) || $data['total_capacity'] < 1)) {
            $errors['total_capacity'] = 'ظرفیت کل باید یک عدد مثبت باشد';
        }
        
        return $errors;
    }
    
    private function getBranchWithStats($id)
    {
        return $this->db->selectOne(
            "SELECT b.*,
                    (SELECT COUNT(*) FROM students WHERE branch_id = b.id AND is_active = 1) as student_count,
                    (SELECT COUNT(*) FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = b.id AND u.is_active = 1) as teacher_count,
                    (SELECT COUNT(*) FROM classes WHERE branch_id = b.id AND is_active = 1) as class_count,
                    (SELECT COUNT(*) FROM departments WHERE branch_id = b.id AND is_active = 1) as department_count,
                    (SELECT COALESCE(SUM(fp.amount), 0) FROM fee_payments fp 
                     JOIN student_fees sf ON fp.student_fee_id = sf.id 
                     JOIN students s ON sf.student_id = s.id 
                     WHERE s.branch_id = b.id AND YEAR(fp.payment_date) = YEAR(CURDATE())) as yearly_revenue
             FROM branches b 
             WHERE b.id = ?",
            [$id]
        );
    }
    
    private function getBranchDetailedStats($id)
    {
        $stats = [];
        
        // Attendance rate for current month
        $attendanceResult = $this->db->selectOne(
            "SELECT 
                COUNT(CASE WHEN sa.status IN ('present', 'late') THEN 1 END) as present_count,
                COUNT(*) as total_count
             FROM student_attendance sa 
             JOIN students s ON sa.student_id = s.id 
             WHERE s.branch_id = ? 
             AND MONTH(sa.attendance_date) = MONTH(CURDATE()) 
             AND YEAR(sa.attendance_date) = YEAR(CURDATE())",
            [$id]
        );
        
        $stats['attendance_rate'] = ($attendanceResult['total_count'] > 0) 
            ? round(($attendanceResult['present_count'] / $attendanceResult['total_count']) * 100, 2) 
            : 0;
        
        // Fee collection rate
        $feeResult = $this->db->selectOne(
            "SELECT 
                COALESCE(SUM(sf.paid_amount), 0) as collected,
                COALESCE(SUM(sf.net_payable), 0) as total
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND sf.status != 'waived'",
            [$id]
        );
        
        $stats['fee_collection_rate'] = ($feeResult['total'] > 0) 
            ? round(($feeResult['collected'] / $feeResult['total']) * 100, 2) 
            : 0;
        
        // Exam pass rate
        $examResult = $this->db->selectOne(
            "SELECT 
                COUNT(CASE WHEN er.marks_obtained >= e.passing_marks THEN 1 END) as passed,
                COUNT(*) as total
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN classes c ON e.class_id = c.id 
             WHERE c.branch_id = ? AND e.exam_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)",
            [$id]
        );
        
        $stats['exam_pass_rate'] = ($examResult['total'] > 0) 
            ? round(($examResult['passed'] / $examResult['total']) * 100, 2) 
            : 0;
        
        return $stats;
    }
    
    private function checkBranchAssociations($branchId)
    {
        $associations = [];
        
        // Check students
        $studentCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM students WHERE branch_id = ?",
            [$branchId]
        );
        if ($studentCount['count'] > 0) {
            $associations[] = $studentCount['count'] . ' دانش‌آموز';
        }
        
        // Check teachers
        $teacherCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = ?",
            [$branchId]
        );
        if ($teacherCount['count'] > 0) {
            $associations[] = $teacherCount['count'] . ' معلم';
        }
        
        // Check classes
        $classCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM classes WHERE branch_id = ?",
            [$branchId]
        );
        if ($classCount['count'] > 0) {
            $associations[] = $classCount['count'] . ' کلاس';
        }
        
        // Check departments
        $deptCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM departments WHERE branch_id = ?",
            [$branchId]
        );
        if ($deptCount['count'] > 0) {
            $associations[] = $deptCount['count'] . ' دپارتمان';
        }
        
        // Check users
        $userCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM users WHERE branch_id = ? AND deleted_at IS NULL",
            [$branchId]
        );
        if ($userCount['count'] > 0) {
            $associations[] = $userCount['count'] . ' کاربر';
        }
        
        return $associations;
    }
    
    private function createDefaultDepartments($branchId)
    {
        $defaultDepts = [
            ['code' => 'MATH', 'name' => 'Mathematics Department'],
            ['code' => 'SCI', 'name' => 'Science Department'],
            ['code' => 'ENG', 'name' => 'English Department'],
            ['code' => 'LIT', 'name' => 'Literature Department'],
            ['code' => 'HIST', 'name' => 'History Department'],
            ['code' => 'GEO', 'name' => 'Geography Department'],
            ['code' => 'ART', 'name' => 'Art Department'],
            ['code' => 'PE', 'name' => 'Physical Education Department'],
            ['code' => 'CS', 'name' => 'Computer Science Department'],
            ['code' => 'LANG', 'name' => 'Foreign Languages Department']
        ];
        
        foreach ($defaultDepts as $dept) {
            $this->db->insert('departments', [
                'branch_id' => $branchId,
                'department_code' => $dept['code'],
                'name' => $dept['name'],
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    private function uploadLogo($file, $branchId)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/logos/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('فرمت فایل مجاز نیست. فقط JPG، PNG، GIF و SVG مجاز هستند');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception('حجم فایل نباید بیشتر از 5 مگابایت باشد');
        }
        
        $filename = 'branch_' . $branchId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/assets/uploads/logos/' . $filename;
        }
        
        throw new \Exception('خطا در آپلود فایل');
    }
    
    private function deleteFile($filePath)
    {
        $fullPath = __DIR__ . '/../../public' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
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