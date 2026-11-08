<?php
namespace App\Controllers;

use App\Core\Controller;

class ClassController extends Controller
{
    public function index()
    {
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT c.*, b.name as branch_name,
                       (SELECT COUNT(*) FROM sections WHERE class_id = c.id AND is_active = 1) as section_count,
                       (SELECT COUNT(*) FROM students WHERE current_class_id = c.id AND is_active = 1) as student_count,
                       (SELECT COUNT(*) FROM class_subjects WHERE class_id = c.id AND is_active = 1) as subject_count
                FROM classes c 
                JOIN branches b ON c.branch_id = b.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND c.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " ORDER BY c.class_level ASC";
        $classes = $this->db->select($sql, $params);
        
        $this->view('classes/index', [
            'title' => 'مدیریت کلاس‌ها',
            'classes' => $classes
        ]);
    }
    
    public function create()
    {
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('classes/create', [
            'title' => 'ایجاد کلاس جدید',
            'branches' => $branches
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['class_name'])) {
            $errors['class_name'] = 'نام کلاس الزامی است';
        }
        
        if (empty($data['class_code'])) {
            $errors['class_code'] = 'کد کلاس الزامی است';
        }
        
        if (empty($data['class_level']) || !is_numeric($data['class_level'])) {
            $errors['class_level'] = 'پایه تحصیلی الزامی است و باید عدد باشد';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/classes/create');
            return;
        }
        
        // Check unique code in branch
        $existingClass = $this->db->selectOne(
            "SELECT id FROM classes WHERE branch_id = ? AND class_code = ?",
            [$data['branch_id'] ?? $this->session->get('branch_id'), strtoupper($data['class_code'])]
        );
        
        if ($existingClass) {
            $this->session->flash('errors', ['class_code' => 'این کد کلاس قبلاً ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect('/classes/create');
            return;
        }
        
        $this->db->insert('classes', [
            'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
            'class_name' => $data['class_name'],
            'class_level' => $data['class_level'],
            'class_code' => strtoupper($data['class_code']),
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'] ?? 40,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('classes', 'create', "کلاس '{$data['class_name']}' (پایه {$data['class_level']}) ایجاد شد");
        
        $this->session->flash('success', 'کلاس با موفقیت ایجاد شد');
        $this->redirect('/classes');
    }
    
    public function edit($id)
    {
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$id]);
        
        if (!$class) {
            $this->session->flash('error', 'کلاس مورد نظر یافت نشد');
            $this->redirect('/classes');
            return;
        }
        
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('classes/edit', [
            'title' => 'ویرایش کلاس',
            'class' => $class,
            'branches' => $branches
        ]);
    }
    
    public function update($id)
    {
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$id]);
        
        if (!$class) {
            $this->session->flash('error', 'کلاس یافت نشد');
            $this->redirect('/classes');
            return;
        }
        
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['class_name'])) {
            $errors['class_name'] = 'نام کلاس الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect("/classes/{$id}/edit");
            return;
        }
        
        $this->db->update('classes', [
            'class_name' => $data['class_name'],
            'class_level' => $data['class_level'],
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'] ?? 40,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('classes', 'update', "کلاس '{$data['class_name']}' به‌روزرسانی شد");
        
        $this->session->flash('success', 'کلاس به‌روزرسانی شد');
        $this->redirect('/classes');
    }
    
    public function delete($id)
    {
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$id]);
        
        if (!$class) {
            $this->json(['success' => false, 'message' => 'کلاس یافت نشد'], 404);
            return;
        }
        
        // Check dependencies
        $sectionCount = $this->db->selectOne("SELECT COUNT(*) as count FROM sections WHERE class_id = ?", [$id])['count'];
        $studentCount = $this->db->selectOne("SELECT COUNT(*) as count FROM students WHERE current_class_id = ? AND is_active = 1", [$id])['count'];
        
        if ($sectionCount > 0 || $studentCount > 0) {
            $this->json(['success' => false, 'message' => 'این کلاس دارای بخش یا دانش‌آموز فعال است و قابل حذف نیست']);
            return;
        }
        
        $this->db->delete('classes', 'id = ?', [$id]);
        
        $this->logActivity('classes', 'delete', "کلاس '{$class['class_name']}' حذف شد");
        
        $this->json(['success' => true, 'message' => 'کلاس حذف شد']);
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