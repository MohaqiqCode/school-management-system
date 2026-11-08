<?php
namespace App\Models;

/**
 * Student Promotion Model
 * مدیریت ارتقاء دانش‌آموزان
 */
class StudentPromotion extends Model
{
    protected $table = 'student_promotions';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'from_section_id',
        'to_section_id',
        'academic_year_id',
        'promotion_date',
        'remarks',
        'promoted_by',
    ];
    
    protected $casts = [
        'promotion_date' => 'date',
    ];
    
    protected $appends = [
        'student_name',
        'from_class_name',
        'to_class_name',
    ];
    
    /**
     * Get promotion history for student
     */
    public function getByStudent($studentId)
    {
        $sql = "SELECT sp.*, 
                       fc.class_name as from_class_name, 
                       tc.class_name as to_class_name,
                       fs.section_name as from_section_name,
                       ts.section_name as to_section_name,
                       ay.year_name,
                       CONCAT(u.first_name, ' ', u.last_name) as promoted_by_name
                FROM student_promotions sp 
                JOIN classes fc ON sp.from_class_id = fc.id 
                JOIN classes tc ON sp.to_class_id = tc.id 
                LEFT JOIN sections fs ON sp.from_section_id = fs.id
                LEFT JOIN sections ts ON sp.to_section_id = ts.id
                JOIN academic_years ay ON sp.academic_year_id = ay.id 
                LEFT JOIN users u ON sp.promoted_by = u.id
                WHERE sp.student_id = ?
                ORDER BY sp.promotion_date DESC";
        
        return $this->db->select($sql, [$studentId]);
    }
    
    /**
     * Get promotions by academic year
     */
    public function getByAcademicYear($academicYearId)
    {
        $sql = "SELECT sp.*, s.student_code, u.first_name, u.last_name,
                       fc.class_name as from_class, tc.class_name as to_class
                FROM student_promotions sp 
                JOIN students s ON sp.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN classes fc ON sp.from_class_id = fc.id 
                JOIN classes tc ON sp.to_class_id = tc.id 
                WHERE sp.academic_year_id = ?
                ORDER BY tc.class_level, u.last_name";
        
        return $this->db->select($sql, [$academicYearId]);
    }
    
    /**
     * Promote single student
     */
    public function promote($studentId, $toClassId, $toSectionId, $academicYearId, $promotedBy, $remarks = '')
    {
        $student = $this->db->selectOne(
            "SELECT current_class_id, current_section_id FROM students WHERE id = ?",
            [$studentId]
        );
        
        if (!$student) return false;
        
        $this->db->beginTransaction();
        
        try {
            // Record promotion
            $this->db->insert('student_promotions', [
                'student_id' => $studentId,
                'from_class_id' => $student['current_class_id'],
                'to_class_id' => $toClassId,
                'from_section_id' => $student['current_section_id'],
                'to_section_id' => $toSectionId,
                'academic_year_id' => $academicYearId,
                'promotion_date' => date('Y-m-d'),
                'remarks' => $remarks,
                'promoted_by' => $promotedBy,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update student
            $this->db->update('students', [
                'current_class_id' => $toClassId,
                'current_section_id' => $toSectionId,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$studentId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Promote all students in a class
     */
    public function promoteClass($fromClassId, $toClassId, $toSectionId, $academicYearId, $promotedBy)
    {
        $students = $this->db->select(
            "SELECT id, current_section_id FROM students WHERE current_class_id = ? AND is_active = 1",
            [$fromClassId]
        );
        
        $this->db->beginTransaction();
        
        try {
            foreach ($students as $student) {
                $this->promote(
                    $student['id'],
                    $toClassId,
                    $toSectionId,
                    $academicYearId,
                    $promotedBy
                );
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get promotion statistics
     */
    public function getStatistics($academicYearId)
    {
        $sql = "SELECT COUNT(*) as total_promotions,
                       COUNT(DISTINCT student_id) as unique_students,
                       COUNT(DISTINCT from_class_id) as classes_promoted
                FROM student_promotions 
                WHERE academic_year_id = ?";
        
        return $this->db->selectOne($sql, [$academicYearId]);
    }
    
    // Accessors
    public function appendStudentNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendFromClassNameAttribute()
    {
        return $this->attributes['from_class_name'] ?? null;
    }
    
    public function appendToClassNameAttribute()
    {
        return $this->attributes['to_class_name'] ?? null;
    }
}