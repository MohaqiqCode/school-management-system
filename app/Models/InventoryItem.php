<?php
namespace App\Models;

/**
 * Inventory Item Model
 * مدیریت اقلام انبار
 */
class InventoryItem extends Model
{
    protected $table = 'inventory_items';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'category_id',
        'branch_id',
        'item_code',
        'item_name',
        'description',
        'unit',
        'quantity_in_stock',
        'reorder_level',
        'unit_price',
        'supplier_name',
        'location',
        'is_active',
    ];
    
    protected $casts = [
        'quantity_in_stock' => 'int',
        'reorder_level' => 'int',
        'unit_price' => 'float',
        'is_active' => 'bool',
    ];
    
    protected $appends = [
        'category_name',
        'stock_value',
        'stock_status',
        'is_low_stock',
    ];
    
    /**
     * Get items with details
     */
    public function getAllWithDetails($filters = [])
    {
        $sql = "SELECT ii.*, ic.category_name, b.name as branch_name
                FROM inventory_items ii 
                JOIN inventory_categories ic ON ii.category_id = ic.id 
                JOIN branches b ON ii.branch_id = b.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND ii.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['branch_id'])) {
            $sql .= " AND ii.branch_id = ?";
            $params[] = $filters['branch_id'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND ii.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY ic.category_name, ii.item_name";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get low stock items
     */
    public function getLowStock($branchId = null)
    {
        $sql = "SELECT ii.*, ic.category_name
                FROM inventory_items ii 
                JOIN inventory_categories ic ON ii.category_id = ic.id 
                WHERE ii.quantity_in_stock <= ii.reorder_level AND ii.is_active = 1";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND ii.branch_id = ?";
            $params[] = $branchId;
        }
        
        $sql .= " ORDER BY ii.quantity_in_stock ASC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get out of stock items
     */
    public function getOutOfStock($branchId = null)
    {
        $sql = "SELECT ii.*, ic.category_name
                FROM inventory_items ii 
                JOIN inventory_categories ic ON ii.category_id = ic.id 
                WHERE ii.quantity_in_stock = 0 AND ii.is_active = 1";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND ii.branch_id = ?";
            $params[] = $branchId;
        }
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Add stock (stock in)
     */
    public function addStock($itemId, $quantity)
    {
        if ($quantity <= 0) return false;
        
        return $this->db->query(
            "UPDATE inventory_items SET quantity_in_stock = quantity_in_stock + ?, updated_at = NOW() WHERE id = ?",
            [$quantity, $itemId]
        );
    }
    
    /**
     * Remove stock (stock out)
     */
    public function removeStock($itemId, $quantity)
    {
        if ($quantity <= 0) return false;
        
        // Check if enough stock
        $item = $this->find($itemId);
        if (!$item || $item->quantity_in_stock < $quantity) return false;
        
        return $this->db->query(
            "UPDATE inventory_items SET quantity_in_stock = quantity_in_stock - ?, updated_at = NOW() WHERE id = ? AND quantity_in_stock >= ?",
            [$quantity, $itemId, $quantity]
        );
    }
    
    /**
     * Get inventory value summary
     */
    public function getValueSummary($branchId = null)
    {
        $sql = "SELECT COUNT(*) as total_items,
                       COALESCE(SUM(quantity_in_stock), 0) as total_quantity,
                       COALESCE(SUM(quantity_in_stock * unit_price), 0) as total_value,
                       COUNT(CASE WHEN quantity_in_stock <= reorder_level THEN 1 END) as low_stock_count,
                       COUNT(CASE WHEN quantity_in_stock = 0 THEN 1 END) as out_of_stock_count
                FROM inventory_items 
                WHERE is_active = 1";
        
        $params = [];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    /**
     * Search items
     */
    public function search($query, $limit = 20)
    {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT ii.*, ic.category_name
                FROM inventory_items ii 
                JOIN inventory_categories ic ON ii.category_id = ic.id 
                WHERE ii.is_active = 1 
                AND (ii.item_name LIKE ? OR ii.item_code LIKE ? OR ii.description LIKE ?)
                ORDER BY ii.item_name
                LIMIT ?";
        
        return $this->db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
    
    // Accessors
    public function appendCategoryNameAttribute()
    {
        return $this->attributes['category_name'] ?? null;
    }
    
    public function appendStockValueAttribute()
    {
        $quantity = $this->attributes['quantity_in_stock'] ?? 0;
        $price = $this->attributes['unit_price'] ?? 0;
        return $quantity * $price;
    }
    
    public function appendStockStatusAttribute()
    {
        $quantity = $this->attributes['quantity_in_stock'] ?? 0;
        $reorder = $this->attributes['reorder_level'] ?? 0;
        
        if ($quantity == 0) return 'ناموجود';
        if ($quantity <= $reorder) return 'کمبود موجودی';
        return 'موجود';
    }
    
    public function appendIsLowStockAttribute()
    {
        $quantity = $this->attributes['quantity_in_stock'] ?? 0;
        $reorder = $this->attributes['reorder_level'] ?? 0;
        return $quantity <= $reorder;
    }
}