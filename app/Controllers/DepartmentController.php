<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class DepartmentController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        $branchFilter = $_GET['branch_id'] ?? '';
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT d.*, b.name as branch_name,
                       (SELECT COUNT(*) FROM teachers WHERE department_id = d.id) as teacher_count,
                       (SELECT COUNT(*) FROM subjects WHERE department_id = d.id AND is_active = 1) as subject_count,
                       CONCAT(u.first_name, ' ', u.last_name) as head_teacher_name
                FROM departments d 
                JOIN branches b ON d.branch_id = b.id 
                LEFT JOIN users u ON d.head_teacher_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND d.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($search) {
            $sql .= " AND (d.name LIKE ? OR d.department_code LIKE ? OR d.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($branchFilter) {
            $sql .= " AND d.branch_id = ?";
            $params[] = $branchFilter;
        }
        
        $countSql = str_replace("SELECT d.*, b.name as branch_name, (SELECT COUNT(*) FROM teachers WHERE department_id = d.id) as teacher_count, (SELECT COUNT(*) FROM subjects WHERE department_id = d.id AND is_active = 1) as subject_count, CONCAT(u.first_name, ' ', u.last_name) as head_teacher_name", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY d.name ASC LIMIT {$perPage} OFFSET {$offset}";
        $departments = $this->db->select($sql, $params);
        
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('departments/index', [
            'title' => 'مدیریت دپارتمان‌ها',
            'departments' => $departments,
            'branches' => $branches,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'branchFilter' => $branchFilter
        ]);
    }
    
    public function create()
    {
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1 ORDER BY u.last_name, u.first_name"
        );
        
        $this->view('departments/create', [
            'title' => 'ایجاد دپارتمان جدید',
            'branches' => $branches,
            'teachers' => $teachers
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // CSRF Protection
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->session->flash('error', 'توکن امنیتی نامعتبر است');
            $this->redirect('/departments/create');
            return;
        }
        
        // Validate
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'نام دپارتمان الزامی است';
        }
        
        if (empty($data['department_code'])) {
            $errors['department_code'] = 'کد دپارتمان الزامی است';
        } elseif (!preg_match('/^[A-Za-z0-9\-_]+$/', $data['department_code'])) {
            $errors['department_code'] = 'کد دپارتمان فقط می‌تواند شامل حروف، اعداد و خط تیره باشد';
        }
        
        if (empty($data['branch_id'])) {
            $errors['branch_id'] = 'انتخاب شعبه الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/departments/create');
            return;
        }
        
        // Check unique code in branch
        $existingDept = $this->db->selectOne(
            "SELECT id FROM departments WHERE branch_id = ? AND department_code = ?",
            [$data['branch_id'], strtoupper($data['department_code'])]
        );
        
        if ($existingDept) {
            $this->session->flash('errors', ['department_code' => 'این کد دپارتمان قبلاً در این شعبه ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect('/departments/create');
            return;
        }
        
        // Create department
        $this->db->insert('departments', [
            'branch_id' => $data['branch_id'],
            'department_code' => strtoupper($data['department_code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'head_teacher_id' => $data['head_teacher_id'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('departments', 'create', "دپارتمان '{$data['name']}' ایجاد شد");
        
        $this->session->flash('success', 'دپارتمان با موفقیت ایجاد شد');
        $this->redirect('/departments');
    }
    
    public function edit($id)
    {
        $department = $this->db->selectOne("SELECT * FROM departments WHERE id = ?", [$id]);
        
        if (!$department) {
            $this->session->flash('error', 'دپارتمان مورد نظر یافت نشد');
            $this->redirect('/departments');
            return;
        }
        
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1 ORDER BY u.last_name, u.first_name"
        );
        
        $this->view('departments/edit', [
            'title' => 'ویرایش دپارتمان',
            'department' => $department,
            'branches' => $branches,
            'teachers' => $teachers
        ]);
    }
    
    public function update($id)
    {
        $department = $this->db->selectOne("SELECT * FROM departments WHERE id = ?", [$id]);
        
        if (!$department) {
            $this->session->flash('error', 'دپارتمان مورد نظر یافت نشد');
            $this->redirect('/departments');
            return;
        }
        
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'نام دپارتمان الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect("/departments/{$id}/edit");
            return;
        }
        
        $this->db->update('departments', [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'head_teacher_id' => $data['head_teacher_id'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('departments', 'update', "دپارتمان '{$data['name']}' به‌روزرسانی شد");
        
        $this->session->flash('success', 'دپارتمان با موفقیت به‌روزرسانی شد');
        $this->redirect('/departments');
    }
    
    public function delete($id)
    {
        $department = $this->db->selectOne("SELECT * FROM departments WHERE id = ?", [$id]);
        
        if (!$department) {
            $this->json(['success' => false, 'message' => 'دپارتمان یافت نشد'], 404);
            return;
        }
        
        // Check associations
        $teacherCount = $this->db->selectOne("SELECT COUNT(*) as count FROM teachers WHERE department_id = ?", [$id])['count'];
        $subjectCount = $this->db->selectOne("SELECT COUNT(*) as count FROM subjects WHERE department_id = ?", [$id])['count'];
        
        if ($teacherCount > 0 || $subjectCount > 0) {
            $this->json(['success' => false, 'message' => 'این دپارتمان دارای معلم یا درس فعال است و قابل حذف نیست']);
            return;
        }
        
        $this->db->delete('departments', 'id = ?', [$id]);
        
        $this->logActivity('departments', 'delete', "دپارتمان '{$department['name']}' حذف شد");
        
        $this->json(['success' => true, 'message' => 'دپارتمان با موفقیت حذف شد']);
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