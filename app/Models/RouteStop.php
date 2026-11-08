<?php
namespace App\Models;

/**
 * Route Stop Model
 * مدیریت ایستگاه‌های مسیر
 */
class RouteStop extends Model
{
    protected $table = 'route_stops';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'route_id',
        'stop_name',
        'stop_order',
        'pickup_time',
        'drop_time',
        'latitude',
        'longitude',
    ];
    
    protected $casts = [
        'stop_order' => 'int',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
    
    /**
     * Get stops for route ordered by stop_order
     */
    public function getByRoute($routeId)
    {
        $sql = "SELECT rs.*, 
                       (SELECT COUNT(*) FROM student_transport WHERE stop_id = rs.id AND is_active = 1) as student_count
                FROM route_stops rs 
                WHERE rs.route_id = ?
                ORDER BY rs.stop_order";
        
        return $this->db->select($sql, [$routeId]);
    }
    
    /**
     * Get students at stop
     */
    public function getStudents($stopId)
    {
        $sql = "SELECT s.*, u.first_name, u.last_name, c.class_name
                FROM student_transport st 
                JOIN students s ON st.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                WHERE st.stop_id = ? AND st.is_active = 1
                ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, [$stopId]);
    }
    
    /**
     * Update stop times
     */
    public function updateTimes($stopId, $pickupTime, $dropTime)
    {
        return $this->db->update('route_stops', [
            'pickup_time' => $pickupTime,
            'drop_time' => $dropTime
        ], 'id = ?', [$stopId]);
    }
    
    /**
     * Move stop up (decrease order)
     */
    public function moveUp($stopId, $routeId)
    {
        $stop = $this->db->selectOne("SELECT * FROM route_stops WHERE id = ? AND route_id = ?", [$stopId, $routeId]);
        if (!$stop || $stop['stop_order'] <= 1) return false;
        
        $newOrder = $stop['stop_order'] - 1;
        
        // Swap with previous stop
        $this->db->update('route_stops', ['stop_order' => $stop['stop_order']], 
            'route_id = ? AND stop_order = ?', [$routeId, $newOrder]);
        
        $this->db->update('route_stops', ['stop_order' => $newOrder], 
            'id = ?', [$stopId]);
        
        return true;
    }
    
    /**
     * Move stop down (increase order)
     */
    public function moveDown($stopId, $routeId)
    {
        $stop = $this->db->selectOne("SELECT * FROM route_stops WHERE id = ? AND route_id = ?", [$stopId, $routeId]);
        if (!$stop) return false;
        
        $maxOrder = $this->db->selectOne(
            "SELECT MAX(stop_order) as max_order FROM route_stops WHERE route_id = ?", [$routeId]
        );
        
        if ($stop['stop_order'] >= $maxOrder['max_order']) return false;
        
        $newOrder = $stop['stop_order'] + 1;
        
        // Swap with next stop
        $this->db->update('route_stops', ['stop_order' => $stop['stop_order']], 
            'route_id = ? AND stop_order = ?', [$routeId, $newOrder]);
        
        $this->db->update('route_stops', ['stop_order' => $newOrder], 
            'id = ?', [$stopId]);
        
        return true;
    }
}