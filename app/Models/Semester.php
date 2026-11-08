<?php
namespace App\Models;

/**
 * Semester Model
 * مدیریت ترم‌های تحصیلی
 */
class Semester extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'academic_year_id',
        'semester_name',
        'semester_number',
        'start_date',
        'end_date',
        'is_current',
    ];
    
    protected $casts = [
        'is_current' => 'bool',
        'semester_number' => 'int',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    protected $appends = [
        'year_name',
        'timetable_count',
        'exam_count',
        'duration_days',
    ];
    
    /**
     * Get current semester
     */
    public function getCurrent($academicYearId = null)
    {
        $sql = "SELECT * FROM semesters WHERE is_current = 1";
        $params = [];
        
        if ($academicYearId) {
            $sql .= " AND academic_year_id = ?";
            $params[] = $academicYearId;
        }
        
        $result = $this->db->selectOne($sql, $params);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get semesters by academic year
     */
    public function getByAcademicYear($academicYearId)
    {
        $sql = "SELECT sem.*, ay.year_name
                FROM semesters sem 
                JOIN academic_years ay ON sem.academic_year_id = ay.id 
                WHERE sem.academic_year_id = ?
                ORDER BY sem.semester_number";
        
        return $this->hydrate($this->db->select($sql, [$academicYearId]));
    }
    
    /**
     * Set as current semester
     */
    public function setAsCurrent($semesterId, $academicYearId)
    {
        $this->db->beginTransaction();
        
        try {
            $this->db->update('semesters', [
                'is_current' => 0
            ], 'academic_year_id = ?', [$academicYearId]);
            
            $this->db->update('semesters', [
                'is_current' => 1
            ], 'id = ?', [$semesterId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Accessors
    public function appendYearNameAttribute()
    {
        return $this->attributes['year_name'] ?? null;
    }
    
    public function appendTimetableCountAttribute()
    {
        $semId = $this->attributes['id'] ?? null;
        if (!$semId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM timetables WHERE semester_id = ?";
        $result = $this->db->selectOne($sql, [$semId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendExamCountAttribute()
    {
        $semId = $this->attributes['id'] ?? null;
        if (!$semId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM exams WHERE semester_id = ?";
        $result = $this->db->selectOne($sql, [$semId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendDurationDaysAttribute()
    {
        $start = $this->attributes['start_date'] ?? null;
        $end = $this->attributes['end_date'] ?? null;
        
        if (!$start || !$end) return 0;
        
        return ceil((strtotime($end) - strtotime($start)) / 86400);
    }
}