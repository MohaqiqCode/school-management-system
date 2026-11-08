<?php
namespace App\Controllers;

use App\Core\Controller;

class TransportController extends Controller
{
    public function index()
    {
        $branchId = $this->session->get('branch_id');
        
        // Get statistics
        $stats = [
            'total_vehicles' => $this->db->selectOne("SELECT COUNT(*) as count FROM vehicles WHERE branch_id = ? AND is_active = 1", [$branchId])['count'],
            'total_routes' => $this->db->selectOne("SELECT COUNT(*) as count FROM transport_routes WHERE branch_id = ? AND is_active = 1", [$branchId])['count'],
            'total_drivers' => $this->db->selectOne("SELECT COUNT(*) as count FROM drivers d JOIN employees e ON d.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE u.branch_id = ? AND d.is_active = 1", [$branchId])['count'],
            'students_using_transport' => $this->db->selectOne("SELECT COUNT(*) as count FROM student_transport st JOIN students s ON st.student_id = s.id WHERE s.branch_id = ? AND st.is_active = 1", [$branchId])['count']
        ];
        
        // Get vehicles
        $vehicles = $this->db->select("SELECT * FROM vehicles WHERE branch_id = ? AND is_active = 1 ORDER BY vehicle_number", [$branchId]);
        
        // Get routes
        $routes = $this->db->select(
            "SELECT tr.*, v.vehicle_number, 
                    CONCAT(u.first_name, ' ', u.last_name) as driver_name,
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
        
        $this->view('transport/index', [
            'title' => 'مدیریت حمل و نقل',
            'stats' => $stats,
            'vehicles' => $vehicles,
            'routes' => $routes
        ]);
    }
    
    // Vehicle Management
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
             WHERE v.branch_id = ? 
             ORDER BY v.vehicle_number",
            [$branchId]
        );
        
