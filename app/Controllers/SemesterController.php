<?php
namespace App\Controllers;

use App\Core\Controller;

class SemesterController extends Controller
{
    public function index()
    {
        $academicYearId = $_GET['academic_year_id'] ?? '';
        
        $sql = "SELECT sem.*, ay.year_name,
                       (SELECT COUNT(*) FROM timetables WHERE semester_id = sem.id) as timetable_count,
                       (SELECT COUNT(*) FROM exams WHERE semester_id = sem.id) as exam_count
                FROM semesters sem 
                JOIN academic_years ay ON sem.academic_year_id = ay.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($academicYearId) {
            $sql .= " AND sem.academic_year_id = ?";
            $params[] = $academicYearId;
        }
        
        $sql .= " ORDER BY ay.start_date DESC, sem.semester_number";
        $semesters = $this->db->select($sql, $params);
        
        $academicYears = $this->db->select("SELECT id, year_name FROM academic_years WHERE status = 'active' ORDER BY start_date DESC");
        
        $this->view('semesters/index', [
            'title' => 'مدیریت ترم‌ها',
            'semesters' => $semesters,
            'academicYears' => $academicYears,
            'academicYearFilter' => $academicYearId
        ]);
    }
    
    public function create()
    {
        $academicYears = $this->db->select("SELECT id, year_name FROM academic_years WHERE status = 'active' ORDER BY start_date DESC");
        
        $this->view('semesters/create', [
            'title' => 'ایجاد ترم جدید',
            'academicYears' => $academicYears
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['semester_name'])) {
            $errors['semester_name'] = 'نام ترم الزامی است';
        }
        
        if (empty($data['academic_year_id'])) {
            $errors['academic_year_id'] = 'سال تحصیلی الزامی است';
        }
        
        if (empty($data['start_date'])) {
            $errors['start_date'] = 'تاریخ شروع الزامی است';
        }
        
        if (empty($data['end_date'])) {
            $errors['end_date'] = 'تاریخ پایان الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/semesters/create');
            return;
        }
        
        // Check unique semester number in academic year
        $existing = $this->db->selectOne(
            "SELECT id FROM semesters WHERE academic_year_id = ? AND semester_number = ?",
            [$data['academic_year_id'], $data['semester_number']]
        );
        
        if ($existing) {
            $this->session->flash('errors', ['semester_number' => 'این شماره ترم قبلاً در این سال تحصیلی ثبت شده است']);
            $this->redirect('/semesters/create');
            return;
        }
        
        // If set as current, deactivate others
        if (isset($data['is_current'])) {
            $this->db->update('semesters', ['is_current' => 0], 'academic_year_id = ?', [$data['academic_year_id']]);
        }
        
        $this->db->insert('semesters', [
            'academic_year_id' => $data['academic_year_id'],
            'semester_name' => $data['semester_name'],
            'semester_number' => $data['semester_number'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_current' => isset($data['is_current']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('semesters', 'create', "ترم '{$data['semester_name']}' ایجاد شد");
        
        $this->session->flash('success', 'ترم با موفقیت ایجاد شد');
        $this->redirect('/semesters');
    }
    
    public function edit($id)
    {
        $semester = $this->db->selectOne("SELECT * FROM semesters WHERE id = ?", [$id]);
        
        if (!$semester) {
            $this->session->flash('error', 'ترم یافت نشد');
            $this->redirect('/semesters');
            return;
        }
        
        $academicYears = $this->db->select("SELECT id, year_name FROM academic_years ORDER BY start_date DESC");
        
        $this->view('semesters/edit', [
            'title' => 'ویرایش ترم',
            'semester' => $semester,
            'academicYears' => $academicYears
        ]);
    }
    
    public function update($id)
    {
        $semester = $this->db->selectOne("SELECT * FROM semesters WHERE id = ?", [$id]);
        
        if (!$semester) {
            $this->session->flash('error', 'ترم یافت نشد');
            $this->redirect('/semesters');
            return;
        }
        
        $data = $_POST;
        
        if (isset($data['is_current'])) {
            $this->db->update('semesters', ['is_current' => 0], 'academic_year_id = ? AND id != ?', [$semester['academic_year_id'], $id]);
        }
        
        $this->db->update('semesters', [
            'semester_name' => $data['semester_name'],
            'semester_number' => $data['semester_number'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_current' => isset($data['is_current']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('semesters', 'update', "ترم {$id} به‌روزرسانی شد");
        
        $this->session->flash('success', 'ترم به‌روزرسانی شد');
        $this->redirect('/semesters');
    }
    
    public function delete($id)
    {
        $semester = $this->db->selectOne("SELECT * FROM semesters WHERE id = ?", [$id]);
        
        if (!$semester) {
            $this->json(['success' => false, 'message' => 'ترم یافت نشد'], 404);
            return;
        }
        
        // Check dependencies
        $timetableCount = $this->db->selectOne("SELECT COUNT(*) as count FROM timetables WHERE semester_id = ?", [$id])['count'];
        
        if ($timetableCount > 0) {
            $this->json(['success' => false, 'message' => 'این ترم دارای برنامه هفتگی است و قابل حذف نیست']);
            return;
        }
        
        $this->db->delete('semesters', 'id = ?', [$id]);
        
        $this->logActivity('semesters', 'delete', "ترم {$id} حذف شد");
        
        $this->json(['success' => true, 'message' => 'ترم حذف شد']);
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