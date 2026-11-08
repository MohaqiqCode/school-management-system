<?php
namespace App\Models;

/**
 * Fee Structure Model
 * مدیریت ساختار شهریه
 */
class FeeStructure extends Model
{
    protected $table = 'fee_structures';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'fee_category_id',
        'class_id',
        'academic_year_id',
        'amount',
        'late_fee_amount',
        'discount_allowed',
        'due_day',
        'is_active',
    ];
    
    protected $casts = [
        'amount' => 'float',
        'late_fee_amount' => 'float',
        'discount_allowed' => 'float',
        'due_day' => 'int',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'category_name',
        'class_name',
        'year_name',
    ];
    
    /**
     * Get structures with details
     */
    public function getAllWithDetails()
    {
        $sql = "SELECT fs.*, fc.category_name, c.class_name, ay.year_name
                FROM fee_structures fs 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                JOIN classes c ON fs.class_id = c.id 
                JOIN academic_years ay ON fs.academic_year_id = ay.id 
                ORDER BY c.class_level, fc.category_name";
        
        return $this->hydrate($this->db->select($sql));
    }
    
    /**
     * Get structure for class and category
     */
    public function getForClass($classId, $academicYearId)
    {
        $sql = "SELECT fs.*, fc.category_name, fc.frequency
                FROM fee_structures fs 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                WHERE fs.class_id = ? AND fs.academic_year_id = ? AND fs.is_active = 1
                ORDER BY fc.category_name";
        
        return $this->db->select($sql, [$classId, $academicYearId]);
    }
    
    /**
     * Get total fee for class
     */
    public function getTotalForClass($classId, $academicYearId)
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM fee_structures 
                WHERE class_id = ? AND academic_year_id = ? AND is_active = 1";
        
        $result = $this->db->selectOne($sql, [$classId, $academicYearId]);
        return (float)($result['total'] ?? 0);
    }
    
    /**
     * Clone structures from one academic year to another
     */
    public function cloneForYear($fromYearId, $toYearId)
    {
        $structures = $this->db->select(
            "SELECT * FROM fee_structures WHERE academic_year_id = ? AND is_active = 1",
            [$fromYearId]
        );
        
        $this->db->beginTransaction();
        
        try {
            foreach ($structures as $structure) {
                // Check if already exists in target year
                $existing = $this->db->selectOne(
                    "SELECT id FROM fee_structures WHERE fee_category_id = ? AND class_id = ? AND academic_year_id = ?",
                    [$structure['fee_category_id'], $structure['class_id'], $toYearId]
                );
                
                if (!$existing) {
                    $this->db->insert('fee_structures', [
                        'fee_category_id' => $structure['fee_category_id'],
                        'class_id' => $structure['class_id'],
                        'academic_year_id' => $toYearId,
                        'amount' => $structure['amount'],
                        'late_fee_amount' => $structure['late_fee_amount'],
                        'discount_allowed' => $structure['discount_allowed'],
                        'due_day' => $structure['due_day'],
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Accessors
    public function appendCategoryNameAttribute()
    {
        return $this->attributes['category_name'] ?? null;
    }
    
    public function appendClassNameAttribute()
    {
        return $this->attributes['class_name'] ?? null;
    }
    
    public function appendYearNameAttribute()
    {
        return $this->attributes['year_name'] ?? null;
    }
}