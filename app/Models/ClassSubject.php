<?php
namespace App\Models;

/**
 * Class-Subject Mapping Model
 * مدیریت ارتباط کلاس‌ها و دروس
 */
class ClassSubject extends Model
{
    protected $table = 'class_subjects';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'class_id',
        'subject_id',
        'academic_year_id',
        'teacher_id',
        'periods_per_week',
        'is_active',
    ];
    
    protected $casts = [
        'periods_per_week' => 'int',
        'is_active' => 'bool',
    ];
    
    /**
     * Assign subject to class
     */
    public function assignSubject($classId, $subjectId, $academicYearId, $teacherId = null, $periodsPerWeek = 3)
    {
        // Check if already assigned
        $existing = $this->db->selectOne(
            "SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ? AND academic_year_id = ?",
            [$classId, $subjectId, $academicYearId]
        );
        
        if ($existing) {
            return $this->db->update('class_subjects', [
                'teacher_id' => $teacherId,
                'periods_per_week' => $periodsPerWeek,
                'is_active' => 1
            ], 'id = ?', [$existing['id']]);
        }
        
        return $this->db->insert('class_subjects', [
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'academic_year_id' => $academicYearId,
            'teacher_id' => $teacherId,
            'periods_per_week' => $periodsPerWeek,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Assign subjects to class in bulk
     */
    public function assignSubjectsBulk($classId, $academicYearId, array $subjects)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($subjects as $subject) {
                $this->assignSubject(
                    $classId,
                    $subject['subject_id'],
                    $academicYearId,
                    $subject['teacher_id'] ?? null,
                    $subject['periods_per_week'] ?? 3
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
     * Get subjects for class with teacher info
     */
    public function getClassSubjectsWithTeachers($classId, $academicYearId)
    {
        $sql = "SELECT cs.*, s.subject_name, s.subject_code, s.credit_hours,
                       CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                FROM class_subjects cs 
                JOIN subjects s ON cs.subject_id = s.id 
                LEFT JOIN teachers t ON cs.teacher_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE cs.class_id = ? AND cs.academic_year_id = ? AND cs.is_active = 1
                ORDER BY s.subject_name";
        
        return $this->db->select($sql, [$classId, $academicYearId]);
    }
    
    /**
     * Get teacher's assigned classes
     */
    public function getTeacherClasses($teacherId)
    {
        $sql = "SELECT cs.*, c.class_name, c.class_level, s.subject_name
                FROM class_subjects cs 
                JOIN classes c ON cs.class_id = c.id 
                JOIN subjects s ON cs.subject_id = s.id 
                WHERE cs.teacher_id = ? AND cs.is_active = 1
                ORDER BY c.class_level, s.subject_name";
        
        return $this->db->select($sql, [$teacherId]);
    }
    
    /**
     * Remove subject from class
     */
    public function removeSubject($classId, $subjectId, $academicYearId)
    {
        return $this->db->update('class_subjects', [
            'is_active' => 0
        ], 'class_id = ? AND subject_id = ? AND academic_year_id = ?', 
        [$classId, $subjectId, $academicYearId]);
    }
}