<?php
namespace App\Models;

/**
 * Book Borrowing Model
 * مدیریت امانت کتاب
 */
class BookBorrowing extends Model
{
    protected $table = 'book_borrowings';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'book_id',
        'user_id',
        'borrow_date',
        'due_date',
        'return_date',
        'fine_amount',
        'fine_paid',
        'status',
        'remarks',
        'issued_by',
    ];
    
    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'float',
        'fine_paid' => 'bool',
    ];
    
    protected $appends = [
        'book_title',
        'user_name',
        'status_text',
        'is_overdue',
        'days_overdue',
    ];
    
    /**
     * Borrow a book
     */
    public function borrowBook($bookId, $userId, $dueDays = 14, $issuedBy = null)
    {
        // Check availability
        $book = $this->db->selectOne(
            "SELECT * FROM library_books WHERE id = ? AND available_copies > 0",
            [$bookId]
        );
        
        if (!$book) return ['success' => false, 'message' => 'کتاب موجود نیست'];
        
        // Check if user already has this book
        $existing = $this->db->selectOne(
            "SELECT id FROM book_borrowings WHERE book_id = ? AND user_id = ? AND status = 'borrowed'",
            [$bookId, $userId]
        );
        
        if ($existing) return ['success' => false, 'message' => 'این کتاب قبلاً توسط شما امانت گرفته شده است'];
        
        // Check user's active borrowings count
        $activeCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM book_borrowings WHERE user_id = ? AND status = 'borrowed'",
            [$userId]
        );
        
        if ($activeCount['count'] >= 5) {
            return ['success' => false, 'message' => 'شما 5 کتاب امانت گرفته‌اید. امکان امانت بیشتر وجود ندارد'];
        }
        
        $this->db->beginTransaction();
        
        try {
            $borrowDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));
            
            $borrowId = $this->db->insert('book_borrowings', [
                'book_id' => $bookId,
                'user_id' => $userId,
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
                'status' => 'borrowed',
                'remarks' => $remarks ?? '',
                'issued_by' => $issuedBy,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update available copies
            $this->db->query(
                "UPDATE library_books SET available_copies = available_copies - 1 WHERE id = ?",
                [$bookId]
            );
            
            $this->db->commit();
            
            return [
                'success' => true,
                'borrow_id' => $borrowId,
                'due_date' => $dueDate
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'خطا در ثبت امانت'];
        }
    }
    
    /**
     * Return a book
     */
    public function returnBook($borrowingId, $returnDate = null)
    {
        $returnDate = $returnDate ?? date('Y-m-d');
        
        $borrowing = $this->db->selectOne(
            "SELECT * FROM book_borrowings WHERE id = ?",
            [$borrowingId]
        );
        
        if (!$borrowing) return ['success' => false, 'message' => 'رکورد امانت یافت نشد'];
        
        if ($borrowing['status'] === 'returned') {
            return ['success' => false, 'message' => 'این کتاب قبلاً برگشت داده شده است'];
        }
        
        // Calculate fine
        $fineAmount = 0;
        $dueDate = strtotime($borrowing['due_date']);
        $returnTimestamp = strtotime($returnDate);
        
        if ($returnTimestamp > $dueDate) {
            $daysLate = ceil(($returnTimestamp - $dueDate) / 86400);
            $fineAmount = $daysLate * 1000; // 1000 units per day
        }
        
        $this->db->beginTransaction();
        
        try {
            $status = $fineAmount > 0 ? 'overdue' : 'returned';
            
            $this->db->update('book_borrowings', [
                'return_date' => $returnDate,
                'fine_amount' => $fineAmount,
                'fine_paid' => $fineAmount == 0 ? 1 : 0,
                'status' => $status
            ], 'id = ?', [$borrowingId]);
            
            // Update available copies
            $this->db->query(
                "UPDATE library_books SET available_copies = available_copies + 1 WHERE id = ?",
                [$borrowing['book_id']]
            );
            
            $this->db->commit();
            
            return [
                'success' => true,
                'fine_amount' => $fineAmount,
                'message' => $fineAmount > 0 ? 'کتاب با جریمه برگشت داده شد' : 'کتاب با موفقیت برگشت داده شد'
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'خطا در ثبت برگشت کتاب'];
        }
    }
    
    /**
     * Pay fine
     */
    public function payFine($borrowingId)
    {
        return $this->db->update('book_borrowings', [
            'fine_paid' => 1
        ], 'id = ?', [$borrowingId]);
    }
    
    /**
     * Get active borrowings for user
     */
    public function getActiveByUser($userId)
    {
        $sql = "SELECT bb.*, lb.title as book_title, lb.author, lb.book_code
                FROM book_borrowings bb 
                JOIN library_books lb ON bb.book_id = lb.id 
                WHERE bb.user_id = ? AND bb.status IN ('borrowed', 'overdue')
                ORDER BY bb.due_date";
        
        return $this->db->select($sql, [$userId]);
    }
    
    /**
     * Get borrowing history for user
     */
    public function getHistoryByUser($userId, $limit = 20)
    {
        $sql = "SELECT bb.*, lb.title as book_title, lb.author
                FROM book_borrowings bb 
                JOIN library_books lb ON bb.book_id = lb.id 
                WHERE bb.user_id = ?
                ORDER BY bb.borrow_date DESC
                LIMIT ?";
        
        return $this->db->select($sql, [$userId, $limit]);
    }
    
    /**
     * Get overdue borrowings
     */
    public function getOverdue()
    {
        $sql = "SELECT bb.*, lb.title as book_title, lb.book_code,
                       u.first_name, u.last_name, u.email
                FROM book_borrowings bb 
                JOIN library_books lb ON bb.book_id = lb.id 
                JOIN users u ON bb.user_id = u.id 
                WHERE (bb.status = 'overdue' OR (bb.status = 'borrowed' AND bb.due_date < CURDATE()))
                ORDER BY bb.due_date";
        
        return $this->db->select($sql);
    }
    
    /**
     * Get borrowing statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT COUNT(*) as total_borrowings,
                       COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as active,
                       COUNT(CASE WHEN status = 'overdue' OR (status = 'borrowed' AND due_date < CURDATE()) THEN 1 END) as overdue,
                       COALESCE(SUM(fine_amount), 0) as total_fines,
                       COALESCE(SUM(CASE WHEN fine_paid = 0 THEN fine_amount ELSE 0 END), 0) as unpaid_fines
                FROM book_borrowings";
        
        return $this->db->selectOne($sql);
    }
    
    // Accessors
    public function appendBookTitleAttribute()
    {
        return $this->attributes['book_title'] ?? null;
    }
    
    public function appendUserNameAttribute()
    {
        $firstName = $this->attributes['first_name'] ?? '';
        $lastName = $this->attributes['last_name'] ?? '';
        return trim($firstName . ' ' . $lastName);
    }
    
    public function appendStatusTextAttribute()
    {
        $statuses = [
            'borrowed' => 'امانت گرفته شده',
            'returned' => 'برگشت داده شده',
            'overdue' => 'دیرکرد',
            'lost' => 'گم شده',
        ];
        
        $status = $this->attributes['status'] ?? '';
        return $statuses[$status] ?? $status;
    }
    
    public function appendIsOverdueAttribute()
    {
        $status = $this->attributes['status'] ?? '';
        $dueDate = $this->attributes['due_date'] ?? null;
        
        if ($status === 'returned') return false;
        if ($status === 'overdue') return true;
        
        return $dueDate && strtotime($dueDate) < time();
    }
    
    public function appendDaysOverdueAttribute()
    {
        if (!$this->is_overdue) return 0;
        
        $dueDate = strtotime($this->attributes['due_date']);
        return max(0, ceil((time() - $dueDate) / 86400));
    }
}