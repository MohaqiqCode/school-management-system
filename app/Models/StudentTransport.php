<?php
namespace App\Models;

/**
 * Student Transport Model
 * مدیریت تخصیص حمل و نقل دانش‌آموزان
 */
class StudentTransport extends Model
{
    protected $table = 'student_transport';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'student_id',
        'route_id',
        'stop_id',
        'allocation_date',
        'end_date',
        'monthly_fee',
        'is_active',
    ];
    
    protected $casts = [
        'allocation_date' => 'date',
        'end_date' => 'date',
        'monthly_fee' => 'float',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'student_name',
        'route_name',
        'stop_name',
    ];
    
    /**
     * Get allocations with details
     */
    public function getAllWithDetails($filters = [])
    {
        $sql = "SELECT st.*, s.student_code, u.first_name, u.last_name,
                       c.class_name, sec.section_name,
                       tr.route_name, tr.route_code,
                       rs.stop_name
                FROM student_transport st 
                JOIN students s ON st.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN transport_routes tr ON st.route_id = tr.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                LEFT JOIN route_stops rs ON st.stop_id = rs.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['route_id'])) {
            $sql .= " AND st.route_id = ?";
            $params[] = $filters['route_id'];
        }
        
        if (!empty($filters['branch_id'])) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $filters['branch_id'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND st.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Allocate student to route
     */
    public function allocate($studentId, $routeId, $stopId = null, $monthlyFee = 0)
    {
        // Check if already allocated
        $existing = $this->db->selectOne(
            "SELECT id FROM student_transport WHERE student_id = ? AND is_active = 1",
            [$studentId]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'این دانش‌آموز قبلاً به یک مسیر تخصیص داده شده است'];
        }
        
        // Update student's transportation_required
        $this->db->update('students', [
            'transportation_required' => 1
        ], 'id = ?', [$studentId]);
        
        $id = $this->db->insert('student_transport', [
            'student_id' => $studentId,
            'route_id' => $routeId,
            'stop_id' => $stopId,
            'allocation_date' => date('Y-m-d'),
            'monthly_fee' => $monthlyFee,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['success' => true, 'id' => $id];
    }
    
    /**
     * Deallocate student
     */
    public function deallocate($allocationId)
    {
        $allocation = $this->db->selectOne("SELECT * FROM student_transport WHERE id = ?", [$allocationId]);
        
        if (!$allocation) return false;
        
        $this->db->update('student_transport', [
            'is_active' => 0,
            'end_date' => date('Y-m-d')
        ], 'id = ?', [$allocationId]);
        
        // Check if student has other active transport
        $activeCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM student_transport WHERE student_id = ? AND is_active = 1",
            [$allocation['student_id']]
        );
        
        if ($activeCount['count'] == 0) {
            $this->db->update('students', [
                'transportation_required' => 0
            ], 'id = ?', [$allocation['student_id']]);
        }
        
        return true;
    }
    
    /**
     * Change route for student
     */
    public function changeRoute($allocationId, $newRouteId, $newStopId = null)
    {
        $allocation = $this->db->selectOne("SELECT * FROM student_transport WHERE id = ?", [$allocationId]);
        
        if (!$allocation) return false;
        
        // Deactivate old allocation
        $this->deallocate($allocationId);
        
        // Create new allocation
        return $this->allocate(
            $allocation['student_id'],
            $newRouteId,
            $newStopId,
            $allocation['monthly_fee']
        );
    }
    
    /**
     * Get route utilization statistics
     */
    public function getRouteUtilization($branchId)
    {
        $sql = "SELECT tr.id, tr.route_name, tr.route_code,
                       v.vehicle_number, v.capacity,
                       COUNT(st.id) as allocated_students,
                       ROUND((COUNT(st.id) / NULLIF(v.capacity, 0)) * 100, 2) as utilization_rate
                FROM transport_routes tr 
                LEFT JOIN vehicles v ON tr.vehicle_id = v.id
                LEFT JOIN student_transport st ON tr.id = st.route_id AND st.is_active = 1
                WHERE tr.branch_id = ? AND tr.is_active = 1
                GROUP BY tr.id
                ORDER BY utilization_rate DESC";
        
        return $this->db->select($sql, [$branchId]);
    }
    
    /**
     * Get monthly collection from transport fees
     */
    public function getMonthlyCollection($branchId, $month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $sql = "SELECT tr.route_name,
                       COUNT(st.id) as student_count,
                       COALESCE(SUM(st.monthly_fee), 0) as total_collection
                FROM student_transport st 
                JOIN transport_routes tr ON st.route_id = tr.id 
                JOIN students s ON st.student_id = s.id 
                WHERE s.branch_id = ? AND st.is_active = 1
                AND MONTH(st.allocation_date) <= ? AND YEAR(st.allocation_date) <= ?
                GROUP BY tr.id
                ORDER BY total_collection DESC";
        
        return $this->db->select($sql, [$branchId, $month, $year]);
    }
    
    // Accessors
    public function appendStudentNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendRouteNameAttribute()
    {
        return $this->attributes['route_name'] ?? null;
    }
    
    public function appendStopNameAttribute()
    {
        return $this->attributes['stop_name'] ?? 'تخصیص نیافته';
    }
}