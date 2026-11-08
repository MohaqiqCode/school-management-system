<?php
namespace App\Models;

/**
 * Exam Model
 * مدیریت آزمون‌ها
 */
class Exam extends Model
{
    protected $table = 'exams';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'exam_type_id',
        'academic_year_id',
        'semester_id',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'total_marks',
        'passing_marks',
        'room_number',
        'invigilator_id',
        'status',
        'created_by',
    ];
    
    protected $casts = [
        'exam_date' => 'date',
        'total_marks' => 'float',
        'passing_marks' => 'float',
    ];
    
    protected $appends = [
        'exam_type_name',
        'subject_name',
        'class_name',
        'results_count',
        'average_marks',
        'pass_percentage',
    ];
    
    /**
     * Get exam with details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT e.*, et.name as exam_type_name, et.exam_category,
                       s.subject_name, s.subject_code,
                       c.class_name, c.class_level,
                       sem.semester_name,
                       ay.year_name,
                       CONCAT(u.first_name, ' ', u.last_name) as invigilator_name
                FROM exams e 
                JOIN exam_types et ON e.exam_type_id = et.id 
                JOIN subjects s ON e.subject_id = s.id 
                JOIN classes c ON e.class_id = c.id 
                LEFT JOIN semesters sem ON e.semester_id = sem.id
                LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
                LEFT JOIN teachers t ON e.invigilator_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE e.id = ?";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get exams by class
     */
    public function getByClass($classId)
    {
        $sql = "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name
                FROM exams e 
                JOIN exam_types et ON e.exam_type_id = et.id 
                JOIN subjects s ON e.subject_id = s.id 
                JOIN classes c ON e.class_id = c.id 
                WHERE e.class_id = ?
                ORDER BY e.exam_date DESC";
        
        return $this->hydrate($this->db->select($sql, [$classId]));
    }
    
    /**
     * Get upcoming exams
     */
    public function getUpcoming($limit = 10)
    {
        $sql = "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name
                FROM exams e 
                JOIN exam_types et ON e.exam_type_id = et.id 
                JOIN subjects s ON e.subject_id = s.id 
                JOIN classes c ON e.class_id = c.id 
                WHERE e.exam_date >= CURDATE() AND e.status = 'scheduled'
                ORDER BY e.exam_date ASC
                LIMIT ?";
        
        return $this->hydrate($this->db->select($sql, [$limit]));
    }
    
    /**
     * Get exam results with statistics
     */
    public function getResults($examId)
    {
        $sql = "SELECT er.*, s.student_code, u.first_name, u.last_name,
                       e.total_marks, e.passing_marks
                FROM exam_results er 
                JOIN students s ON er.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN exams e ON er.exam_id = e.id 
                WHERE er.exam_id = ?
                ORDER BY er.marks_obtained DESC";
        
        return $this->db->select($sql, [$examId]);
    }
    
    /**
     * Get exam statistics
     */
    public function getStatistics($examId)
    {
        $sql = "SELECT COUNT(*) as total_students,
                       COUNT(CASE WHEN er.marks_obtained >= e.passing_marks THEN 1 END) as passed,
                       COUNT(CASE WHEN er.marks_obtained < e.passing_marks AND er.is_absent = 0 THEN 1 END) as failed,
                       COUNT(CASE WHEN er.is_absent = 1 THEN 1 END) as absent,
                       ROUND(AVG(er.marks_obtained), 2) as average,
                       MAX(er.marks_obtained) as highest,
                       MIN(CASE WHEN er.is_absent = 0 THEN er.marks_obtained END) as lowest,
                       ROUND((COUNT(CASE WHEN er.marks_obtained >= e.passing_marks THEN 1 END) / COUNT(*)) * 100, 2) as pass_percentage
                FROM exam_results er 
                JOIN exams e ON er.exam_id = e.id 
                WHERE er.exam_id = ?";
        
        return $this->db->selectOne($sql, [$examId]);
    }
    
    /**
     * Enter marks for students
     */
    public function enterMarks($examId, array $marks)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($marks as $studentId => $data) {
                $marksObtained = floatval($data['marks_obtained'] ?? 0);
                $isAbsent = isset($data['is_absent']) ? 1 : 0;
                
                // Calculate grade
                $grade = $this->calculateGrade($marksObtained);
                
                $existing = $this->db->selectOne(
                    "SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ?",
                    [$examId, $studentId]
                );
                
                $resultData = [
                    'marks_obtained' => $marksObtained,
                    'practical_marks' => $data['practical_marks'] ?? 0,
                    'internal_assessment' => $data['internal_assessment'] ?? 0,
                    'grade' => $grade['grade'],
                    'grade_point' => $grade['grade_point'],
                    'is_absent' => $isAbsent,
                    'remarks' => $data['remarks'] ?? '',
                    'entered_by' => $data['entered_by'] ?? null,
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
            $this->db->update('exams', [
                'status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$examId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Calculate grade based on marks
     */
    private function calculateGrade($marksObtained, $totalMarks = null)
    {
        $totalMarks = $totalMarks ?? $this->attributes['total_marks'] ?? 100;
        
        if ($totalMarks == 0) {
            return ['grade' => 'N/A', 'grade_point' => 0];
        }
        
        $percentage = ($marksObtained / $totalMarks) * 100;
        
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
    
    // Accessors
    public function appendExamTypeNameAttribute()
    {
        return $this->attributes['exam_type_name'] ?? null;
    }
    
    public function appendSubjectNameAttribute()
    {
        return $this->attributes['subject_name'] ?? null;
    }
    
    public function appendClassNameAttribute()
    {
        return $this->attributes['class_name'] ?? null;
    }
    
    public function appendResultsCountAttribute()
    {
        $examId = $this->attributes['id'] ?? null;
        if (!$examId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM exam_results WHERE exam_id = ?";
        $result = $this->db->selectOne($sql, [$examId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendAverageMarksAttribute()
    {
        $examId = $this->attributes['id'] ?? null;
        if (!$examId) return 0;
        
        $sql = "SELECT ROUND(AVG(marks_obtained), 2) as avg FROM exam_results WHERE exam_id = ? AND is_absent = 0";
        $result = $this->db->selectOne($sql, [$examId]);
        return (float)($result['avg'] ?? 0);
    }
    
    public function appendPassPercentageAttribute()
    {
        $examId = $this->attributes['id'] ?? null;
        if (!$examId) return 0;
        
        $sql = "SELECT COUNT(*) as total,
                       COUNT(CASE WHEN marks_obtained >= (SELECT passing_marks FROM exams WHERE id = ?) THEN 1 END) as passed
                FROM exam_results WHERE exam_id = ?";
        $result = $this->db->selectOne($sql, [$examId, $examId]);
        
        if (($result['total'] ?? 0) > 0) {
            return round(($result['passed'] / $result['total']) * 100, 2);
        }
        
        return 0;
    }
}