<?php
namespace App\Models;

/**
 * Grading Scale Model
 * مدیریت مقیاس‌های نمره‌دهی
 */
class GradingScale extends Model
{
    protected $table = 'grading_scales';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'grade_name',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'description',
    ];
    
    protected $casts = [
        'min_percentage' => 'float',
        'max_percentage' => 'float',
        'grade_point' => 'float',
    ];
    
    /**
     * Get grading scales for branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT * FROM grading_scales WHERE branch_id = ? ORDER BY min_percentage DESC";
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get grade for percentage
     */
    public function getGradeForPercentage($branchId, $percentage)
    {
        $sql = "SELECT * FROM grading_scales 
                WHERE branch_id = ? AND min_percentage <= ? AND max_percentage >= ?
                LIMIT 1";
        
        $result = $this->db->selectOne($sql, [$branchId, $percentage, $percentage]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    /**
     * Calculate grade and grade point
     */
    public function calculate($branchId, $marksObtained, $totalMarks)
    {
        if ($totalMarks == 0) {
            return ['grade' => 'N/A', 'grade_point' => 0];
        }
        
        $percentage = ($marksObtained / $totalMarks) * 100;
        $grade = $this->getGradeForPercentage($branchId, $percentage);
        
        if ($grade) {
            return [
                'grade' => $grade->grade_name,
                'grade_point' => $grade->grade_point,
                'percentage' => round($percentage, 2),
            ];
        }
        
        return ['grade' => 'F', 'grade_point' => 0, 'percentage' => round($percentage, 2)];
    }
    
    /**
     * Set default grading scales for branch
     */
    public function setDefaultForBranch($branchId)
    {
        $defaults = [
            ['grade_name' => 'A+', 'min' => 95, 'max' => 100, 'point' => 4.0, 'desc' => 'عالی'],
            ['grade_name' => 'A', 'min' => 90, 'max' => 94.99, 'point' => 4.0, 'desc' => 'بسیار خوب'],
            ['grade_name' => 'A-', 'min' => 85, 'max' => 89.99, 'point' => 3.7, 'desc' => 'خوب'],
            ['grade_name' => 'B+', 'min' => 80, 'max' => 84.99, 'point' => 3.3, 'desc' => 'خوب'],
            ['grade_name' => 'B', 'min' => 75, 'max' => 79.99, 'point' => 3.0, 'desc' => 'متوسط'],
            ['grade_name' => 'B-', 'min' => 70, 'max' => 74.99, 'point' => 2.7, 'desc' => 'متوسط'],
            ['grade_name' => 'C+', 'min' => 65, 'max' => 69.99, 'point' => 2.3, 'desc' => 'قابل قبول'],
            ['grade_name' => 'C', 'min' => 60, 'max' => 64.99, 'point' => 2.0, 'desc' => 'قابل قبول'],
            ['grade_name' => 'C-', 'min' => 55, 'max' => 59.99, 'point' => 1.7, 'desc' => 'ضعیف'],
            ['grade_name' => 'D', 'min' => 50, 'max' => 54.99, 'point' => 1.0, 'desc' => 'ضعیف'],
            ['grade_name' => 'F', 'min' => 0, 'max' => 49.99, 'point' => 0.0, 'desc' => 'مردود'],
        ];
        
        $this->db->beginTransaction();
        
        try {
            // Remove existing
            $this->db->delete('grading_scales', 'branch_id = ?', [$branchId]);
            
            // Insert defaults
            foreach ($defaults as $grade) {
                $this->db->insert('grading_scales', [
                    'branch_id' => $branchId,
                    'grade_name' => $grade['grade_name'],
                    'min_percentage' => $grade['min'],
                    'max_percentage' => $grade['max'],
                    'grade_point' => $grade['point'],
                    'description' => $grade['desc'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}