<?php
namespace App\Models;

/**
 * Parent Model
 * مدیریت والدین
 */
class ParentModel extends Model
{
    protected $table = 'parents';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',
        'parent_code',
        'occupation',
        'workplace',
        'annual_income',
        'relationship',
        'is_primary_contact',
    ];
    
    protected $casts = [
        'annual_income' => 'float',
        'is_primary_contact' => 'bool',
    ];
    
    protected $appends = [
        'full_name',
        'children_count',
    ];
    
    /**
     * Get parent with details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.address, u.city
                FROM parents p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.id = ? AND u.deleted_at IS NULL";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Find parent by user ID
     */
    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM parents WHERE user_id = ?";
        $result = $this->db->selectOne($sql, [$userId]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get children of parent
     */
    public function getChildren($parentId)
    {
        $sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo,
                       c.class_name, sec.section_name,
                       sp.relationship, sp.is_emergency_contact
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                JOIN student_parent sp ON s.id = sp.student_id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                WHERE sp.parent_id = ? AND s.is_active = 1
                ORDER BY c.class_level, u.first_name";
        
        return $this->db->select($sql, [$parentId]);
    }
    
    /**
     * Get children's attendance
     */
    public function getChildrenAttendance($parentId, $date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        $sql = "SELECT s.id, u.first_name, u.last_name, sa.status, sa.attendance_date
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                JOIN student_parent sp ON s.id = sp.student_id 
                LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = ?
                WHERE sp.parent_id = ? AND s.is_active = 1";
        
        return $this->db->select($sql, [$date, $parentId]);
    }
    
    /**
     * Get children's fees
     */
    public function getChildrenFees($parentId)
    {
        $sql = "SELECT s.id, u.first_name, u.last_name,
                       COALESCE(SUM(sf.balance_amount), 0) as total_outstanding,
                       COUNT(CASE WHEN sf.status = 'overdue' THEN 1 END) as overdue_count
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                JOIN student_parent sp ON s.id = sp.student_id 
                LEFT JOIN student_fees sf ON s.id = sf.student_id AND sf.status != 'paid'
                WHERE sp.parent_id = ? AND s.is_active = 1
                GROUP BY s.id";
        
        return $this->db->select($sql, [$parentId]);
    }
    
    /**
     * Link parent to student
     */
    public function linkToStudent($parentId, $studentId, $relationship = 'parent')
    {
        $existing = $this->db->selectOne(
            "SELECT id FROM student_parent WHERE student_id = ? AND parent_id = ?",
            [$studentId, $parentId]
        );
        
        if ($existing) {
            return $existing['id'];
        }
        
        return $this->db->insert('student_parent', [
            'student_id' => $studentId,
            'parent_id' => $parentId,
            'relationship' => $relationship,
            'is_emergency_contact' => 0,
            'can_pickup' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Accessors
    public function getFullNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendChildrenCountAttribute()
    {
        $parentId = $this->attributes['id'] ?? null;
        if (!$parentId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM student_parent WHERE parent_id = ?";
        $result = $this->db->selectOne($sql, [$parentId]);
        return (int)($result['total'] ?? 0);
    }
}