        $this->view('transport/vehicles', [
            'title' => 'مدیریت وسایل نقلیه',
            'vehicles' => $vehicles
        ]);
    }
    
    public function createVehicle()
    {
        $this->view('transport/vehicle_create', [
            'title' => 'افزودن وسیله نقلیه جدید'
        ]);
    }
    
    public function storeVehicle()
    {
        $data = $_POST;
        
        // Validate
        if (empty($data['vehicle_number'])) {
            $this->session->flash('error', 'شماره وسیله نقلیه الزامی است');
            $this->redirect('/transport/vehicles/create');
            return;
        }
        
        // Check unique vehicle number
        $existing = $this->db->selectOne("SELECT id FROM vehicles WHERE vehicle_number = ?", [$data['vehicle_number']]);
        
        if ($existing) {
            $this->session->flash('error', 'این شماره وسیله نقلیه قبلاً ثبت شده است');
            $this->redirect('/transport/vehicles/create');
            return;
        }
        
        $this->db->insert('vehicles', [
            'branch_id' => $this->session->get('branch_id'),
            'vehicle_number' => $data['vehicle_number'],
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'model' => $data['model'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'registration_date' => $data['registration_date'] ?? null,
            'insurance_expiry' => $data['insurance_expiry'] ?? null,
            'fitness_certificate_expiry' => $data['fitness_certificate_expiry'] ?? null,
            'fuel_type' => $data['fuel_type'] ?? 'diesel',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('transport', 'create_vehicle', "وسیله نقلیه {$data['vehicle_number']} ثبت شد");
        
        $this->session->flash('success', 'وسیله نقلیه با موفقیت ثبت شد');
        $this->redirect('/transport/vehicles');
    }
    
    public function editVehicle($id)
    {
        $vehicle = $this->db->selectOne("SELECT * FROM vehicles WHERE id = ?", [$id]);
        
        if (!$vehicle) {
            $this->session->flash('error', 'وسیله نقلیه یافت نشد');
            $this->redirect('/transport/vehicles');
            return;
        }
        
        $this->view('transport/vehicle_edit', [
            'title' => 'ویرایش وسیله نقلیه',
            'vehicle' => $vehicle
        ]);
    }
    
    public function updateVehicle($id)
    {
        $vehicle = $this->db->selectOne("SELECT * FROM vehicles WHERE id = ?", [$id]);
        
        if (!$vehicle) {
            $this->session->flash('error', 'وسیله نقلیه یافت نشد');
            $this->redirect('/transport/vehicles');
            return;
        }
        
        $data = $_POST;
        
        $this->db->update('vehicles', [
            'vehicle_number' => $data['vehicle_number'],
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'model' => $data['model'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'registration_date' => $data['registration_date'] ?? null,
            'insurance_expiry' => $data['insurance_expiry'] ?? null,
            'fitness_certificate_expiry' => $data['fitness_certificate_expiry'] ?? null,
            'fuel_type' => $data['fuel_type'] ?? 'diesel',
            'is_active' => isset($data['is_active']) ? 1 : 0
        ], 'id = ?', [$id]);
        
        $this->logActivity('transport', 'update_vehicle', "وسیله نقلیه {$id} به‌روزرسانی شد");
        
        $this->session->flash('success', 'اطلاعات وسیله نقلیه به‌روزرسانی شد');
        $this->redirect('/transport/vehicles');
    }
    
    // Route Management
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
             WHERE tr.branch_id = ?
             ORDER BY tr.route_name",
            [$branchId]
        );
        
        $this->view('transport/routes', [
            'title' => 'مدیریت مسیرها',
            'routes' => $routes
        ]);
    }
    
    public function createRoute()
    {
        $branchId = $this->session->get('branch_id');
        
        $vehicles = $this->db->select("SELECT id, vehicle_number FROM vehicles WHERE branch_id = ? AND is_active = 1", [$branchId]);
        $drivers = $this->db->select(
            "SELECT d.id, u.first_name, u.last_name FROM drivers d JOIN employees e ON d.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE u.branch_id = ? AND d.is_active = 1",
            [$branchId]
        );
        
        $this->view('transport/route_create', [
            'title' => 'ایجاد مسیر جدید',
            'vehicles' => $vehicles,
            'drivers' => $drivers
        ]);
    }
    
    public function storeRoute()
    {
        $data = $_POST;
        
        // Validate
        if (empty($data['route_name']) || empty($data['route_code'])) {
            $this->session->flash('error', 'نام و کد مسیر الزامی است');
            $this->redirect('/transport/routes/create');
            return;
        }
        
        $this->db->insert('transport_routes', [
            'branch_id' => $this->session->get('branch_id'),
            'route_name' => $data['route_name'],
            'route_code' => strtoupper($data['route_code']),
            'start_point' => $data['start_point'] ?? null,
            'end_point' => $data['end_point'] ?? null,
            'distance_km' => $data['distance_km'] ?? null,
            'estimated_time_minutes' => $data['estimated_time_minutes'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'monthly_fee' => $data['monthly_fee'] ?? 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('transport', 'create_route', "مسیر '{$data['route_name']}' ایجاد شد");
        
        $this->session->flash('success', 'مسیر با موفقیت ایجاد شد');
        $this->redirect('/transport/routes');
    }
    
    // Student Transport Allocation
    public function studentAllocation()
    {
        $routeId = $_GET['route_id'] ?? '';
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
                WHERE s.branch_id = ?";
        
        $params = [$branchId];
        
        if ($routeId) {
            $sql .= " AND st.route_id = ?";
            $params[] = $routeId;
        }
        
        $sql .= " ORDER BY u.last_name, u.first_name";
        $allocations = $this->db->select($sql, $params);
        
        $routes = $this->db->select("SELECT id, route_name FROM transport_routes WHERE branch_id = ? AND is_active = 1", [$branchId]);
        
        $this->view('transport/student_allocation', [
            'title' => 'تخصیص حمل و نقل دانش‌آموزان',
            'allocations' => $allocations,
            'routes' => $routes,
            'selectedRoute' => $routeId
        ]);
    }
    
    public function allocateStudent()
    {
        $data = $_POST;
        
        $studentId = $data['student_id'] ?? '';
        $routeId = $data['route_id'] ?? '';
        $stopId = $data['stop_id'] ?? null;
        $allocationDate = $data['allocation_date'] ?? date('Y-m-d');
        $monthlyFee = $data['monthly_fee'] ?? 0;
        
        if (!$studentId || !$routeId) {
            $this->json(['success' => false, 'message' => 'دانش‌آموز و مسیر الزامی است']);
            return;
        }
        
        // Check if already allocated
        $existing = $this->db->selectOne(
            "SELECT id FROM student_transport WHERE student_id = ? AND is_active = 1",
            [$studentId]
        );
        
        if ($existing) {
            $this->json(['success' => false, 'message' => 'این دانش‌آموز قبلاً به یک مسیر تخصیص داده شده است']);
            return;
        }
        
        $this->db->insert('student_transport', [
            'student_id' => $studentId,
            'route_id' => $routeId,
            'stop_id' => $stopId,
            'allocation_date' => $allocationDate,
            'monthly_fee' => $monthlyFee,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('transport', 'allocate_student', "دانش‌آموز {$studentId} به مسیر {$routeId} تخصیص یافت");
        
        $this->json(['success' => true, 'message' => 'دانش‌آموز با موفقیت به مسیر تخصیص یافت']);
    }
    
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
        
        $this->logActivity('transport', 'deallocate_student', "تخصیص حمل و نقل {$id} لغو شد");
        
        $this->json(['success' => true, 'message' => 'تخصیص حمل و نقل لغو شد']);
    }
    
    public function getRouteStops($routeId)
    {
        $stops = $this->db->select(
            "SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order",
            [$routeId]
        );
        
        $this->json(['success' => true, 'data' => $stops]);
    }
    
    public function storeStop()
    {
        $data = $_POST;
        
        $this->db->insert('route_stops', [
            'route_id' => $data['route_id'],
            'stop_name' => $data['stop_name'],
            'stop_order' => $data['stop_order'] ?? 1,
            'pickup_time' => $data['pickup_time'] ?? null,
            'drop_time' => $data['drop_time'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->json(['success' => true, 'message' => 'ایستگاه اضافه شد']);
    }
    
    private function logActivity($module, $action, $description)
    {
        $userId = $this->session->get('user_id');
        
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}