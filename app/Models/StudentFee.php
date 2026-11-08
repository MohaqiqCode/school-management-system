<?php
namespace App\Models;

/**
 * Student Fee Model
 * مدیریت شهریه دانش‌آموزان
 */
class StudentFee extends Model
{
    protected $table = 'student_fees';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'semester_id',
        'total_amount',
        'discount_amount',
        'net_payable',
        'paid_amount',
        'due_date',
        'status',
    ];
    
    protected $casts = [
        'total_amount' => 'float',
        'discount_amount' => 'float',
        'net_payable' => 'float',
        'paid_amount' => 'float',
        'balance_amount' => 'float',
        'due_date' => 'date',
    ];
    
    protected $appends = [
        'student_name',
        'category_name',
        'class_name',
        'status_text',
    ];
    
    /**
     * Get fees for student
     */
    public function getByStudent($studentId, $status = null)
    {
        $sql = "SELECT sf.*, fc.category_name, fc.frequency,
                       fs.amount as structure_amount
                FROM student_fees sf 
                JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                WHERE sf.student_id = ?";
        
        $params = [$studentId];
        
        if ($status) {
            $sql .= " AND sf.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY sf.due_date DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get pending fees
     */
    public function getPending($studentId)
    {
        return $this->getByStudent($studentId, 'pending');
    }
    
    /**
     * Get overdue fees
     */
    public function getOverdue()
    {
        $sql = "SELECT sf.*, s.student_code, u.first_name, u.last_name,
                       c.class_name, fc.category_name
                FROM student_fees sf 
                JOIN students s ON sf.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                JOIN classes c ON s.current_class_id = c.id
                WHERE sf.status = 'overdue' OR (sf.status IN ('pending', 'partial') AND sf.due_date < CURDATE())
                ORDER BY sf.due_date";
        
        return $this->db->select($sql);
    }
    
    /**
     * Generate fees for student
     */
    public function generateForStudent($studentId, $academicYearId)
    {
        // Get student's class
        $student = $this->db->selectOne(
            "SELECT current_class_id FROM students WHERE id = ?",
            [$studentId]
        );
        
        if (!$student) return false;
        
        // Get fee structures for class
        $structures = $this->db->select(
            "SELECT fs.*, fc.frequency, fc.is_recurring 
             FROM fee_structures fs 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             WHERE fs.class_id = ? AND fs.academic_year_id = ? AND fs.is_active = 1",
            [$student['current_class_id'], $academicYearId]
        );
        
        $this->db->beginTransaction();
        
        try {
            foreach ($structures as $structure) {
                // Calculate due date
                $dueDay = $structure['due_day'] ?? 10;
                $dueDate = date('Y-m-' . str_pad($dueDay, 2, '0', STR_PAD_LEFT));
                
                if ($structure['frequency'] === 'monthly') {
                    // Generate for each month
                    for ($i = 1; $i <= 12; $i++) {
                        $dueDate = date("Y-{$i}-" . str_pad($dueDay, 2, '0', STR_PAD_LEFT));
                        
                        // Skip past months
                        if (strtotime($dueDate) < strtotime(date('Y-m-01'))) {
                            continue;
                        }
                        
                        $this->createFeeRecord($studentId, $structure['id'], $structure['amount'], $dueDate);
                    }
                } else {
                    $this->createFeeRecord($studentId, $structure['id'], $structure['amount'], $dueDate);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Create single fee record
     */
    private function createFeeRecord($studentId, $structureId, $amount, $dueDate)
    {
        // Check if already exists
        $existing = $this->db->selectOne(
            "SELECT id FROM student_fees WHERE student_id = ? AND fee_structure_id = ? AND due_date = ?",
            [$studentId, $structureId, $dueDate]
        );
        
        if ($existing) return $existing['id'];
        
        return $this->db->insert('student_fees', [
            'student_id' => $studentId,
            'fee_structure_id' => $structureId,
            'total_amount' => $amount,
            'discount_amount' => 0,
            'net_payable' => $amount,
            'paid_amount' => 0,
            'due_date' => $dueDate,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Apply discount
     */
    public function applyDiscount($feeId, $discountAmount)
    {
        $fee = $this->db->selectOne("SELECT * FROM student_fees WHERE id = ?", [$feeId]);
        
        if (!$fee) return false;
        
        $newNetPayable = $fee['total_amount'] - $discountAmount;
        $newBalance = $newNetPayable - $fee['paid_amount'];
        
        $newStatus = 'pending';
        if ($newBalance <= 0 && $fee['paid_amount'] > 0) {
            $newStatus = 'paid';
        }
        
        return $this->db->update('student_fees', [
            'discount_amount' => $discountAmount,
            'net_payable' => $newNetPayable,
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$feeId]);
    }
    
    /**
     * Waive fee
     */
    public function waive($feeId)
    {
        return $this->db->update('student_fees', [
            'status' => 'waived',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$feeId]);
    }
    
    /**
     * Get collection summary by class
     */
    public function getCollectionSummaryByClass()
    {
        $sql = "SELECT c.class_name,
                       COUNT(DISTINCT sf.student_id) as student_count,
                       COALESCE(SUM(sf.net_payable), 0) as total_fees,
                       COALESCE(SUM(sf.paid_amount), 0) as collected,
                       COALESCE(SUM(sf.balance_amount), 0) as outstanding,
                       ROUND((COALESCE(SUM(sf.paid_amount), 0) / NULLIF(COALESCE(SUM(sf.net_payable), 0), 0)) * 100, 2) as collection_rate
                FROM student_fees sf 
                JOIN students s ON sf.student_id = s.id 
                JOIN classes c ON s.current_class_id = c.id 
                WHERE sf.status != 'waived'
                GROUP BY c.id
                ORDER BY c.class_level";
        
        return $this->db->select($sql);
    }
    
    // Accessors
    public function appendStudentNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendCategoryNameAttribute()
    {
        return $this->attributes['category_name'] ?? null;
    }
    
    public function appendClassNameAttribute()
    {
        return $this->attributes['class_name'] ?? null;
    }
    
    public function appendStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'در انتظار پرداخت',
            'partial' => 'پرداخت جزئی',
            'paid' => 'پرداخت شده',
            'overdue' => 'سررسید گذشته',
            'waived' => 'بخشیده شده',
        ];
        
        $status = $this->attributes['status'] ?? 'pending';
        return $statuses[$status] ?? $status;
    }
}