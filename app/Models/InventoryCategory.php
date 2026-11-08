<?php
namespace App\Models;

/**
 * Inventory Category Model
 * مدیریت دسته‌بندی‌های انبار
 */
class InventoryCategory extends Model
{
    protected $table = 'inventory_categories';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'branch_id',
        'category_name',
        'description',
    ];
    
    protected $appends = [
        'item_count',
        'total_value',
        'low_stock_count',
    ];
    
    /**
     * Get categories by branch
     */
    public function getByBranch($branchId)
    {
        $sql = "SELECT ic.*, 
                       (SELECT COUNT(*) FROM inventory_items WHERE category_id = ic.id) as item_count
                FROM inventory_categories ic 
                WHERE ic.branch_id = ?
                ORDER BY ic.category_name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    /**
     * Get categories with item count
     */
    public function getAllWithCount($branchId)
    {
        $sql = "SELECT ic.*, 
                       COUNT(ii.id) as item_count,
                       COALESCE(SUM(ii.quantity_in_stock * ii.unit_price), 0) as total_value
                FROM inventory_categories ic 
                LEFT JOIN inventory_items ii ON ic.id = ii.category_id AND ii.is_active = 1
                WHERE ic.branch_id = ?
                GROUP BY ic.id
                ORDER BY ic.category_name";
        
        return $this->hydrate($this->db->select($sql, [$branchId]));
    }
    
    // Accessors
    public function appendItemCountAttribute()
    {
        return $this->attributes['item_count'] ?? 0;
    }
    
    public function appendTotalValueAttribute()
    {
        return $this->attributes['total_value'] ?? 0;
    }
    
    public function appendLowStockCountAttribute()
    {
        $catId = $this->attributes['id'] ?? null;
        if (!$catId) return 0;
        
        $sql = "SELECT COUNT(*) as total FROM inventory_items 
                WHERE category_id = ? AND quantity_in_stock <= reorder_level AND is_active = 1";
        $result = $this->db->selectOne($sql, [$catId]);
        return (int)($result['total'] ?? 0);
    }
}