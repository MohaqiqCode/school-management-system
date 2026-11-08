<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Helpers\Helper;

class StudentController extends Controller
{
    /**
     * List students with pagination and filters
     * GET /api/v1/students
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $perPage = min(100, $this->request->get('per_page', 25));
        $search = $this->request->get('search', '');
        $classId = $this->request->get('class_id', '');
        $sectionId = $this->request->get('section_id', '');
        $status = $this->request->get('status', '');
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.gender, u.date_of_birth,
                       c.class_name, c.class_level,
                       sec.section_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                WHERE u.deleted_at IS NULL";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($search) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_code LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($classId) {
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
        }
        
        if ($sectionId) {
            $sql .= " AND s.current_section_id = ?";
            $params[] = $sectionId;
        }
        
        if ($status !== '') {
            $sql .= " AND s.is_active = ?";
            $params[] = $status;
        }
        
        // Count
        $countSql = str_replace("SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo, u.gender, u.date_of_birth, c.class_name, c.class_level, sec.section_name", "SELECT COUNT(*) as total", $sql);
        $total = $this->db->selectOne($countSql, $params)['total'] ?? 0;
        
        // Data
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY u.last_name, u.first_name LIMIT {$perPage} OFFSET {$offset}";
        $students = $this->db->select($sql, $params);
        
        $this->json([
            'success' => true,
            'data' => $students,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * Get single student details
     * GET /api/v1/students/{id}
     */
    public function show($id)
    {
        $student = $this->db->selectOne(
            "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                    u.gender, u.date_of_birth, u.address, u.city,
                    c.class_name, c.class_level,
                    sec.section_name,
                    b.name as branch_name
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id 
             JOIN branches b ON s.branch_id = b.id 
             WHERE s.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز یافت نشد'], 404);
            return;
        }
        
        // Get parents
        $parents = $this->db->select(
            "SELECT p.*, u.first_name, u.last_name, u.email, u.phone,
                    sp.relationship, sp.is_emergency_contact
             FROM parents p 
             JOIN users u ON p.user_id = u.id 
             JOIN student_parent sp ON p.id = sp.parent_id 
             WHERE sp.student_id = ?",
            [$id]
        );
        
        $student['parents'] = $parents;
        
        $this->json(['success' => true, 'data' => $student]);
    }
    
