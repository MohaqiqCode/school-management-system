<?php
namespace App\Models;

/**
 * Payroll Model
 * مدیریت حقوق و دستمزد
 */
class Payroll extends Model
{
    protected $table = 'payroll';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'salary_structure_id',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'allowances',
        'deductions',
        'payment_date',
        'payment_method',
        'status',
    ];
    
    protected $casts = [
        'basic_salary' => 'float',
        'allowances' => 'float',
        'deductions' => 'float',
        'gross_salary' => 'float',
        'net_salary' => 'float',
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'payment_date' => 'date',
    ];
    
    protected $appends = [
        'employee_name',
        'status_text',
    ];
    
    /**
     * Get payroll for period
     */
    public function getByPeriod($startDate, $endDate, $branchId = null)
    {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email,
                       ss.salary_grade, ss.basic_salary as grade_basic,
                       b.name as branch_name
                FROM payroll p 
                JOIN users u ON p.user_id = u.id 
                JOIN salary_structures ss ON p.salary_structure_id = ss.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE p.pay_period_start = ? AND p.pay_period_end = ?";
        
        $params = [$startDate, $endDate];
        
        if ($branchId) {
            $sql .= " AND u.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " ORDER BY u.last_name, u.first_name";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get payroll history for employee
     */
    public function getByEmployee($userId, $limit = 12)
    {
        $sql = "SELECT p.*, ss.salary_grade
                FROM payroll p 
                JOIN salary_structures ss ON p.salary_structure_id = ss.id 
                WHERE p.user_id = ?
                ORDER BY p.pay_period_start DESC
                LIMIT ?";
        
        return $this->db->select($sql, [$userId, $limit]);
    }
    
    /**
     * Process payroll for period
     */
    public function processPayroll($branchId, $startDate, $endDate)
    {
        $this->db->beginTransaction();
        
        try {
            // Get all active teachers
            $teachers = $this->db->select(
                "SELECT t.user_id, t.salary_grade, u.first_name, u.last_name
                 FROM teachers t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE u.branch_id = ? AND u.is_active = 1",
                [$branchId]
            );
            
            // Get all active employees
            $employees = $this->db->select(
                "SELECT e.user_id, e.salary_grade, u.first_name, u.last_name
                 FROM employees e 
                 JOIN users u ON e.user_id = u.id 
                 WHERE u.branch_id = ? AND u.is_active = 1",
                [$branchId]
            );
            
            $allStaff = array_merge(
                array_map(function($t) { return array_merge($t, ['type' => 'teacher']); }, $teachers),
                array_map(function($e) { return array_merge($e, ['type' => 'staff']); }, $employees)
            );
            
            foreach ($allStaff as $staff) {
                // Get salary structure
                $structure = $this->db->selectOne(
                    "SELECT * FROM salary_structures 
                     WHERE branch_id = ? AND employee_type = ? AND salary_grade = ? AND is_active = 1",
                    [$branchId, $staff['type'], $staff['salary_grade']]
                );
                
                if (!$structure) continue;
                
                // Check if already processed
                $existing = $this->db->selectOne(
                    "SELECT id FROM payroll 
                     WHERE user_id = ? AND pay_period_start = ? AND pay_period_end = ?",
                    [$staff['user_id'], $startDate, $endDate]
                );
                
                if ($existing) continue;
                
                $allowances = $structure['house_allowance'] + 
                             $structure['medical_allowance'] + 
                             $structure['transport_allowance'] + 
                             $structure['other_allowances'];
                
                $this->db->insert('payroll', [
                    'user_id' => $staff['user_id'],
                    'salary_structure_id' => $structure['id'],
                    'pay_period_start' => $startDate,
                    'pay_period_end' => $endDate,
                    'basic_salary' => $structure['basic_salary'],
                    'allowances' => $allowances,
                    'deductions' => $structure['deductions'],
                    'payment_date' => date('Y-m-d'),
                    'payment_method' => 'bank_transfer',
                    'status' => 'processed',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Mark as paid
     */
    public function markAsPaid($payrollId)
    {
        return $this->db->update('payroll', [
            'status' => 'paid',
            'payment_date' => date('Y-m-d')
        ], 'id = ?', [$payrollId]);
    }
    
    /**
     * Get payroll summary for period
     */
    public function getPeriodSummary($startDate, $endDate, $branchId = null)
    {
        $sql = "SELECT COUNT(*) as total_employees,
                       COALESCE(SUM(basic_salary), 0) as total_basic,
                       COALESCE(SUM(allowances), 0) as total_allowances,
                       COALESCE(SUM(deductions), 0) as total_deductions,
                       COALESCE(SUM(gross_salary), 0) as total_gross,
                       COALESCE(SUM(net_salary), 0) as total_net
                FROM payroll 
                WHERE pay_period_start = ? AND pay_period_end = ?";
        
        $params = [$startDate, $endDate];
        
        if ($branchId) {
            $sql .= " AND user_id IN (SELECT id FROM users WHERE branch_id = ?)";
            $params[] = $branchId;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    // Accessors
    public function appendEmployeeNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'در انتظار',
            'processed' => 'پردازش شده',
            'paid' => 'پرداخت شده',
        ];
        
        $status = $this->attributes['status'] ?? 'pending';
        return $statuses[$status] ?? $status;
    }
}