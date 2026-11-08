<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Helpers\Helper;

class TeacherController extends Controller
{
    /**
     * List teachers
     * GET /api/v1/teachers
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $perPage = min(100, $this->request->get('per_page', 25));
        $search = $this->request->get('search', '');
        $departmentId = $this->request->get('department_id', '');
        
        $sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       d.name as department_name,
                       (SELECT COUNT(DISTINCT cs.class_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as class_count,
                       (SELECT COUNT(DISTINCT cs.subject_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as subject_count
                FROM teachers t 
                JOIN users u ON t.user_id = u.id 
                LEFT JOIN departments d ON t.department_id = d.id 
                WHERE u.deleted_at IS NULL AND u.is_active = 1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR t.teacher_code LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($departmentId) {
            $sql .= " AND t.department_id = ?";
            $params[] = $departmentId;
        }
        
        $countSql = str_replace("SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo, d.name as department_name, (SELECT COUNT(DISTINCT cs.class_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as class_count, (SELECT COUNT(DISTINCT cs.subject_id) FROM class_subjects cs WHERE cs.teacher_id = t.id AND cs.is_active = 1) as subject_count", "SELECT COUNT(*) as total", $sql);
        $total = $this->db->selectOne($countSql, $params)['total'] ?? 0;
        
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY u.last_name, u.first_name LIMIT {$perPage} OFFSET {$offset}";
        $teachers = $this->db->select($sql, $params);
        
        $this->json([
            'success' => true,
            'data' => $teachers,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * Get single teacher
     * GET /api/v1/teachers/{id}
     */
    public function show($id)
    {
        $teacher = $this->db->selectOne(
            "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                    u.gender, u.date_of_birth, u.address,
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
            $this->json(['success' => false, 'message' => 'معلم یافت نشد'], 404);
            return;
        }
        
        // Get assigned classes
        $teacher['assignments'] = $this->db->select(
            "SELECT cs.*, c.class_name, c.class_level, s.subject_name
             FROM class_subjects cs 
             JOIN classes c ON cs.class_id = c.id 
             JOIN subjects s ON cs.subject_id = s.id 
             WHERE cs.teacher_id = ? AND cs.is_active = 1",
            [$id]
        );
        
        $this->json(['success' => true, 'data' => $teacher]);
    }
    
