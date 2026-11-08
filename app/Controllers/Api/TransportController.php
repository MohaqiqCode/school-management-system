<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class TransportController extends Controller
{
    /**
     * Get vehicles
     * GET /api/v1/transport/vehicles
     */
    public function vehicles()
    {
        $branchId = $this->session->get('branch_id');

        $vehicles = $this->db->select(
            "SELECT v.*, 
                    CONCAT(u.first_name, ' ', u.last_name) as driver_name
             FROM vehicles v 
             LEFT JOIN drivers d ON v.id = d.assigned_vehicle_id AND d.is_active = 1
             LEFT JOIN employees e ON d.employee_id = e.id
             LEFT JOIN users u ON e.user_id = u.id
             WHERE v.branch_id = ? AND v.is_active = 1
             ORDER BY v.vehicle_number",
            [$branchId]
        );

        $this->json(['success' => true, 'data' => $vehicles]);
    }

    /**
     * Get routes
     * GET /api/v1/transport/routes
     */
    public function routes()
    {
        $branchId = $this->session->get('branch_id');

        $routes = $this->db->select(
            "SELECT tr.*, v.vehicle_number,
                    CONCAT(u.first_name, ' ', u.last_name) as driver_name,
                    (SELECT COUNT(*) FROM route_stops WHERE route_id = tr.id) as stop_count,
                    (SELECT COUNT(*) FROM student_transport WHERE route_id = tr.id AND is_active = 1) as student_count
             FROM transport_routes tr 
             LEFT JOIN vehicles v ON tr.vehicle_id = v.id 
             LEFT JOIN drivers d ON tr.driver_id = d.id
             LEFT JOIN employees e ON d.employee_id = e.id
             LEFT JOIN users u ON e.user_id = u.id
             WHERE tr.branch_id = ? AND tr.is_active = 1
             ORDER BY tr.route_name",
            [$branchId]
        );

        $this->json(['success' => true, 'data' => $routes]);
    }

    /**
     * Get route stops
     * GET /api/v1/transport/routes/{id}/stops
     */
    public function stops($id)
    {
        $stops = $this->db->select(
            "SELECT rs.*, 
                    (SELECT COUNT(*) FROM student_transport WHERE stop_id = rs.id AND is_active = 1) as student_count
             FROM route_stops rs 
             WHERE rs.route_id = ?
             ORDER BY rs.stop_order",
            [$id]
        );

        $route = $this->db->selectOne(
            "SELECT tr.*, v.vehicle_number FROM transport_routes tr 
             LEFT JOIN vehicles v ON tr.vehicle_id = v.id 
             WHERE tr.id = ?", 
            [$id]
        );

        $this->json(['success' => true, 'data' => ['route' => $route, 'stops' => $stops]]);
    }

    /**
     * Get student allocations
     * GET /api/v1/transport/allocations
     */
    public function allocations()
    {
        $routeId = $this->request->get('route_id', '');
        $branchId = $this->session->get('branch_id');

        $sql = "SELECT st.*, s.student_code, u.first_name, u.last_name,
                       c.class_name, sec.section_name,
                       tr.route_name, rs.stop_name
                FROM student_transport st 
                JOIN students s ON st.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN transport_routes tr ON st.route_id = tr.id 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                LEFT JOIN sections sec ON s.current_section_id = sec.id 
                LEFT JOIN route_stops rs ON st.stop_id = rs.id
                WHERE s.branch_id = ? AND st.is_active = 1";

        $params = [$branchId];

        if ($routeId) {
            $sql .= " AND st.route_id = ?";
            $params[] = $routeId;
        }

        $sql .= " ORDER BY u.last_name, u.first_name";
        $allocations = $this->db->select($sql, $params);

        $this->json(['success' => true, 'data' => $allocations]);
    }

    /**
     * Allocate student to route
     * POST /api/v1/transport/allocate
     */
    public function allocateStudent()
    {
        $data = $this->request->getJson();

        $studentId = $data['student_id'] ?? '';
        $routeId = $data['route_id'] ?? '';
        $stopId = $data['stop_id'] ?? null;
        $monthlyFee = floatval($data['monthly_fee'] ?? 0);

        if (!$studentId || !$routeId) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز و مسیر الزامی است'], 422);
            return;
        }

        // Check if already allocated
        $existing = $this->db->selectOne(
            "SELECT id FROM student_transport WHERE student_id = ? AND is_active = 1",
            [$studentId]
        );

        if ($existing) {
            $this->json(['success' => false, 'message' => 'این دانش‌آموز قبلاً به یک مسیر تخصیص داده شده است'], 422);
            return;
        }

        $allocationId = $this->db->insert('student_transport', [
            'student_id' => $studentId,
            'route_id' => $routeId,
            'stop_id' => $stopId,
            'allocation_date' => date('Y-m-d'),
            'monthly_fee' => $monthlyFee,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update student transportation flag
        $this->db->update('students', ['transportation_required' => 1], 'id = ?', [$studentId]);

        $this->json([
            'success' => true,
            'message' => 'دانش‌آموز با موفقیت به مسیر تخصیص یافت',
            'data' => ['id' => $allocationId]
        ], 201);
    }

    /**
     * Deallocate student from route
     * POST /api/v1/transport/deallocate/{id}
     */
    public function deallocateStudent($id)
    {
        $allocation = $this->db->selectOne("SELECT * FROM student_transport WHERE id = ?", [$id]);
        if (!$allocation) {
            $this->json(['success' => false, 'message' => 'تخصیص یافت نشد'], 404);
            return;
        }

        $this->db->update('student_transport', [
            'is_active' => 0,
            'end_date' => date('Y-m-d')
        ], 'id = ?', [$id]);

        // Check if student has other active transport
        $activeCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM student_transport WHERE student_id = ? AND is_active = 1",
            [$allocation['student_id']]
        )['count'] ?? 0;

        if ($activeCount == 0) {
            $this->db->update('students', ['transportation_required' => 0], 'id = ?', [$allocation['student_id']]);
        }

        $this->json(['success' => true, 'message' => 'تخصیص حمل و نقل لغو شد']);
    }
}