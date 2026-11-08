<?php
namespace App\Models;

/**
 * Fee Payment Model
 * مدیریت پرداخت‌های شهریه
 */
class FeePayment extends Model
{
    protected $table = 'fee_payments';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'student_fee_id',
        'payment_number',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_date',
        'received_by',
        'receipt_number',
        'remarks',
    ];
    
    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'datetime',
    ];
    
    protected $appends = [
        'student_name',
        'category_name',
        'payment_method_text',
    ];
    
    /**
     * Get payment with details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT fp.*, u.first_name as student_first_name, u.last_name as student_last_name,
                       s.student_code, fc.category_name, c.class_name,
                       CONCAT(ru.first_name, ' ', ru.last_name) as received_by_name
                FROM fee_payments fp 
                JOIN student_fees sf ON fp.student_fee_id = sf.id 
                JOIN students s ON sf.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                JOIN classes c ON s.current_class_id = c.id
                LEFT JOIN users ru ON fp.received_by = ru.id
                WHERE fp.id = ?";
        
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result) {
            $this->fill($result);
            $this->original = $result;
            return $this;
        }
        
        return null;
    }
    
    /**
     * Process payment
     */
    public function processPayment($studentFeeId, $amount, $paymentMethod, $receivedBy, $remarks = '')
    {
        $this->db->beginTransaction();
        
        try {
            // Get student fee record
            $studentFee = $this->db->selectOne(
                "SELECT * FROM student_fees WHERE id = ?",
                [$studentFeeId]
            );
            
            if (!$studentFee) {
                throw new \Exception('Student fee record not found');
            }
            
            if ($amount > $studentFee['balance_amount']) {
                throw new \Exception('Payment amount exceeds balance');
            }
            
            // Generate payment number
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create payment record
            $this->db->insert('fee_payments', [
                'student_fee_id' => $studentFeeId,
                'payment_number' => $paymentNumber,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_date' => date('Y-m-d H:i:s'),
                'received_by' => $receivedBy,
                'receipt_number' => $receiptNumber,
                'remarks' => $remarks,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update student fee record
            $newPaidAmount = $studentFee['paid_amount'] + $amount;
            $newBalance = $studentFee['net_payable'] - $newPaidAmount;
            
            $newStatus = 'paid';
            if ($newBalance > 0) {
                $newStatus = 'partial';
            }
            
            $this->db->update('student_fees', [
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$studentFeeId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'payment_number' => $paymentNumber,
                'receipt_number' => $receiptNumber,
                'new_balance' => $newBalance,
                'status' => $newStatus,
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get payments by date range
     */
    public function getByDateRange($startDate, $endDate, $branchId = null)
    {
        $sql = "SELECT fp.*, u.first_name, u.last_name, s.student_code,
                       fc.category_name, c.class_name
                FROM fee_payments fp 
                JOIN student_fees sf ON fp.student_fee_id = sf.id 
                JOIN students s ON sf.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                JOIN classes c ON s.current_class_id = c.id
                WHERE DATE(fp.payment_date) BETWEEN ? AND ?";
        
        $params = [$startDate, $endDate];
        
        if ($branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " ORDER BY fp.payment_date DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get daily collection summary
     */
    public function getDailyCollection($branchId = null)
    {
        $sql = "SELECT DATE(payment_date) as date,
                       payment_method,
                       SUM(amount) as total,
                       COUNT(*) as count
                FROM fee_payments fp 
                JOIN student_fees sf ON fp.student_fee_id = sf.id 
                JOIN students s ON sf.student_id = s.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(payment_date), payment_method
                  ORDER BY date DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get collection statistics
     */
    public function getCollectionStats($branchId = null)
    {
        $sql = "SELECT COALESCE(SUM(fp.amount), 0) as total_collected,
                       COUNT(*) as total_transactions,
                       COUNT(DISTINCT sf.student_id) as unique_students
                FROM fee_payments fp 
                JOIN student_fees sf ON fp.student_fee_id = sf.id 
                JOIN students s ON sf.student_id = s.id 
                WHERE MONTH(fp.payment_date) = MONTH(CURDATE()) 
                AND YEAR(fp.payment_date) = YEAR(CURDATE())";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * Generate payment number
     */
    public function generatePaymentNumber()
    {
        return 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
    
    /**
     * Generate receipt number
     */
    public function generateReceiptNumber()
    {
        return 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    // Accessors
    public function appendStudentNameAttribute()
    {
        $firstName = $this->attributes['student_first_name'] ?? '';
        $lastName = $this->attributes['student_last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendCategoryNameAttribute()
    {
        return $this->attributes['category_name'] ?? null;
    }
    
    public function appendPaymentMethodTextAttribute()
    {
        $methods = [
            'cash' => 'نقد',
            'check' => 'چک',
            'bank_transfer' => 'انتقال بانکی',
            'credit_card' => 'کارت اعتباری',
            'online' => 'آنلاین',
            'other' => 'سایر',
        ];
        
        $method = $this->attributes['payment_method'] ?? '';
        return $methods[$method] ?? $method;
    }
}