<?php
namespace App\Models;

/**
 * Driver Model
 * مدیریت رانندگان
 */
class Driver extends Model
{
    protected $table = 'drivers';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'employee_id',
        'license_number',
        'license_expiry',
        'experience_years',
        'assigned_vehicle_id',
        'is_active',
    ];
    
    protected $casts = [
        'experience_years' => 'int',
        'license_expiry' => 'date',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'driver_name',
        'vehicle_number',
        'route_count',
        'license_status',
    ];
    
    /**
     * Get drivers with details
     */
    public function getAllWithDetails($branchId = null)
    {
        $sql = "SELECT d.*, u.first_name, u.last_name, u.phone, u.email,
                       v.vehicle_number,
                       (SELECT COUNT(*) FROM transport_routes WHERE driver_id = d.id) as route_count
                FROM drivers d 
                JOIN employees e ON d.employee_id = e.id
                JOIN users u ON e.user_id = u.id 
                LEFT JOIN vehicles v ON d.assigned_vehicle_id = v.id
                WHERE 1=1";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, $params));
    }
    
    /**
     * Get available drivers
     */
    public function getAvailable($branchId = null)
    {
        $sql = "SELECT d.*, u.first_name, u.last_name
                FROM drivers d 
                JOIN employees e ON d.employee_id = e.id
                JOIN users u ON e.user_id = u.id 
                WHERE d.is_active = 1 AND d.assigned_vehicle_id IS NULL";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, $params));
    }
    
    /**
     * Assign vehicle to driver
     */
    public function assignVehicle($driverId, $vehicleId)
    {
        return $this->db->update('drivers', [
            'assigned_vehicle_id' => $vehicleId
        ], 'id = ?', [$driverId]);
    }
    
    /**
     * Unassign vehicle from driver
     */
    public function unassignVehicle($driverId)
    {
        return $this->db->update('drivers', [
            'assigned_vehicle_id' => null
        ], 'id = ?', [$driverId]);
    }
    
    // Accessors
    public function appendDriverNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendVehicleNumberAttribute()
    {
        return $this->attributes['vehicle_number'] ?? 'تخصیص نیافته';
    }
    
    public function appendRouteCountAttribute()
    {
        return $this->attributes['route_count'] ?? 0;
    }
    
    public function appendLicenseStatusAttribute()
    {
        $expiry = $this->attributes['license_expiry'] ?? null;
        if (!$expiry) return 'نامشخص';
        
        $daysLeft = ceil((strtotime($expiry) - time()) / 86400);
        
        if ($daysLeft < 0) return 'منقضی شده';
        if ($daysLeft < 30) return 'در شرف انقضا';
        return 'معتبر';
    }
}