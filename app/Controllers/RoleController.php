<?php
namespace App\Controllers;

use App\Core\Controller;

class RoleController extends Controller
{
    public function index()
    {
        $roles = $this->db->select(
            "SELECT r.*, 
                    (SELECT COUNT(*) FROM users WHERE role_id = r.id AND deleted_at IS NULL) as user_count
             FROM roles r 
             ORDER BY r.display_name"
        );
        
        $this->view('roles/index', [
            'title' => 'مدیریت نقش‌ها',
            'roles' => $roles
        ]);
    }
    
    public function create()
    {
        $permissions = $this->db->select(
            "SELECT * FROM permissions ORDER BY module, name"
        );
        
        // Group permissions by module
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $groupedPermissions[$permission['module']][] = $permission;
        }
        
        $this->view('roles/create', [
            'title' => 'ایجاد نقش جدید',
            'groupedPermissions' => $groupedPermissions
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        if (empty($data['name']) || empty($data['display_name'])) {
            $this->session->flash('error', 'نام و نام نمایشی نقش الزامی است');
            $this->redirect('/roles/create');
            return;
        }
        
        // Check unique name
        $existing = $this->db->selectOne("SELECT id FROM roles WHERE name = ?", [$data['name']]);
        
        if ($existing) {
            $this->session->flash('error', 'این نام نقش قبلاً ثبت شده است');
            $this->redirect('/roles/create');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create role
            $roleId = $this->db->insert('roles', [
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Assign permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                foreach ($data['permissions'] as $permissionId) {
                    $this->db->insert('role_permission', [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId
                    ]);
                }
            }
            
            $this->db->commit();
            
            $this->logActivity('roles', 'create', "نقش جدید '{$data['display_name']}' ایجاد شد");
            
            $this->session->flash('success', 'نقش با موفقیت ایجاد شد');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در ایجاد نقش: ' . $e->getMessage());
        }
        
        $this->redirect('/roles');
    }
    
    public function edit($id)
    {
        $role = $this->db->selectOne("SELECT * FROM roles WHERE id = ?", [$id]);
        
        if (!$role) {
            $this->session->flash('error', 'نقش مورد نظر یافت نشد');
            $this->redirect('/roles');
            return;
        }
        
        // Get all permissions
        $allPermissions = $this->db->select("SELECT * FROM permissions ORDER BY module, name");
        
        // Get role permissions
        $rolePermissions = $this->db->select(
            "SELECT permission_id FROM role_permission WHERE role_id = ?",
            [$id]
        );
        
        $assignedPermissions = array_column($rolePermissions, 'permission_id');
        
        // Group permissions by module
        $groupedPermissions = [];
        foreach ($allPermissions as $permission) {
            $permission['assigned'] = in_array($permission['id'], $assignedPermissions);
            $groupedPermissions[$permission['module']][] = $permission;
        }
        
        $this->view('roles/edit', [
            'title' => 'ویرایش نقش',
            'role' => $role,
            'groupedPermissions' => $groupedPermissions
        ]);
    }
    
    public function update($id)
    {
        $role = $this->db->selectOne("SELECT * FROM roles WHERE id = ?", [$id]);
        
        if (!$role) {
            $this->session->flash('error', 'نقش مورد نظر یافت نشد');
            $this->redirect('/roles');
            return;
        }
        
        $data = $_POST;
        
        try {
            $this->db->beginTransaction();
            
            // Update role
            $this->db->update('roles', [
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? '',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            // Update permissions
            $this->db->delete('role_permission', 'role_id = ?', [$id]);
            
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                foreach ($data['permissions'] as $permissionId) {
                    $this->db->insert('role_permission', [
                        'role_id' => $id,
                        'permission_id' => $permissionId
                    ]);
                }
            }
            
            $this->db->commit();
            
            $this->logActivity('roles', 'update', "نقش '{$data['display_name']}' به‌روزرسانی شد");
            
            $this->session->flash('success', 'نقش با موفقیت به‌روزرسانی شد');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در به‌روزرسانی نقش: ' . $e->getMessage());
        }
        
        $this->redirect('/roles');
    }
    
    public function delete($id)
    {
        $role = $this->db->selectOne("SELECT * FROM roles WHERE id = ?", [$id]);
        
        if (!$role) {
            $this->session->flash('error', 'نقش مورد نظر یافت نشد');
            $this->redirect('/roles');
            return;
        }
        
        // Check if role has users
        $userCount = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM users WHERE role_id = ? AND deleted_at IS NULL",
            [$id]
        );
        
        if ($userCount['count'] > 0) {
            $this->session->flash('error', 'این نقش دارای کاربران فعال است و قابل حذف نیست');
            $this->redirect('/roles');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $this->db->delete('role_permission', 'role_id = ?', [$id]);
            $this->db->delete('roles', 'id = ?', [$id]);
            
            $this->db->commit();
            
            $this->logActivity('roles', 'delete', "نقش '{$role['display_name']}' حذف شد");
            
            $this->session->flash('success', 'نقش با موفقیت حذف شد');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا در حذف نقش: ' . $e->getMessage());
        }
        
        $this->redirect('/roles');
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