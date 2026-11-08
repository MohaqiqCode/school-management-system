<?php
namespace App\Models;

/**
 * Exam Result Model
 * مدیریت نتایج آزمون‌ها
 */
class ExamResult extends Model
{
    protected $table = 'exam_results';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'exam_id',
        'student_id',
        'marks_obtained',
        'practical_marks',
        'internal_assessment',
        'grade',
        'grade_point',
        'remarks',
        'is_absent',
        'entered_by',
        'verified_by',
    ];
    
    protected $casts = [
        'marks_obtained' => 'float',
        'practical_marks' => 'float',
        'internal_assessment' => 'float',
        'grade_point' => 'float',
        'is_absent' => 'bool',
    ];
    
    protected $appends = [
        'student_name',
        'subject_name',
        'total_marks',
        'percentage',
        'result_status',
    ];
    
    /**
     * Get results for exam
     */
    public function getByExam($examId)
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
     * Get results for student
     */
    public function getByStudent($studentId, $academicYearId = null)
    {
        $sql = "SELECT er.*, e.exam_date, et.name as exam_type,
                       s.subject_name, e.total_marks, e.passing_marks
                FROM exam_results er 
                JOIN exams e ON er.exam_id = e.id 
                JOIN exam_types et ON e.exam_type_id = et.id 
                JOIN subjects s ON e.subject_id = s.id 
                WHERE er.student_id = ?";
        
        $params = [$studentId];
        
        if ($academicYearId) {
            $sql .= " AND e.academic_year_id = ?";
            $params[] = $academicYearId;
        }
        
        $sql .= " ORDER BY e.exam_date DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get top students for exam
     */
    public function getTopStudents($examId, $limit = 10)
    {
        $sql = "SELECT er.*, s.student_code, u.first_name, u.last_name
                FROM exam_results er 
                JOIN students s ON er.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                WHERE er.exam_id = ? AND er.is_absent = 0
                ORDER BY er.marks_obtained DESC
                LIMIT ?";
        
        return $this->db->select($sql, [$examId, $limit]);
    }
    
    /**
     * Calculate GPA for student
     */
    public function calculateGPA($studentId, $academicYearId = null)
    {
        $sql = "SELECT AVG(er.grade_point) as gpa,
                       SUM(e.total_marks) as total_max_marks,
                       SUM(er.marks_obtained) as total_obtained,
                       COUNT(*) as subject_count
                FROM exam_results er 
                JOIN exams e ON er.exam_id = e.id 
                WHERE er.student_id = ? AND er.is_absent = 0";
        
        $params = [$studentId];
        
        if ($academicYearId) {
            $sql .= " AND e.academic_year_id = ?";
            $params[] = $academicYearId;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * Get failed students for exam
     */
    public function getFailedStudents($examId)
    {
        $sql = "SELECT er.*, s.student_code, u.first_name, u.last_name
                FROM exam_results er 
                JOIN students s ON er.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN exams e ON er.exam_id = e.id 
                WHERE er.exam_id = ? AND er.is_absent = 0 
                AND er.marks_obtained < e.passing_marks
                ORDER BY er.marks_obtained";
        
        return $this->db->select($sql, [$examId]);
    }
    
    /**
     * Verify result
     */
    public function verifyResult($resultId, $verifiedBy)
    {
        return $this->db->update('exam_results', [
            'verified_by' => $verifiedBy,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$resultId]);
    }
    
    // Accessors
    public function appendStudentNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendSubjectNameAttribute()
    {
        return $this->attributes['subject_name'] ?? null;
    }
    
    public function appendTotalMarksAttribute()
    {
        $marks = $this->attributes['marks_obtained'] ?? 0;
        $practical = $this->attributes['practical_marks'] ?? 0;
        $internal = $this->attributes['internal_assessment'] ?? 0;
        return $marks + $practical + $internal;
    }
    
    public function appendPercentageAttribute()
    {
        $totalMarks = $this->attributes['total_marks'] ?? 100;
        $obtained = $this->total_marks;
        
        return $totalMarks > 0 ? round(($obtained / $totalMarks) * 100, 2) : 0;
    }
    
    public function appendResultStatusAttribute()
    {
        if ($this->attributes['is_absent'] ?? false) {
            return 'غایب';
        }
        
        $passingMarks = $this->attributes['passing_marks'] ?? 0;
        $obtained = $this->total_marks;
        
        return $obtained >= $passingMarks ? 'قبول' : 'مردود';
    }
}