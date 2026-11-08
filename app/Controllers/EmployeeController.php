<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class EmployeeController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        $typeFilter = $_GET['employee_type'] ?? '';
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT e.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.is_active as user_active,
                       d.name as department_name,
                       b.name as branch_name,
                       CONCAT(sup_u.first_name, ' ', sup_u.last_name) as supervisor_name
                FROM employees e 
                JOIN users u ON e.user_id = u.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                JOIN branches b ON u.branch_id = b.id 
                LEFT JOIN employees sup ON e.supervisor_id = sup.id
                LEFT JOIN users sup_u ON sup.user_id = sup_u.id
                WHERE u.deleted_at IS NULL";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($search) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR e.employee_code LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($typeFilter) {
            $sql .= " AND e.employee_type = ?";
            $params[] = $typeFilter;
        }
        
        $countSql = str_replace("SELECT e.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo, u.is_active as user_active, d.name as department_name, b.name as branch_name, CONCAT(sup_u.first_name, ' ', sup_u.last_name) as supervisor_name", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY u.last_name, u.first_name LIMIT {$perPage} OFFSET {$offset}";
        $employees = $this->db->select($sql, $params);
        
        $this->view('employees/index', [
            'title' => 'مدیریت کارمندان',
            'employees' => $employees,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'typeFilter' => $typeFilter
        ]);
    }
    
    public function create()
    {
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        $employees = $this->db->select(
            "SELECT e.id, u.first_name, u.last_name FROM employees e JOIN users u ON e.user_id = u.id WHERE u.is_active = 1"
        );
        
        $this->view('employees/create', [
            'title' => 'ثبت کارمند جدید',
            'departments' => $departments,
            'employees' => $employees
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
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
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/employees/create');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $userCode = Helper::generateCode('EMP');
            $employeeCode = Helper::generateCode('EML');
            $hashedPassword = Helper::encryptPassword($data['password'] ?? 'employee123');
            
            $userId = $this->db->insert('users', [
                'user_code' => $userCode,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'role_id' => $this->getEmployeeRoleId(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? 'other',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->insert('employees', [
                'user_id' => $userId,
                'employee_code' => $employeeCode,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'department_id' => $data['department_id'] ?? null,
                'designation' => $data['designation'],
                'employee_type' => $data['employee_type'] ?? 'administrative',
                'joining_date' => $data['joining_date'] ?? date('Y-m-d'),
                'salary_grade' => $data['salary_grade'] ?? null,
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            
            $this->logActivity('employees', 'create', "کارمند جدید '{$data['first_name']} {$data['last_name']}' ثبت شد");
            
            $this->session->flash('success', 'کارمند با موفقیت ثبت شد');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا: ' . $e->getMessage());
        }
        
        $this->redirect('/employees');
    }
    
    public function edit($id)
    {
        $employee = $this->db->selectOne(
            "SELECT e.*, u.first_name, u.last_name, u.email, u.phone, u.gender
             FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ?",
            [$id]
        );
        
        if (!$employee) {
            $this->session->flash('error', 'کارمند یافت نشد');
            $this->redirect('/employees');
            return;
        }
        
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        $employees = $this->db->select(
            "SELECT e.id, u.first_name, u.last_name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id != ? AND u.is_active = 1",
            [$id]
        );
        
        $this->view('employees/edit', [
            'title' => 'ویرایش کارمند',
            'employee' => $employee,
            'departments' => $departments,
            'employees' => $employees
        ]);
    }
    
    public function update($id)
    {
        $employee = $this->db->selectOne("SELECT * FROM employees WHERE id = ?", [$id]);
        
        if (!$employee) {
            $this->session->flash('error', 'کارمند یافت نشد');
            $this->redirect('/employees');
            return;
        }
        
        $data = $_POST;
        
        try {
            $this->db->beginTransaction();
            
            $this->db->update('users', [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$employee['user_id']]);
            
            $this->db->update('employees', [
                'department_id' => $data['department_id'] ?? null,
                'designation' => $data['designation'],
                'employee_type' => $data['employee_type'] ?? 'administrative',
                'salary_grade' => $data['salary_grade'] ?? null,
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            $this->db->commit();
            
            $this->logActivity('employees', 'update', "کارمند '{$data['first_name']} {$data['last_name']}' به‌روزرسانی شد");
            
            $this->session->flash('success', 'اطلاعات کارمند به‌روزرسانی شد');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا: ' . $e->getMessage());
        }
        
        $this->redirect('/employees');
    }
    
    public function delete($id)
    {
        $employee = $this->db->selectOne("SELECT * FROM employees WHERE id = ?", [$id]);
        
        if (!$employee) {
            $this->json(['success' => false, 'message' => 'کارمند یافت نشد'], 404);
            return;
        }
        
        $this->db->beginTransaction();
        
        try {
            $this->db->update('users', [
                'deleted_at' => date('Y-m-d H:i:s'),
                'is_active' => 0
            ], 'id = ?', [$employee['user_id']]);
            
            $this->db->commit();
            
            $this->logActivity('employees', 'delete', "کارمند {$id} حذف شد");
            
            $this->json(['success' => true, 'message' => 'کارمند حذف شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
    }
    
    private function getEmployeeRoleId()
    {
        $role = $this->db->selectOne("SELECT id FROM roles WHERE name = 'employee' LIMIT 1");
        return $role['id'] ?? 7;
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