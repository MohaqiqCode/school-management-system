<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class StudentController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        $classFilter = $_GET['class_id'] ?? '';
        $sectionFilter = $_GET['section_id'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.gender, u.date_of_birth,
                       c.class_name, c.class_level,
                       sec.section_name,
                       b.name as branch_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                JOIN branches b ON s.branch_id = b.id 
                WHERE u.deleted_at IS NULL";
        
        $params = [];
        
        // Filter by branch (for non-super-admin users)
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($search) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_code LIKE ? OR s.admission_number LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($classFilter) {
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classFilter;
        }
        
        if ($sectionFilter) {
            $sql .= " AND s.current_section_id = ?";
            $params[] = $sectionFilter;
        }
        
        if ($statusFilter !== '') {
            $sql .= " AND s.is_active = ?";
            $params[] = $statusFilter;
        }
        
        // Pagination
        $countSql = str_replace(
            "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo, u.gender, u.date_of_birth, c.class_name, c.class_level, sec.section_name, b.name as branch_name", 
            "SELECT COUNT(*) as total", 
            $sql
        );
        
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY s.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $students = $this->db->select($sql, $params);
        
        // Get filters data
        $classes = $this->db->select("SELECT id, class_name, class_level FROM classes WHERE is_active = 1 ORDER BY class_level");
        $sections = $classFilter ? $this->db->select("SELECT id, section_name FROM sections WHERE class_id = ? AND is_active = 1", [$classFilter]) : [];
        
        $this->view('students/index', [
            'title' => 'مدیریت دانش‌آموزان',
            'students' => $students,
            'classes' => $classes,
            'sections' => $sections,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'classFilter' => $classFilter,
            'sectionFilter' => $sectionFilter,
            'statusFilter' => $statusFilter
        ]);
    }
    
    public function create()
    {
        $branchId = $this->session->get('branch_id');
        $classes = $this->db->select("SELECT id, class_name, class_level FROM classes WHERE branch_id = ? AND is_active = 1 ORDER BY class_level", [$branchId]);
        
        $this->view('students/create', [
            'title' => 'ثبت نام دانش‌آموز جدید',
            'classes' => $classes
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = $this->validateStudentData($data);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/students/create');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Generate codes
            $userCode = Helper::generateCode('STU');
            $studentCode = Helper::generateCode('STD');
            $admissionNumber = 'ADM-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            
            // Create user
            $hashedPassword = Helper::encryptPassword($data['password'] ?? 'student123');
            
            $userId = $this->db->insert('users', [
                'user_code' => $userCode,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'role_id' => $this->getStudentRoleId(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? 'other',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Handle profile photo
            $profilePhoto = null;
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $profilePhoto = $this->uploadProfilePhoto($_FILES['profile_photo'], $userId);
                if ($profilePhoto) {
                    $this->db->update('users', ['profile_photo' => $profilePhoto], 'id = ?', [$userId]);
                }
            }
            
            // Create student record
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
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                'previous_school' => $data['previous_school'] ?? null,
                'transportation_required' => isset($data['transportation_required']) ? 1 : 0,
                'hostel_required' => isset($data['hostel_required']) ? 1 : 0,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Handle documents upload
            if (isset($_FILES['documents'])) {
                foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                        $documentType = $_POST['document_types'][$key] ?? 'other';
                        $fileData = [
                            'name' => $_FILES['documents']['name'][$key],
                            'tmp_name' => $tmpName,
                            'size' => $_FILES['documents']['size'][$key],
                            'type' => $_FILES['documents']['type'][$key]
                        ];
                        
                        $this->uploadStudentDocument($studentId, $fileData, $documentType);
                    }
                }
            }
            
            // Create parent if data provided
            if (!empty($data['parent_first_name']) && !empty($data['parent_email'])) {
                $parentId = $this->createParent($data, $userId);
                
                if ($parentId) {
                    $this->db->insert('student_parent', [
                        'student_id' => $studentId,
                        'parent_id' => $parentId,
                        'relationship' => $data['parent_relation'] ?? 'parent',
                        'is_emergency_contact' => 1,
                        'can_pickup' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $this->db->commit();
            
            $this->logActivity('students', 'create', "دانش‌آموز جدید '{$data['first_name']} {$data['last_name']}' با کد {$studentCode} ثبت شد");
            
            $this->session->flash('success', 'دانش‌آموز جدید با موفقیت ثبت نام شد');
            $this->redirect('/students');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در ثبت نام دانش‌آموز: ' . $e->getMessage());
            $this->session->flash('old', $data);
            $this->redirect('/students/create');
        }
    }
    
    public function show($id)
    {
        $student = $this->db->selectOne(
            "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                    u.gender, u.date_of_birth, u.address, u.city, u.state, u.country,
                    u.is_active as user_active,
                    c.class_name, c.class_level,
                    sec.section_name,
                    b.name as branch_name, b.branch_code
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id 
             JOIN branches b ON s.branch_id = b.id 
             WHERE s.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
        
        if (!$student) {
            $this->session->flash('error', 'دانش‌آموز مورد نظر یافت نشد');
            $this->redirect('/students');
            return;
        }
        
        // Get parents
        $parents = $this->db->select(
            "SELECT p.*, u.first_name, u.last_name, u.email, u.phone,
                    sp.relationship, sp.is_emergency_contact, sp.can_pickup
             FROM parents p 
             JOIN users u ON p.user_id = u.id 
             JOIN student_parent sp ON p.id = sp.parent_id 
             WHERE sp.student_id = ?",
            [$id]
        );
        
        // Get documents
        $documents = $this->db->select(
            "SELECT * FROM student_documents WHERE student_id = ? ORDER BY upload_date DESC",
            [$id]
        );
        
        // Get attendance summary
        $attendanceSummary = $this->getStudentAttendanceSummary($id);
        
        // Get recent results
        $recentResults = $this->db->select(
            "SELECT er.*, s.subject_name, e.exam_date, et.name as exam_type,
                    e.total_marks, e.passing_marks
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN exam_types et ON e.exam_type_id = et.id 
             WHERE er.student_id = ? 
             ORDER BY e.exam_date DESC 
             LIMIT 10",
            [$id]
        );
        
        // Get promotion history
        $promotionHistory = $this->db->select(
            "SELECT sp.*, 
                    fc.class_name as from_class_name, 
                    tc.class_name as to_class_name,
                    ay.year_name,
                    up.first_name as promoted_by_first, up.last_name as promoted_by_last
             FROM student_promotions sp 
             JOIN classes fc ON sp.from_class_id = fc.id 
             JOIN classes tc ON sp.to_class_id = tc.id 
             JOIN academic_years ay ON sp.academic_year_id = ay.id 
             LEFT JOIN users up ON sp.promoted_by = up.id 
             WHERE sp.student_id = ? 
             ORDER BY sp.promotion_date DESC",
            [$id]
        );
        
        // Get fee status
        $feeStatus = $this->db->select(
            "SELECT sf.*, fc.category_name, fs.amount as structure_amount
             FROM student_fees sf 
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             WHERE sf.student_id = ? 
             ORDER BY sf.due_date DESC 
             LIMIT 10",
            [$id]
        );
        
        $this->view('students/show', [
            'title' => 'پروفایل دانش‌آموز',
            'student' => $student,
            'parents' => $parents,
            'documents' => $documents,
            'attendanceSummary' => $attendanceSummary,
            'recentResults' => $recentResults,
            'promotionHistory' => $promotionHistory,
            'feeStatus' => $feeStatus
        ]);
    }
    
    public function edit($id)
    {
        $student = $this->db->selectOne(
            "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                    u.gender, u.date_of_birth, u.address, u.city, u.state
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.id = ?",
            [$id]
        );
        
        if (!$student) {
            $this->session->flash('error', 'دانش‌آموز مورد نظر یافت نشد');
            $this->redirect('/students');
            return;
        }
        
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE is_active = 1 ORDER BY class_level");
        $sections = $this->db->select("SELECT id, section_name FROM sections WHERE class_id = ? AND is_active = 1", [$student['current_class_id']]);
        
        $this->view('students/edit', [
            'title' => 'ویرایش اطلاعات دانش‌آموز',
            'student' => $student,
            'classes' => $classes,
            'sections' => $sections
        ]);
    }
    
    public function update($id)
    {
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        
        if (!$student) {
            $this->session->flash('error', 'دانش‌آموز مورد نظر یافت نشد');
            $this->redirect('/students');
            return;
        }
        
        $data = $_POST;
        
        // Validate
        $errors = $this->validateStudentData($data, $id);
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect("/students/{$id}/edit");
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update user
            $userUpdateData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? 'other',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle profile photo
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $userData = $this->db->selectOne("SELECT profile_photo FROM users WHERE id = ?", [$student['user_id']]);
                if ($userData['profile_photo']) {
                    $this->deleteFile($userData['profile_photo']);
                }
                
                $profilePhoto = $this->uploadProfilePhoto($_FILES['profile_photo'], $student['user_id']);
                if ($profilePhoto) {
                    $userUpdateData['profile_photo'] = $profilePhoto;
                }
            }
            
            $this->db->update('users', $userUpdateData, 'id = ?', [$student['user_id']]);
            
            // Update student
            $this->db->update('students', [
                'current_class_id' => $data['class_id'] ?? null,
                'current_section_id' => $data['section_id'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'medical_conditions' => $data['medical_conditions'] ?? null,
                'allergies' => $data['allergies'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                'previous_school' => $data['previous_school'] ?? null,
                'transportation_required' => isset($data['transportation_required']) ? 1 : 0,
                'hostel_required' => isset($data['hostel_required']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            $this->db->commit();
            
            $this->logActivity('students', 'update', "اطلاعات دانش‌آموز '{$data['first_name']} {$data['last_name']}' به‌روزرسانی شد");
            
            $this->session->flash('success', 'اطلاعات دانش‌آموز با موفقیت به‌روزرسانی شد');
            $this->redirect('/students');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در به‌روزرسانی اطلاعات: ' . $e->getMessage());
            $this->redirect("/students/{$id}/edit");
        }
    }
    
    public function delete($id)
    {
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز مورد نظر یافت نشد'], 404);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Soft delete user
            $this->db->update('users', [
                'deleted_at' => date('Y-m-d H:i:s'),
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$student['user_id']]);
            
            // Deactivate student
            $this->db->update('students', [
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            $this->db->commit();
            
            $userData = $this->db->selectOne("SELECT first_name, last_name FROM users WHERE id = ?", [$student['user_id']]);
            $this->logActivity('students', 'delete', "دانش‌آموز '{$userData['first_name']} {$userData['last_name']}' حذف شد");
            
            $this->json(['success' => true, 'message' => 'دانش‌آموز با موفقیت حذف شد']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در حذف دانش‌آموز: ' . $e->getMessage()]);
        }
    }
    
    public function promote($id)
    {
        $student = $this->db->selectOne(
            "SELECT s.*, u.first_name, u.last_name, c.class_name, c.class_level
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             WHERE s.id = ?",
            [$id]
        );
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز یافت نشد']);
            return;
        }
        
        $data = $_POST;
        
        $toClassId = $data['to_class_id'] ?? '';
        $toSectionId = $data['to_section_id'] ?? null;
        $academicYearId = $data['academic_year_id'] ?? '';
        $promotionDate = $data['promotion_date'] ?? date('Y-m-d');
        $remarks = $data['remarks'] ?? '';
        
        if (!$toClassId || !$academicYearId) {
            $this->json(['success' => false, 'message' => 'کلاس مقصد و سال تحصیلی الزامی است']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Record promotion
            $this->db->insert('student_promotions', [
                'student_id' => $id,
                'from_class_id' => $student['current_class_id'],
                'to_class_id' => $toClassId,
                'from_section_id' => $student['current_section_id'],
                'to_section_id' => $toSectionId,
                'academic_year_id' => $academicYearId,
                'promotion_date' => $promotionDate,
                'remarks' => $remarks,
                'promoted_by' => $this->session->get('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update student current class
            $this->db->update('students', [
                'current_class_id' => $toClassId,
                'current_section_id' => $toSectionId,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            $this->db->commit();
            
            $toClass = $this->db->selectOne("SELECT class_name FROM classes WHERE id = ?", [$toClassId]);
            $this->logActivity('students', 'promote', "دانش‌آموز '{$student['first_name']} {$student['last_name']}' به کلاس {$toClass['class_name']} ارتقاء یافت");
            
            $this->json(['success' => true, 'message' => 'دانش‌آموز با موفقیت ارتقاء یافت']);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در ارتقاء دانش‌آموز: ' . $e->getMessage()]);
        }
    }
    
    public function documents($id)
    {
        $student = $this->db->selectOne(
            "SELECT s.*, u.first_name, u.last_name 
             FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$id]
        );
        
        if (!$student) {
            $this->session->flash('error', 'دانش‌آموز یافت نشد');
            $this->redirect('/students');
            return;
        }
        
        $documents = $this->db->select(
            "SELECT sd.*, up.first_name as uploaded_by_name, up.last_name as uploaded_by_last
             FROM student_documents sd 
             LEFT JOIN users up ON sd.uploaded_by = up.id 
             WHERE sd.student_id = ? 
             ORDER BY sd.upload_date DESC",
            [$id]
        );
        
        $this->view('students/documents', [
            'title' => 'مدارک دانش‌آموز',
            'student' => $student,
            'documents' => $documents
        ]);
    }
    
    public function uploadDocument($id)
    {
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز یافت نشد']);
            return;
        }
        
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'فایل آپلود نشده است']);
            return;
        }
        
        $documentType = $_POST['document_type'] ?? 'other';
        $expiryDate = $_POST['expiry_date'] ?? null;
        
        $fileData = $_FILES['document'];
        
        try {
            $documentPath = $this->uploadStudentDocument($id, $fileData, $documentType);
            
            if ($documentPath) {
                $this->logActivity('students', 'document_upload', "مدرک جدید برای دانش‌آموز {$id} آپلود شد");
                
                $this->json(['success' => true, 'message' => 'مدرک با موفقیت آپلود شد', 'path' => $documentPath]);
            } else {
                $this->json(['success' => false, 'message' => 'خطا در آپلود مدرک']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
    }
    
    public function verifyDocument($documentId)
    {
        $document = $this->db->selectOne("SELECT * FROM student_documents WHERE id = ?", [$documentId]);
        
        if (!$document) {
            $this->json(['success' => false, 'message' => 'مدرک یافت نشد']);
            return;
        }
        
        $status = $_POST['status'] ?? 'verified';
        $notes = $_POST['verification_notes'] ?? '';
        
        $this->db->update('student_documents', [
            'status' => $status,
            'verification_notes' => $notes
        ], 'id = ?', [$documentId]);
        
        $this->logActivity('students', 'document_verify', "مدرک {$documentId} تایید شد");
        
        $this->json(['success' => true, 'message' => 'وضعیت مدرک به‌روزرسانی شد']);
    }
    
    private function validateStudentData($data, $studentId = null)
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
        } else {
            // Check unique email
            $sql = "SELECT id FROM users WHERE email = ? AND deleted_at IS NULL";
            $params = [$data['email']];
            
            if ($studentId) {
                $student = $this->db->selectOne("SELECT user_id FROM students WHERE id = ?", [$studentId]);
                if ($student) {
                    $sql .= " AND id != ?";
                    $params[] = $student['user_id'];
                }
            }
            
            $existing = $this->db->selectOne($sql, $params);
            if ($existing) {
                $errors['email'] = 'این ایمیل قبلاً ثبت شده است';
            }
        }
        
        if (empty($data['admission_date'])) {
            $errors['admission_date'] = 'تاریخ ثبت نام الزامی است';
        }
        
        return $errors;
    }
    
    private function getStudentRoleId()
    {
        $role = $this->db->selectOne("SELECT id FROM roles WHERE name = 'student' LIMIT 1");
        return $role['id'] ?? 10; // Default student role ID
    }
    
    private function getStudentAttendanceSummary($studentId)
    {
        $currentMonth = $this->db->selectOne(
            "SELECT 
                COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) as present,
                COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
                COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused,
                COUNT(*) as total
             FROM student_attendance 
             WHERE student_id = ? 
             AND MONTH(attendance_date) = MONTH(CURDATE()) 
             AND YEAR(attendance_date) = YEAR(CURDATE())",
            [$studentId]
        );
        
        $overall = $this->db->selectOne(
            "SELECT 
                COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) as present,
                COUNT(*) as total
             FROM student_attendance 
             WHERE student_id = ?",
            [$studentId]
        );
        
        return [
            'current_month' => $currentMonth,
            'overall' => $overall,
            'current_percentage' => ($currentMonth['total'] > 0) 
                ? round(($currentMonth['present'] / $currentMonth['total']) * 100, 2) 
                : 0,
            'overall_percentage' => ($overall['total'] > 0) 
                ? round(($overall['present'] / $overall['total']) * 100, 2) 
                : 0
        ];
    }
    
    private function createParent($data, $studentUserId)
    {
        // Check if parent user already exists
        $existingUser = $this->db->selectOne("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL", [$data['parent_email']]);
        
        if ($existingUser) {
            // Check if parent record exists
            $existingParent = $this->db->selectOne("SELECT id FROM parents WHERE user_id = ?", [$existingUser['id']]);
            
            if ($existingParent) {
                return $existingParent['id'];
            }
            
            $parentUserId = $existingUser['id'];
        } else {
            // Create new user for parent
            $userCode = Helper::generateCode('PRT');
            $hashedPassword = Helper::encryptPassword($data['parent_password'] ?? 'parent123');
            
            $parentUserId = $this->db->insert('users', [
                'user_code' => $userCode,
                'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
                'role_id' => $this->getParentRoleId(),
                'first_name' => $data['parent_first_name'],
                'last_name' => $data['parent_last_name'] ?? '',
                'email' => $data['parent_email'],
                'password' => $hashedPassword,
                'phone' => $data['parent_phone'] ?? null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Create parent record
        $parentCode = Helper::generateCode('PAR');
        
        $parentId = $this->db->insert('parents', [
            'user_id' => $parentUserId,
            'parent_code' => $parentCode,
            'occupation' => $data['parent_occupation'] ?? null,
            'workplace' => $data['parent_workplace'] ?? null,
            'annual_income' => $data['parent_income'] ?? null,
            'relationship' => $data['parent_relation'] ?? 'father',
            'is_primary_contact' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $parentId;
    }
    
    private function getParentRoleId()
    {
        $role = $this->db->selectOne("SELECT id FROM roles WHERE name = 'parent' LIMIT 1");
        return $role['id'] ?? 11;
    }
    
    private function uploadProfilePhoto($file, $userId)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/profiles/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('فرمت فایل مجاز نیست');
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \Exception('حجم فایل نباید بیشتر از 2 مگابایت باشد');
        }
        
        $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/assets/uploads/profiles/' . $filename;
        }
        
        return null;
    }
    
    private function uploadStudentDocument($studentId, $file, $documentType)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/documents/students/' . $studentId . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('فرمت فایل مجاز نیست');
        }
        
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new \Exception('حجم فایل نباید بیشتر از 10 مگابایت باشد');
        }
        
        $filename = $documentType . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $relativePath = '/assets/uploads/documents/students/' . $studentId . '/' . $filename;
            
            $this->db->insert('student_documents', [
                'student_id' => $studentId,
                'document_type' => $documentType,
                'document_name' => $file['name'],
                'file_path' => $relativePath,
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'uploaded_by' => $this->session->get('user_id'),
                'upload_date' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ]);
            
            return $relativePath;
        }
        
        return null;
    }
    
    private function deleteFile($filePath)
    {
        $fullPath = __DIR__ . '/../../public' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
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