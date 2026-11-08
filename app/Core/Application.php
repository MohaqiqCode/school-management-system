<?php
namespace App\Core;

class Application
{
    private static $instance = null;
    private $config = [];
    private $db = null;
    private $session = null;
    private $router = null;
    private $request = null;
    private $logger = null;
    private $cache = null;
    private $autoloader = null;
    private $basePath;
    private $isInitialized = false;
    
    private function __construct()
    {
        $this->basePath = dirname(dirname(__DIR__));
        $this->initializeAutoloader();
        $this->loadConfiguration();
        $this->initializeSession();
        $this->initializeDatabase();
        $this->initializeRouter();
        $this->initializeLogger();
        $this->initializeCache();
        $this->isInitialized = true;
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeAutoloader()
    {
        $this->autoloader = new Autoloader();
        $this->autoloader->register();
    }
    
    private function loadConfiguration()
    {
        $configPath = $this->basePath . '/config/';
        
        $this->config['app'] = require $configPath . 'app.php';
        $this->config['database'] = require $configPath . 'database.php';
        $this->config['permissions'] = require $configPath . 'permissions.php';
        
        // Set timezone
        date_default_timezone_set($this->config['app']['app_timezone'] ?? 'UTC');
        
        // Set error reporting
        if ($this->config['app']['app_debug'] ?? false) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
    }
    
    private function initializeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionConfig = $this->config['app']['session'] ?? [];
            
            session_set_cookie_params([
                'lifetime' => $sessionConfig['lifetime'] ?? 120,
                'path' => '/',
                'domain' => '',
                'secure' => $sessionConfig['secure'] ?? false,
                'httponly' => $sessionConfig['httponly'] ?? true,
                'samesite' => $sessionConfig['samesite'] ?? 'Lax'
            ]);
            
            session_start();
        }
        
        $this->session = new Session();
        $this->request = new Request();
    }
    
    private function initializeDatabase()
    {
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            if ($this->config['app']['app_debug'] ?? false) {
                throw $e;
            }
            // Log error and show friendly message
            $this->getLogger()->critical('Database connection failed: ' . $e->getMessage());
            die('A database error occurred. Please try again later.');
        }
    }
    
    private function initializeRouter()
    {
        $this->router = new Router($this->request);
    }
    
    private function initializeLogger()
    {
        $logPath = $this->basePath . '/storage/logs/';
        $this->logger = new Logger($logPath);
    }
    
    private function initializeCache()
    {
        $cachePath = $this->basePath . '/storage/cache/';
        $this->cache = new Cache($cachePath);
    }
    
    public function run()
    {
        try {
            // Load routes
            require $this->basePath . '/routes/web.php';
            require $this->basePath . '/routes/api.php';
            
            // Dispatch request
            $this->router->dispatch();
            
        } catch (Exception\HttpException $e) {
            $this->handleHttpException($e);
        } catch (Exception\AuthenticationException $e) {
            $this->handleAuthenticationException($e);
        } catch (Exception\ValidationException $e) {
            $this->handleValidationException($e);
        } catch (Exception\DatabaseException $e) {
            $this->handleDatabaseException($e);
        } catch (\Exception $e) {
            $this->handleGeneralException($e);
        }
    }
    
    private function handleHttpException(Exception\HttpException $e)
    {
        http_response_code($e->getStatusCode());
        
        if ($this->request->wantsJson()) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getStatusCode()
            ], $e->getStatusCode());
        } else {
            $view = new View();
            $view->render('errors/' . $e->getStatusCode(), [
                'message' => $e->getMessage(),
                'code' => $e->getStatusCode()
            ]);
        }
    }
    
    private function handleAuthenticationException(Exception\AuthenticationException $e)
    {
        if ($this->request->wantsJson()) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        } else {
            $this->session->flash('error', $e->getMessage());
            header('Location: /login');
            exit;
        }
    }
    
    private function handleValidationException(Exception\ValidationException $e)
    {
        if ($this->request->wantsJson()) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], 422);
        } else {
            $this->session->flash('errors', $e->getErrors());
            $this->session->flash('old', $this->request->all());
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/');
            exit;
        }
    }
    
    private function handleDatabaseException(Exception\DatabaseException $e)
    {
        $this->logger->error('Database Error: ' . $e->getMessage(), [
            'query' => $e->getQuery(),
            'params' => $e->getParams()
        ]);
        
        if ($this->config['app']['app_debug'] ?? false) {
            throw $e;
        }
        
        if ($this->request->wantsJson()) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'A database error occurred'
            ], 500);
        } else {
            $view = new View();
            $view->render('errors/500', [
                'message' => 'A database error occurred. Please try again later.'
            ]);
        }
    }
    
    private function handleGeneralException(\Exception $e)
    {
        $this->logger->critical($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($this->config['app']['app_debug'] ?? false) {
            throw $e;
        }
        
        if ($this->request->wantsJson()) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'An unexpected error occurred'
            ], 500);
        } else {
            $view = new View();
            $view->render('errors/500', [
                'message' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }
    
    // Getters
    public function getConfig($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config['app'];
        }
        
        $keys = explode('.', $key);
        $value = $this->config['app'];
        
        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    public function getDB()
    {
        return $this->db;
    }
    
    public function getSession()
    {
        return $this->session;
    }
    
    public function getRouter()
    {
        return $this->router;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getLogger()
    {
        return $this->logger;
    }
    
    public function getCache()
    {
        return $this->cache;
    }
    
    public function getBasePath()
    {
        return $this->basePath;
    }
    
    public function isInitialized()
    {
        return $this->isInitialized;
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}