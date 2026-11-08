<?php
namespace App\Controllers;

use App\Core\Controller;

class ExamController extends Controller
{
    public function index()
    {
        $academicYearId = $_GET['academic_year_id'] ?? '';
        $classId = $_GET['class_id'] ?? '';
        
        $query = "SELECT e.*, et.name as exam_type_name, s.subject_name, 
                         c.class_name, sem.semester_name,
                         CONCAT(ut.first_name, ' ', ut.last_name) as invigilator_name
                  FROM exams e 
                  JOIN exam_types et ON e.exam_type_id = et.id 
                  JOIN subjects s ON e.subject_id = s.id 
                  JOIN classes c ON e.class_id = c.id 
                  LEFT JOIN semesters sem ON e.semester_id = sem.id
                  LEFT JOIN teachers t ON e.invigilator_id = t.id
                  LEFT JOIN users ut ON t.user_id = ut.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($academicYearId) {
            $query .= " AND e.academic_year_id = ?";
            $params[] = $academicYearId;
        }
        
        if ($classId) {
            $query .= " AND e.class_id = ?";
            $params[] = $classId;
        }
        
        $query .= " ORDER BY e.exam_date DESC, c.class_level, s.subject_name";
        
        $exams = $this->db->select($query, $params);
        
        // Get dropdown data
        $academicYears = $this->db->select("SELECT id, year_name FROM academic_years WHERE status = 'active' ORDER BY start_date DESC");
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE is_active = 1 ORDER BY class_level");
        
        $this->view('exams/index', [
            'title' => 'مدیریت آزمون‌ها',
            'exams' => $exams,
            'academicYears' => $academicYears,
            'classes' => $classes,
            'selectedYear' => $academicYearId,
            'selectedClass' => $classId
        ]);
    }
    
    public function create()
    {
        $examTypes = $this->db->select("SELECT * FROM exam_types WHERE is_active = 1");
        $academicYears = $this->db->select("SELECT * FROM academic_years WHERE status = 'active' ORDER BY start_date DESC");
        $classes = $this->db->select("SELECT * FROM classes WHERE is_active = 1 ORDER BY class_level");
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1"
        );
        
        $this->view('exams/create', [
            'title' => 'ایجاد آزمون جدید',
            'examTypes' => $examTypes,
            'academicYears' => $academicYears,
            'classes' => $classes,
            'teachers' => $teachers
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['exam_type_id'])) {
            $errors['exam_type_id'] = 'نوع آزمون الزامی است';
        }
        
        if (empty($data['academic_year_id'])) {
            $errors['academic_year_id'] = 'سال تحصیلی الزامی است';
        }
        
        if (empty($data['class_id'])) {
            $errors['class_id'] = 'کلاس الزامی است';
        }
        
        if (empty($data['subject_id'])) {
            $errors['subject_id'] = 'درس الزامی است';
        }
        
        if (empty($data['exam_date'])) {
            $errors['exam_date'] = 'تاریخ آزمون الزامی است';
        }
        
        if (empty($data['total_marks']) || $data['total_marks'] <= 0) {
            $errors['total_marks'] = 'نمره کل باید بزرگتر از صفر باشد';
        }
        
        if (empty($data['passing_marks']) || $data['passing_marks'] <= 0) {
            $errors['passing_marks'] = 'نمره قبولی باید بزرگتر از صفر باشد';
        }
        
        if ($data['passing_marks'] > $data['total_marks']) {
            $errors['passing_marks'] = 'نمره قبولی نمی‌تواند بیشتر از نمره کل باشد';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/exams/create');
            return;
        }
        
        // Create exam
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
        
        $this->logActivity('exams', 'create', "آزمون جدید با شناسه {$examId} ایجاد شد");
        
