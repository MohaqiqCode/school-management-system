<?php
namespace App\Models;

/**
 * Transport Route Model
 * مدیریت مسیرهای حمل و نقل
 */
class TransportRoute extends Model
{
    protected $table = 'transport_routes';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'route_name',
        'route_code',
        'start_point',
        'end_point',
        'distance_km',
        'estimated_time_minutes',
        'vehicle_id',
        'driver_id',
        'monthly_fee',
        'is_active',
    ];
    
    protected $casts = [
        'distance_km' => 'float',
        'estimated_time_minutes' => 'int',
        'monthly_fee' => 'float',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'vehicle_number',
        'driver_name',
        'stop_count',
        'student_count',
    ];
    
    /**
     * Get routes with details
     */
    public function getAllWithDetails($branchId)
    {
        $sql = "SELECT tr.*, v.vehicle_number,
                       CONCAT(du.first_name, ' ', du.last_name) as driver_name,
                       (SELECT COUNT(*) FROM route_stops WHERE route_id = tr.id) as stop_count,
                       (SELECT COUNT(*) FROM student_transport WHERE route_id = tr.id AND is_active = 1) as student_count
                FROM transport_routes tr 
                LEFT JOIN vehicles v ON tr.vehicle_id = v.id 
                LEFT JOIN drivers d ON tr.driver_id = d.id
                LEFT JOIN employees e ON d.employee_id = e.id
                LEFT JOIN users du ON e.user_id = du.id
                WHERE tr.branch_id = ?
                ORDER BY tr.route_name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get route with stops
     */
    public function getWithStops($routeId)
    {
        $route = $this->db->selectOne(
            "SELECT tr.*, v.vehicle_number,
                    CONCAT(du.first_name, ' ', du.last_name) as driver_name
             FROM transport_routes tr 
             LEFT JOIN vehicles v ON tr.vehicle_id = v.id 
             LEFT JOIN drivers d ON tr.driver_id = d.id
             LEFT JOIN employees e ON d.employee_id = e.id
             LEFT JOIN users du ON e.user_id = du.id
             WHERE tr.id = ?",
            [$routeId]
        );
        
        if ($route) {
            $route['stops'] = $this->db->select(
                "SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order",
                [$routeId]
            );
        }
        
        return $route;
    }
    
    /**
     * Get active routes
     */
    public function getActive($branchId)
    {
        $sql = "SELECT * FROM transport_routes WHERE branch_id = ? AND is_active = 1 ORDER BY route_name";
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get students on route
     */
    public function getStudents($routeId)
    {
        $sql = "SELECT st.*, s.student_code, u.first_name, u.last_name,
                       c.class_name, rs.stop_name
                FROM student_transport st 
                JOIN students s ON st.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN route_stops rs ON st.stop_id = rs.id
                WHERE st.route_id = ? AND st.is_active = 1
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$routeId]);
    }
    
    /**
     * Add stop to route
     */
    public function addStop($routeId, $data)
    {
        // Get max stop order
        $maxOrder = $this->db->selectOne(
            "SELECT COALESCE(MAX(stop_order), 0) as max_order FROM route_stops WHERE route_id = ?",
            [$routeId]
        );
        
        $stopOrder = $data['stop_order'] ?? ($maxOrder['max_order'] + 1);
        
        return $this->db->insert('route_stops', [
            'route_id' => $routeId,
            'stop_name' => $data['stop_name'],
            'stop_order' => $stopOrder,
            'pickup_time' => $data['pickup_time'] ?? null,
            'drop_time' => $data['drop_time'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Reorder stops
     */
    public function reorderStops($routeId, array $stopOrders)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($stopOrders as $stopId => $order) {
                $this->db->update('route_stops', [
                    'stop_order' => $order
                ], 'id = ? AND route_id = ?', [$stopId, $routeId]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Accessors
    public function appendVehicleNumberAttribute()
    {
        return $this->attributes['vehicle_number'] ?? 'تخصیص نیافته';
    }
    
    public function appendDriverNameAttribute()
    {
        return $this->attributes['driver_name'] ?? 'تخصیص نیافته';
    }
    
    public function appendStopCountAttribute()
    {
        return $this->attributes['stop_count'] ?? 0;
    }
    
    public function appendStudentCountAttribute()
    {
        return $this->attributes['student_count'] ?? 0;
    }
}