<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class FinanceController extends Controller
{
    /**
     * Get all fees with filters
     * GET /api/v1/finance/fees
     */
    public function fees()
    {
        $page = $this->request->get('page', 1);
        $perPage = min(100, $this->request->get('per_page', 25));
        $status = $this->request->get('status', '');
        $classId = $this->request->get('class_id', '');
        $studentSearch = $this->request->get('search', '');
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT sf.*, u.first_name, u.last_name, s.student_code,
                       c.class_name, fc.category_name
                FROM student_fees sf 
                JOIN students s ON sf.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                JOIN classes c ON s.current_class_id = c.id 
                JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND s.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($status) {
            $sql .= " AND sf.status = ?";
            $params[] = $status;
        }
        
        if ($classId) {
            $sql .= " AND c.id = ?";
            $params[] = $classId;
        }
        
        if ($studentSearch) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_code LIKE ?)";
            $searchTerm = "%{$studentSearch}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        $countSql = "SELECT COUNT(*) as total FROM student_fees sf JOIN students s ON sf.student_id = s.id JOIN users u ON s.user_id = u.id JOIN classes c ON s.current_class_id = c.id WHERE 1=1";
        // Apply same filters to count
        $total = $this->db->selectOne($countSql, $params)['total'] ?? 0;
        
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY sf.due_date DESC LIMIT {$perPage} OFFSET {$offset}";
        $fees = $this->db->select($sql, $params);
        
        // Summary
        $summary = $this->db->selectOne(
            "SELECT COALESCE(SUM(sf.net_payable),0) as total,
                    COALESCE(SUM(sf.paid_amount),0) as collected,
                    COALESCE(SUM(sf.balance_amount),0) as pending,
                    ROUND((COALESCE(SUM(sf.paid_amount),0) / NULLIF(COALESCE(SUM(sf.net_payable),0),0)) * 100, 2) as collection_rate
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.is_active = 1",
            []
        );
        
        $this->json([
            'success' => true,
            'data' => $fees,
            'summary' => $summary,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * Get single fee detail
     * GET /api/v1/finance/fees/{id}
     */
    public function feeDetail($id)
    {
        $fee = $this->db->selectOne(
            "SELECT sf.*, u.first_name, u.last_name, s.student_code,
                    c.class_name, fc.category_name, fc.frequency,
                    fs.amount as structure_amount, fs.due_day
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             JOIN classes c ON s.current_class_id = c.id 
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             WHERE sf.id = ?",
            [$id]
        );
        
        if (!$fee) {
            $this->json(['success' => false, 'message' => 'رکورد شهریه یافت نشد'], 404);
            return;
        }
        
        // Get payment history
        $payments = $this->db->select(
            "SELECT * FROM fee_payments WHERE student_fee_id = ? ORDER BY payment_date DESC",
            [$id]
        );
        
        $fee['payments'] = $payments;
        
        $this->json(['success' => true, 'data' => $fee]);
    }
    
    /**
     * Process payment
     * POST /api/v1/finance/fees/pay
     */
    public function processPayment()
    {
        $data = $this->request->getJson();
        
        $studentFeeId = $data['student_fee_id'] ?? '';
        $amount = floatval($data['amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $transactionId = $data['transaction_id'] ?? '';
        $remarks = $data['remarks'] ?? '';
        
        if (!$studentFeeId || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'اطلاعات پرداخت نامعتبر است'], 422);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $studentFee = $this->db->selectOne("SELECT * FROM student_fees WHERE id = ?", [$studentFeeId]);
            if (!$studentFee) {
                throw new \Exception('رکورد شهریه یافت نشد');
            }
            
            if ($amount > $studentFee['balance_amount']) {
                throw new \Exception('مبلغ پرداختی بیشتر از مبلغ باقیمانده است');
            }
            
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $this->db->insert('fee_payments', [
                'student_fee_id' => $studentFeeId,
                'payment_number' => $paymentNumber,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'payment_date' => date('Y-m-d H:i:s'),
                'received_by' => $this->session->get('user_id'),
                'receipt_number' => $receiptNumber,
                'remarks' => $remarks,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $newPaid = $studentFee['paid_amount'] + $amount;
            $newBalance = $studentFee['net_payable'] - $newPaid;
            $newStatus = $newBalance <= 0 ? 'paid' : 'partial';
            
            $this->db->update('student_fees', [
                'paid_amount' => $newPaid,
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$studentFeeId]);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت ثبت شد',
                'data' => [
                    'payment_number' => $paymentNumber,
                    'receipt_number' => $receiptNumber,
                    'new_balance' => $newBalance,
                    'status' => $newStatus
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get financial report
     * GET /api/v1/finance/fees/report
     */
    public function feeReport()
    {
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-t'));
        $branchId = $this->session->get('branch_id');
        
        // Daily collection
        $dailyCollection = $this->db->select(
            "SELECT DATE(fp.payment_date) as date,
                    SUM(fp.amount) as total,
                    COUNT(*) as count
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?
             GROUP BY DATE(fp.payment_date)
             ORDER BY date",
            [$branchId, $startDate, $endDate]
        );
        
        // Collection by method
        $byMethod = $this->db->select(
            "SELECT payment_method, SUM(amount) as total, COUNT(*) as count
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?
             GROUP BY payment_method",
            [$branchId, $startDate, $endDate]
        );
        
        // Outstanding by class
        $outstanding = $this->db->select(
            "SELECT c.class_name,
                    COUNT(DISTINCT sf.student_id) as student_count,
                    COALESCE(SUM(sf.balance_amount),0) as total_outstanding
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN classes c ON s.current_class_id = c.id 
             WHERE s.branch_id = ? AND sf.status IN ('pending','partial','overdue')
             GROUP BY c.id
             ORDER BY total_outstanding DESC",
            [$branchId]
        );
        
        // Totals
        $totals = $this->db->selectOne(
            "SELECT COALESCE(SUM(fp.amount),0) as total_collected,
                    COUNT(*) as total_transactions
             FROM fee_payments fp 
             JOIN student_fees sf ON fp.student_fee_id = sf.id 
             JOIN students s ON sf.student_id = s.id 
             WHERE s.branch_id = ? AND DATE(fp.payment_date) BETWEEN ? AND ?",
            [$branchId, $startDate, $endDate]
        );
        
        $this->json([
            'success' => true,
            'data' => [
                'daily_collection' => $dailyCollection,
                'by_method' => $byMethod,
                'outstanding' => $outstanding,
                'totals' => $totals
            ]
        ]);
    }
    
    /**
     * Get expenses
     * GET /api/v1/finance/expenses
     */
    public function expenses()
    {
        $page = $this->request->get('page', 1);
        $perPage = min(100, $this->request->get('per_page', 25));
        $category = $this->request->get('category', '');
        $status = $this->request->get('status', '');
        $dateFrom = $this->request->get('date_from', '');
        $dateTo = $this->request->get('date_to', '');
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT e.*, b.name as branch_name,
                       CONCAT(u.first_name, ' ', u.last_name) as approved_by_name
                FROM expenses e 
                JOIN branches b ON e.branch_id = b.id 
                LEFT JOIN users u ON e.approved_by = u.id 
                WHERE e.branch_id = ?";
        
        $params = [$branchId];
        
        if ($category) {
            $sql .= " AND e.expense_category = ?";
            $params[] = $category;
        }
        
        if ($status) {
            $sql .= " AND e.status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $sql .= " AND e.expense_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND e.expense_date <= ?";
            $params[] = $dateTo;
        }
        
        $countSql = str_replace("SELECT e.*, b.name as branch_name, CONCAT(u.first_name, ' ', u.last_name) as approved_by_name", "SELECT COUNT(*) as total", $sql);
        $total = $this->db->selectOne($countSql, $params)['total'] ?? 0;
        
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY e.expense_date DESC LIMIT {$perPage} OFFSET {$offset}";
        $expenses = $this->db->select($sql, $params);
        
        $this->json([
            'success' => true,
            'data' => $expenses,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * Store expense
     * POST /api/v1/finance/expenses
     */
    public function storeExpense()
    {
        $data = $this->request->getJson();
        
        $errors = [];
        if (empty($data['expense_category'])) $errors['expense_category'] = 'دسته‌بندی الزامی است';
        if (empty($data['description'])) $errors['description'] = 'شرح الزامی است';
        if (empty($data['amount']) || $data['amount'] <= 0) $errors['amount'] = 'مبلغ نامعتبر است';
        if (empty($data['expense_date'])) $errors['expense_date'] = 'تاریخ الزامی است';
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'خطای اعتبارسنجی', 'errors' => $errors], 422);
            return;
        }
        
        $this->db->insert('expenses', [
            'branch_id' => $this->session->get('branch_id'),
            'expense_category' => $data['expense_category'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'approved_by' => $data['approved_by'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'vendor_name' => $data['vendor_name'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->json(['success' => true, 'message' => 'هزینه با موفقیت ثبت شد'], 201);
    }
}