<?php
namespace App\Controllers;

use App\Core\Controller;

class SubjectController extends Controller
{
    public function index()
    {
        $departmentFilter = $_GET['department_id'] ?? '';
        
        $sql = "SELECT s.*, d.name as department_name,
                       (SELECT COUNT(*) FROM class_subjects WHERE subject_id = s.id AND is_active = 1) as class_count
                FROM subjects s 
                JOIN departments d ON s.department_id = d.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($departmentFilter) {
            $sql .= " AND s.department_id = ?";
            $params[] = $departmentFilter;
        }
        
        $sql .= " ORDER BY s.subject_name";
        $subjects = $this->db->select($sql, $params);
        
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        
        $this->view('subjects/index', [
            'title' => 'مدیریت دروس',
            'subjects' => $subjects,
            'departments' => $departments,
            'departmentFilter' => $departmentFilter
        ]);
    }
    
    public function create()
    {
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        
        $this->view('subjects/create', [
            'title' => 'ایجاد درس جدید',
            'departments' => $departments
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['subject_name'])) {
            $errors['subject_name'] = 'نام درس الزامی است';
        }
        
        if (empty($data['subject_code'])) {
            $errors['subject_code'] = 'کد درس الزامی است';
        }
        
        if (empty($data['department_id'])) {
            $errors['department_id'] = 'انتخاب دپارتمان الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/subjects/create');
            return;
        }
        
        // Check unique code
        $existingSubject = $this->db->selectOne(
            "SELECT id FROM subjects WHERE subject_code = ?",
            [strtoupper($data['subject_code'])]
        );
        
        if ($existingSubject) {
            $this->session->flash('errors', ['subject_code' => 'این کد درس قبلاً ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect('/subjects/create');
            return;
        }
        
        $this->db->insert('subjects', [
            'department_id' => $data['department_id'],
            'subject_code' => strtoupper($data['subject_code']),
            'subject_name' => $data['subject_name'],
            'description' => $data['description'] ?? null,
            'credit_hours' => $data['credit_hours'] ?? 1.0,
            'is_core' => isset($data['is_core']) ? 1 : 0,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('subjects', 'create', "درس '{$data['subject_name']}' ایجاد شد");
        
        $this->session->flash('success', 'درس با موفقیت ایجاد شد');
        $this->redirect('/subjects');
    }
    
    public function edit($id)
    {
        $subject = $this->db->selectOne("SELECT * FROM subjects WHERE id = ?", [$id]);
        
        if (!$subject) {
            $this->session->flash('error', 'درس مورد نظر یافت نشد');
            $this->redirect('/subjects');
            return;
        }
        
        $departments = $this->db->select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        
        $this->view('subjects/edit', [
            'title' => 'ویرایش درس',
            'subject' => $subject,
            'departments' => $departments
        ]);
    }
    
    public function update($id)
    {
        $subject = $this->db->selectOne("SELECT * FROM subjects WHERE id = ?", [$id]);
        
        if (!$subject) {
            $this->session->flash('error', 'درس یافت نشد');
            $this->redirect('/subjects');
            return;
        }
        
        $data = $_POST;
        
        $this->db->update('subjects', [
            'department_id' => $data['department_id'],
            'subject_name' => $data['subject_name'],
            'description' => $data['description'] ?? null,
            'credit_hours' => $data['credit_hours'] ?? 1.0,
            'is_core' => isset($data['is_core']) ? 1 : 0,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('subjects', 'update', "درس '{$data['subject_name']}' به‌روزرسانی شد");
        
        $this->session->flash('success', 'درس به‌روزرسانی شد');
        $this->redirect('/subjects');
    }
    
    public function delete($id)
    {
        $subject = $this->db->selectOne("SELECT * FROM subjects WHERE id = ?", [$id]);
        
        if (!$subject) {
            $this->json(['success' => false, 'message' => 'درس یافت نشد'], 404);
            return;
        }
        
        $classCount = $this->db->selectOne("SELECT COUNT(*) as count FROM class_subjects WHERE subject_id = ? AND is_active = 1", [$id])['count'];
        
        if ($classCount > 0) {
            $this->json(['success' => false, 'message' => 'این درس در کلاس‌های فعال استفاده شده است و قابل حذف نیست']);
            return;
        }
        
        $this->db->delete('subjects', 'id = ?', [$id]);
        
        $this->logActivity('subjects', 'delete', "درس '{$subject['subject_name']}' حذف شد");
        
        $this->json(['success' => true, 'message' => 'درس حذف شد']);
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