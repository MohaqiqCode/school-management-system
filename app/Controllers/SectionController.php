<?php
namespace App\Controllers;

use App\Core\Controller;

class SectionController extends Controller
{
    public function index()
    {
        $classId = $_GET['class_id'] ?? '';
        
        $sql = "SELECT sec.*, c.class_name, c.class_level,
                       CONCAT(u.first_name, ' ', u.last_name) as class_teacher_name,
                       (SELECT COUNT(*) FROM students WHERE current_section_id = sec.id AND is_active = 1) as student_count
                FROM sections sec 
                JOIN classes c ON sec.class_id = c.id 
                LEFT JOIN users u ON sec.class_teacher_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if ($classId) {
            $sql .= " AND sec.class_id = ?";
            $params[] = $classId;
        }
        
        $sql .= " ORDER BY c.class_level, sec.section_name";
        $sections = $this->db->select($sql, $params);
        
        $classes = $this->db->select("SELECT id, class_name, class_level FROM classes WHERE is_active = 1 ORDER BY class_level");
        
        $this->view('sections/index', [
            'title' => 'مدیریت بخش‌ها',
            'sections' => $sections,
            'classes' => $classes,
            'selectedClass' => $classId
        ]);
    }
    
    public function create()
    {
        $classes = $this->db->select("SELECT id, class_name, class_level FROM classes WHERE is_active = 1 ORDER BY class_level");
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1 ORDER BY u.last_name, u.first_name"
        );
        
        $this->view('sections/create', [
            'title' => 'ایجاد بخش جدید',
            'classes' => $classes,
            'teachers' => $teachers
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['section_name'])) {
            $errors['section_name'] = 'نام بخش الزامی است';
        }
        
        if (empty($data['section_code'])) {
            $errors['section_code'] = 'کد بخش الزامی است';
        }
        
        if (empty($data['class_id'])) {
            $errors['class_id'] = 'انتخاب کلاس الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/sections/create');
            return;
        }
        
        // Check unique code in class
        $existingSection = $this->db->selectOne(
            "SELECT id FROM sections WHERE class_id = ? AND section_code = ?",
            [$data['class_id'], strtoupper($data['section_code'])]
        );
        
        if ($existingSection) {
            $this->session->flash('errors', ['section_code' => 'این کد بخش قبلاً در این کلاس ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect('/sections/create');
            return;
        }
        
        $this->db->insert('sections', [
            'class_id' => $data['class_id'],
            'section_name' => $data['section_name'],
            'section_code' => strtoupper($data['section_code']),
            'capacity' => $data['capacity'] ?? 40,
            'class_teacher_id' => $data['class_teacher_id'] ?? null,
            'room_number' => $data['room_number'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('sections', 'create', "بخش '{$data['section_name']}' ایجاد شد");
        
        $this->session->flash('success', 'بخش با موفقیت ایجاد شد');
        $this->redirect('/sections');
    }
    
    public function edit($id)
    {
        $section = $this->db->selectOne("SELECT * FROM sections WHERE id = ?", [$id]);
        
        if (!$section) {
            $this->session->flash('error', 'بخش مورد نظر یافت نشد');
            $this->redirect('/sections');
            return;
        }
        
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE is_active = 1 ORDER BY class_level");
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1 ORDER BY u.last_name, u.first_name"
        );
        
        $this->view('sections/edit', [
            'title' => 'ویرایش بخش',
            'section' => $section,
            'classes' => $classes,
            'teachers' => $teachers
        ]);
    }
    
    public function update($id)
    {
        $section = $this->db->selectOne("SELECT * FROM sections WHERE id = ?", [$id]);
        
        if (!$section) {
            $this->session->flash('error', 'بخش یافت نشد');
            $this->redirect('/sections');
            return;
        }
        
        $data = $_POST;
        
        $this->db->update('sections', [
            'section_name' => $data['section_name'],
            'capacity' => $data['capacity'] ?? 40,
            'class_teacher_id' => $data['class_teacher_id'] ?? null,
            'room_number' => $data['room_number'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('sections', 'update', "بخش '{$data['section_name']}' به‌روزرسانی شد");
        
        $this->session->flash('success', 'بخش به‌روزرسانی شد');
        $this->redirect('/sections');
    }
    
    public function delete($id)
    {
        $section = $this->db->selectOne("SELECT * FROM sections WHERE id = ?", [$id]);
        
        if (!$section) {
            $this->json(['success' => false, 'message' => 'بخش یافت نشد'], 404);
            return;
        }
        
        $studentCount = $this->db->selectOne("SELECT COUNT(*) as count FROM students WHERE current_section_id = ? AND is_active = 1", [$id])['count'];
        
        if ($studentCount > 0) {
            $this->json(['success' => false, 'message' => 'این بخش دارای دانش‌آموز فعال است و قابل حذف نیست']);
            return;
        }
        
        $this->db->delete('sections', 'id = ?', [$id]);
        
        $this->logActivity('sections', 'delete', "بخش '{$section['section_name']}' حذف شد");
        
        $this->json(['success' => true, 'message' => 'بخش حذف شد']);
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