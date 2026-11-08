<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class UserController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $branchFilter = $_GET['branch'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        
        $sql = "SELECT u.*, r.name as role_name, r.display_name as role_display,
                       b.name as branch_name
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE u.deleted_at IS NULL";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.user_code LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($roleFilter) {
            $sql .= " AND u.role_id = ?";
            $params[] = $roleFilter;
        }
        
        if ($branchFilter) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchFilter;
        }
        
        if ($statusFilter !== '') {
            $sql .= " AND u.is_active = ?";
            $params[] = $statusFilter;
        }
        
        // Get total count for pagination
        $countSql = str_replace("SELECT u.*, r.name as role_name, r.display_name as role_display, b.name as branch_name", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'];
        
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY u.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $users = $this->db->select($sql, $params);
        
        // Get roles and branches for filters
        $roles = $this->db->select("SELECT id, name, display_name FROM roles ORDER BY display_name");
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('users/index', [
            'title' => 'مدیریت کاربران',
            'users' => $users,
            'roles' => $roles,
            'branches' => $branches,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'branchFilter' => $branchFilter,
            'statusFilter' => $statusFilter
        ]);
    }
    
    public function create()
    {
        $roles = $this->db->select("SELECT id, name, display_name FROM roles ORDER BY display_name");
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('users/create', [
            'title' => 'ایجاد کاربر جدید',
            'roles' => $roles,
            'branches' => $branches
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = $this->validateUserData($data);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/users/create');
            return;
        }
        
        // Check unique email
        $existingUser = $this->db->selectOne(
            "SELECT id FROM users WHERE email = ? AND deleted_at IS NULL", 
            [$data['email']]
        );
        
        if ($existingUser) {
            $this->session->flash('errors', ['email' => 'این ایمیل قبلاً ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect('/users/create');
            return;
        }
        
        // Generate user code
        $userCode = Helper::generateCode('USR');
        
        // Hash password
        $hashedPassword = Helper::encryptPassword($data['password']);
        
        // Handle profile photo upload
        $profilePhoto = null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $profilePhoto = $this->uploadProfilePhoto($_FILES['profile_photo']);
        }
        
        // Create user
        $userId = $this->db->insert('users', [
            'user_code' => $userCode,
            'branch_id' => $data['branch_id'],
            'role_id' => $data['role_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'phone' => $data['phone'] ?? null,
            'alternate_phone' => $data['alternate_phone'] ?? null,
            'gender' => $data['gender'] ?? 'other',
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'profile_photo' => $profilePhoto,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'Iran',
            'postal_code' => $data['postal_code'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'email_verified_at' => isset($data['email_verified']) ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log activity
        $this->logActivity('users', 'create', "کاربر جدید ایجاد شد: {$data['first_name']} {$data['last_name']} ({$data['email']})");
        
        $this->session->flash('success', 'کاربر با موفقیت ایجاد شد');
        $this->redirect('/users');
    }
    
    public function show($id)
    {
        $user = $this->db->selectOne(
            "SELECT u.*, r.name as role_name, r.display_name as role_display,
                    b.name as branch_name
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE u.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
        
        if (!$user) {
            $this->session->flash('error', 'کاربر مورد نظر یافت نشد');
            $this->redirect('/users');
            return;
        }
        
        // Get user's login history
        $loginHistory = $this->db->select(
            "SELECT * FROM audit_logs 
             WHERE user_id = ? AND action IN ('login', 'logout', 'login_failed')
             ORDER BY created_at DESC 
             LIMIT 10",
            [$id]
        );
        
        // Get user's permissions
        $permissions = $this->getUserPermissions($user['role_id']);
        
        $this->view('users/show', [
            'title' => 'پروفایل کاربر',
            'user' => $user,
            'loginHistory' => $loginHistory,
            'permissions' => $permissions
        ]);
    }
    
    public function edit($id)
    {
        $user = $this->db->selectOne(
            "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
        
        if (!$user) {
            $this->session->flash('error', 'کاربر مورد نظر یافت نشد');
            $this->redirect('/users');
            return;
        }
        
        $roles = $this->db->select("SELECT id, name, display_name FROM roles ORDER BY display_name");
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('users/edit', [
            'title' => 'ویرایش کاربر',
            'user' => $user,
            'roles' => $roles,
            'branches' => $branches
        ]);
    }
    
    public function update($id)
    {
        $user = $this->db->selectOne("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        
        if (!$user) {
            $this->session->flash('error', 'کاربر مورد نظر یافت نشد');
            $this->redirect('/users');
            return;
        }
        
        $data = $_POST;
        
        // Validate
        $errors = $this->validateUserData($data, $id);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect("/users/{$id}/edit");
            return;
        }
        
        // Check unique email (ignore current user)
        $existingUser = $this->db->selectOne(
            "SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL", 
            [$data['email'], $id]
        );
        
        if ($existingUser) {
            $this->session->flash('errors', ['email' => 'این ایمیل قبلاً توسط کاربر دیگری ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect("/users/{$id}/edit");
            return;
        }
        
        // Update data
        $updateData = [
            'branch_id' => $data['branch_id'],
            'role_id' => $data['role_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'alternate_phone' => $data['alternate_phone'] ?? null,
            'gender' => $data['gender'] ?? 'other',
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'Iran',
            'postal_code' => $data['postal_code'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Helper::encryptPassword($data['password']);
        }
        
        // Handle profile photo
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            // Delete old photo if exists
            if ($user['profile_photo']) {
                $this->deleteFile($user['profile_photo']);
            }
            
            $updateData['profile_photo'] = $this->uploadProfilePhoto($_FILES['profile_photo']);
        }
        
        $this->db->update('users', $updateData, 'id = ?', [$id]);
        
        // Log activity
        $this->logActivity('users', 'update', "کاربر ویرایش شد: {$data['first_name']} {$data['last_name']} (ID: {$id})");
        
        $this->session->flash('success', 'اطلاعات کاربر با موفقیت به‌روزرسانی شد');
        $this->redirect('/users');
    }
    
    public function delete($id)
    {
        $user = $this->db->selectOne("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        
        if (!$user) {
            $this->session->flash('error', 'کاربر مورد نظر یافت نشد');
            $this->redirect('/users');
            return;
        }
        
        // Soft delete
        $this->db->update('users', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        // Revoke all sessions
        $this->db->delete('user_sessions', 'user_id = ?', [$id]);
        $this->db->update('jwt_tokens', ['is_revoked' => 1], 'user_id = ? AND is_revoked = 0', [$id]);
        
        // Log activity
        $this->logActivity('users', 'delete', "کاربر حذف شد: {$user['first_name']} {$user['last_name']} (ID: {$id})");
        
        $this->session->flash('success', 'کاربر با موفقیت حذف شد');
        $this->redirect('/users');
    }
    
    public function profile()
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        $userDetails = $this->db->selectOne(
            "SELECT u.*, r.name as role_name, r.display_name as role_display,
                    b.name as branch_name
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE u.id = ?",
            [$user['id']]
        );
        
        // Get additional details based on role
        $additionalDetails = null;
        
        switch ($user['role_name']) {
            case 'student':
                $additionalDetails = $this->db->selectOne(
                    "SELECT * FROM students WHERE user_id = ?", 
                    [$user['id']]
                );
                break;
            case 'teacher':
                $additionalDetails = $this->db->selectOne(
                    "SELECT * FROM teachers WHERE user_id = ?", 
                    [$user['id']]
                );
                break;
            case 'parent':
                $additionalDetails = $this->db->selectOne(
                    "SELECT * FROM parents WHERE user_id = ?", 
                    [$user['id']]
                );
                break;
        }
        
        $this->view('users/profile', [
            'title' => 'پروفایل کاربری',
            'user' => $userDetails,
            'additionalDetails' => $additionalDetails
        ]);
    }
    
    public function updateProfile()
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        $data = $_POST;
        
        // Validate profile data
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'نام الزامی است';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'نام خانوادگی الزامی است';
        }
        
        if (empty($data['phone'])) {
            $errors['phone'] = 'شماره تماس الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect('/profile');
            return;
        }
        
        // Update user data
        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'alternate_phone' => $data['alternate_phone'] ?? null,
            'gender' => $data['gender'] ?? 'other',
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Handle profile photo
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            if ($user['profile_photo']) {
                $this->deleteFile($user['profile_photo']);
            }
            
            $updateData['profile_photo'] = $this->uploadProfilePhoto($_FILES['profile_photo']);
            $this->session->set('profile_photo', $updateData['profile_photo']);
        }
        
        $this->db->update('users', $updateData, 'id = ?', [$user['id']]);
        
        // Update session name
        $this->session->set('user_name', $data['first_name'] . ' ' . $data['last_name']);
        
        // Log activity
        $this->logActivity('profile', 'update', 'پروفایل کاربری به‌روزرسانی شد');
        
        $this->session->flash('success', 'پروفایل با موفقیت به‌روزرسانی شد');
        $this->redirect('/profile');
    }
    
    public function toggleStatus($id)
    {
        $user = $this->db->selectOne("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'کاربر یافت نشد'], 404);
            return;
        }
        
        $newStatus = $user['is_active'] ? 0 : 1;
        
        $this->db->update('users', [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $statusText = $newStatus ? 'فعال' : 'غیرفعال';
        $this->logActivity('users', 'status_change', "وضعیت کاربر {$user['first_name']} {$user['last_name']} به {$statusText} تغییر یافت");
        
        $this->json([
            'success' => true,
            'message' => "وضعیت کاربر با موفقیت به {$statusText} تغییر یافت",
            'is_active' => $newStatus
        ]);
    }
    
    private function validateUserData($data, $userId = null)
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
        }
        
        if (empty($data['branch_id'])) {
            $errors['branch_id'] = 'انتخاب شعبه الزامی است';
        }
        
        if (empty($data['role_id'])) {
            $errors['role_id'] = 'انتخاب نقش الزامی است';
        }
        
        // Password validation for new users
        if (!$userId) {
            if (empty($data['password'])) {
                $errors['password'] = 'رمز عبور الزامی است';
            } elseif (strlen($data['password']) < 8) {
                $errors['password'] = 'رمز عبور باید حداقل 8 کاراکتر باشد';
            }
        } elseif (!empty($data['password'])) {
            // Password validation for existing users (if password is being changed)
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'رمز عبور باید حداقل 8 کاراکتر باشد';
            }
        }
        
        if (!empty($data['phone']) && !preg_match('/^[0-9\-\(\)\/\+\s]*$/', $data['phone'])) {
            $errors['phone'] = 'فرمت شماره تماس نامعتبر است';
        }
        
        return $errors;
    }
    
    private function uploadProfilePhoto($file)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/profiles/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('فرمت فایل مجاز نیست. فقط JPG، PNG و GIF مجاز هستند');
        }
        
        if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            throw new \Exception('حجم فایل نباید بیشتر از 2 مگابایت باشد');
        }
        
        $filename = 'profile_' . time() . '_' . Helper::generateRandomString(8) . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/assets/uploads/profiles/' . $filename;
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
    
    private function getUserPermissions($roleId)
    {
        return $this->db->select(
            "SELECT p.id, p.name, p.display_name, p.module
             FROM permissions p 
             JOIN role_permission rp ON p.id = rp.permission_id 
             WHERE rp.role_id = ?
             ORDER BY p.module, p.name",
            [$roleId]
        );
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