<?php
namespace App\Models;

/**
 * Branch Model
 * مدیریت شعب
 */
class Branch extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'branch_code',
        'name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'principal_name',
        'established_date',
        'total_capacity',
        'is_active',
        'logo_path',
        'settings',
    ];
    
    protected $casts = [
        'is_active' => 'bool',
        'total_capacity' => 'int',
        'established_date' => 'date',
        'settings' => 'json',
    ];
    
    protected $appends = [
        'student_count',
        'teacher_count',
        'employee_count',
        'class_count',
        'monthly_revenue',
        'occupancy_rate',
    ];
    
    /**
     * Get branch with statistics
     */
    public function getWithStats($id)
    {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM students WHERE branch_id = b.id AND is_active = 1) as student_count,
                       (SELECT COUNT(*) FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = b.id AND u.is_active = 1) as teacher_count,
                       (SELECT COUNT(*) FROM employees e JOIN users u ON e.user_id = u.id WHERE u.branch_id = b.id AND u.is_active = 1) as employee_count,
                       (SELECT COUNT(*) FROM classes WHERE branch_id = b.id AND is_active = 1) as class_count,
                       (SELECT COALESCE(SUM(fp.amount), 0) FROM fee_payments fp 
                        JOIN student_fees sf ON fp.student_fee_id = sf.id 
                        JOIN students s ON sf.student_id = s.id 
                        WHERE s.branch_id = b.id AND MONTH(fp.payment_date) = MONTH(CURDATE()) AND YEAR(fp.payment_date) = YEAR(CURDATE())) as monthly_revenue
                FROM branches b 
                WHERE b.id = ?";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get all branches with statistics
     */
    public function getAllWithStats()
    {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM students WHERE branch_id = b.id AND is_active = 1) as student_count,
                       (SELECT COUNT(*) FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = b.id AND u.is_active = 1) as teacher_count,
                       (SELECT COUNT(*) FROM classes WHERE branch_id = b.id AND is_active = 1) as class_count
                FROM branches b 
                WHERE b.is_active = 1
                ORDER BY b.name";
        
        return $this->hydrate($this->db->select($sql));
    }
    
    /**
     * Get branch by code
     */
    public function findByCode($code)
    {
        $sql = "SELECT * FROM branches WHERE branch_code = ?";
        $result = $this->db->selectOne($sql, [$code]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get active branches
     */
    public function getActive()
    {
        $sql = "SELECT * FROM branches WHERE is_active = 1 ORDER BY name";
        return $this->hydrate($this->db->select($sql));
    }
    
    /**
     * Get branch revenue report
     */
    public function getRevenueReport($branchId, $startDate, $endDate)
    {
        $sql = "SELECT DATE(fp.payment_date) as date,
                       SUM(fp.amount) as total,
                       COUNT(*) as transaction_count
                FROM fee_payments fp 
                JOIN student_fees sf ON fp.student_fee_id = sf.id 
                JOIN students s ON sf.student_id = s.id 
                WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?
                GROUP BY DATE(fp.payment_date)
                ORDER BY date";
        
        return $this->db->select($sql, [$branchId, $startDate, $endDate]);
    }
    
    /**
     * Get class distribution
     */
    public function getClassDistribution($branchId)
    {
        $sql = "SELECT c.class_name, c.class_level,
                       COUNT(s.id) as student_count,
                       c.capacity
                FROM classes c 
                LEFT JOIN students s ON c.id = s.current_class_id AND s.is_active = 1 
                WHERE c.branch_id = ? AND c.is_active = 1 
                GROUP BY c.id 
                ORDER BY c.class_level";
        
        return $this->db->select($sql, [$branchId]);
    }
    
    // Accessors
    public function appendStudentCountAttribute()
    {
        $branchId = $this->attributes['id'] ?? null;
        if (!$branchId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM students WHERE branch_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$branchId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendTeacherCountAttribute()
    {
        $branchId = $this->attributes['id'] ?? null;
        if (!$branchId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.branch_id = ? AND u.is_active = 1";
        $result = $this->db->selectOne($sql, [$branchId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendEmployeeCountAttribute()
    {
        $branchId = $this->attributes['id'] ?? null;
        if (!$branchId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM employees e JOIN users u ON e.user_id = u.id WHERE u.branch_id = ? AND u.is_active = 1";
        $result = $this->db->selectOne($sql, [$branchId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendClassCountAttribute()
    {
        $branchId = $this->attributes['id'] ?? null;
        if (!$branchId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM classes WHERE branch_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$branchId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendMonthlyRevenueAttribute()
    {
        $branchId = $this->attributes['id'] ?? null;
        if (!$branchId) return 0;
        
        $sql = "SELECT COALESCE(SUM(fp.amount), 0) as total 
                FROM fee_payments fp 
                JOIN student_fees sf ON fp.student_fee_id = sf.id 
                JOIN students s ON sf.student_id = s.id 
                WHERE s.branch_id = ? AND MONTH(fp.payment_date) = MONTH(CURDATE()) AND YEAR(fp.payment_date) = YEAR(CURDATE())";
        $result = $this->db->selectOne($sql, [$branchId]);
        return (float)($result['total'] ?? 0);
    }
    
    public function appendOccupancyRateAttribute()
    {
        $branchId = $this->attributes['id'] ?? null;
        $capacity = $this->attributes['total_capacity'] ?? 1;
        if (!$branchId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM students WHERE branch_id = ? AND is_active = 1";
        $result = $this->db->selectOne($sql, [$branchId]);
        $students = (int)($result['total'] ?? 0);
        
        return $capacity > 0 ? round(($students / $capacity) * 100, 2) : 0;
    }
}