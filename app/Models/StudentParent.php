<?php
namespace App\Models;

/**
 * Student-Parent Pivot Model
 * مدیریت ارتباط دانش‌آموز و والد
 */
class StudentParent extends Model
{
    protected $table = 'student_parent';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    /**
     * Get parents for student
     */
    public function getByStudent($studentId)
    {
        $sql = "SELECT sp.*, p.parent_code, u.first_name, u.last_name, u.email, u.phone
                FROM student_parent sp 
                JOIN parents p ON sp.parent_id = p.id 
                JOIN users u ON p.user_id = u.id 
                WHERE sp.student_id = ? AND u.deleted_at IS NULL
                ORDER BY sp.is_emergency_contact DESC";
        
        return $this->db->select($sql, [$studentId]);
    }
    
    /**
     * Get students for parent
     */
    public function getByParent($parentId)
    {
        $sql = "SELECT sp.*, s.student_code, u.first_name, u.last_name,
                       c.class_name, sec.section_name
                FROM student_parent sp 
                JOIN students s ON sp.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id
                WHERE sp.parent_id = ? AND s.is_active = 1
                ORDER BY c.class_level, u.first_name";
        
        return $this->db->select($sql, [$parentId]);
    }
    
    /**
     * Set emergency contact
     */
    public function setEmergencyContact($studentId, $parentId)
    {
        $this->db->beginTransaction();
        
        try {
            // Remove emergency contact from all parents
            $this->db->update('student_parent', [
                'is_emergency_contact' => 0
            ], 'student_id = ?', [$studentId]);
            
            // Set new emergency contact
            $this->db->update('student_parent', [
                'is_emergency_contact' => 1
            ], 'student_id = ? AND parent_id = ?', [$studentId, $parentId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Set pickup permission
     */
    public function setPickupPermission($studentId, $parentId, $canPickup = true)
    {
        return $this->db->update('student_parent', [
            'can_pickup' => $canPickup ? 1 : 0
        ], 'student_id = ? AND parent_id = ?', [$studentId, $parentId]);
    }
    
    /**
     * Remove parent-student link
     */
    public function unlink($studentId, $parentId)
    {
        return $this->db->delete('student_parent', 
            'student_id = ? AND parent_id = ?', 
            [$studentId, $parentId]
        );
    }
    
    /**
     * Count students for parent
     */
    public function countByParent($parentId)
    {
        $sql = "SELECT COUNT(*) as total FROM student_parent WHERE parent_id = ?";
        $result = $this->db->selectOne($sql, [$parentId]);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Count parents for student
     */
    public function countByStudent($studentId)
    {
        $sql = "SELECT COUNT(*) as total FROM student_parent WHERE student_id = ?";
        $result = $this->db->selectOne($sql, [$studentId]);
        return (int)($result['total'] ?? 0);
    }
}