    /**
     * Create new student
     * POST /api/v1/students
     */
    public function store()
    {
        $data = $this->request->getJson();
        
        // Validate
        $errors = [];
        if (empty($data['first_name'])) $errors['first_name'] = 'نام الزامی است';
        if (empty($data['last_name'])) $errors['last_name'] = 'نام خانوادگی الزامی است';
        if (empty($data['email'])) $errors['email'] = 'ایمیل الزامی است';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'ایمیل نامعتبر است';
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'خطای اعتبارسنجی', 'errors' => $errors], 422);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $userCode = Helper::generateCode('STU');
            $studentCode = Helper::generateCode('STD');
            $admissionNumber = 'ADM-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $hashedPassword = Helper::encryptPassword($data['password'] ?? 'student123');
            $studentRoleId = $this->db->selectOne("SELECT id FROM roles WHERE name = 'student' LIMIT 1")['id'] ?? 10;
            
            $userId = $this->db->insert('users', [
                'user_code' => $userCode,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'role_id' => $studentRoleId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? 'other',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $studentId = $this->db->insert('students', [
                'user_id' => $userId,
                'student_code' => $studentCode,
                'admission_number' => $admissionNumber,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'current_class_id' => $data['class_id'] ?? null,
                'current_section_id' => $data['section_id'] ?? null,
                'admission_date' => $data['admission_date'] ?? date('Y-m-d'),
                'admission_type' => $data['admission_type'] ?? 'new',
                'blood_group' => $data['blood_group'] ?? null,
                'medical_conditions' => $data['medical_conditions'] ?? null,
                'allergies' => $data['allergies'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'previous_school' => $data['previous_school'] ?? null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            
            $this->logActivity('students', 'api_create', "Student {$studentCode} created via API");
            
            $this->json([
                'success' => true,
                'message' => 'دانش‌آموز با موفقیت ثبت شد',
                'data' => ['id' => $studentId, 'student_code' => $studentCode]
            ], 201);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در ثبت دانش‌آموز: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Update student
     * PUT /api/v1/students/{id}
     */
    public function update($id)
    {
        $data = $this->request->getJson();
        
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        if (!$student) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز یافت نشد'], 404);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update user
            if (isset($data['first_name']) || isset($data['last_name']) || isset($data['phone'])) {
                $userData = ['updated_at' => date('Y-m-d H:i:s')];
                if (isset($data['first_name'])) $userData['first_name'] = $data['first_name'];
                if (isset($data['last_name'])) $userData['last_name'] = $data['last_name'];
                if (isset($data['phone'])) $userData['phone'] = $data['phone'];
                if (isset($data['gender'])) $userData['gender'] = $data['gender'];
                if (isset($data['date_of_birth'])) $userData['date_of_birth'] = $data['date_of_birth'];
                
                $this->db->update('users', $userData, 'id = ?', [$student['user_id']]);
            }
            
            // Update student
            $studentData = ['updated_at' => date('Y-m-d H:i:s')];
            $updatableFields = ['current_class_id', 'current_section_id', 'blood_group', 
                               'medical_conditions', 'allergies', 'emergency_contact_name',
                               'emergency_contact_phone', 'previous_school', 'is_active'];
            
            foreach ($updatableFields as $field) {
                if (isset($data[$field])) {
                    $studentData[$field] = $data[$field];
                }
            }
            
            $this->db->update('students', $studentData, 'id = ?', [$id]);
            
            $this->db->commit();
            
            $this->logActivity('students', 'api_update', "Student {$id} updated via API");
            
            $this->json(['success' => true, 'message' => 'اطلاعات با موفقیت به‌روزرسانی شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در به‌روزرسانی: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete student
     * DELETE /api/v1/students/{id}
     */
    public function delete($id)
    {
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        if (!$student) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز یافت نشد'], 404);
            return;
        }
        
        $this->db->update('users', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$student['user_id']]);
        
        $this->db->update('students', [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('students', 'api_delete', "Student {$id} deleted via API");
        
        $this->json(['success' => true, 'message' => 'دانش‌آموز با موفقیت حذف شد']);
    }
    
    /**
     * Get student attendance
     * GET /api/v1/students/{id}/attendance
     */
    public function attendance($id)
    {
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-t'));
        
        $attendance = $this->db->select(
            "SELECT attendance_date, status, check_in_time, remarks
             FROM student_attendance 
             WHERE student_id = ? AND attendance_date BETWEEN ? AND ?
             ORDER BY attendance_date DESC",
            [$id, $startDate, $endDate]
        );
        
        $stats = $this->db->selectOne(
            "SELECT COUNT(*) as total,
                    COUNT(CASE WHEN status IN ('present','late') THEN 1 END) as present,
                    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent
             FROM student_attendance 
             WHERE student_id = ? AND attendance_date BETWEEN ? AND ?",
            [$id, $startDate, $endDate]
        );
        
        $this->json([
            'success' => true,
            'data' => [
                'records' => $attendance,
                'stats' => $stats,
                'attendance_rate' => ($stats['total'] ?? 0) > 0 ? round(($stats['present'] / $stats['total']) * 100, 2) : 0
            ]
        ]);
    }
    
    /**
     * Get student attendance stats
     * GET /api/v1/students/{id}/attendance/stats
     */
    public function attendanceStats($id)
    {
        $stats = $this->db->selectOne(
            "SELECT COUNT(*) as total_days,
                    COUNT(CASE WHEN status IN ('present','late') THEN 1 END) as present_days,
                    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days,
                    COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused_days
             FROM student_attendance 
             WHERE student_id = ?",
            [$id]
        );
        
        $this->json(['success' => true, 'data' => $stats]);
    }
    
    /**
     * Get student results
     * GET /api/v1/students/{id}/results
     */
    public function results($id)
    {
        $results = $this->db->select(
            "SELECT er.*, e.exam_date, et.name as exam_type,
                    s.subject_name, e.total_marks, e.passing_marks
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             WHERE er.student_id = ?
             ORDER BY e.exam_date DESC",
            [$id]
        );
        
        $this->json(['success' => true, 'data' => $results]);
    }
    
    /**
     * Get student report card
     * GET /api/v1/students/{id}/report-card
     */
    public function reportCard($id)
    {
        $academicYearId = $this->request->get('academic_year_id');
        
        $student = $this->db->selectOne(
            "SELECT s.*, u.first_name, u.last_name, c.class_name, sec.section_name
             FROM students s JOIN users u ON s.user_id = u.id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id
             WHERE s.id = ?", [$id]
        );
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز یافت نشد'], 404);
            return;
        }
        
        $results = $this->db->select(
            "SELECT er.*, e.exam_date, et.name as exam_type,
                    s.subject_name, e.total_marks, e.passing_marks
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             WHERE er.student_id = ?" . ($academicYearId ? " AND e.academic_year_id = ?" : "") . "
             ORDER BY e.exam_date",
            $academicYearId ? [$id, $academicYearId] : [$id]
        );
        
        $this->json(['success' => true, 'data' => ['student' => $student, 'results' => $results]]);
    }
    
    /**
     * Get student fees
     * GET /api/v1/students/{id}/fees
     */
    public function fees($id)
    {
        $fees = $this->db->select(
            "SELECT sf.*, fc.category_name, fs.amount as structure_amount
             FROM student_fees sf 
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             WHERE sf.student_id = ?
             ORDER BY sf.due_date DESC",
            [$id]
        );
        
        $summary = $this->db->selectOne(
            "SELECT COALESCE(SUM(net_payable),0) as total,
                    COALESCE(SUM(paid_amount),0) as paid,
                    COALESCE(SUM(balance_amount),0) as balance
             FROM student_fees WHERE student_id = ? AND status != 'waived'",
            [$id]
        );
        
        $this->json(['success' => true, 'data' => ['fees' => $fees, 'summary' => $summary]]);
    }
    
    /**
     * Pay student fee
     * POST /api/v1/students/{id}/fees/pay
     */
    public function payFee($id)
    {
        $data = $this->request->getJson();
        $studentFeeId = $data['student_fee_id'] ?? '';
        $amount = floatval($data['amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'online';
        
        if (!$studentFeeId || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'اطلاعات پرداخت نامعتبر است'], 422);
            return;
        }
        
        $studentFee = $this->db->selectOne("SELECT * FROM student_fees WHERE id = ? AND student_id = ?", [$studentFeeId, $id]);
        if (!$studentFee) {
            $this->json(['success' => false, 'message' => 'رکورد شهریه یافت نشد'], 404);
            return;
        }
        
        if ($amount > $studentFee['balance_amount']) {
            $this->json(['success' => false, 'message' => 'مبلغ پرداختی بیشتر از مبلغ باقیمانده است'], 422);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $this->db->insert('fee_payments', [
                'student_fee_id' => $studentFeeId,
                'payment_number' => $paymentNumber,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_date' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $newPaid = $studentFee['paid_amount'] + $amount;
            $newBalance = $studentFee['net_payable'] - $newPaid;
            $newStatus = $newBalance <= 0 ? 'paid' : 'partial';
            
            $this->db->update('student_fees', [
                'paid_amount' => $newPaid,
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$studentFeeId]);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت ثبت شد',
                'data' => ['payment_number' => $paymentNumber, 'new_balance' => $newBalance]
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در ثبت پرداخت'], 500);
        }
    }
    
    /**
     * Get student documents
     * GET /api/v1/students/{id}/documents
     */
    public function documents($id)
    {
        $documents = $this->db->select(
            "SELECT * FROM student_documents WHERE student_id = ? ORDER BY upload_date DESC",
            [$id]
        );
        
        $this->json(['success' => true, 'data' => $documents]);
    }
    
    /**
     * Upload student document
     * POST /api/v1/students/{id}/documents
     */
    public function uploadDocument($id)
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'فایلی آپلود نشده است'], 422);
            return;
        }
        
        $file = $_FILES['file'];
        $documentType = $_POST['document_type'] ?? 'other';
        
        // Validate file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowedTypes)) {
            $this->json(['success' => false, 'message' => 'فرمت فایل مجاز نیست'], 422);
            return;
        }
        
        if ($file['size'] > 10 * 1024 * 1024) {
            $this->json(['success' => false, 'message' => 'حجم فایل نباید بیشتر از ۱۰ مگابایت باشد'], 422);
            return;
        }
        
        $uploadDir = __DIR__ . '/../../../public/assets/uploads/documents/students/' . $id . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $filename = $documentType . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $relativePath = '/assets/uploads/documents/students/' . $id . '/' . $filename;
            
            $this->db->insert('student_documents', [
                'student_id' => $id,
                'document_type' => $documentType,
                'document_name' => $file['name'],
                'file_path' => $relativePath,
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'uploaded_by' => $this->session->get('user_id'),
                'upload_date' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ]);
            
            $this->json(['success' => true, 'message' => 'مدرک با موفقیت آپلود شد', 'data' => ['path' => $relativePath]]);
        } else {
            $this->json(['success' => false, 'message' => 'خطا در ذخیره فایل'], 500);
        }
    }
    
    private function logActivity($module, $action, $description)
    {
        try {
            $this->db->insert('audit_logs', [
                'user_id' => $this->session->get('user_id'),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => $this->request->getIpAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {}
    }
}