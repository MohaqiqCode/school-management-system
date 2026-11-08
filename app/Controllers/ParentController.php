<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Helper;

class ParentController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $search = $_GET['search'] ?? '';
        
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                       u.is_active as user_active,
                       (SELECT COUNT(*) FROM student_parent WHERE parent_id = p.id) as children_count
                FROM parents p 
                JOIN users u ON p.user_id = u.id 
                WHERE u.deleted_at IS NULL";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR p.parent_code LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $countSql = str_replace("SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo, u.is_active as user_active, (SELECT COUNT(*) FROM student_parent WHERE parent_id = p.id) as children_count", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY u.last_name, u.first_name LIMIT {$perPage} OFFSET {$offset}";
        $parents = $this->db->select($sql, $params);
        
        $this->view('parents/index', [
            'title' => 'مدیریت والدین',
            'parents' => $parents,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'search' => $search
        ]);
    }
    
    public function show($id)
    {
        $parent = $this->db->selectOne(
            "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.profile_photo,
                    u.address, u.city
             FROM parents p 
             JOIN users u ON p.user_id = u.id 
             WHERE p.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
        
        if (!$parent) {
            $this->session->flash('error', 'والد مورد نظر یافت نشد');
            $this->redirect('/parents');
            return;
        }
        
        // Get children
        $children = $this->db->select(
            "SELECT s.*, u.first_name, u.last_name, u.profile_photo,
                    c.class_name, sec.section_name,
                    sp.relationship, sp.is_emergency_contact, sp.can_pickup
             FROM students s 
             JOIN users u ON s.user_id = u.id 
             JOIN student_parent sp ON s.id = sp.student_id 
             LEFT JOIN classes c ON s.current_class_id = c.id 
             LEFT JOIN sections sec ON s.current_section_id = sec.id
             WHERE sp.parent_id = ? AND s.is_active = 1
             ORDER BY c.class_level, u.first_name",
            [$id]
        );
        
        // Get fee summary for all children
        $feeSummary = [];
        foreach ($children as $child) {
            $fees = $this->db->select(
                "SELECT sf.*, fc.category_name
                 FROM student_fees sf 
                 JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                 JOIN fee_categories fc ON fs.fee_category_id = fc.id 
                 WHERE sf.student_id = ? AND sf.status != 'paid'
                 ORDER BY sf.due_date",
                [$child['id']]
            );
            $feeSummary[$child['id']] = $fees;
        }
        
        $this->view('parents/show', [
            'title' => 'پروفایل والد',
            'parent' => $parent,
            'children' => $children,
            'feeSummary' => $feeSummary
        ]);
    }
    
    public function edit($id)
    {
        $parent = $this->db->selectOne(
            "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.address, u.city
             FROM parents p JOIN users u ON p.user_id = u.id WHERE p.id = ?",
            [$id]
        );
        
        if (!$parent) {
            $this->session->flash('error', 'والد یافت نشد');
            $this->redirect('/parents');
            return;
        }
        
        $this->view('parents/edit', [
            'title' => 'ویرایش اطلاعات والد',
            'parent' => $parent
        ]);
    }
    
    public function update($id)
    {
        $parent = $this->db->selectOne("SELECT * FROM parents WHERE id = ?", [$id]);
        
        if (!$parent) {
            $this->session->flash('error', 'والد یافت نشد');
            $this->redirect('/parents');
            return;
        }
        
        $data = $_POST;
        
        $this->db->beginTransaction();
        
        try {
            $this->db->update('users', [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$parent['user_id']]);
            
            $this->db->update('parents', [
                'occupation' => $data['occupation'] ?? null,
                'workplace' => $data['workplace'] ?? null,
                'annual_income' => $data['annual_income'] ?? null,
                'relationship' => $data['relationship'] ?? 'father',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $profilePhoto = $this->uploadProfilePhoto($_FILES['profile_photo'], $parent['user_id']);
                if ($profilePhoto) {
                    $this->db->update('users', ['profile_photo' => $profilePhoto], 'id = ?', [$parent['user_id']]);
                }
            }
            
            $this->db->commit();
            
            $this->logActivity('parents', 'update', "اطلاعات والد '{$data['first_name']} {$data['last_name']}' به‌روزرسانی شد");
            
            $this->session->flash('success', 'اطلاعات والد به‌روزرسانی شد');
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->flash('error', 'خطا: ' . $e->getMessage());
        }
        
        $this->redirect('/parents');
    }
    
    private function uploadProfilePhoto($file, $userId)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/profiles/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedTypes)) {
            return null;
        }
        
        $filename = 'parent_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/assets/uploads/profiles/' . $filename;
        }
        
        return null;
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