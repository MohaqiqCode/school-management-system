<?php
namespace App\Controllers;

use App\Core\Controller;

class InventoryController extends Controller
{
    public function index()
    {
        $branchId = $this->session->get('branch_id');
        $categoryFilter = $_GET['category_id'] ?? '';
        
        $sql = "SELECT ii.*, ic.category_name, b.name as branch_name
                FROM inventory_items ii 
                JOIN inventory_categories ic ON ii.category_id = ic.id 
                JOIN branches b ON ii.branch_id = b.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($this->session->get('user_role') !== 'super_admin' && $branchId) {
            $sql .= " AND ii.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($categoryFilter) {
            $sql .= " AND ii.category_id = ?";
            $params[] = $categoryFilter;
        }
        
        $sql .= " ORDER BY ic.category_name, ii.item_name";
        $items = $this->db->select($sql, $params);
        
        // Get categories
        $categories = $this->db->select("SELECT * FROM inventory_categories WHERE branch_id = ? ORDER BY category_name", [$branchId]);
        
        // Get low stock items
        $lowStock = $this->db->select(
            "SELECT * FROM inventory_items WHERE quantity_in_stock <= reorder_level AND is_active = 1"
        );
        
        $this->view('inventory/index', [
            'title' => 'مدیریت انبار',
            'items' => $items,
            'categories' => $categories,
            'lowStock' => $lowStock,
            'categoryFilter' => $categoryFilter
        ]);
    }
    
    public function categories()
    {
        $branchId = $this->session->get('branch_id');
        
        $categories = $this->db->select(
            "SELECT ic.*, 
                    (SELECT COUNT(*) FROM inventory_items WHERE category_id = ic.id) as item_count
             FROM inventory_categories ic 
             WHERE ic.branch_id = ?
             ORDER BY ic.category_name",
            [$branchId]
        );
        
        $this->view('inventory/categories', [
            'title' => 'دسته‌بندی‌های انبار',
            'categories' => $categories
        ]);
    }
    
