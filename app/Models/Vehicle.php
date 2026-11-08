<?php
namespace App\Models;

/**
 * Vehicle Model
 * مدیریت وسایل نقلیه
 */
class Vehicle extends Model
{
    protected $table = 'vehicles';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'vehicle_number',
        'vehicle_type',
        'model',
        'capacity',
        'registration_date',
        'insurance_expiry',
        'fitness_certificate_expiry',
        'fuel_type',
        'is_active',
    ];
    
    protected $casts = [
        'capacity' => 'int',
        'registration_date' => 'date',
        'insurance_expiry' => 'date',
        'fitness_certificate_expiry' => 'date',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'driver_name',
        'route_count',
        'insurance_status',
        'fitness_status',
    ];
    
    /**
     * Get vehicles by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT v.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as driver_name
                FROM vehicles v 
                LEFT JOIN drivers d ON v.id = d.assigned_vehicle_id AND d.is_active = 1
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                WHERE v.branch_id = ?
                ORDER BY v.vehicle_number";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get available vehicles (not assigned to any driver)
     */
    public function getAvailable($branchId)
    {
        $sql = "SELECT v.* FROM vehicles v 
                WHERE v.branch_id = ? AND v.is_active = 1
                AND v.id NOT IN (SELECT assigned_vehicle_id FROM drivers WHERE assigned_vehicle_id IS NOT NULL)
                ORDER BY v.vehicle_number";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get vehicles with expiring insurance
     */
    public function getExpiringInsurance($days = 30)
    {
        $sql = "SELECT v.*, b.name as branch_name
                FROM vehicles v 
                JOIN branches b ON v.branch_id = b.id 
                WHERE v.insurance_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND v.is_active = 1
                ORDER BY v.insurance_expiry";
        
        return $this->db->select($sql, [$days]);
    }
    
    /**
     * Get vehicles with expiring fitness certificate
     */
    public function getExpiringFitness($days = 30)
    {
        $sql = "SELECT v.*, b.name as branch_name
                FROM vehicles v 
                JOIN branches b ON v.branch_id = b.id 
                WHERE v.fitness_certificate_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND v.is_active = 1
                ORDER BY v.fitness_certificate_expiry";
        
        return $this->db->select($sql, [$days]);
    }
    
    // Accessors
    public function appendDriverNameAttribute()
    {
        return $this->attributes['driver_name'] ?? 'تخصیص نیافته';
    }
    
    public function appendRouteCountAttribute()
    {
        $vehicleId = $this->attributes['id'] ?? null;
        if (!$vehicleId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM transport_routes WHERE vehicle_id = ?";
        $result = $this->db->selectOne($sql, [$vehicleId]);
        return (int)($result['total'] ?? 0);
    }
    
    public function appendInsuranceStatusAttribute()
    {
        $expiry = $this->attributes['insurance_expiry'] ?? null;
        if (!$expiry) return 'نامشخص';
        
        $daysLeft = ceil((strtotime($expiry) - time()) / 86400);
        
        if ($daysLeft < 0) return 'منقضی شده';
        if ($daysLeft < 30) return 'در شرف انقضا';
        return 'معتبر';
    }
    
    public function appendFitnessStatusAttribute()
    {
        $expiry = $this->attributes['fitness_certificate_expiry'] ?? null;
        if (!$expiry) return 'نامشخص';
        
        $daysLeft = ceil((strtotime($expiry) - time()) / 86400);
        
        if ($daysLeft < 0) return 'منقضی شده';
        if ($daysLeft < 30) return 'در شرف انقضا';
        return 'معتبر';
    }
}