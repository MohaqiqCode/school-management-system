<?php
namespace App\Models;

/**
 * Salary Structure Model
 * مدیریت ساختار حقوق و دستمزد
 */
class SalaryStructure extends Model
{
    protected $table = 'salary_structures';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'employee_type',
        'salary_grade',
        'basic_salary',
        'house_allowance',
        'medical_allowance',
        'transport_allowance',
        'other_allowances',
        'deductions',
        'is_active',
    ];
    
    protected $casts = [
        'basic_salary' => 'float',
        'house_allowance' => 'float',
        'medical_allowance' => 'float',
        'transport_allowance' => 'float',
        'other_allowances' => 'float',
        'deductions' => 'float',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'total_allowances',
        'gross_salary',
        'employee_type_text',
    ];
    
    /**
     * Get structures by branch and type
     */
    public function getByBranch($branchId, $employeeType = null)
    {
        $sql = "SELECT * FROM salary_structures WHERE branch_id = ?";
        $params = [$branchId];
        
        if ($employeeType) {
            $sql .= " AND employee_type = ?";
            $params[] = $employeeType;
        }
        
        $sql .= " ORDER BY employee_type, salary_grade";
        
        return $this->hydrate($this->db->select($sql, $params));
    }
    
    /**
     * Get structure for grade
     */
    public function getByGrade($branchId, $employeeType, $grade)
    {
        $sql = "SELECT * FROM salary_structures 
                WHERE branch_id = ? AND employee_type = ? AND salary_grade = ? AND is_active = 1";
        
        $result = $this->db->selectOne($sql, [$branchId, $employeeType, $grade]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    /**
     * Calculate gross salary
     */
    public function calculateGross($structureId)
    {
        $structure = $this->find($structureId);
        
        if (!$structure) return 0;
        
        return $structure->basic_salary + 
               $structure->house_allowance + 
               $structure->medical_allowance + 
               $structure->transport_allowance + 
               $structure->other_allowances;
    }
    
    /**
     * Calculate net salary
     */
    public function calculateNet($structureId)
    {
        $gross = $this->calculateGross($structureId);
        $structure = $this->find($structureId);
        
        if (!$structure) return 0;
        
        return $gross - $structure->deductions;
    }
    
    // Accessors
    public function appendTotalAllowancesAttribute()
    {
        $house = $this->attributes['house_allowance'] ?? 0;
        $medical = $this->attributes['medical_allowance'] ?? 0;
        $transport = $this->attributes['transport_allowance'] ?? 0;
        $other = $this->attributes['other_allowances'] ?? 0;
        
        return $house + $medical + $transport + $other;
    }
    
    public function appendGrossSalaryAttribute()
    {
        $basic = $this->attributes['basic_salary'] ?? 0;
        $allowances = $this->total_allowances;
        
        return $basic + $allowances;
    }
    
    public function appendEmployeeTypeTextAttribute()
    {
        $types = [
            'teacher' => 'معلم',
            'staff' => 'کارمند',
        ];
        
        $type = $this->attributes['employee_type'] ?? '';
        return $types[$type] ?? $type;
    }
}