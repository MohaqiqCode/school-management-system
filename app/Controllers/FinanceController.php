<?php
namespace App\Controllers;

use App\Core\Controller;

class FinanceController extends Controller
{
    public function index()
    {
        $status = $_GET['status'] ?? '';
        $classId = $_GET['class_id'] ?? '';
        $studentSearch = $_GET['search'] ?? '';
        
        $query = "SELECT sf.*, 
                         u.first_name, u.last_name, u.profile_photo,
                         s.student_code, c.class_name,
                         fc.category_name, fc.frequency,
                         fs.amount as structure_amount
                  FROM student_fees sf 
                  JOIN students s ON sf.student_id = s.id 
                  JOIN users u ON s.user_id = u.id 
                  JOIN classes c ON s.current_class_id = c.id 
                  JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                  JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                  WHERE 1=1";
        
        $params = [];
        
        if ($status) {
            $query .= " AND sf.status = ?";
            $params[] = $status;
        }
        
        if ($classId) {
            $query .= " AND c.id = ?";
            $params[] = $classId;
        }
        
        if ($studentSearch) {
            $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_code LIKE ?)";
            $searchTerm = "%{$studentSearch}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        $query .= " ORDER BY sf.due_date DESC";
        
        $fees = $this->db->select($query, $params);
        
        // Summary statistics
        $summary = $this->db->selectOne(
            "SELECT 
                COUNT(*) as total_records,
                COALESCE(SUM(sf.net_payable), 0) as total_amount,
                COALESCE(SUM(sf.paid_amount), 0) as collected_amount,
                COALESCE(SUM(sf.balance_amount), 0) as pending_amount,
                COUNT(CASE WHEN sf.status = 'overdue' THEN 1 END) as overdue_count,
                COUNT(CASE WHEN sf.status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN sf.status = 'pending' THEN 1 END) as pending_count,
                ROUND((COALESCE(SUM(sf.paid_amount), 0) / NULLIF(COALESCE(SUM(sf.net_payable), 0), 0)) * 100, 2) as collection_rate
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             WHERE s.is_active = 1"
        );
        
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE is_active = 1 ORDER BY class_level");
        
        $this->view('finance/index', [
            'title' => 'مدیریت مالی',
            'fees' => $fees,
            'summary' => $summary,
            'classes' => $classes,
            'selectedStatus' => $status,
            'selectedClass' => $classId,
            'search' => $studentSearch
        ]);
    }
    
    public function structures()
    {
        $structures = $this->db->select(
            "SELECT fs.*, fc.category_name, c.class_name, ay.year_name
             FROM fee_structures fs 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             JOIN classes c ON fs.class_id = c.id 
             JOIN academic_years ay ON fs.academic_year_id = ay.id 
             ORDER BY c.class_level, fc.category_name"
        );
        
        $this->view('finance/structures', [
            'title' => 'ساختار شهریه',
            'structures' => $structures
        ]);
    }
    
