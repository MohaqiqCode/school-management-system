<?php
namespace App\Controllers;

use App\Core\Controller;

class AcademicYearController extends Controller
{
    public function index()
    {
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT ay.*, b.name as branch_name,
                       (SELECT COUNT(*) FROM semesters WHERE academic_year_id = ay.id) as semester_count,
                       (SELECT COUNT(*) FROM student_promotions WHERE academic_year_id = ay.id) as promotion_count
                FROM academic_years ay 
                JOIN branches b ON ay.branch_id = b.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND ay.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " ORDER BY ay.start_date DESC";
        $academicYears = $this->db->select($sql, $params);
        
        $this->view('academic_years/index', [
            'title' => 'مدیریت سال‌های تحصیلی',
            'academicYears' => $academicYears
        ]);
    }
    
    public function create()
    {
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('academic_years/create', [
            'title' => 'ایجاد سال تحصیلی جدید',
            'branches' => $branches
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['year_name'])) {
            $errors['year_name'] = 'نام سال تحصیلی الزامی است';
        }
        
        if (empty($data['start_date'])) {
            $errors['start_date'] = 'تاریخ شروع الزامی است';
        }
        
        if (empty($data['end_date'])) {
            $errors['end_date'] = 'تاریخ پایان الزامی است';
        }
        
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
                $errors['end_date'] = 'تاریخ پایان باید بعد از تاریخ شروع باشد';
            }
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/academic-years/create');
            return;
        }
        
        // If set as current, deactivate other current years for this branch
        if (isset($data['is_current'])) {
            $this->db->update('academic_years', 
                ['is_current' => 0], 
                'branch_id = ? AND is_current = 1', 
                [$data['branch_id'] ?? $this->session->get('branch_id')]
            );
        }
        
        $yearId = $this->db->insert('academic_years', [
            'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
            'year_name' => $data['year_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_current' => isset($data['is_current']) ? 1 : 0,
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Auto-create semesters
        if (isset($data['auto_create_semesters'])) {
            $this->createDefaultSemesters($yearId, $data['start_date'], $data['end_date']);
        }
        
        $this->logActivity('academic_years', 'create', "سال تحصیلی '{$data['year_name']}' ایجاد شد");
        
        $this->session->flash('success', 'سال تحصیلی با موفقیت ایجاد شد');
        $this->redirect('/academic-years');
    }
    
    public function edit($id)
    {
        $academicYear = $this->db->selectOne("SELECT * FROM academic_years WHERE id = ?", [$id]);
        
        if (!$academicYear) {
            $this->session->flash('error', 'سال تحصیلی مورد نظر یافت نشد');
            $this->redirect('/academic-years');
            return;
        }
        
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('academic_years/edit', [
            'title' => 'ویرایش سال تحصیلی',
            'academicYear' => $academicYear,
            'branches' => $branches
        ]);
    }
    
    public function update($id)
    {
        $academicYear = $this->db->selectOne("SELECT * FROM academic_years WHERE id = ?", [$id]);
        
        if (!$academicYear) {
            $this->session->flash('error', 'سال تحصیلی یافت نشد');
            $this->redirect('/academic-years');
            return;
        }
        
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['year_name'])) {
            $errors['year_name'] = 'نام سال تحصیلی الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect("/academic-years/{$id}/edit");
            return;
        }
        
        if (isset($data['is_current'])) {
            $this->db->update('academic_years', 
                ['is_current' => 0], 
                'branch_id = ? AND id != ? AND is_current = 1', 
                [$academicYear['branch_id'], $id]
            );
        }
        
        $this->db->update('academic_years', [
            'year_name' => $data['year_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_current' => isset($data['is_current']) ? 1 : 0,
            'status' => $data['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('academic_years', 'update', "سال تحصیلی '{$data['year_name']}' به‌روزرسانی شد");
        
        $this->session->flash('success', 'سال تحصیلی به‌روزرسانی شد');
        $this->redirect('/academic-years');
    }
    
    public function delete($id)
    {
        $academicYear = $this->db->selectOne("SELECT * FROM academic_years WHERE id = ?", [$id]);
        
        if (!$academicYear) {
            $this->json(['success' => false, 'message' => 'سال تحصیلی یافت نشد'], 404);
            return;
        }
        
        // Check dependencies
        $semesterCount = $this->db->selectOne("SELECT COUNT(*) as count FROM semesters WHERE academic_year_id = ?", [$id])['count'];
        $examCount = $this->db->selectOne("SELECT COUNT(*) as count FROM exams WHERE academic_year_id = ?", [$id])['count'];
        
        if ($semesterCount > 0 || $examCount > 0) {
            $this->json(['success' => false, 'message' => 'این سال تحصیلی دارای داده‌های مرتبط است و قابل حذف نیست']);
            return;
        }
        
        $this->db->delete('academic_years', 'id = ?', [$id]);
        
        $this->logActivity('academic_years', 'delete', "سال تحصیلی '{$academicYear['year_name']}' حذف شد");
        
        $this->json(['success' => true, 'message' => 'سال تحصیلی حذف شد']);
    }
    
    private function createDefaultSemesters($yearId, $startDate, $endDate)
    {
        $semesters = [
            ['name' => 'نیمسال اول', 'number' => 1],
            ['name' => 'نیمسال دوم', 'number' => 2]
        ];
        
        $midPoint = strtotime($startDate) + ((strtotime($endDate) - strtotime($startDate)) / 2);
        
        foreach ($semesters as $index => $sem) {
            $semStart = $index == 0 ? $startDate : date('Y-m-d', $midPoint);
            $semEnd = $index == 0 ? date('Y-m-d', $midPoint - 86400) : $endDate;
            
            $this->db->insert('semesters', [
                'academic_year_id' => $yearId,
                'semester_name' => $sem['name'],
                'semester_number' => $sem['number'],
                'start_date' => $semStart,
                'end_date' => $semEnd,
                'is_current' => $index == 0 ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
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