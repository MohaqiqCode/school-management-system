<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class ExamController extends Controller
{
    /**
     * List exams with filters
     * GET /api/v1/exams
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $perPage = min(100, $this->request->get('per_page', 25));
        $academicYearId = $this->request->get('academic_year_id', '');
        $classId = $this->request->get('class_id', '');
        $status = $this->request->get('status', '');
        $branchId = $this->session->get('branch_id');

        $sql = "SELECT e.*, et.name as exam_type_name, et.exam_category,
                       s.subject_name, s.subject_code,
                       c.class_name, c.class_level,
                       sem.semester_name, ay.year_name,
                       CONCAT(u.first_name, ' ', u.last_name) as invigilator_name,
                       (SELECT COUNT(*) FROM exam_results WHERE exam_id = e.id) as result_count
                FROM exams e 
                JOIN exam_types et ON e.exam_type_id = et.id 
                JOIN subjects s ON e.subject_id = s.id 
                JOIN classes c ON e.class_id = c.id 
                LEFT JOIN semesters sem ON e.semester_id = sem.id
                LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
                LEFT JOIN teachers t ON e.invigilator_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE 1=1";

        $params = [];

        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND c.branch_id = ?";
            $params[] = $branchId;
        }

        if ($academicYearId) {
            $sql .= " AND e.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        if ($classId) {
            $sql .= " AND e.class_id = ?";
            $params[] = $classId;
        }

        if ($status) {
            $sql .= " AND e.status = ?";
            $params[] = $status;
        }

        $countSql = str_replace(
            "SELECT e.*, et.name as exam_type_name, et.exam_category, s.subject_name, s.subject_code, c.class_name, c.class_level, sem.semester_name, ay.year_name, CONCAT(u.first_name, ' ', u.last_name) as invigilator_name, (SELECT COUNT(*) FROM exam_results WHERE exam_id = e.id) as result_count",
            "SELECT COUNT(*) as total",
            $sql
        );
        $total = $this->db->selectOne($countSql, $params)['total'] ?? 0;

        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY e.exam_date DESC LIMIT {$perPage} OFFSET {$offset}";
        $exams = $this->db->select($sql, $params);

        $this->json([
            'success' => true,
            'data' => $exams,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Get single exam details
     * GET /api/v1/exams/{id}
     */
    public function show($id)
    {
        $exam = $this->db->selectOne(
            "SELECT e.*, et.name as exam_type_name, et.exam_category,
                    s.subject_name, c.class_name, c.class_level,
                    sem.semester_name, ay.year_name,
                    CONCAT(u.first_name, ' ', u.last_name) as invigilator_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             LEFT JOIN semesters sem ON e.semester_id = sem.id
             LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
             LEFT JOIN teachers t ON e.invigilator_id = t.id
             LEFT JOIN users u ON t.user_id = u.id
             WHERE e.id = ?",
            [$id]
        );

        if (!$exam) {
            $this->json(['success' => false, 'message' => 'آزمون یافت نشد'], 404);
            return;
        }

        $this->json(['success' => true, 'data' => $exam]);
    }

    /**
     * Create new exam
     * POST /api/v1/exams
     */
    public function store()
    {
        $data = $this->request->getJson();

        // Validate
        $errors = [];
        if (empty($data['exam_type_id'])) $errors['exam_type_id'] = 'نوع آزمون الزامی است';
        if (empty($data['academic_year_id'])) $errors['academic_year_id'] = 'سال تحصیلی الزامی است';
        if (empty($data['class_id'])) $errors['class_id'] = 'کلاس الزامی است';
        if (empty($data['subject_id'])) $errors['subject_id'] = 'درس الزامی است';
        if (empty($data['exam_date'])) $errors['exam_date'] = 'تاریخ آزمون الزامی است';
        if (empty($data['total_marks']) || $data['total_marks'] <= 0) $errors['total_marks'] = 'نمره کل نامعتبر است';
        if (empty($data['passing_marks']) || $data['passing_marks'] <= 0) $errors['passing_marks'] = 'نمره قبولی نامعتبر است';
        if (($data['passing_marks'] ?? 0) > ($data['total_marks'] ?? 0)) $errors['passing_marks'] = 'نمره قبولی نمی‌تواند بیشتر از نمره کل باشد';

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'خطای اعتبارسنجی', 'errors' => $errors], 422);
            return;
        }

        $examId = $this->db->insert('exams', [
            'exam_type_id' => $data['exam_type_id'],
            'academic_year_id' => $data['academic_year_id'],
            'semester_id' => $data['semester_id'] ?? null,
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'exam_date' => $data['exam_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'total_marks' => $data['total_marks'],
            'passing_marks' => $data['passing_marks'],
            'room_number' => $data['room_number'] ?? null,
            'invigilator_id' => $data['invigilator_id'] ?? null,
            'status' => 'scheduled',
            'created_by' => $this->session->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->logActivity('exams', 'api_create', "Exam {$examId} created via API");

        $this->json([
            'success' => true,
            'message' => 'آزمون با موفقیت ایجاد شد',
            'data' => ['id' => $examId]
        ], 201);
    }

    /**
     * Update exam
     * PUT /api/v1/exams/{id}
     */
    public function update($id)
    {
        $data = $this->request->getJson();

        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if (!$exam) {
            $this->json(['success' => false, 'message' => 'آزمون یافت نشد'], 404);
            return;
        }

        $updateData = [];
        $updatableFields = ['exam_type_id', 'semester_id', 'subject_id', 'exam_date', 
                           'start_time', 'end_time', 'total_marks', 'passing_marks',
                           'room_number', 'invigilator_id', 'status'];

        foreach ($updatableFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->db->update('exams', $updateData, 'id = ?', [$id]);
            $this->logActivity('exams', 'api_update', "Exam {$id} updated via API");
        }

        $this->json(['success' => true, 'message' => 'آزمون با موفقیت به‌روزرسانی شد']);
    }

    /**
     * Delete exam
     * DELETE /api/v1/exams/{id}
     */
    public function delete($id)
    {
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if (!$exam) {
            $this->json(['success' => false, 'message' => 'آزمون یافت نشد'], 404);
            return;
        }

        // Check if results exist
        $resultCount = $this->db->selectOne("SELECT COUNT(*) as count FROM exam_results WHERE exam_id = ?", [$id])['count'] ?? 0;
        if ($resultCount > 0) {
            $this->json(['success' => false, 'message' => 'این آزمون دارای نتایج ثبت شده است و قابل حذف نیست'], 422);
            return;
        }

        $this->db->delete('exams', 'id = ?', [$id]);
        $this->logActivity('exams', 'api_delete', "Exam {$id} deleted via API");

        $this->json(['success' => true, 'message' => 'آزمون با موفقیت حذف شد']);
    }

    /**
     * Get exam results
     * GET /api/v1/exams/{id}/results
     */
    public function results($id)
    {
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if (!$exam) {
            $this->json(['success' => false, 'message' => 'آزمون یافت نشد'], 404);
            return;
        }

        $results = $this->db->select(
            "SELECT er.*, s.student_code, u.first_name, u.last_name,
                    e.total_marks, e.passing_marks
             FROM exam_results er 
             JOIN students s ON er.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             JOIN exams e ON er.exam_id = e.id 
             WHERE er.exam_id = ?
             ORDER BY er.marks_obtained DESC",
            [$id]
        );

        // Statistics
        $stats = $this->db->selectOne(
            "SELECT COUNT(*) as total_students,
                    COUNT(CASE WHEN er.marks_obtained >= e.passing_marks THEN 1 END) as passed,
                    COUNT(CASE WHEN er.marks_obtained < e.passing_marks AND er.is_absent = 0 THEN 1 END) as failed,
                    COUNT(CASE WHEN er.is_absent = 1 THEN 1 END) as absent,
                    ROUND(AVG(er.marks_obtained), 2) as average,
                    MAX(er.marks_obtained) as highest,
                    MIN(CASE WHEN er.is_absent = 0 THEN er.marks_obtained END) as lowest,
                    ROUND((COUNT(CASE WHEN er.marks_obtained >= e.passing_marks THEN 1 END) / COUNT(*)) * 100, 2) as pass_percentage
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             WHERE er.exam_id = ?",
            [$id]
        );

        $this->json([
            'success' => true,
            'data' => [
                'exam' => $exam,
                'results' => $results,
                'statistics' => $stats
            ]
        ]);
    }

    /**
     * Store marks for exam
     * POST /api/v1/exams/{id}/marks
     */
    public function storeMarks($id)
    {
        $data = $this->request->getJson();
        $marks = $data['marks'] ?? [];

        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if (!$exam) {
            $this->json(['success' => false, 'message' => 'آزمون یافت نشد'], 404);
            return;
        }

        if (empty($marks)) {
            $this->json(['success' => false, 'message' => 'داده‌ای برای ثبت وجود ندارد'], 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            foreach ($marks as $studentId => $markData) {
                $marksObtained = floatval($markData['marks_obtained'] ?? 0);
                $isAbsent = isset($markData['is_absent']) && $markData['is_absent'] ? 1 : 0;

                // Calculate grade
                $percentage = $exam['total_marks'] > 0 ? ($marksObtained / $exam['total_marks']) * 100 : 0;
                $grade = $this->calculateGrade($percentage);

                $existing = $this->db->selectOne(
                    "SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ?",
                    [$id, $studentId]
                );

                $resultData = [
                    'marks_obtained' => $marksObtained,
                    'practical_marks' => $markData['practical_marks'] ?? 0,
                    'internal_assessment' => $markData['internal_assessment'] ?? 0,
                    'grade' => $grade['grade'],
                    'grade_point' => $grade['grade_point'],
                    'is_absent' => $isAbsent,
                    'remarks' => $markData['remarks'] ?? '',
                    'entered_by' => $this->session->get('user_id'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($existing) {
                    $this->db->update('exam_results', $resultData, 'id = ?', [$existing['id']]);
                } else {
                    $resultData['exam_id'] = $id;
                    $resultData['student_id'] = $studentId;
                    $resultData['result_date'] = date('Y-m-d H:i:s');
                    $resultData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('exam_results', $resultData);
                }
            }

            // Update exam status to completed
            $this->db->update('exams', ['status' => 'completed'], 'id = ?', [$id]);

            $this->db->commit();
            $this->json(['success' => true, 'message' => 'نمرات با موفقیت ثبت شد']);

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در ثبت نمرات: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade($percentage)
    {
        if ($percentage >= 95) return ['grade' => 'A+', 'grade_point' => 4.0];
        if ($percentage >= 90) return ['grade' => 'A', 'grade_point' => 4.0];
        if ($percentage >= 85) return ['grade' => 'A-', 'grade_point' => 3.7];
        if ($percentage >= 80) return ['grade' => 'B+', 'grade_point' => 3.3];
        if ($percentage >= 75) return ['grade' => 'B', 'grade_point' => 3.0];
        if ($percentage >= 70) return ['grade' => 'B-', 'grade_point' => 2.7];
        if ($percentage >= 65) return ['grade' => 'C+', 'grade_point' => 2.3];
        if ($percentage >= 60) return ['grade' => 'C', 'grade_point' => 2.0];
        if ($percentage >= 55) return ['grade' => 'C-', 'grade_point' => 1.7];
        if ($percentage >= 50) return ['grade' => 'D', 'grade_point' => 1.0];
        return ['grade' => 'F', 'grade_point' => 0.0];
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