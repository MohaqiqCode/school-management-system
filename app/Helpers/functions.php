<?php
/**
 * Global Helper Functions
 * توابع کمکی گلوبال
 */

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env($key, $default = null)
    {
        return \App\Helpers\Helper::env($key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config($key = null, $default = null)
    {
        return \App\Core\Application::getInstance()->getConfig($key, $default);
    }
}

if (!function_exists('app')) {
    /**
     * Get application instance
     */
    function app()
    {
        return \App\Core\Application::getInstance();
    }
}

if (!function_exists('db')) {
    /**
     * Get database instance
     */
    function db()
    {
        return \App\Core\Application::getInstance()->getDB();
    }
}

if (!function_exists('session')) {
    /**
     * Get session instance
     */
    function session()
    {
        return \App\Core\Application::getInstance()->getSession();
    }
}

if (!function_exists('request')) {
    /**
     * Get request instance
     */
    function request()
    {
        return \App\Core\Application::getInstance()->getRequest();
    }
}

if (!function_exists('auth')) {
    /**
     * Get current authenticated user
     */
    function auth()
    {
        $userId = session()->get('user_id');
        
        if (!$userId) {
            return null;
        }
        
        return db()->selectOne(
            "SELECT u.*, r.name as role_name 
             FROM users u JOIN roles r ON u.role_id = r.id 
             WHERE u.id = ?",
            [$userId]
        );
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old($key, $default = '')
    {
        return session()->flash('old')[$key] ?? $default;
    }
}

if (!function_exists('error')) {
    /**
     * Get validation error
     */
    function error($key)
    {
        $errors = session()->flash('errors') ?? [];
        return $errors[$key][0] ?? null;
    }
}

if (!function_exists('has_error')) {
    /**
     * Check if validation error exists
     */
    function has_error($key)
    {
        $errors = session()->flash('errors') ?? [];
        return isset($errors[$key]);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token
     */
    function csrf_token()
    {
        $token = session()->get('csrf_token');
        
        if (!$token) {
            $token = \App\Helpers\Helper::generateToken();
            session()->set('csrf_token', $token);
        }
        
        return $token;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden field
     */
    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate method spoofing field
     */
    function method_field($method)
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('asset')) {
    /**
     * Get asset URL
     */
    function asset($path)
    {
        return config('app_url') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url($path = '')
    {
        return rtrim(config('app_url'), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    /**
     * Generate route URL
     */
    function route($name, $params = [])
    {
        // Simple route generation
        return url($name);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     */
    function redirect($url = '/')
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back
     */
    function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }
}

if (!function_exists('view')) {
    /**
     * Render view
     */
    function view($view, $data = [])
    {
        $viewInstance = new \App\Core\View();
        $viewInstance->render($view, $data);
    }
}

if (!function_exists('json_response')) {
    /**
     * Send JSON response
     */
    function json_response($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP status code
     */
    function abort($statusCode, $message = '')
    {
        http_response_code($statusCode);
        
        if (request()->wantsJson()) {
            json_response(['error' => $message ?: 'Error ' . $statusCode], $statusCode);
        } else {
            echo "<h1>Error {$statusCode}</h1>";
            if ($message) {
                echo "<p>{$message}</p>";
            }
        }
        exit;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(...$args)
    {
        echo '<pre style="background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.5; direction: ltr;">';
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump without dying
     */
    function dump(...$args)
    {
        echo '<pre style="background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.5; direction: ltr;">';
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
    }
}

if (!function_exists('logger')) {
    /**
     * Log message
     */
    function logger($level = 'info', $message = '', $context = [])
    {
        return app()->getLogger()->$level($message, $context);
    }
}

if (!function_exists('cache')) {
    /**
     * Cache helper
     */
    function cache($key = null, $value = null, $ttl = null)
    {
        $cache = app()->getCache();
        
        if (is_null($key)) {
            return $cache;
        }
        
        if (is_null($value)) {
            return $cache->get($key);
        }
        
        return $cache->set($key, $value, $ttl);
    }
}

if (!function_exists('now')) {
    /**
     * Get current datetime
     */
    function now($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }
}

if (!function_exists('today')) {
    /**
     * Get current date
     */
    function today($format = 'Y-m-d')
    {
        return date($format);
    }
}

if (!function_exists('encrypt')) {
    /**
     * Encrypt value
     */
    function encrypt($value)
    {
        return \App\Core\Security::encrypt($value);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt value
     */
    function decrypt($value)
    {
        return \App\Core\Security::decrypt($value);
    }
}

if (!function_exists('bcrypt')) {
    /**
     * Hash password
     */
    function bcrypt($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

if (!function_exists('trans')) {
    /**
     * Translate text (placeholder)
     */
    function trans($key, $replace = [])
    {
        // Simple translation placeholder
        $translations = [
            'welcome' => 'خوش آمدید',
            'login' => 'ورود',
            'logout' => 'خروج',
            'save' => 'ذخیره',
            'cancel' => 'انصراف',
            'delete' => 'حذف',
            'edit' => 'ویرایش',
            'create' => 'ایجاد',
            'search' => 'جستجو',
            'actions' => 'عملیات',
        ];
        
        $text = $translations[$key] ?? $key;
        
        foreach ($replace as $key => $value) {
            $text = str_replace(':' . $key, $value, $text);
        }
        
        return $text;
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date
     */
    function format_date($date, $format = 'Y/m/d')
    {
        return \App\Helpers\DateHelper::format($date, $format);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency
     */
    function format_currency($amount, $currency = 'تومان')
    {
        return number_format($amount) . ' ' . $currency;
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limit string
     */
    function str_limit($string, $limit = 100, $end = '...')
    {
        return \App\Helpers\StringHelper::limit($string, $limit, $end);
    }
}

if (!function_exists('array_get')) {
    /**
     * Get array value using dot notation
     */
    function array_get($array, $key, $default = null)
    {
        return \App\Helpers\ArrayHelper::get($array, $key, $default);
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Check if current language is RTL
     */
    function is_rtl()
    {
        return config('app_direction', 'rtl') === 'rtl';
    }
}