<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class LibraryController extends Controller
{
    /**
     * List books
     * GET /api/v1/library/books
     */
    public function index()
    {
        $page = $this->request->get('page', 1);
        $perPage = min(100, $this->request->get('per_page', 25));
        $search = $this->request->get('search', '');
        $category = $this->request->get('category', '');
        $available = $this->request->get('available', '');
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

        if ($category) {
            $sql .= " AND lb.category = ?";
            $params[] = $category;
        }

        if ($available === '1') {
            $sql .= " AND lb.available_copies > 0";
        }

        $countSql = str_replace(
            "SELECT lb.*, b.name as branch_name, (SELECT COUNT(*) FROM book_borrowings WHERE book_id = lb.id AND status = 'borrowed') as borrowed_count",
            "SELECT COUNT(*) as total",
            $sql
        );
        $total = $this->db->selectOne($countSql, $params)['total'] ?? 0;

        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY lb.title ASC LIMIT {$perPage} OFFSET {$offset}";
        $books = $this->db->select($sql, $params);

        $this->json([
            'success' => true,
            'data' => $books,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Get single book
     * GET /api/v1/library/books/{id}
     */
    public function show($id)
    {
        $book = $this->db->selectOne(
            "SELECT lb.*, b.name as branch_name
             FROM library_books lb JOIN branches b ON lb.branch_id = b.id 
             WHERE lb.id = ?",
            [$id]
        );

        if (!$book) {
            $this->json(['success' => false, 'message' => 'کتاب یافت نشد'], 404);
            return;
        }

        // Get borrowing history
        $book['borrowings'] = $this->db->select(
            "SELECT bb.*, u.first_name, u.last_name
             FROM book_borrowings bb 
             JOIN users u ON bb.user_id = u.id 
             WHERE bb.book_id = ?
             ORDER BY bb.borrow_date DESC LIMIT 20",
            [$id]
        );

        $this->json(['success' => true, 'data' => $book]);
    }

    /**
     * Create new book
     * POST /api/v1/library/books
     */
    public function store()
    {
        $data = $this->request->getJson();

        $errors = [];
        if (empty($data['title'])) $errors['title'] = 'عنوان کتاب الزامی است';
        if (empty($data['author'])) $errors['author'] = 'نویسنده الزامی است';
        if (empty($data['book_code'])) $errors['book_code'] = 'کد کتاب الزامی است';

        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'خطای اعتبارسنجی', 'errors' => $errors], 422);
            return;
        }

        // Check unique book code
        $existing = $this->db->selectOne("SELECT id FROM library_books WHERE book_code = ?", [strtoupper($data['book_code'])]);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'این کد کتاب قبلاً ثبت شده است'], 422);
            return;
        }

        $totalCopies = intval($data['total_copies'] ?? 1);

        $bookId = $this->db->insert('library_books', [
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

        $this->json([
            'success' => true,
            'message' => 'کتاب با موفقیت اضافه شد',
            'data' => ['id' => $bookId]
        ], 201);
    }

    /**
     * Search books
     * GET /api/v1/library/books/search
     */
    public function search()
    {
        $query = $this->request->get('q', '');
        $limit = min(50, $this->request->get('limit', 20));

        if (empty($query)) {
            $this->json(['success' => true, 'data' => []]);
            return;
        }

        $searchTerm = "%{$query}%";
        $books = $this->db->select(
            "SELECT id, book_code, title, author, isbn, available_copies
             FROM library_books 
             WHERE is_active = 1 
             AND (title LIKE ? OR author LIKE ? OR isbn LIKE ? OR book_code LIKE ?)
             ORDER BY title
             LIMIT ?",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]
        );

        $this->json(['success' => true, 'data' => $books]);
    }

    /**
     * Get borrowings
     * GET /api/v1/library/borrowings
     */
    public function borrowings()
    {
        $page = $this->request->get('page', 1);
        $perPage = min(100, $this->request->get('per_page', 25));
        $status = $this->request->get('status', '');
        $userId = $this->request->get('user_id', '');

        $sql = "SELECT bb.*, lb.title as book_title, lb.author, lb.book_code,
                       u.first_name, u.last_name, u.email
                FROM book_borrowings bb 
                JOIN library_books lb ON bb.book_id = lb.id 
                JOIN users u ON bb.user_id = u.id 
                WHERE 1=1";

        $params = [];

        if ($status) {
            $sql .= " AND bb.status = ?";
            $params[] = $status;
        }

        if ($userId) {
            $sql .= " AND bb.user_id = ?";
            $params[] = $userId;
        }

        $countSql = str_replace(
            "SELECT bb.*, lb.title as book_title, lb.author, lb.book_code, u.first_name, u.last_name, u.email",
            "SELECT COUNT(*) as total",
            $sql
        );
        $total = $this->db->selectOne($countSql, $params)['total'] ?? 0;

        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY bb.borrow_date DESC LIMIT {$perPage} OFFSET {$offset}";
        $borrowings = $this->db->select($sql, $params);

        $this->json([
            'success' => true,
            'data' => $borrowings,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Borrow a book
     * POST /api/v1/library/borrow
     */
    public function borrowBook()
    {
        $data = $this->request->getJson();

        $bookId = $data['book_id'] ?? '';
        $userId = $data['user_id'] ?? '';
        $dueDays = intval($data['due_days'] ?? 14);

        if (!$bookId || !$userId) {
            $this->json(['success' => false, 'message' => 'کتاب و کاربر الزامی است'], 422);
            return;
        }

        // Check book availability
        $book = $this->db->selectOne("SELECT * FROM library_books WHERE id = ? AND is_active = 1", [$bookId]);
        if (!$book) {
            $this->json(['success' => false, 'message' => 'کتاب یافت نشد'], 404);
            return;
        }

        if ($book['available_copies'] <= 0) {
            $this->json(['success' => false, 'message' => 'نسخه موجود نیست'], 422);
            return;
        }

        // Check if user already has this book
        $existing = $this->db->selectOne(
            "SELECT id FROM book_borrowings WHERE book_id = ? AND user_id = ? AND status = 'borrowed'",
            [$bookId, $userId]
        );

        if ($existing) {
            $this->json(['success' => false, 'message' => 'این کاربر قبلاً این کتاب را امانت گرفته است'], 422);
            return;
        }

        // Check user's active borrowings count
        $activeCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM book_borrowings WHERE user_id = ? AND status = 'borrowed'",
            [$userId]
        )['count'] ?? 0;

        if ($activeCount >= 5) {
            $this->json(['success' => false, 'message' => 'حداکثر ۵ کتاب می‌توانید امانت بگیرید'], 422);
            return;
        }

        try {
            $this->db->beginTransaction();

            $borrowDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));

            $borrowId = $this->db->insert('book_borrowings', [
                'book_id' => $bookId,
                'user_id' => $userId,
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
                'status' => 'borrowed',
                'issued_by' => $this->session->get('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->query(
                "UPDATE library_books SET available_copies = available_copies - 1 WHERE id = ?",
                [$bookId]
            );

            $this->db->commit();

            $this->json([
                'success' => true,
                'message' => 'کتاب با موفقیت امانت داده شد',
                'data' => ['borrow_id' => $borrowId, 'due_date' => $dueDate]
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Return a book
     * POST /api/v1/library/return
     */
    public function returnBook()
    {
        $data = $this->request->getJson();

        $borrowingId = $data['borrowing_id'] ?? '';
        $returnDate = $data['return_date'] ?? date('Y-m-d');

        if (!$borrowingId) {
            $this->json(['success' => false, 'message' => 'شناسه امانت الزامی است'], 422);
            return;
        }

        $borrowing = $this->db->selectOne("SELECT * FROM book_borrowings WHERE id = ?", [$borrowingId]);
        if (!$borrowing) {
            $this->json(['success' => false, 'message' => 'رکورد امانت یافت نشد'], 404);
            return;
        }

        if ($borrowing['status'] === 'returned') {
            $this->json(['success' => false, 'message' => 'این کتاب قبلاً برگشت داده شده است'], 422);
            return;
        }

        // Calculate fine
        $fineAmount = 0;
        $dueDate = strtotime($borrowing['due_date']);
        $returnTimestamp = strtotime($returnDate);

        if ($returnTimestamp > $dueDate) {
            $daysLate = ceil(($returnTimestamp - $dueDate) / 86400);
            $fineAmount = $daysLate * 1000;
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

            $this->db->query(
                "UPDATE library_books SET available_copies = available_copies + 1 WHERE id = ?",
                [$borrowing['book_id']]
            );

            $this->db->commit();

            $message = 'کتاب با موفقیت برگشت داده شد';
            if ($fineAmount > 0) {
                $message .= '. جریمه دیرکرد: ' . number_format($fineAmount) . ' تومان';
            }

            $this->json([
                'success' => true,
                'message' => $message,
                'data' => ['fine_amount' => $fineAmount]
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'خطا: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get fines
     * GET /api/v1/library/fines
     */
    public function fines()
    {
        $paid = $this->request->get('paid', '');

        $sql = "SELECT bb.*, lb.title as book_title, lb.book_code,
                       u.first_name, u.last_name
                FROM book_borrowings bb 
                JOIN library_books lb ON bb.book_id = lb.id 
                JOIN users u ON bb.user_id = u.id 
                WHERE bb.fine_amount > 0";

        $params = [];

        if ($paid !== '') {
            $sql .= " AND bb.fine_paid = ?";
            $params[] = $paid;
        }

        $sql .= " ORDER BY bb.return_date DESC";

        $fines = $this->db->select($sql, $params);

        $summary = $this->db->selectOne(
            "SELECT COALESCE(SUM(fine_amount),0) as total,
                    COALESCE(SUM(CASE WHEN fine_paid = 1 THEN fine_amount ELSE 0 END),0) as paid,
                    COALESCE(SUM(CASE WHEN fine_paid = 0 THEN fine_amount ELSE 0 END),0) as unpaid
             FROM book_borrowings WHERE fine_amount > 0"
        );

        $this->json([
            'success' => true,
            'data' => ['fines' => $fines, 'summary' => $summary]
        ]);
    }
}