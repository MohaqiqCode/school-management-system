<?php
namespace App\Models;

/**
 * Expense Model
 * مدیریت هزینه‌ها
 */
class Expense extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'expense_category',
        'description',
        'amount',
        'expense_date',
        'approved_by',
        'payment_method',
        'vendor_name',
        'invoice_number',
        'receipt_path',
        'status',
    ];
    
    protected $casts = [
        'amount' => 'float',
        'expense_date' => 'date',
    ];
    
    protected $appends = [
        'branch_name',
        'approved_by_name',
        'category_text',
        'status_text',
    ];
    
    /**
     * Get expenses by branch
     */
    public function getByBranch($branchId, $startDate = null, $endDate = null)
    {
        $sql = "SELECT e.*, b.name as branch_name,
                       CONCAT(u.first_name, ' ', u.last_name) as approved_by_name
                FROM expenses e 
                JOIN branches b ON e.branch_id = b.id 
                LEFT JOIN users u ON e.approved_by = u.id
                WHERE e.branch_id = ?";
        
        $params = [$branchId];
        
        if ($startDate) {
            $sql .= " AND e.expense_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND e.expense_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY e.expense_date DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get expenses by category
     */
    public function getByCategory($branchId, $category, $startDate = null, $endDate = null)
    {
        $sql = "SELECT e.* FROM expenses e 
                WHERE e.branch_id = ? AND e.expense_category = ?";
        
        $params = [$branchId, $category];
        
        if ($startDate) {
            $sql .= " AND e.expense_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND e.expense_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY e.expense_date DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get expense summary
     */
    public function getSummary($branchId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? date('Y-m-01');
        $endDate = $endDate ?? date('Y-m-t');
        
        $sql = "SELECT expense_category,
                       COUNT(*) as count,
                       COALESCE(SUM(amount), 0) as total
                FROM expenses 
                WHERE branch_id = ? AND expense_date BETWEEN ? AND ? AND status = 'paid'
                GROUP BY expense_category
                ORDER BY total DESC";
        
        return $this->db->select($sql, [$branchId, $startDate, $endDate]);
    }
    
    /**
     * Approve expense
     */
    public function approve($expenseId, $approvedBy)
    {
        return $this->db->update('expenses', [
            'status' => 'approved',
            'approved_by' => $approvedBy
        ], 'id = ?', [$expenseId]);
    }
    
    /**
     * Mark as paid
     */
    public function markAsPaid($expenseId)
    {
        return $this->db->update('expenses', [
            'status' => 'paid'
        ], 'id = ?', [$expenseId]);
    }
    
    /**
     * Get monthly total
     */
    public function getMonthlyTotal($branchId, $month, $year)
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM expenses 
                WHERE branch_id = ? 
                AND MONTH(expense_date) = ? AND YEAR(expense_date) = ?
                AND status = 'paid'";
        
        $result = $this->db->selectOne($sql, [$branchId, $month, $year]);
        return (float)($result['total'] ?? 0);
    }
    
    // Accessors
    public function appendBranchNameAttribute()
    {
        return $this->attributes['branch_name'] ?? null;
    }
    
    public function appendApprovedByNameAttribute()
    {
        return $this->attributes['approved_by_name'] ?? null;
    }
    
    public function appendCategoryTextAttribute()
    {
        $categories = [
            'salary' => 'حقوق و دستمزد',
            'utilities' => 'آب و برق و گاز',
            'maintenance' => 'تعمیر و نگهداری',
            'supplies' => 'لوازم و ملزومات',
            'transport' => 'حمل و نقل',
            'events' => 'رویدادها و مراسم',
            'rent' => 'اجاره',
            'insurance' => 'بیمه',
            'tax' => 'مالیات',
            'training' => 'آموزش',
            'other' => 'سایر',
        ];
        
        $category = $this->attributes['expense_category'] ?? '';
        return $categories[$category] ?? $category;
    }
    
    public function appendStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'در انتظار',
            'approved' => 'تأیید شده',
            'paid' => 'پرداخت شده',
            'cancelled' => 'لغو شده',
        ];
        
        $status = $this->attributes['status'] ?? 'pending';
        return $statuses[$status] ?? $status;
    }
}