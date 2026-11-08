<?php
namespace App\Core;

class View
{
    private $basePath;
    private $data = [];
    private $sharedData = [];
    private $sections = [];
    private $currentSection;
    private $layout;
    
    public function __construct()
    {
        $this->basePath = Application::getInstance()->getBasePath() . '/app/Views/';
    }
    
    public function render($view, $data = [])
    {
        $viewPath = $this->resolveViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View [{$view}] not found at path: {$viewPath}");
        }
        
        $data = array_merge($this->sharedData, $data);
        
        extract($data);
        
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        // If layout is set, wrap content in layout
        if ($this->layout) {
            $layoutPath = $this->resolveViewPath($this->layout);
            $layoutData = array_merge($data, ['content' => $content]);
            extract($layoutData);
            
            ob_start();
            require $layoutPath;
            $content = ob_get_clean();
        }
        
        echo $content;
    }
    
    public function share($key, $value)
    {
        $this->sharedData[$key] = $value;
    }
    
    public function layout($layout)
    {
        $this->layout = $layout;
    }
    
    public function section($name)
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    public function endSection()
    {
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }
    
    public function yield($name)
    {
        echo $this->sections[$name] ?? '';
    }
    
    public function include($view, $data = [])
    {
        $viewPath = $this->resolveViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View [{$view}] not found");
        }
        
        extract(array_merge($this->sharedData, $data));
        require $viewPath;
    }
    
    public function exists($view)
    {
        return file_exists($this->resolveViewPath($view));
    }
    
    private function resolveViewPath($view)
    {
        // Convert dot notation to path
        $view = str_replace('.', '/', $view);
        
        // Add .php extension if not present
        if (substr($view, -4) !== '.php') {
            $view .= '.php';
        }
        
        return $this->basePath . $view;
    }
    
    // Template helper methods
    public function escape($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    public function old($key, $default = '')
    {
        return $_SESSION['__old_input'][$key] ?? $default;
    }
    
    public function error($key)
    {
        $errors = $_SESSION['__errors'] ?? [];
        return $errors[$key][0] ?? null;
    }
    
    public function hasError($key)
    {
        $errors = $_SESSION['__errors'] ?? [];
        return isset($errors[$key]);
    }
    
    public function csrf()
    {
        $token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
    
    public function method($method)
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
    
    public function asset($path)
    {
        return '/assets/' . ltrim($path, '/');
    }
    
    public function url($path)
    {
        return '/' . ltrim($path, '/');
    }
    
    public function route($name, $params = [])
    {
        // Route URL generation would be implemented here
        return '#' . $name;
    }
    
    public function config($key, $default = null)
    {
        return Application::getInstance()->getConfig($key, $default);
    }
    
    public function auth()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function user()
    {
        return $this->sharedData['currentUser'] ?? null;
    }
    
    public function role()
    {
        return $this->sharedData['currentUser']['role_name'] ?? null;
    }
    
    public function can($permission)
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user['role_name'] === 'super_admin') return true;
        
        // Check permission logic
        return true; // Simplified
    }
    
    public function formatDate($date, $format = 'Y-m-d')
    {
        return date($format, strtotime($date));
    }
    
    public function formatCurrency($amount, $currency = 'تومان')
    {
        return number_format($amount) . ' ' . $currency;
    }
    
    public function truncate($text, $length = 100, $end = '...')
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . $end;
    }
}