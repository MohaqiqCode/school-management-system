<?php
namespace App\Models;

use App\Helpers\Helper;

/**
 * Student Model
 * مدیریت دانش‌آموزان
 */
class Student extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',
        'student_code',
        'admission_number',
        'branch_id',
        'current_class_id',
        'current_section_id',
        'admission_date',
        'admission_type',
        'blood_group',
        'medical_conditions',
        'allergies',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'previous_school',
        'transportation_required',
        'hostel_required',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'bool',
        'transportation_required' => 'bool',
        'hostel_required' => 'bool',
        'admission_date' => 'date',
    ];
    
    protected $appends = [
        'full_name',
        'class_name',
        'section_name',
        'attendance_percentage',
        'outstanding_fees',
    ];
    
    /**
     * Get student with full details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.gender, u.date_of_birth, u.address, u.city,
                       c.class_name, c.class_level,
                       sec.section_name,
                       b.name as branch_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                JOIN branches b ON s.branch_id = b.id 
                WHERE s.id = ? AND u.deleted_at IS NULL";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get students by class
     */
    public function getByClass($classId)
    {
        $sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo,
                       c.class_name, sec.section_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                WHERE s.current_class_id = ? AND s.is_active = 1 AND u.deleted_at IS NULL
                ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, [$classId]));
    }
    
    /**
     * Get students by section
     */
    public function getBySection($sectionId)
    {
        $sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.current_section_id = ? AND s.is_active = 1 AND u.deleted_at IS NULL
                ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, [$sectionId]));
    }
    
    /**
     * Search students
     */
    public function search($query, $limit = 20)
    {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo,
                       c.class_name
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                WHERE u.deleted_at IS NULL 
                AND (u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_code LIKE ? OR s.admission_number LIKE ?)
                AND s.is_active = 1
                ORDER BY u.last_name, u.first_name
                LIMIT ?";
        
        return $this->hydrate(
            $this->db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit])
        );
    }
    
    /**
     * Get student parents
     */
    public function getParents($studentId)
    {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone,
                       sp.relationship, sp.is_emergency_contact
                FROM parents p 
                JOIN users u ON p.user_id = u.id 
                JOIN student_parent sp ON p.id = sp.parent_id 
                WHERE sp.student_id = ? AND u.deleted_at IS NULL";
        
        return $this->db->select($sql, [$studentId]);
    }
    
    /**
     * Get student attendance summary
     */
    public function getAttendanceSummary($studentId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? date('Y-m-01');
        $endDate = $endDate ?? date('Y-m-t');
        
        $sql = "SELECT COUNT(*) as total_days,
                       COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) as present_days,
                       COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
                       COUNT(CASE WHEN status = 'excused' THEN 1 END) as excused_days,
                       COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days
                FROM student_attendance 
                WHERE student_id = ? AND attendance_date BETWEEN ? AND ?";
        
        return $this->db->selectOne($sql, [$studentId, $startDate, $endDate]);
    }
    
    /**
     * Get student exam results
     */
    public function getResults($studentId, $academicYearId = null)
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
     * Get student fees
     */
    public function getFees($studentId)
    {
        $sql = "SELECT sf.*, fc.category_name, fs.amount as structure_amount
                FROM student_fees sf 
                JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                WHERE sf.student_id = ?
                ORDER BY sf.due_date DESC";
        
        return $this->db->select($sql, [$studentId]);
    }
    
    /**
     * Get promotion history
     */
    public function getPromotionHistory($studentId)
    {
        $sql = "SELECT sp.*, 
                       fc.class_name as from_class, 
                       tc.class_name as to_class,
                       ay.year_name
                FROM student_promotions sp 
                JOIN classes fc ON sp.from_class_id = fc.id 
                JOIN classes tc ON sp.to_class_id = tc.id 
                JOIN academic_years ay ON sp.academic_year_id = ay.id 
                WHERE sp.student_id = ?
                ORDER BY sp.promotion_date DESC";
        
        return $this->db->select($sql, [$studentId]);
    }
    
    /**
     * Promote student to next class
     */
    public function promote($studentId, $toClassId, $toSectionId, $academicYearId, $promotedBy)
    {
        $student = $this->find($studentId);
        
        if (!$student) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // Record promotion
            $this->db->insert('student_promotions', [
                'student_id' => $studentId,
                'from_class_id' => $student->current_class_id,
                'to_class_id' => $toClassId,
                'from_section_id' => $student->current_section_id,
                'to_section_id' => $toSectionId,
                'academic_year_id' => $academicYearId,
                'promotion_date' => date('Y-m-d'),
                'promoted_by' => $promotedBy,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update student class
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
     * Get students count by class
     */
    public function getCountByClass($classId)
    {
        $sql = "SELECT COUNT(*) as total FROM students WHERE current_class_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$classId]);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Generate student code
     */
    public function generateStudentCode($branchCode)
    {
        $count = $this->count() + 1;
        return 'STD-' . strtoupper($branchCode) . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate admission number
     */
    public function generateAdmissionNumber()
    {
        $year = date('Y');
        $count = $this->count() + 1;
        return 'ADM-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
    
    // Accessors
    public function getFullNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendClassNameAttribute()
    {
        return $this->attributes['class_name'] ?? null;
    }
    
    public function appendSectionNameAttribute()
    {
        return $this->attributes['section_name'] ?? null;
    }
    
    public function appendAttendancePercentageAttribute()
    {
        $studentId = $this->attributes['id'] ?? null;
        if (!$studentId) return 0;
        
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        
        $sql = "SELECT COUNT(*) as total,
                       COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) as present
                FROM student_attendance 
                WHERE student_id = ? AND attendance_date BETWEEN ? AND ?";
        
        $result = $this->db->selectOne($sql, [$studentId, $startDate, $endDate]);
        
        if (($result['total'] ?? 0) > 0) {
            return round(($result['present'] / $result['total']) * 100, 2);
        }
        
        return 0;
    }
    
    public function appendOutstandingFeesAttribute()
    {
        $studentId = $this->attributes['id'] ?? null;
        if (!$studentId) return 0;
        
        $sql = "SELECT COALESCE(SUM(balance_amount), 0) as total 
                FROM student_fees 
                WHERE student_id = ? AND status IN ('pending', 'partial', 'overdue')";
        
        $result = $this->db->selectOne($sql, [$studentId]);
        return (float)($result['total'] ?? 0);
    }
}