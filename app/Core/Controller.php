<?php
namespace App\Core;

use App\Helpers\Helper;

abstract class Controller
{
    protected $db;
    protected $session;
    protected $config;
    protected $request;
    protected $response;
    protected $view;
    protected $logger;
    protected $cache;
    protected $currentUser = null;
    
    public function __construct()
    {
        $app = Application::getInstance();
        
        $this->db = $app->getDB();
        $this->session = $app->getSession();
        $this->config = $app->getConfig();
        $this->request = $app->getRequest();
        $this->logger = $app->getLogger();
        $this->cache = $app->getCache();
        $this->response = new Response();
        $this->view = new View();
        
        // Load current user if authenticated
        $this->currentUser = $this->getCurrentUser();
        
        // Share common data with views
        $this->view->share('currentUser', $this->currentUser);
        $this->view->share('config', $this->config);
    }
    
    protected function view($view, $data = [])
    {
        $viewPath = str_replace('.', '/', $view);
        $this->view->render($viewPath, $data);
    }
    
    protected function json($data, $statusCode = 200)
    {
        $this->response->json($data, $statusCode);
    }
    
    protected function redirect($url)
    {
        $this->response->redirect($url);
    }
    
    protected function back()
    {
        $this->response->back();
    }
    
    protected function redirectWith($url, $type, $message)
    {
        $this->session->flash($type, $message);
        $this->redirect($url);
    }
    
    protected function redirectWithErrors($url, $errors, $old = [])
    {
        $this->session->flash('errors', $errors);
        $this->session->flash('old', $old);
        $this->redirect($url);
    }
    
    protected function validate($data, $rules, $messages = [])
    {
        $validator = new Validator();
        
        if (!$validator->validate($data, $rules, $messages)) {
            throw new Exception\ValidationException(
                'Validation failed',
                $validator->getErrors()
            );
        }
        
        return $validator->getValidated();
    }
    
    protected function getCurrentUser()
    {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            return null;
        }
        
        // Try from cache first
        $cacheKey = 'user_' . $userId;
        $user = $this->cache->get($cacheKey);
        
        if ($user === null) {
            $user = $this->db->selectOne(
                "SELECT u.*, r.name as role_name, r.display_name as role_display,
                        b.name as branch_name, b.id as branch_id
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 JOIN branches b ON u.branch_id = b.id 
                 WHERE u.id = ? AND u.deleted_at IS NULL AND u.is_active = 1",
                [$userId]
            );
            
            if ($user) {
                // Cache for 5 minutes
                $this->cache->set($cacheKey, $user, 300);
            }
        }
        
        return $user;
    }
    
    protected function checkPermission($permission)
    {
        if (!$this->currentUser) {
            return false;
        }
        
        // Super admin has all permissions
        if ($this->currentUser['role_name'] === 'super_admin') {
            return true;
        }
        
        $cacheKey = 'permission_' . $this->currentUser['role_id'] . '_' . $permission;
        $hasPermission = $this->cache->get($cacheKey);
        
        if ($hasPermission === null) {
            $result = $this->db->selectOne(
                "SELECT COUNT(*) as count 
                 FROM role_permission rp 
                 JOIN permissions p ON rp.permission_id = p.id 
                 WHERE rp.role_id = ? AND p.name = ?",
                [$this->currentUser['role_id'], $permission]
            );
            
            $hasPermission = ($result['count'] ?? 0) > 0;
            $this->cache->set($cacheKey, $hasPermission, 3600);
        }
        
        return $hasPermission;
    }
    
    protected function requirePermission($permission)
    {
        if (!$this->checkPermission($permission)) {
            throw new Exception\HttpException('شما دسترسی به این بخش را ندارید', 403);
        }
    }
    
    protected function requireAuthentication()
    {
        if (!$this->currentUser) {
            throw new Exception\AuthenticationException('لطفاً ابتدا وارد سیستم شوید');
        }
    }
    
    protected function logActivity($module, $action, $description, $metadata = [])
    {
        $userId = $this->session->get('user_id');
        
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_values' => !empty($metadata['old_values']) ? json_encode($metadata['old_values']) : null,
            'new_values' => !empty($metadata['new_values']) ? json_encode($metadata['new_values']) : null,
            'ip_address' => $this->request->getIpAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Also log to system log for important actions
        if (in_array($action, ['create', 'update', 'delete', 'login', 'logout'])) {
            $this->logger->info("{$module}.{$action}: {$description}", [
                'user_id' => $userId,
                'ip' => $this->request->getIpAddress()
            ]);
        }
    }
    
    protected function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    protected function commit()
    {
        return $this->db->commit();
    }
    
    protected function rollback()
    {
        return $this->db->rollBack();
    }
    
    protected function paginate($query, $params = [], $perPage = null)
    {
        $page = $this->request->get('page', 1);
        $perPage = $perPage ?? $this->config['pagination']['per_page'] ?? 25;
        $maxPerPage = $this->config['pagination']['max_per_page'] ?? 100;
        
        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }
        
        $paginator = new Paginator($this->db, $query, $params, $perPage);
        return $paginator->getPage($page);
    }
    
    protected function fileUploader()
    {
        return new FileUploader($this->config['upload'] ?? []);
    }
    
    protected function sendEmail($to, $subject, $body, $attachments = [])
    {
        $mailer = new Mailer($this->config['email'] ?? []);
        return $mailer->send($to, $subject, $body, $attachments);
    }
    
    protected function generateCsrfToken()
    {
        $token = Security::generateToken();
        $this->session->set('csrf_token', $token);
        return $token;
    }
    
    protected function validateCsrfToken($token = null)
    {
        $token = $token ?? $this->request->post('csrf_token') ?? $this->request->header('X-CSRF-TOKEN');
        $sessionToken = $this->session->get('csrf_token');
        
        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            throw new Exception\HttpException('توکن امنیتی نامعتبر است', 419);
        }
        
        return true;
    }
}