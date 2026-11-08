<?php
namespace App\Controllers;

use App\Core\Controller;

class PayrollController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $statusFilter = $_GET['status'] ?? '';
        $monthFilter = $_GET['month'] ?? date('m');
        $yearFilter = $_GET['year'] ?? date('Y');
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email,
                       ss.salary_grade, ss.basic_salary,
                       b.name as branch_name
                FROM payroll p 
                JOIN users u ON p.user_id = u.id 
                JOIN salary_structures ss ON p.salary_structure_id = ss.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE MONTH(p.pay_period_start) = ? AND YEAR(p.pay_period_start) = ?";
        
        $params = [$monthFilter, $yearFilter];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($statusFilter) {
            $sql .= " AND p.status = ?";
            $params[] = $statusFilter;
        }
        
        $countSql = str_replace("SELECT p.*, u.first_name, u.last_name, u.email, ss.salary_grade, ss.basic_salary, b.name as branch_name", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY u.last_name, u.first_name LIMIT {$perPage} OFFSET {$offset}";
        $payrolls = $this->db->select($sql, $params);
        
        // Summary
        $summary = $this->db->selectOne(
            "SELECT 
                COUNT(*) as total_employees,
                COALESCE(SUM(gross_salary), 0) as total_gross,
                COALESCE(SUM(net_salary), 0) as total_net,
                COALESCE(SUM(deductions), 0) as total_deductions
             FROM payroll 
             WHERE MONTH(pay_period_start) = ? AND YEAR(pay_period_start) = ?",
            [$monthFilter, $yearFilter]
        );
        
        $this->view('payroll/index', [
            'title' => 'مدیریت حقوق و دستمزد',
            'payrolls' => $payrolls,
            'summary' => $summary,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'statusFilter' => $statusFilter,
            'monthFilter' => $monthFilter,
            'yearFilter' => $yearFilter
        ]);
    }
    
    public function salaryStructures()
    {
        $branchId = $this->session->get('branch_id');
        
        $structures = $this->db->select(
            "SELECT ss.*, b.name as branch_name
             FROM salary_structures ss 
             JOIN branches b ON ss.branch_id = b.id 
             WHERE ss.branch_id = ?
             ORDER BY ss.employee_type, ss.salary_grade",
            [$branchId]
        );
        
        $this->view('payroll/structures', [
            'title' => 'ساختار حقوق و دستمزد',
            'structures' => $structures
        ]);
    }
    
    public function storeStructure()
    {
        $data = $_POST;
        
        // Validate
        if (empty($data['salary_grade']) || empty($data['basic_salary']) || empty($data['employee_type'])) {
            $this->session->flash('error', 'اطلاعات ساختار حقوق ناقص است');
            $this->redirect('/payroll/structures');
            return;
        }
        
        // Check unique
        $existing = $this->db->selectOne(
            "SELECT id FROM salary_structures WHERE branch_id = ? AND employee_type = ? AND salary_grade = ?",
            [$this->session->get('branch_id'), $data['employee_type'], $data['salary_grade']]
        );
        
        if ($existing) {
            $this->session->flash('error', 'این گرید حقوقی قبلاً تعریف شده است');
            $this->redirect('/payroll/structures');
            return;
        }
        
        $this->db->insert('salary_structures', [
            'branch_id' => $this->session->get('branch_id'),
            'employee_type' => $data['employee_type'],
            'salary_grade' => $data['salary_grade'],
            'basic_salary' => $data['basic_salary'],
            'house_allowance' => $data['house_allowance'] ?? 0,
            'medical_allowance' => $data['medical_allowance'] ?? 0,
            'transport_allowance' => $data['transport_allowance'] ?? 0,
            'other_allowances' => $data['other_allowances'] ?? 0,
            'deductions' => $data['deductions'] ?? 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('payroll', 'create_structure', "ساختار حقوقی {$data['salary_grade']} ایجاد شد");
        
        $this->session->flash('success', 'ساختار حقوقی با موفقیت ایجاد شد');
        $this->redirect('/payroll/structures');
    }
    
    public function processPayroll()
    {
        $branchId = $this->session->get('branch_id');
        
        // Get all active teachers and employees
        $teachers = $this->db->select(
            "SELECT t.id, t.user_id, t.salary_grade, u.first_name, u.last_name,
                    ss.id as structure_id, ss.basic_salary, ss.house_allowance,
                    ss.medical_allowance, ss.transport_allowance, ss.other_allowances, ss.deductions
             FROM teachers t 
             JOIN users u ON t.user_id = u.id 
             JOIN salary_structures ss ON t.salary_grade = ss.salary_grade 
                 AND ss.employee_type = 'teacher' AND ss.branch_id = ?
             WHERE u.branch_id = ? AND u.is_active = 1 AND ss.is_active = 1",
            [$branchId, $branchId]
        );
        
        $employees = $this->db->select(
            "SELECT e.id, e.user_id, e.salary_grade, u.first_name, u.last_name,
                    ss.id as structure_id, ss.basic_salary, ss.house_allowance,
                    ss.medical_allowance, ss.transport_allowance, ss.other_allowances, ss.deductions
             FROM employees e 
             JOIN users u ON e.user_id = u.id 
             JOIN salary_structures ss ON e.salary_grade = ss.salary_grade 
                 AND ss.employee_type = 'staff' AND ss.branch_id = ?
             WHERE u.branch_id = ? AND u.is_active = 1 AND ss.is_active = 1",
            [$branchId, $branchId]
        );
        
        $allStaff = array_merge($teachers, $employees);
        
        $this->view('payroll/process', [
            'title' => 'پردازش حقوق و دستمزد',
            'allStaff' => $allStaff
        ]);
    }
    
    public function storePayroll()
    {
        $data = $_POST;
        
        $payPeriodStart = $data['pay_period_start'] ?? date('Y-m-01');
        $payPeriodEnd = $data['pay_period_end'] ?? date('Y-m-t');
        $paymentDate = $data['payment_date'] ?? date('Y-m-t');
        
        if (empty($data['staff']) || !is_array($data['staff'])) {
            $this->session->flash('error', 'داده‌ای برای پردازش وجود ندارد');
            $this->redirect('/payroll/process');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($data['staff'] as $staffData) {
                $userId = $staffData['user_id'];
                $structureId = $staffData['structure_id'];
                $basicSalary = floatval($staffData['basic_salary'] ?? 0);
                $allowances = floatval($staffData['allowances'] ?? 0);
                $deductions = floatval($staffData['deductions'] ?? 0);
                
                // Check if payroll already exists for this period
                $existing = $this->db->selectOne(
                    "SELECT id FROM payroll WHERE user_id = ? AND pay_period_start = ? AND pay_period_end = ?",
                    [$userId, $payPeriodStart, $payPeriodEnd]
                );
                
                if ($existing) {
                    continue; // Skip if already processed
                }
                
                $this->db->insert('payroll', [
                    'user_id' => $userId,
                    'salary_structure_id' => $structureId,
                    'pay_period_start' => $payPeriodStart,
                    'pay_period_end' => $payPeriodEnd,
                    'basic_salary' => $basicSalary,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'payment_date' => $paymentDate,
                    'payment_method' => $staffData['payment_method'] ?? 'bank_transfer',
                    'status' => 'processed',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            
            $this->logActivity('payroll', 'process', "حقوق دوره {$payPeriodStart} تا {$payPeriodEnd} پردازش شد");
            
            $this->session->flash('success', 'حقوق و دستمزد با موفقیت پردازش شد');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در پردازش حقوق: ' . $e->getMessage());
        }
        
        $this->redirect('/payroll');
    }
    
    public function payslip($id)
    {
        $payroll = $this->db->selectOne(
            "SELECT p.*, u.first_name, u.last_name, u.email, u.employee_code as emp_code,
                    ss.salary_grade, ss.basic_salary as grade_basic,
                    b.name as branch_name
             FROM payroll p 
             JOIN users u ON p.user_id = u.id 
             JOIN salary_structures ss ON p.salary_structure_id = ss.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE p.id = ?",
            [$id]
        );
        
        if (!$payroll) {
            $this->session->flash('error', 'فیش حقوقی یافت نشد');
            $this->redirect('/payroll');
            return;
        }
        
        $this->view('payroll/payslip', [
            'title' => 'فیش حقوقی',
            'payroll' => $payroll
        ]);
    }
    
    public function markAsPaid($id)
    {
        $payroll = $this->db->selectOne("SELECT * FROM payroll WHERE id = ?", [$id]);
        
        if (!$payroll) {
            $this->json(['success' => false, 'message' => 'رکورد حقوق یافت نشد'], 404);
            return;
        }
        
        $this->db->update('payroll', [
            'status' => 'paid',
            'payment_date' => date('Y-m-d')
        ], 'id = ?', [$id]);
        
        $this->logActivity('payroll', 'mark_paid', "پرداخت حقوق {$id} تأیید شد");
        
        $this->json(['success' => true, 'message' => 'پرداخت حقوق تأیید شد']);
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