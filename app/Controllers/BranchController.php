<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Branch;

class BranchController extends Controller
{
    private $branchModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->branchModel = new Branch();
    }
    
    public function index()
    {
        $branches = $this->branchModel->getAllWithStats();
        
        $this->view('branches/index', [
            'title' => 'Branch Management',
            'branches' => $branches
        ]);
    }
    
    public function create()
    {
        $this->view('branches/create', [
            'title' => 'Add New Branch'
        ]);
    }
    
    public function store()
    {
        // CSRF Protection
        $this->validateCSRF();
        
        // Validate input
        $data = $_POST;
        $errors = [];
        
        if (empty($data['branch_code'])) {
            $errors['branch_code'] = 'Branch code is required';
        }
        if (empty($data['name'])) {
            $errors['name'] = 'Branch name is required';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Check unique branch code
        $existingBranch = $this->db->selectOne(
            "SELECT id FROM branches WHERE branch_code = ?",
            [$data['branch_code']]
        );
        if ($existingBranch) {
            $errors['branch_code'] = 'Branch code already exists';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/branches/create');
            return;
        }
        
        // Create branch
        $branchId = $this->branchModel->create([
            'branch_code' => $data['branch_code'],
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'United States',
            'postal_code' => $data['postal_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'principal_name' => $data['principal_name'] ?? null,
            'established_date' => $data['established_date'] ?? null,
            'total_capacity' => $data['total_capacity'] ?? 500,
            'is_active' => $data['is_active'] ?? true
        ]);
        
        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = $this->uploadLogo($_FILES['logo'], $branchId);
            if ($logoPath) {
                $this->branchModel->update($branchId, ['logo_path' => $logoPath]);
            }
        }
        
        // Log activity
        $this->logActivity('branches', 'create', "Branch '{$data['name']}' created successfully");
        
        $this->session->flash('success', 'Branch created successfully');
        $this->redirect('/branches');
    }
    
    public function edit($id)
    {
        $branch = $this->branchModel->find($id);
        
        if (!$branch) {
            $this->session->flash('error', 'Branch not found');
            $this->redirect('/branches');
            return;
        }
        
        $this->view('branches/edit', [
            'title' => 'Edit Branch',
            'branch' => $branch
        ]);
    }
    
    public function update($id)
    {
        $this->validateCSRF();
        
        $data = $_POST;
        $branch = $this->branchModel->find($id);
        
        if (!$branch) {
            $this->session->flash('error', 'Branch not found');
            $this->redirect('/branches');
            return;
        }
        
        // Validate
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'Branch name is required';
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->redirect("/branches/{$id}/edit");
            return;
        }
        
        // Update branch
        $this->branchModel->update($id, [
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'principal_name' => $data['principal_name'] ?? null,
            'established_date' => $data['established_date'] ?? null,
            'total_capacity' => $data['total_capacity'] ?? 500,
            'is_active' => $data['is_active'] ?? true
        ]);
        
        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = $this->uploadLogo($_FILES['logo'], $id);
            if ($logoPath) {
                $this->branchModel->update($id, ['logo_path' => $logoPath]);
            }
        }
        
        $this->logActivity('branches', 'update', "Branch '{$data['name']}' updated successfully");
        
        $this->session->flash('success', 'Branch updated successfully');
        $this->redirect('/branches');
    }
    
    public function delete($id)
    {
        $branch = $this->branchModel->find($id);
        
        if (!$branch) {
            $this->session->flash('error', 'Branch not found');
            $this->redirect('/branches');
            return;
        }
        
        // Check if branch has associated data
        $hasStudents = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM students WHERE branch_id = ?",
            [$id]
        );
        
        if ($hasStudents['count'] > 0) {
            $this->session->flash('error', 'Cannot delete branch with associated students');
            $this->redirect('/branches');
            return;
        }
        
        $this->branchModel->delete($id);
        
        $this->logActivity('branches', 'delete', "Branch '{$branch['name']}' deleted");
        
        $this->session->flash('success', 'Branch deleted successfully');
        $this->redirect('/branches');
    }
    
    private function uploadLogo($file, $branchId)
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads/logos/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'branch_' . $branchId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($extension), $allowedTypes)) {
            return false;
        }
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/assets/uploads/logos/' . $filename;
        }
        
        return false;
    }
    
    private function validateCSRF()
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->session->get('csrf_token')) {
            $this->session->flash('error', 'Invalid security token');
            $this->back();
            exit;
        }
    }
    
    private function logActivity($module, $action, $description)
    {
        $userId = $this->session->get('user_id');
        
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}