        $this->session->flash('success', 'آزمون با موفقیت ایجاد شد');
        $this->redirect('/exams');
    }
    
    public function enterMarks($examId)
    {
        $exam = $this->db->selectOne(
            "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             WHERE e.id = ?",
            [$examId]
        );
        
        if (!$exam) {
            $this->session->flash('error', 'آزمون مورد نظر یافت نشد');
            $this->redirect('/exams');
            return;
        }
        
        // Get students
        $students = $this->db->select(
            "SELECT s.id, s.student_code, u.first_name, u.last_name,
                    er.marks_obtained, er.practical_marks, er.internal_assessment,
                    er.grade, er.is_absent, er.remarks
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             JOIN sections sec ON s.current_section_id = sec.id
             LEFT JOIN exam_results er ON s.id = er.student_id AND er.exam_id = ?
             WHERE sec.class_id = ? AND s.is_active = 1
             ORDER BY u.last_name, u.first_name",
            [$examId, $exam['class_id']]
        );
        
        $this->view('exams/enter_marks', [
            'title' => 'ثبت نمرات',
            'exam' => $exam,
            'students' => $students
        ]);
    }
    
    public function storeMarks($examId)
    {
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$examId]);
        
        if (!$exam) {
            $this->json(['success' => false, 'message' => 'آزمون یافت نشد']);
            return;
        }
        
        $marks = $_POST['marks'] ?? [];
        $enteredBy = $this->session->get('user_id');
        
        if (empty($marks)) {
            $this->json(['success' => false, 'message' => 'داده‌ای برای ثبت وجود ندارد']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($marks as $studentId => $data) {
                $marksObtained = floatval($data['marks_obtained'] ?? 0);
                $practicalMarks = floatval($data['practical_marks'] ?? 0);
                $internalAssessment = floatval($data['internal_assessment'] ?? 0);
                $isAbsent = isset($data['is_absent']) ? 1 : 0;
                $remarks = $data['remarks'] ?? '';
                
                // Calculate grade
                $grade = $this->calculateGrade($marksObtained + $practicalMarks + $internalAssessment, $exam['total_marks'], $exam['passing_marks']);
                
                $existing = $this->db->selectOne(
                    "SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ?",
                    [$examId, $studentId]
                );
                
                $resultData = [
                    'marks_obtained' => $marksObtained,
                    'practical_marks' => $practicalMarks,
                    'internal_assessment' => $internalAssessment,
                    'grade' => $grade['grade'],
                    'grade_point' => $grade['grade_point'],
                    'is_absent' => $isAbsent,
                    'remarks' => $remarks,
                    'entered_by' => $enteredBy,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    $this->db->update('exam_results', $resultData, 'id = ?', [$existing['id']]);
                } else {
                    $resultData['exam_id'] = $examId;
                    $resultData['student_id'] = $studentId;
                    $resultData['result_date'] = date('Y-m-d H:i:s');
                    $resultData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('exam_results', $resultData);
                }
            }
            
            // Update exam status
            $this->db->update('exams', ['status' => 'completed'], 'id = ?', [$examId]);
            
            $this->db->commit();
            
            $this->logActivity('exams', 'marks_entry', "نمرات آزمون {$examId} ثبت شد");
            
            $this->json(['success' => true, 'message' => 'نمرات با موفقیت ثبت شد']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در ثبت نمرات: ' . $e->getMessage()]);
        }
    }
    
    public function results()
    {
        $classId = $_GET['class_id'] ?? '';
        $examId = $_GET['exam_id'] ?? '';
        $studentId = $_GET['student_id'] ?? '';
        
        $results = [];
        
        if ($examId) {
            $results = $this->db->select(
                "SELECT er.*, s.student_code, u.first_name, u.last_name,
                        e.total_marks, e.passing_marks
                 FROM exam_results er 
                 JOIN students s ON er.student_id = s.id 
                 JOIN users u ON s.user_id = u.id 
                 JOIN exams e ON er.exam_id = e.id 
                 WHERE er.exam_id = ?
                 ORDER BY er.marks_obtained DESC",
                [$examId]
            );
        }
        
        $this->view('exams/results', [
            'title' => 'نتایج آزمون‌ها',
            'results' => $results,
            'selectedExam' => $examId
        ]);
    }
    
    public function reportCard($studentId)
    {
        $academicYearId = $_GET['academic_year_id'] ?? '';
        
        if (!$academicYearId) {
            // Get current academic year
            $currentYear = $this->db->selectOne(
                "SELECT id FROM academic_years WHERE is_current = 1 AND status = 'active'"
            );
            $academicYearId = $currentYear['id'] ?? null;
        }
        
        $student = $this->db->selectOne(
            "SELECT s.*, u.first_name, u.last_name, u.profile_photo,
                    c.class_name, sec.section_name
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id
             WHERE s.id = ?",
            [$studentId]
        );
        
        if (!$student) {
            $this->session->flash('error', 'دانش‌آموز یافت نشد');
            $this->redirect('/students');
            return;
        }
        
        // Get all results for this academic year
        $results = $this->db->select(
            "SELECT er.*, e.exam_date, et.name as exam_type,
                    s.subject_name, e.total_marks, e.passing_marks
             FROM exam_results er 
             JOIN exams e ON er.exam_id = e.id 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             WHERE er.student_id = ? AND e.academic_year_id = ?
             ORDER BY e.exam_date, s.subject_name",
            [$studentId, $academicYearId]
        );
        
        $this->view('exams/report_card', [
            'title' => 'کارنامه تحصیلی',
            'student' => $student,
            'results' => $results
        ]);
    }
    
    private function calculateGrade($marksObtained, $totalMarks, $passingMarks)
    {
        if ($totalMarks == 0) {
            return ['grade' => 'N/A', 'grade_point' => 0];
        }
        
        $percentage = ($marksObtained / $totalMarks) * 100;
        
        if ($percentage >= 95) {
            return ['grade' => 'A+', 'grade_point' => 4.0];
        } elseif ($percentage >= 90) {
            return ['grade' => 'A', 'grade_point' => 4.0];
        } elseif ($percentage >= 85) {
            return ['grade' => 'A-', 'grade_point' => 3.7];
        } elseif ($percentage >= 80) {
            return ['grade' => 'B+', 'grade_point' => 3.3];
        } elseif ($percentage >= 75) {
            return ['grade' => 'B', 'grade_point' => 3.0];
        } elseif ($percentage >= 70) {
            return ['grade' => 'B-', 'grade_point' => 2.7];
        } elseif ($percentage >= 65) {
            return ['grade' => 'C+', 'grade_point' => 2.3];
        } elseif ($percentage >= 60) {
            return ['grade' => 'C', 'grade_point' => 2.0];
        } elseif ($percentage >= 55) {
            return ['grade' => 'C-', 'grade_point' => 1.7];
        } elseif ($percentage >= 50) {
            return ['grade' => 'D', 'grade_point' => 1.0];
        } else {
            return ['grade' => 'F', 'grade_point' => 0.0];
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