<?php
namespace App\Models;

/**
 * Academic Year Model
 * مدیریت سال‌های تحصیلی
 */
class AcademicYear extends Model
{
    protected $table = 'academic_years';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'branch_id',
        'year_name',
        'start_date',
        'end_date',
        'is_current',
        'status',
    ];
    
    protected $casts = [
        'is_current' => 'bool',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    protected $appends = [
        'branch_name',
        'semester_count',
        'student_count',
        'duration_days',
    ];
    
    /**
     * Get current academic year for branch
     */
    public function getCurrent($branchId)
    {
        $sql = "SELECT * FROM academic_years 
                WHERE branch_id = ? AND is_current = 1 AND status = 'active'";
        
        $result = $this->db->selectOne($sql, [$branchId]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    /**
     * Set as current year (and unset others)
     */
    public function setAsCurrent($yearId, $branchId)
    {
        $this->db->beginTransaction();
        
        try {
            // Unset all current
            $this->db->update('academic_years', [
                'is_current' => 0
            ], 'branch_id = ?', [$branchId]);
            
            // Set this as current
            $this->db->update('academic_years', [
                'is_current' => 1
            ], 'id = ?', [$yearId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get academic years by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT ay.*, 
                       (SELECT COUNT(*) FROM semesters WHERE academic_year_id = ay.id) as semester_count
                FROM academic_years ay 
                WHERE ay.branch_id = ?
                ORDER BY ay.start_date DESC";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Create academic year with auto-generated semesters
     */
    public function createWithSemesters($data)
    {
        $this->db->beginTransaction();
        
        try {
            $yearId = $this->db->insert('academic_years', [
                'branch_id' => $data['branch_id'],
                'year_name' => $data['year_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_current' => $data['is_current'] ?? 0,
                'status' => $data['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($data['auto_create_semesters'] ?? false) {
                $this->createDefaultSemesters($yearId, $data['start_date'], $data['end_date']);
            }
            
            if ($data['is_current'] ?? false) {
                $this->setAsCurrent($yearId, $data['branch_id']);
            }
            
            $this->db->commit();
            return $yearId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Create default semesters for academic year
     */
    private function createDefaultSemesters($yearId, $startDate, $endDate)
    {
        $semesters = [
            ['name' => 'نیمسال اول', 'number' => 1],
            ['name' => 'نیمسال دوم', 'number' => 2],
        ];
        
        $midPoint = strtotime($startDate) + ((strtotime($endDate) - strtotime($startDate)) / 2);
        
        foreach ($semesters as $index => $sem) {
            $semStart = $index == 0 ? $startDate : date('Y-m-d', $midPoint);
            $semEnd = $index == 0 ? date('Y-m-d', $midPoint - 86400) : $endDate;
            
            $this->db->insert('semesters', [
                'academic_year_id' => $yearId,
                'semester_name' => $sem['name'],
                'semester_number' => $sem['number'],
                'start_date' => $semStart,
                'end_date' => $semEnd,
                'is_current' => $index == 0 ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Accessors
    public function appendBranchNameAttribute()
    {
        return $this->attributes['branch_name'] ?? null;
    }
    
    public function appendSemesterCountAttribute()
    {
        return $this->attributes['semester_count'] ?? 0;
    }
    
    public function appendStudentCountAttribute()
    {
        $yearId = $this->attributes['id'] ?? null;
        if (!$yearId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM student_promotions WHERE academic_year_id = ?";
        $result = $this->db->selectOne($sql, [$yearId]);
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