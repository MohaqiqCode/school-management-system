<?php
namespace App\Models;

/**
 * Library Book Model
 * مدیریت کتاب‌های کتابخانه
 */
class LibraryBook extends Model
{
    protected $table = 'library_books';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'isbn',
        'book_code',
        'title',
        'author',
        'publisher',
        'edition',
        'category',
        'subject',
        'publication_year',
        'total_copies',
        'available_copies',
        'shelf_location',
        'purchase_date',
        'purchase_price',
        'is_active',
    ];
    
    protected $casts = [
        'total_copies' => 'int',
        'available_copies' => 'int',
        'publication_year' => 'int',
        'purchase_price' => 'float',
        'purchase_date' => 'date',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'borrowed_count',
        'is_available',
    ];
    
    /**
     * Search books
     */
    public function search($query, $limit = 20)
    {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT * FROM library_books 
                WHERE is_active = 1 
                AND (title LIKE ? OR author LIKE ? OR isbn LIKE ? OR book_code LIKE ? OR category LIKE ?)
                ORDER BY title
                LIMIT ?";
        
        return $this->hydrate(
            $this->db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit])
        );
    }
    
    /**
     * Get books by category
     */
    public function getByCategory($category)
    {
        $sql = "SELECT * FROM library_books WHERE category = ? AND is_active = 1 ORDER BY title";
        return $this->hydrate($this->db->select($sql, [$category]));
    }
    
    /**
     * Get all categories
     */
    public function getCategories()
    {
        $sql = "SELECT DISTINCT category FROM library_books WHERE category IS NOT NULL AND category != '' ORDER BY category";
        return $this->db->selectColumn($sql);
    }
    
    /**
     * Get popular books
     */
    public function getPopular($limit = 10)
    {
        $sql = "SELECT lb.*, COUNT(bb.id) as borrow_count
                FROM library_books lb 
                LEFT JOIN book_borrowings bb ON lb.id = bb.book_id 
                WHERE lb.is_active = 1
                GROUP BY lb.id
                ORDER BY borrow_count DESC
                LIMIT ?";
        
        return $this->hydrate($this->db->select($sql, [$limit]));
    }
    
    /**
     * Get available books
     */
    public function getAvailable()
    {
        $sql = "SELECT * FROM library_books WHERE available_copies > 0 AND is_active = 1 ORDER BY title";
        return $this->hydrate($this->db->select($sql));
    }
    
    /**
     * Check if book is available
     */
    public function isAvailable($bookId)
    {
        $book = $this->find($bookId);
        return $book && $book->available_copies > 0;
    }
    
    /**
     * Update copies after borrow/return
     */
    public function updateCopies($bookId, $change)
    {
        return $this->db->query(
            "UPDATE library_books SET available_copies = available_copies + ? WHERE id = ?",
            [$change, $bookId]
        );
    }
    
    /**
     * Get book by ISBN
     */
    public function findByIsbn($isbn)
    {
        $sql = "SELECT * FROM library_books WHERE isbn = ?";
        $result = $this->db->selectOne($sql, [$isbn]);
        
        if ($result) {
            $this->fill($result);
            return $this;
        }
        
        return null;
    }
    
    // Accessors
    public function appendBorrowedCountAttribute()
    {
        $total = $this->attributes['total_copies'] ?? 0;
        $available = $this->attributes['available_copies'] ?? 0;
        return max(0, $total - $available);
    }
    
    public function appendIsAvailableAttribute()
    {
        return ($this->attributes['available_copies'] ?? 0) > 0;
    }
}