    public function storeCategory()
    {
        $data = $_POST;
        
        if (empty($data['category_name'])) {
            $this->json(['success' => false, 'message' => 'نام دسته‌بندی الزامی است']);
            return;
        }
        
        $this->db->insert('inventory_categories', [
            'branch_id' => $this->session->get('branch_id'),
            'category_name' => $data['category_name'],
            'description' => $data['description'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('inventory', 'create_category', "دسته‌بندی '{$data['category_name']}' ایجاد شد");
        
        $this->json(['success' => true, 'message' => 'دسته‌بندی ایجاد شد']);
    }
    
    public function create()
    {
        $branchId = $this->session->get('branch_id');
        $categories = $this->db->select("SELECT * FROM inventory_categories WHERE branch_id = ? ORDER BY category_name", [$branchId]);
        
        $this->view('inventory/create', [
            'title' => 'افزودن کالای جدید',
            'categories' => $categories
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['item_name'])) {
            $errors['item_name'] = 'نام کالا الزامی است';
        }
        
        if (empty($data['item_code'])) {
            $errors['item_code'] = 'کد کالا الزامی است';
        }
        
        if (empty($data['category_id'])) {
            $errors['category_id'] = 'دسته‌بندی الزامی است';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/inventory/create');
            return;
        }
        
        // Check unique item code
        $existing = $this->db->selectOne("SELECT id FROM inventory_items WHERE item_code = ?", [strtoupper($data['item_code'])]);
        
        if ($existing) {
            $this->session->flash('errors', ['item_code' => 'این کد کالا قبلاً ثبت شده است']);
            $this->redirect('/inventory/create');
            return;
        }
        
        $this->db->insert('inventory_items', [
            'category_id' => $data['category_id'],
            'branch_id' => $this->session->get('branch_id'),
            'item_code' => strtoupper($data['item_code']),
            'item_name' => $data['item_name'],
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'] ?? 'piece',
            'quantity_in_stock' => $data['quantity_in_stock'] ?? 0,
            'reorder_level' => $data['reorder_level'] ?? 10,
            'unit_price' => $data['unit_price'] ?? 0,
            'supplier_name' => $data['supplier_name'] ?? null,
            'location' => $data['location'] ?? null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('inventory', 'create', "کالای '{$data['item_name']}' به انبار اضافه شد");
        
        $this->session->flash('success', 'کالا با موفقیت اضافه شد');
        $this->redirect('/inventory');
    }
    
    public function edit($id)
    {
        $item = $this->db->selectOne("SELECT * FROM inventory_items WHERE id = ?", [$id]);
        
        if (!$item) {
            $this->session->flash('error', 'کالا یافت نشد');
            $this->redirect('/inventory');
            return;
        }
        
        $branchId = $this->session->get('branch_id');
        $categories = $this->db->select("SELECT * FROM inventory_categories WHERE branch_id = ? ORDER BY category_name", [$branchId]);
        
        $this->view('inventory/edit', [
            'title' => 'ویرایش کالا',
            'item' => $item,
            'categories' => $categories
        ]);
    }
    
    public function update($id)
    {
        $item = $this->db->selectOne("SELECT * FROM inventory_items WHERE id = ?", [$id]);
        
        if (!$item) {
            $this->session->flash('error', 'کالا یافت نشد');
            $this->redirect('/inventory');
            return;
        }
        
        $data = $_POST;
        
        $this->db->update('inventory_items', [
            'category_id' => $data['category_id'],
            'item_name' => $data['item_name'],
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'] ?? 'piece',
            'quantity_in_stock' => $data['quantity_in_stock'] ?? 0,
            'reorder_level' => $data['reorder_level'] ?? 10,
            'unit_price' => $data['unit_price'] ?? 0,
            'supplier_name' => $data['supplier_name'] ?? null,
            'location' => $data['location'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('inventory', 'update', "کالای {$id} به‌روزرسانی شد");
        
        $this->session->flash('success', 'اطلاعات کالا به‌روزرسانی شد');
        $this->redirect('/inventory');
    }
    
    public function delete($id)
    {
        $item = $this->db->selectOne("SELECT * FROM inventory_items WHERE id = ?", [$id]);
        
        if (!$item) {
            $this->json(['success' => false, 'message' => 'کالا یافت نشد'], 404);
            return;
        }
        
        $this->db->delete('inventory_items', 'id = ?', [$id]);
        
        $this->logActivity('inventory', 'delete', "کالای '{$item['item_name']}' حذف شد");
        
        $this->json(['success' => true, 'message' => 'کالا حذف شد']);
    }
    
    public function stockIn($id)
    {
        $item = $this->db->selectOne("SELECT * FROM inventory_items WHERE id = ?", [$id]);
        
        if (!$item) {
            $this->json(['success' => false, 'message' => 'کالا یافت نشد'], 404);
            return;
        }
        
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($quantity <= 0) {
            $this->json(['success' => false, 'message' => 'مقدار ورودی باید بزرگتر از صفر باشد']);
            return;
        }
        
        $newQuantity = $item['quantity_in_stock'] + $quantity;
        
        $this->db->update('inventory_items', [
            'quantity_in_stock' => $newQuantity,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('inventory', 'stock_in', "ورود {$quantity} عدد به کالای {$id}");
        
        $this->json(['success' => true, 'message' => 'موجودی به‌روزرسانی شد', 'new_quantity' => $newQuantity]);
    }
    
    public function stockOut($id)
    {
        $item = $this->db->selectOne("SELECT * FROM inventory_items WHERE id = ?", [$id]);
        
        if (!$item) {
            $this->json(['success' => false, 'message' => 'کالا یافت نشد'], 404);
            return;
        }
        
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($quantity <= 0) {
            $this->json(['success' => false, 'message' => 'مقدار خروجی باید بزرگتر از صفر باشد']);
            return;
        }
        
        if ($quantity > $item['quantity_in_stock']) {
            $this->json(['success' => false, 'message' => 'موجودی کافی نیست']);
            return;
        }
        
        $newQuantity = $item['quantity_in_stock'] - $quantity;
        
        $this->db->update('inventory_items', [
            'quantity_in_stock' => $newQuantity,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('inventory', 'stock_out', "خروج {$quantity} عدد از کالای {$id}");
        
        $this->json(['success' => true, 'message' => 'موجودی به‌روزرسانی شد', 'new_quantity' => $newQuantity]);
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