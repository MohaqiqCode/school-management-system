<?php
namespace App\Middleware;

use App\Core\Application;
use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Response;

/**
 * Base Middleware Class
 * کلاس پایه برای تمام middleware ها
 */
abstract class Middleware
{
    /**
     * @var Database
     */
    protected $db;
    
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Response
     */
    protected $response;
    
    /**
     * @var Application
     */
    protected $app;
    
    /**
     * @var array Middleware configuration
     */
    protected $config = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->db = $this->app->getDB();
        $this->session = $this->app->getSession();
        $this->request = $this->app->getRequest();
        $this->response = new Response();
        $this->config = $this->app->getConfig();
    }
    
    /**
     * Handle the middleware
     * 
     * @return bool|void Return true to pass, false to block, or void/null to continue
     */
    abstract public function handle();
    
    /**
     * Get current authenticated user
     * 
     * @return array|null
     */
    protected function getCurrentUser()
    {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            return null;
        }
        
        $user = $this->db->selectOne(
            "SELECT u.*, r.name as role_name, r.display_name as role_display,
                    b.name as branch_name, b.id as branch_id
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             JOIN branches b ON u.branch_id = b.id 
             WHERE u.id = ? AND u.deleted_at IS NULL AND u.is_active = 1",
            [$userId]
        );
        
        return $user ?: null;
    }
    
    /**
     * Check if user has specific permission
     * 
     * @param string $permission
     * @return bool
     */
    protected function hasPermission($permission)
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        // Super admin has all permissions
        if ($user['role_name'] === 'super_admin') {
            return true;
        }
        
        $result = $this->db->selectOne(
            "SELECT COUNT(*) as count 
             FROM role_permission rp 
             JOIN permissions p ON rp.permission_id = p.id 
             WHERE rp.role_id = ? AND p.name = ?",
            [$user['role_id'], $permission]
        );
        
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Check if user has specific role
     * 
     * @param string|array $roles
     * @return bool
     */
    protected function hasRole($roles)
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        $roles = (array) $roles;
        
        return in_array($user['role_name'], $roles);
    }
    
    /**
     * Abort with HTTP error
     * 
     * @param int $statusCode
     * @param string $message
     */
    protected function abort($statusCode, $message = '')
    {
        if ($this->request->wantsJson()) {
            $this->response->json([
                'success' => false,
                'message' => $message ?: $this->getDefaultMessage($statusCode),
                'code' => $statusCode
            ], $statusCode);
        } else {
            http_response_code($statusCode);
            
            if ($statusCode === 403) {
                $this->session->flash('error', $message ?: 'شما دسترسی به این بخش را ندارید');
                $this->response->redirect('/dashboard');
            } elseif ($statusCode === 401) {
                $this->session->flash('error', $message ?: 'لطفاً ابتدا وارد سیستم شوید');
                $this->response->redirect('/login');
            } else {
                echo "<h1>Error {$statusCode}</h1>";
                if ($message) {
                    echo "<p>{$message}</p>";
                }
            }
        }
        
        exit;
    }
    
    /**
     * Get default error message for status code
     * 
     * @param int $statusCode
     * @return string
     */
    protected function getDefaultMessage($statusCode)
    {
        $messages = [
            400 => 'درخواست نامعتبر',
            401 => 'احراز هویت نشده',
            403 => 'دسترسی غیرمجاز',
            404 => 'یافت نشد',
            405 => 'متد غیرمجاز',
            419 => 'توکن امنیتی منقضی شده',
            422 => 'داده‌های ورودی نامعتبر',
            429 => 'تعداد درخواست‌ها بیش از حد مجاز',
            500 => 'خطای سرور',
            503 => 'سرویس در دسترس نیست',
        ];
        
        return $messages[$statusCode] ?? 'خطای ناشناخته';
    }
    
    /**
     * Log middleware activity
     * 
     * @param string $action
     * @param string $description
     */
    protected function log($action, $description)
    {
        try {
            $userId = $this->session->get('user_id');
            
            $this->db->insert('audit_logs', [
                'user_id' => $userId,
                'action' => $action,
                'module' => 'middleware',
                'description' => $description,
                'ip_address' => $this->request->getIpAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Silently fail if logging fails
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    protected function getClientIp()
    {
        return $this->request->getIpAddress();
    }
}