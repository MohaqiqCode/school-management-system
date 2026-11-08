<?php
namespace App\Models;

/**
 * Employee Model
 * مدیریت کارمندان غیرآموزشی
 */
class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',
        'employee_code',
        'branch_id',
        'department_id',
        'designation',
        'employee_type',
        'joining_date',
        'salary_grade',
        'supervisor_id',
        'bank_name',
        'bank_account_number',
    ];
    
    protected $casts = [
        'joining_date' => 'date',
    ];
    
    protected $appends = [
        'full_name',
        'department_name',
        'supervisor_name',
        'employee_type_text',
    ];
    
    /**
     * Get employee with details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT e.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       d.name as department_name,
                       b.name as branch_name,
                       CONCAT(su.first_name, ' ', su.last_name) as supervisor_name
                FROM employees e 
                JOIN users u ON e.user_id = u.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                JOIN branches b ON u.branch_id = b.id 
                LEFT JOIN employees sup ON e.supervisor_id = sup.id
                LEFT JOIN users su ON sup.user_id = su.id
                WHERE e.id = ? AND u.deleted_at IS NULL";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Get employees by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT e.*, u.first_name, u.last_name, u.email,
                       d.name as department_name
                FROM employees e 
                JOIN users u ON e.user_id = u.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                WHERE u.branch_id = ? AND u.is_active = 1 AND u.deleted_at IS NULL
                ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get employees by type
     */
    public function getByType($type)
    {
        $sql = "SELECT e.*, u.first_name, u.last_name, u.email,
                       d.name as department_name, b.name as branch_name
                FROM employees e 
                JOIN users u ON e.user_id = u.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                JOIN branches b ON u.branch_id = b.id 
                WHERE e.employee_type = ? AND u.is_active = 1 AND u.deleted_at IS NULL
                ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, [$type]));
    }
    
    /**
     * Get subordinates
     */
    public function getSubordinates($supervisorId)
    {
        $sql = "SELECT e.*, u.first_name, u.last_name, u.email
                FROM employees e 
                JOIN users u ON e.user_id = u.id 
                WHERE e.supervisor_id = ? AND u.is_active = 1
                ORDER BY u.last_name, u.first_name";
        
        return $this->hydrate($this->db->select($sql, [$supervisorId]));
    }
    
    /**
     * Find employee by user ID
     */
    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM employees WHERE user_id = ?";
        $result = $this->db->selectOne($sql, [$userId]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    // Accessors
    public function getFullNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendDepartmentNameAttribute()
    {
        return $this->attributes['department_name'] ?? null;
    }
    
    public function appendSupervisorNameAttribute()
    {
        return $this->attributes['supervisor_name'] ?? null;
    }
    
    public function appendEmployeeTypeTextAttribute()
    {
        $types = [
            'administrative' => 'اداری',
            'support' => 'پشتیبانی',
            'maintenance' => 'تاسیسات',
            'security' => 'حراست',
            'other' => 'سایر',
        ];
        
        $type = $this->attributes['employee_type'] ?? '';
        return $types[$type] ?? $type;
    }
}