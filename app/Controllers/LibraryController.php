<?php
namespace App\Controllers;

use App\Core\Controller;

class LibraryController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        $categoryFilter = $_GET['category'] ?? '';
        $branchId = $this->session->get('branch_id');
        
        $sql = "SELECT lb.*, b.name as branch_name,
                       (SELECT COUNT(*) FROM book_borrowings WHERE book_id = lb.id AND status = 'borrowed') as borrowed_count
                FROM library_books lb 
                JOIN branches b ON lb.branch_id = b.id 
                WHERE lb.is_active = 1";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND lb.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($search) {
            $sql .= " AND (lb.title LIKE ? OR lb.author LIKE ? OR lb.isbn LIKE ? OR lb.book_code LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($categoryFilter) {
            $sql .= " AND lb.category = ?";
            $params[] = $categoryFilter;
        }
        
        $countSql = str_replace("SELECT lb.*, b.name as branch_name, (SELECT COUNT(*) FROM book_borrowings WHERE book_id = lb.id AND status = 'borrowed') as borrowed_count", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY lb.title ASC LIMIT {$perPage} OFFSET {$offset}";
        $books = $this->db->select($sql, $params);
        
        // Get categories for filter
        $categories = $this->db->select("SELECT DISTINCT category FROM library_books WHERE category IS NOT NULL AND category != '' ORDER BY category");
        
        $this->view('library/index', [
            'title' => 'مدیریت کتابخانه',
            'books' => $books,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search,
            'categoryFilter' => $categoryFilter
        ]);
    }
    
    public function create()
    {
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('library/create', [
            'title' => 'افزودن کتاب جدید',
            'branches' => $branches
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'عنوان کتاب الزامی است';
        }
        
        if (empty($data['book_code'])) {
            $errors['book_code'] = 'کد کتاب الزامی است';
        }
        
        if (empty($data['author'])) {
            $errors['author'] = 'نام نویسنده الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/library/books/add');
            return;
        }
        
        // Check unique book code
        $existingBook = $this->db->selectOne(
            "SELECT id FROM library_books WHERE book_code = ?",
            [strtoupper($data['book_code'])]
        );
        
        if ($existingBook) {
            $this->session->flash('errors', ['book_code' => 'این کد کتاب قبلاً ثبت شده است']);
            $this->session->flash('old', $data);
            $this->redirect('/library/books/add');
            return;
        }
        
        $totalCopies = intval($data['total_copies'] ?? 1);
        
        $this->db->insert('library_books', [
            'branch_id' => $data['branch_id'] ?? $this->session->get('branch_id'),
            'isbn' => $data['isbn'] ?? null,
            'book_code' => strtoupper($data['book_code']),
            'title' => $data['title'],
            'author' => $data['author'],
            'publisher' => $data['publisher'] ?? null,
            'edition' => $data['edition'] ?? null,
            'category' => $data['category'] ?? null,
            'subject' => $data['subject'] ?? null,
            'publication_year' => $data['publication_year'] ?? null,
            'total_copies' => $totalCopies,
            'available_copies' => $totalCopies,
            'shelf_location' => $data['shelf_location'] ?? null,
            'purchase_date' => $data['purchase_date'] ?? date('Y-m-d'),
            'purchase_price' => $data['purchase_price'] ?? null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('library', 'create', "کتاب '{$data['title']}' به کتابخانه اضافه شد");
        
        $this->session->flash('success', 'کتاب با موفقیت اضافه شد');
        $this->redirect('/library/books');
    }
    
    public function edit($id)
    {
        $book = $this->db->selectOne("SELECT * FROM library_books WHERE id = ?", [$id]);
        
        if (!$book) {
            $this->session->flash('error', 'کتاب مورد نظر یافت نشد');
            $this->redirect('/library/books');
            return;
        }
        
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name");
        
        $this->view('library/edit', [
            'title' => 'ویرایش کتاب',
            'book' => $book,
            'branches' => $branches
        ]);
    }
    
    public function update($id)
    {
        $book = $this->db->selectOne("SELECT * FROM library_books WHERE id = ?", [$id]);
        
        if (!$book) {
            $this->session->flash('error', 'کتاب یافت نشد');
            $this->redirect('/library/books');
            return;
        }
        
        $data = $_POST;
        
        $this->db->update('library_books', [
            'isbn' => $data['isbn'] ?? null,
            'title' => $data['title'],
            'author' => $data['author'],
            'publisher' => $data['publisher'] ?? null,
            'edition' => $data['edition'] ?? null,
            'category' => $data['category'] ?? null,
            'subject' => $data['subject'] ?? null,
            'publication_year' => $data['publication_year'] ?? null,
            'shelf_location' => $data['shelf_location'] ?? null,
            'purchase_price' => $data['purchase_price'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0
        ], 'id = ?', [$id]);
        
        // Update copies if changed
        $newTotal = intval($data['total_copies'] ?? $book['total_copies']);
        if ($newTotal != $book['total_copies']) {
            $borrowedCount = $book['total_copies'] - $book['available_copies'];
            $newAvailable = max(0, $newTotal - $borrowedCount);
            
            $this->db->update('library_books', [
                'total_copies' => $newTotal,
                'available_copies' => $newAvailable
            ], 'id = ?', [$id]);
        }
        
        $this->logActivity('library', 'update', "کتاب '{$data['title']}' به‌روزرسانی شد");
        
        $this->session->flash('success', 'اطلاعات کتاب به‌روزرسانی شد');
        $this->redirect('/library/books');
    }
    
    public function delete($id)
    {
        $book = $this->db->selectOne("SELECT * FROM library_books WHERE id = ?", [$id]);
        
        if (!$book) {
            $this->json(['success' => false, 'message' => 'کتاب یافت نشد'], 404);
            return;
        }
        
        // Check if book is borrowed
        $borrowedCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM book_borrowings WHERE book_id = ? AND status = 'borrowed'",
            [$id]
        )['count'];
        
        if ($borrowedCount > 0) {
            $this->json(['success' => false, 'message' => 'این کتاب در حال حاضر امانت داده شده است و قابل حذف نیست']);
            return;
        }
        
        $this->db->update('library_books', ['is_active' => 0], 'id = ?', [$id]);
        
        $this->logActivity('library', 'delete', "کتاب '{$book['title']}' حذف شد");
        
        $this->json(['success' => true, 'message' => 'کتاب حذف شد']);
    }
    
    public function issueBook()
    {
        $data = $_POST;
        
        $bookId = $data['book_id'] ?? '';
        $userId = $data['user_id'] ?? '';
        $borrowDate = $data['borrow_date'] ?? date('Y-m-d');
        $dueDays = intval($data['due_days'] ?? 14);
        
        if (!$bookId || !$userId) {
            $this->json(['success' => false, 'message' => 'کتاب و کاربر الزامی است']);
            return;
        }
        
        // Check book availability
        $book = $this->db->selectOne("SELECT * FROM library_books WHERE id = ? AND is_active = 1", [$bookId]);
        
        if (!$book) {
            $this->json(['success' => false, 'message' => 'کتاب یافت نشد']);
            return;
        }
        
        if ($book['available_copies'] <= 0) {
            $this->json(['success' => false, 'message' => 'نسخه موجود نیست']);
            return;
        }
        
        // Check if user already has this book
        $existingBorrow = $this->db->selectOne(
            "SELECT id FROM book_borrowings WHERE book_id = ? AND user_id = ? AND status = 'borrowed'",
            [$bookId, $userId]
        );
        
        if ($existingBorrow) {
            $this->json(['success' => false, 'message' => 'این کاربر قبلاً این کتاب را امانت گرفته است']);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $dueDate = date('Y-m-d', strtotime($borrowDate . ' + ' . $dueDays . ' days'));
            
            $this->db->insert('book_borrowings', [
                'book_id' => $bookId,
                'user_id' => $userId,
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
                'status' => 'borrowed',
                'issued_by' => $this->session->get('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update available copies
            $this->db->update('library_books', [
                'available_copies' => $book['available_copies'] - 1
            ], 'id = ?', [$bookId]);
            
            $this->db->commit();
            
            $this->logActivity('library', 'issue', "کتاب '{$book['title']}' به کاربر {$userId} امانت داده شد");
            
            $this->json(['success' => true, 'message' => 'کتاب با موفقیت امانت داده شد', 'due_date' => $dueDate]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
    }
    
    public function returnBook()
    {
        $data = $_POST;
        
        $borrowingId = $data['borrowing_id'] ?? '';
        $returnDate = $data['return_date'] ?? date('Y-m-d');
        
        if (!$borrowingId) {
            $this->json(['success' => false, 'message' => 'شناسه امانت الزامی است']);
            return;
        }
        
        $borrowing = $this->db->selectOne("SELECT * FROM book_borrowings WHERE id = ?", [$borrowingId]);
        
        if (!$borrowing) {
            $this->json(['success' => false, 'message' => 'رکورد امانت یافت نشد']);
            return;
        }
        
        if ($borrowing['status'] === 'returned') {
            $this->json(['success' => false, 'message' => 'این کتاب قبلاً برگشت داده شده است']);
            return;
        }
        
        // Calculate fine
        $fineAmount = 0;
        $dueDate = strtotime($borrowing['due_date']);
        $returnDateTs = strtotime($returnDate);
        
        if ($returnDateTs > $dueDate) {
            $daysLate = ceil(($returnDateTs - $dueDate) / (60 * 60 * 24));
            $fineAmount = $daysLate * 1000; // 1000 units per day late
        }
        
        try {
            $this->db->beginTransaction();
            
            $status = $fineAmount > 0 ? 'overdue' : 'returned';
            
            $this->db->update('book_borrowings', [
                'return_date' => $returnDate,
                'fine_amount' => $fineAmount,
                'fine_paid' => $fineAmount == 0 ? 1 : 0,
                'status' => $status
            ], 'id = ?', [$borrowingId]);
            
            // Update available copies
            $book = $this->db->selectOne("SELECT * FROM library_books WHERE id = ?", [$borrowing['book_id']]);
            $this->db->update('library_books', [
                'available_copies' => $book['available_copies'] + 1
            ], 'id = ?', [$borrowing['book_id']]);
            
            $this->db->commit();
            
            $message = 'کتاب با موفقیت برگشت داده شد';
            if ($fineAmount > 0) {
                $message .= '. جریمه دیرکرد: ' . number_format($fineAmount) . ' تومان';
            }
            
            $this->logActivity('library', 'return', "کتاب {$borrowing['book_id']} برگشت داده شد");
            
            $this->json(['success' => true, 'message' => $message, 'fine_amount' => $fineAmount]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
    }
    
    public function borrowings()
    {
        $statusFilter = $_GET['status'] ?? '';
        $userIdFilter = $_GET['user_id'] ?? '';
        
        $sql = "SELECT bb.*, lb.title as book_title, lb.author, lb.book_code,
                       u.first_name, u.last_name, u.email
                FROM book_borrowings bb 
                JOIN library_books lb ON bb.book_id = lb.id 
                JOIN users u ON bb.user_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($statusFilter) {
            $sql .= " AND bb.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($userIdFilter) {
            $sql .= " AND bb.user_id = ?";
            $params[] = $userIdFilter;
        }
        
        $sql .= " ORDER BY bb.borrow_date DESC LIMIT 100";
        $borrowings = $this->db->select($sql, $params);
        
        $this->view('library/borrowings', [
            'title' => 'امانت‌های کتاب',
            'borrowings' => $borrowings,
            'statusFilter' => $statusFilter,
            'userIdFilter' => $userIdFilter
        ]);
    }
    
    public function searchUsers($query = '')
    {
        $users = $this->db->select(
            "SELECT id, first_name, last_name, email, user_code 
             FROM users 
             WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR user_code LIKE ?) 
             AND is_active = 1 AND deleted_at IS NULL 
             LIMIT 20",
            ["%{$query}%", "%{$query}%", "%{$query}%", "%{$query}%"]
        );
        
        $this->json(['success' => true, 'data' => $users]);
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