    public function storeStructure()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['fee_category_id'])) {
            $errors['fee_category_id'] = 'دسته‌بندی شهریه الزامی است';
        }
        
        if (empty($data['class_id'])) {
            $errors['class_id'] = 'کلاس الزامی است';
        }
        
        if (empty($data['academic_year_id'])) {
            $errors['academic_year_id'] = 'سال تحصیلی الزامی است';
        }
        
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'مبلغ شهریه باید بزرگتر از صفر باشد';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect('/fees/structures');
            return;
        }
        
        // Check for duplicate
        $existing = $this->db->selectOne(
            "SELECT id FROM fee_structures WHERE fee_category_id = ? AND class_id = ? AND academic_year_id = ?",
            [$data['fee_category_id'], $data['class_id'], $data['academic_year_id']]
        );
        
        if ($existing) {
            $this->session->flash('error', 'این ساختار شهریه قبلاً تعریف شده است');
            $this->redirect('/fees/structures');
            return;
        }
        
        $this->db->insert('fee_structures', [
            'fee_category_id' => $data['fee_category_id'],
            'class_id' => $data['class_id'],
            'academic_year_id' => $data['academic_year_id'],
            'amount' => $data['amount'],
            'late_fee_amount' => $data['late_fee_amount'] ?? 0,
            'discount_allowed' => $data['discount_allowed'] ?? 0,
            'due_day' => $data['due_day'] ?? 10,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('finance', 'create', "ساختار شهریه جدید ایجاد شد");
        
        $this->session->flash('success', 'ساختار شهریه با موفقیت ایجاد شد');
        $this->redirect('/fees/structures');
    }
    
    public function collect()
    {
        $studentId = $_GET['student_id'] ?? '';
        
        if ($studentId) {
            $pendingFees = $this->db->select(
                "SELECT sf.*, fc.category_name, c.class_name
                 FROM student_fees sf 
                 JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                 JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                 JOIN students s ON sf.student_id = s.id 
                 JOIN classes c ON s.current_class_id = c.id 
                 WHERE sf.student_id = ? AND sf.status IN ('pending', 'partial', 'overdue')
                 ORDER BY sf.due_date",
                [$studentId]
            );
            
            $student = $this->db->selectOne(
                "SELECT s.*, u.first_name, u.last_name, u.profile_photo
                 FROM students s 
                 JOIN users u ON s.user_id = u.id 
                 WHERE s.id = ?",
                [$studentId]
            );
        }
        
        $this->view('finance/collect', [
            'title' => 'دریافت شهریه',
            'pendingFees' => $pendingFees ?? [],
            'student' => $student ?? null,
            'searchStudent' => $studentId
        ]);
    }
    
    public function processPayment()
    {
        $data = $_POST;
        
        $studentFeeId = $data['student_fee_id'] ?? '';
        $amount = floatval($data['amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $transactionId = $data['transaction_id'] ?? '';
        $remarks = $data['remarks'] ?? '';
        
        if (!$studentFeeId || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'اطلاعات پرداخت نامعتبر است']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get student fee record
            $studentFee = $this->db->selectOne("SELECT * FROM student_fees WHERE id = ?", [$studentFeeId]);
            
            if (!$studentFee) {
                throw new \Exception('رکورد شهریه یافت نشد');
            }
            
            if ($amount > $studentFee['balance_amount']) {
                throw new \Exception('مبلغ پرداختی بیشتر از مبلغ باقیمانده است');
            }
            
            // Generate payment number
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Create payment record
            $this->db->insert('fee_payments', [
                'student_fee_id' => $studentFeeId,
                'payment_number' => $paymentNumber,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'payment_date' => date('Y-m-d H:i:s'),
                'received_by' => $this->session->get('user_id'),
                'receipt_number' => 'RCP-' . date('Ymd') . '-' . rand(1000, 9999),
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
            
            $this->logActivity('finance', 'payment', "پرداخت شهریه به مبلغ {$amount} با شماره {$paymentNumber} ثبت شد");
            
            $this->json([
                'success' => true, 
                'message' => 'پرداخت با موفقیت ثبت شد',
                'receipt_number' => $paymentNumber,
                'payment_number' => $paymentNumber
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا در ثبت پرداخت: ' . $e->getMessage()]);
        }
    }
    
    public function expenses()
    {
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        $query = "SELECT e.*, u.first_name as approved_by_name, u.last_name as approved_by_last,
                         b.name as branch_name
                  FROM expenses e 
                  LEFT JOIN users u ON e.approved_by = u.id 
                  JOIN branches b ON e.branch_id = b.id 
                  WHERE 1=1";
        
        $params = [];
        
        if ($category) {
            $query .= " AND e.expense_category = ?";
            $params[] = $category;
        }
        
        if ($status) {
            $query .= " AND e.status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $query .= " AND e.expense_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $query .= " AND e.expense_date <= ?";
            $params[] = $dateTo;
        }
        
        $query .= " ORDER BY e.expense_date DESC";
        
        $expenses = $this->db->select($query, $params);
        
        // Summary
        $summary = $this->db->selectOne(
            "SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count
             FROM expenses 
             WHERE 1=1" . 
             ($category ? " AND expense_category = '{$category}'" : "") .
             ($dateFrom ? " AND expense_date >= '{$dateFrom}'" : "") .
             ($dateTo ? " AND expense_date <= '{$dateTo}'" : "")
        );
        
        $this->view('finance/expenses', [
            'title' => 'مدیریت هزینه‌ها',
            'expenses' => $expenses,
            'summary' => $summary,
            'selectedCategory' => $category,
            'selectedStatus' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
    }
    
    public function storeExpense()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['expense_category'])) {
            $errors['expense_category'] = 'دسته‌بندی هزینه الزامی است';
        }
        
        if (empty($data['description'])) {
            $errors['description'] = 'شرح هزینه الزامی است';
        }
        
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'مبلغ هزینه باید بزرگتر از صفر باشد';
        }
        
        if (empty($data['expense_date'])) {
            $errors['expense_date'] = 'تاریخ هزینه الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect('/expenses');
            return;
        }
        
        // Handle receipt upload
        $receiptPath = null;
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $receiptPath = $this->uploadReceipt($_FILES['receipt']);
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
            'receipt_path' => $receiptPath,
            'status' => $data['status'] ?? 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('finance', 'expense', "هزینه جدید به مبلغ {$data['amount']} ثبت شد");
        
        $this->session->flash('success', 'هزینه با موفقیت ثبت شد');
        $this->redirect('/expenses');
    }
    
    public function financialReport()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        // Revenue
        $revenue = $this->db->select(
            "SELECT DATE(payment_date) as date, 
                    payment_method,
                    SUM(amount) as total,
                    COUNT(*) as transaction_count
             FROM fee_payments 
             WHERE DATE(payment_date) BETWEEN ? AND ?
             GROUP BY DATE(payment_date), payment_method
             ORDER BY date",
            [$startDate, $endDate]
        );
        
        // Expenses
        $expenses = $this->db->select(
            "SELECT expense_category, 
                    SUM(amount) as total,
                    COUNT(*) as count
             FROM expenses 
             WHERE expense_date BETWEEN ? AND ? AND status = 'paid'
             GROUP BY expense_category
             ORDER BY total DESC",
            [$startDate, $endDate]
        );
        
        // Summary
        $summary = [
            'total_revenue' => array_sum(array_column($revenue, 'total')),
            'total_expenses' => array_sum(array_column($expenses, 'total')),
            'net_income' => array_sum(array_column($revenue, 'total')) - array_sum(array_column($expenses, 'total'))
        ];
        
        $this->view('finance/report', [
            'title' => 'گزارش مالی',
            'revenue' => $revenue,
            'expenses' => $expenses,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    private function uploadReceipt($file)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/receipts/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception('فرمت فایل مجاز نیست');
        }
        
        $filename = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/assets/uploads/receipts/' . $filename;
        }
        
        throw new \Exception('خطا در آپلود فایل');
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