<?php
namespace App\Models;

/**
 * Fee Category Model
 * مدیریت دسته‌بندی‌های شهریه
 */
class FeeCategory extends Model
{
    protected $table = 'fee_categories';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'category_name',
        'description',
        'is_recurring',
        'frequency',
    ];
    
    protected $casts = [
        'is_recurring' => 'bool',
    ];
    
    protected $appends = [
        'structure_count',
        'frequency_text',
    ];
    
    /**
     * Get categories by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT fc.*, 
                       (SELECT COUNT(*) FROM fee_structures WHERE fee_category_id = fc.id) as structure_count
                FROM fee_categories fc 
                WHERE fc.branch_id = ?
                ORDER BY fc.category_name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get recurring categories
     */
    public function getRecurring($branchId)
    {
        $sql = "SELECT * FROM fee_categories WHERE branch_id = ? AND is_recurring = 1
                ORDER BY category_name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get categories with structures for class
     */
    public function getForClass($branchId, $classId, $academicYearId)
    {
        $sql = "SELECT fc.*, fs.id as structure_id, fs.amount, fs.discount_allowed, fs.due_day
                FROM fee_categories fc 
                LEFT JOIN fee_structures fs ON fc.id = fs.fee_category_id 
                    AND fs.class_id = ? AND fs.academic_year_id = ? AND fs.is_active = 1
                WHERE fc.branch_id = ?
                ORDER BY fc.category_name";
        
        return $this->db->select($sql, [$classId, $academicYearId, $branchId]);
    }
    
    // Accessors
    public function appendStructureCountAttribute()
    {
        return $this->attributes['structure_count'] ?? 0;
    }
    
    public function appendFrequencyTextAttribute()
    {
        $frequencies = [
            'monthly' => 'ماهانه',
            'quarterly' => 'سه ماهه',
            'half_yearly' => 'شش ماهه',
            'yearly' => 'سالانه',
            'one_time' => 'یکبار',
        ];
        
        $frequency = $this->attributes['frequency'] ?? '';
        return $frequencies[$frequency] ?? $frequency;
    }
}