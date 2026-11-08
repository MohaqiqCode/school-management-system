<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class TeacherController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        $departmentFilter = $_GET['department_id'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.gender, u.is_active as user_active,
                       d.name as department_name,
                       b.name as branch_name,
                       (SELECT COUNT(DISTINCT cs.class_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as class_count,
                       (SELECT COUNT(DISTINCT cs.subject_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as subject_count
                FROM teachers t 
                JOIN users u ON t.user_id = u.id 
                LEFT JOIN departments d ON t.department_id = d.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE u.deleted_at IS NULL";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($search) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR t.teacher_code LIKE ? OR u.email LIKE ? OR t.employee_id LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($departmentFilter) {
            $sql .= " AND t.department_id = ?";
            $params[] = $departmentFilter;
        }
        
        if ($statusFilter !== '') {
            $sql .= " AND u.is_active = ?";
            $params[] = $statusFilter;
        }
        
        $countSql = str_replace(
            "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo, u.gender, u.is_active as user_active, d.name as department_name, b.name as branch_name, (SELECT COUNT(DISTINCT cs.class_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as class_count, (SELECT COUNT(DISTINCT cs.subject_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as subject_count",
            "SELECT COUNT(*) as total",
            $sql
        );
        
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY u.last_name, u.first_name LIMIT {$perPage} OFFSET {$offset}";
        $teachers = $this->db->select($sql, $params);
        
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        
        $this->view('teachers/index', [
            'title' => 'مدیریت معلمان',
            'teachers' => $teachers,
            'departments' => $departments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'departmentFilter' => $departmentFilter,
            'statusFilter' => $statusFilter
        ]);
    }
    
    public function create()
    {
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        $branchId = $this->session->get('branch_id');
        
        $this->view('teachers/create', [
            'title' => 'ثبت معلم جدید',
            'departments' => $departments
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        $errors = $this->validateTeacherData($data);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/teachers/create');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $userCode = Helper::generateCode('TCH');
            $teacherCode = Helper::generateCode('TEA');
            $employeeId = 'EMP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create user
            $hashedPassword = Helper::encryptPassword($data['password'] ?? 'teacher123');
            
            $userId = $this->db->insert('users', [
                'user_code' => $userCode,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'role_id' => $this->getTeacherRoleId(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? 'other',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Handle profile photo
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $profilePhoto = $this->uploadProfilePhoto($_FILES['profile_photo'], $userId);
                if ($profilePhoto) {
                    $this->db->update('users', ['profile_photo' => $profilePhoto], 'id = ?', [$userId]);
                }
            }
            
            // Create teacher record
            $teacherId = $this->db->insert('teachers', [
                'user_id' => $userId,
                'teacher_code' => $teacherCode,
                'employee_id' => $employeeId,
                'department_id' => $data['department_id'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'teaching_experience_years' => $data['teaching_experience_years'] ?? 0,
                'joining_date' => $data['joining_date'] ?? date('Y-m-d'),
                'contract_type' => $data['contract_type'] ?? 'permanent',
                'salary_grade' => $data['salary_grade'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            
            $this->logActivity('teachers', 'create', "معلم جدید '{$data['first_name']} {$data['last_name']}' با کد {$teacherCode} ثبت شد");
            
            $this->session->flash('success', 'معلم جدید با موفقیت ثبت شد');
            $this->redirect('/teachers');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در ثبت معلم: ' . $e->getMessage());
            $this->session->flash('old', $data);
            $this->redirect('/teachers/create');
        }
    }
    
    public function show($id)
    {
        $teacher = $this->db->selectOne(
            "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                    u.gender, u.date_of_birth, u.address, u.city,
                    d.name as department_name,
                    b.name as branch_name
             FROM teachers t 
             JOIN users u ON t.user_id = u.id 
             LEFT JOIN departments d ON t.department_id = d.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE t.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
        
        if (!$teacher) {
            $this->session->flash('error', 'معلم مورد نظر یافت نشد');
            $this->redirect('/teachers');
            return;
        }
        
        // Get assigned classes and subjects
        $assignments = $this->db->select(
            "SELECT cs.*, c.class_name, c.class_level, s.subject_name, s.subject_code,
                    ay.year_name
             FROM class_subjects cs 
             JOIN classes c ON cs.class_id = c.id 
             JOIN subjects s ON cs.subject_id = s.id 
             JOIN academic_years ay ON cs.academic_year_id = ay.id 
             WHERE cs.teacher_id = ? AND cs.is_active = 1
             ORDER BY c.class_level, s.subject_name",
            [$id]
        );
        
        // Get timetable
        $timetable = $this->db->select(
            "SELECT tt.*, sec.section_name, s.subject_name, c.class_name
             FROM timetables tt 
             JOIN sections sec ON tt.section_id = sec.id 
             JOIN subjects s ON tt.subject_id = s.id 
             JOIN classes c ON sec.class_id = c.id 
             WHERE tt.teacher_id = ? 
             ORDER BY FIELD(tt.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), 
                      tt.start_time",
            [$id]
        );
        
        // Get attendance summary
        $attendanceSummary = $this->getTeacherAttendanceSummary($id);
        
        // Get recent payroll
        $recentPayroll = $this->db->select(
            "SELECT * FROM payroll WHERE user_id = ? ORDER BY pay_period_start DESC LIMIT 5",
            [$teacher['user_id']]
        );
        
        $this->view('teachers/show', [
            'title' => 'پروفایل معلم',
            'teacher' => $teacher,
            'assignments' => $assignments,
            'timetable' => $timetable,
            'attendanceSummary' => $attendanceSummary,
            'recentPayroll' => $recentPayroll
        ]);
    }
    
    public function edit($id)
    {
        $teacher = $this->db->selectOne(
            "SELECT t.*, u.first_name, u.last_name, u.email, u.phone,
                    u.gender, u.date_of_birth, u.address, u.city
             FROM teachers t 
             JOIN users u ON t.user_id = u.id 
             WHERE t.id = ?",
            [$id]
        );
        
        if (!$teacher) {
            $this->session->flash('error', 'معلم مورد نظر یافت نشد');
            $this->redirect('/teachers');
            return;
        }
        
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        
        $this->view('teachers/edit', [
            'title' => 'ویرایش اطلاعات معلم',
            'teacher' => $teacher,
            'departments' => $departments
        ]);
    }
    
    public function update($id)
    {
        $teacher = $this->db->selectOne("SELECT * FROM teachers WHERE id = ?", [$id]);
        
        if (!$teacher) {
            $this->session->flash('error', 'معلم مورد نظر یافت نشد');
            $this->redirect('/teachers');
            return;
        }
        
        $data = $_POST;
        
        $errors = $this->validateTeacherData($data, $id);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect("/teachers/{$id}/edit");
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update user
            $userUpdateData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? 'other',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $userData = $this->db->selectOne("SELECT profile_photo FROM users WHERE id = ?", [$teacher['user_id']]);
                if ($userData['profile_photo']) {
                    $this->deleteFile($userData['profile_photo']);
                }
                
                $profilePhoto = $this->uploadProfilePhoto($_FILES['profile_photo'], $teacher['user_id']);
                if ($profilePhoto) {
                    $userUpdateData['profile_photo'] = $profilePhoto;
                }
            }
            
            $this->db->update('users', $userUpdateData, 'id = ?', [$teacher['user_id']]);
            
            // Update teacher
            $this->db->update('teachers', [
                'department_id' => $data['department_id'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'teaching_experience_years' => $data['teaching_experience_years'] ?? 0,
                'contract_type' => $data['contract_type'] ?? 'permanent',
                'salary_grade' => $data['salary_grade'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            $this->db->commit();
            
            $this->logActivity('teachers', 'update', "اطلاعات معلم '{$data['first_name']} {$data['last_name']}' به‌روزرسانی شد");
            
            $this->session->flash('success', 'اطلاعات معلم با موفقیت به‌روزرسانی شد');
            $this->redirect('/teachers');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در به‌روزرسانی اطلاعات: ' . $e->getMessage());
            $this->redirect("/teachers/{$id}/edit");
        }
    }
    
    public function delete($id)
    {
        $teacher = $this->db->selectOne("SELECT * FROM teachers WHERE id = ?", [$id]);
        
        if (!$teacher) {
            $this->json(['success' => false, 'message' => 'معلم مورد نظر یافت نشد'], 404);
            return;
        }
        
        // Check if teacher has active assignments
        $activeAssignments = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1",
            [$id]
        );
        
        if ($activeAssignments['count'] > 0) {
            $this->json(['success' => false, 'message' => 'این معلم دارای کلاس‌های فعال است. ابتدا کلاس‌ها را انتقال دهید']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $this->db->update('users', [
                'deleted_at' => date('Y-m-d H:i:s'),
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$teacher['user_id']]);
            
            $this->db->commit();
            
            $userData = $this->db->selectOne("SELECT first_name, last_name FROM users WHERE id = ?", [$teacher['user_id']]);
            $this->logActivity('teachers', 'delete', "معلم '{$userData['first_name']} {$userData['last_name']}' حذف شد");
            
            $this->json(['success' => true, 'message' => 'معلم با موفقیت حذف شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در حذف معلم: ' . $e->getMessage()]);
        }
    }
    
    public function workload($id)
    {
        $teacher = $this->db->selectOne(
            "SELECT t.*, u.first_name, u.last_name 
             FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = ?",
            [$id]
        );
        
        if (!$teacher) {
            $this->session->flash('error', 'معلم یافت نشد');
            $this->redirect('/teachers');
            return;
        }
        
        // Calculate workload
        $weeklyPeriods = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM timetables WHERE teacher_id = ?",
            [$id]
        )['count'];
        
        $assignedClasses = $this->db->selectOne(
            "SELECT COUNT(DISTINCT class_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1",
            [$id]
        )['count'];
        
        $assignedSubjects = $this->db->selectOne(
            "SELECT COUNT(DISTINCT subject_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1",
            [$id]
        )['count'];
        
        $totalStudents = $this->db->selectOne(
            "SELECT COUNT(DISTINCT s.id) as count 
             FROM class_subjects cs 
             JOIN sections sec ON cs.class_id = sec.class_id
             JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1
             WHERE cs.teacher_id = ? AND cs.is_active = 1",
            [$id]
        )['count'];
        
        $workload = [
            'weekly_periods' => $weeklyPeriods,
            'assigned_classes' => $assignedClasses,
            'assigned_subjects' => $assignedSubjects,
            'total_students' => $totalStudents,
            'utilization_percentage' => min(round(($weeklyPeriods / 40) * 100, 2), 100)
        ];
        
        // Get detailed timetable
        $timetable = $this->db->select(
            "SELECT tt.*, sec.section_name, s.subject_name, c.class_name
             FROM timetables tt 
             JOIN sections sec ON tt.section_id = sec.id 
             JOIN subjects s ON tt.subject_id = s.id 
             JOIN classes c ON sec.class_id = c.id 
             WHERE tt.teacher_id = ? 
             ORDER BY FIELD(tt.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), 
                      tt.start_time",
            [$id]
        );
        
        $this->view('teachers/workload', [
            'title' => 'حجم کاری معلم',
            'teacher' => $teacher,
            'workload' => $workload,
            'timetable' => $timetable
        ]);
    }
    
    private function validateTeacherData($data, $teacherId = null)
    {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'نام الزامی است';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'نام خانوادگی الزامی است';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'ایمیل الزامی است';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'فرمت ایمیل نامعتبر است';
        } else {
            $sql = "SELECT id FROM users WHERE email = ? AND deleted_at IS NULL";
            $params = [$data['email']];
            
            if ($teacherId) {
                $teacher = $this->db->selectOne("SELECT user_id FROM teachers WHERE id = ?", [$teacherId]);
                if ($teacher) {
                    $sql .= " AND id != ?";
                    $params[] = $teacher['user_id'];
                }
            }
            
            $existing = $this->db->selectOne($sql, $params);
            if ($existing) {
                $errors['email'] = 'این ایمیل قبلاً ثبت شده است';
            }
        }
        
        if (empty($data['joining_date'])) {
            $errors['joining_date'] = 'تاریخ استخدام الزامی است';
        }
        
        if (!empty($data['phone']) && !preg_match('/^[0-9\-\(\)\/\+\s]*$/', $data['phone'])) {
            $errors['phone'] = 'فرمت شماره تماس نامعتبر است';
        }
        
        return $errors;
    }
    
    private function getTeacherRoleId()
    {
        $role = $this->db->selectOne("SELECT id FROM roles WHERE name = 'teacher' LIMIT 1");
        return $role['id'] ?? 6;
    }
    
    private function getTeacherAttendanceSummary($teacherId)
    {
        $currentMonth = $this->db->selectOne(
            "SELECT 
                COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
                COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                COUNT(CASE WHEN status = 'on_leave' THEN 1 END) as on_leave,
                COUNT(*) as total
             FROM teacher_attendance 
             WHERE teacher_id = ? 
             AND MONTH(attendance_date) = MONTH(CURDATE()) 
             AND YEAR(attendance_date) = YEAR(CURDATE())",
            [$teacherId]
        );
        
        return [
            'current_month' => $currentMonth,
            'attendance_rate' => ($currentMonth['total'] > 0) 
                ? round(($currentMonth['present'] / $currentMonth['total']) * 100, 2) 
                : 0
        ];
    }
    
    private function uploadProfilePhoto($file, $userId)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/profiles/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('فرمت فایل مجاز نیست');
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \Exception('حجم فایل نباید بیشتر از 2 مگابایت باشد');
        }
        
        $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/assets/uploads/profiles/' . $filename;
        }
        
        return null;
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