    /**
     * Create teacher
     * POST /api/v1/teachers
     */
    public function store()
    {
        $data = $this->request->getJson();
        
        $errors = [];
        if (empty($data['first_name'])) $errors['first_name'] = 'نام الزامی است';
        if (empty($data['last_name'])) $errors['last_name'] = 'نام خانوادگی الزامی است';
        if (empty($data['email'])) $errors['email'] = 'ایمیل الزامی است';
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'خطای اعتبارسنجی', 'errors' => $errors], 422);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $userCode = Helper::generateCode('TCH');
            $teacherCode = Helper::generateCode('TEA');
            $employeeId = 'EMP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $hashedPassword = Helper::encryptPassword($data['password'] ?? 'teacher123');
            $teacherRoleId = $this->db->selectOne("SELECT id FROM roles WHERE name = 'teacher' LIMIT 1")['id'] ?? 6;
            
            $userId = $this->db->insert('users', [
                'user_code' => $userCode,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'role_id' => $teacherRoleId,
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
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'معلم با موفقیت ثبت شد',
                'data' => ['id' => $teacherId, 'teacher_code' => $teacherCode]
            ], 201);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Update teacher
     * PUT /api/v1/teachers/{id}
     */
    public function update($id)
    {
        $data = $this->request->getJson();
        
        $teacher = $this->db->selectOne("SELECT * FROM teachers WHERE id = ?", [$id]);
        if (!$teacher) {
            $this->json(['success' => false, 'message' => 'معلم یافت نشد'], 404);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            if (isset($data['first_name']) || isset($data['last_name'])) {
                $userData = ['updated_at' => date('Y-m-d H:i:s')];
                if (isset($data['first_name'])) $userData['first_name'] = $data['first_name'];
                if (isset($data['last_name'])) $userData['last_name'] = $data['last_name'];
                if (isset($data['phone'])) $userData['phone'] = $data['phone'];
                $this->db->update('users', $userData, 'id = ?', [$teacher['user_id']]);
            }
            
            $teacherData = ['updated_at' => date('Y-m-d H:i:s')];
            $fields = ['department_id', 'qualification', 'specialization', 'teaching_experience_years', 'contract_type', 'salary_grade'];
            foreach ($fields as $f) {
                if (isset($data[$f])) $teacherData[$f] = $data[$f];
            }
            $this->db->update('teachers', $teacherData, 'id = ?', [$id]);
            
            $this->db->commit();
            $this->json(['success' => true, 'message' => 'اطلاعات به‌روزرسانی شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete teacher
     * DELETE /api/v1/teachers/{id}
     */
    public function delete($id)
    {
        $teacher = $this->db->selectOne("SELECT * FROM teachers WHERE id = ?", [$id]);
        if (!$teacher) {
            $this->json(['success' => false, 'message' => 'معلم یافت نشد'], 404);
            return;
        }
        
        $this->db->update('users', ['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0], 'id = ?', [$teacher['user_id']]);
        $this->json(['success' => true, 'message' => 'معلم حذف شد']);
    }
    
    /**
     * Get teacher timetable
     * GET /api/v1/teachers/{id}/timetable
     */
    public function timetable($id)
    {
        $timetable = $this->db->select(
            "SELECT tt.*, sec.section_name, s.subject_name, c.class_name
             FROM timetables tt 
             JOIN sections sec ON tt.section_id = sec.id 
             JOIN subjects s ON tt.subject_id = s.id 
             JOIN classes c ON sec.class_id = c.id 
             WHERE tt.teacher_id = ?
             ORDER BY FIELD(tt.day_of_week, 'Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'), tt.start_time",
            [$id]
        );
        
        $this->json(['success' => true, 'data' => $timetable]);
    }
    
    /**
     * Get teacher workload
     * GET /api/v1/teachers/{id}/workload
     */
    public function workload($id)
    {
        $weeklyPeriods = $this->db->selectOne("SELECT COUNT(*) as count FROM timetables WHERE teacher_id = ?", [$id])['count'] ?? 0;
        $classCount = $this->db->selectOne("SELECT COUNT(DISTINCT class_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1", [$id])['count'] ?? 0;
        $subjectCount = $this->db->selectOne("SELECT COUNT(DISTINCT subject_id) as count FROM class_subjects WHERE teacher_id = ? AND is_active = 1", [$id])['count'] ?? 0;
        $studentCount = $this->db->selectOne(
            "SELECT COUNT(DISTINCT s.id) as count FROM class_subjects cs 
             JOIN sections sec ON cs.class_id = sec.class_id 
             JOIN students s ON sec.id = s.current_section_id AND s.is_active = 1 
             WHERE cs.teacher_id = ? AND cs.is_active = 1", [$id]
        )['count'] ?? 0;
        
        $this->json([
            'success' => true,
            'data' => [
                'weekly_periods' => (int)$weeklyPeriods,
                'class_count' => (int)$classCount,
                'subject_count' => (int)$subjectCount,
                'student_count' => (int)$studentCount,
                'utilization' => min(round(($weeklyPeriods / 40) * 100, 2), 100)
            ]
        ]);
    }
    
    /**
     * Get teacher attendance
     * GET /api/v1/teachers/{id}/attendance
     */
    public function attendance($id)
    {
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-t'));
        
        $records = $this->db->select(
            "SELECT * FROM teacher_attendance WHERE teacher_id = ? AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date DESC",
            [$id, $startDate, $endDate]
        );
        
        $this->json(['success' => true, 'data' => $records]);
    }
}