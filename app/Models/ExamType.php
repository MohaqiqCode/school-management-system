<?php
namespace App\Models;

/**
 * Exam Type Model
 * مدیریت انواع آزمون‌ها
 */
class ExamType extends Model
{
    protected $table = 'exam_types';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'name',
        'exam_category',
        'weightage_percentage',
        'is_active',
    ];
    
    protected $casts = [
        'weightage_percentage' => 'float',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'exam_count',
        'category_text',
    ];
    
    /**
     * Get active exam types
     */
    public function getActive()
    {
        $sql = "SELECT * FROM exam_types WHERE is_active = 1 ORDER BY exam_category, name";
        return $this->hydrate($this->db->select($sql));
    }
    
    /**
     * Get exam types by category
     */
    public function getByCategory($category)
    {
        $sql = "SELECT * FROM exam_types WHERE exam_category = ? AND is_active = 1 ORDER BY name";
        return $this->hydrate($this->db->select($sql, [$category]));
    }
    
    /**
     * Get all categories
     */
    public function getCategories()
    {
        $sql = "SELECT DISTINCT exam_category FROM exam_types WHERE is_active = 1";
        return $this->db->selectColumn($sql);
    }
    
    /**
     * Get exam types with count
     */
    public function getAllWithCount()
    {
        $sql = "SELECT et.*, 
                       (SELECT COUNT(*) FROM exams WHERE exam_type_id = et.id) as exam_count
                FROM exam_types et 
                WHERE et.is_active = 1
                ORDER BY et.exam_category, et.name";
        
        return $this->hydrate($this->db->select($sql));
    }
    
    // Accessors
    public function appendExamCountAttribute()
    {
        return $this->attributes['exam_count'] ?? 0;
    }
    
    public function appendCategoryTextAttribute()
    {
        $categories = [
            'mid_term' => 'میان ترم',
            'final' => 'پایان ترم',
            'unit_test' => 'آزمونک',
            'quarterly' => 'فصلی',
            'half_yearly' => 'نیمسال',
            'annual' => 'سالانه',
            'practical' => 'عملی',
            'oral' => 'شفاهی',
        ];
        
        $category = $this->attributes['exam_category'] ?? '';
        return $categories[$category] ?? $category;
    }
}