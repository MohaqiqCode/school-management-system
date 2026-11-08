<?php
/**
 * School Management System - Front Controller
 * نقطه ورود اصلی برنامه
 * 
 * تمام درخواست‌ها از طریق این فایل پردازش می‌شوند
 */

// ============================================
// Define Application Start Time
// ============================================
define('APP_START', microtime(true));

// ============================================
// Define Base Path
// ============================================
define('BASE_PATH', dirname(__DIR__));

// ============================================
// Error Reporting
// ============================================
// در محیط production خطاها نمایش داده نمی‌شوند
// تنظیمات دقیق پس از لود config اعمال می‌شود
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php_errors.log');

// ============================================
// Autoloader
// ============================================
// ابتدا autoloader خودمان
require BASE_PATH . '/app/Core/Autoloader.php';

$autoloader = new App\Core\Autoloader();
$autoloader->register();

// سپس Composer autoloader (اگر موجود باشد)
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
}

// ============================================
// Load Environment Variables
// ============================================
if (file_exists(BASE_PATH . '/.env')) {
    $envFile = file_get_contents(BASE_PATH . '/.env');
    $lines = explode("\n", $envFile);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            if (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            
            // Set environment variable
            if (!isset($_ENV[$key])) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
    }
}

// ============================================
// Load Configuration
// ============================================
$appConfig = require BASE_PATH . '/config/app.php';

// Set timezone
date_default_timezone_set($appConfig['app_timezone'] ?? 'Asia/Tehran');

// Set error reporting based on debug mode
$debug = env('APP_DEBUG', $appConfig['app_debug'] ?? false);
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ============================================
// Check Maintenance Mode
// ============================================
$maintenanceFile = BASE_PATH . '/storage/maintenance.lock';
if (file_exists($maintenanceFile)) {
    $maintenanceConfig = $appConfig['maintenance'] ?? [];
    $allowedIps = $maintenanceConfig['allowed_ips'] ?? ['127.0.0.1'];
    $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    if (!in_array($clientIp, $allowedIps)) {
        $retryAfter = $maintenanceConfig['retry_after'] ?? 60;
        $message = $maintenanceConfig['message'] ?? 'سیستم در حال بروزرسانی است. لطفاً چند دقیقه دیگر مراجعه کنید.';
        
        http_response_code(503);
        header("Retry-After: {$retryAfter}");
        
        // Check if request wants JSON
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($acceptHeader, 'application/json') !== false) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => $message,
                'retry_after' => $retryAfter
            ], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/html; charset=utf-8');
            ?>
            <!DOCTYPE html>
            <html lang="fa" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>در حال بروزرسانی - <?= htmlspecialchars($appConfig['app_name'] ?? '') ?></title>
                <style>
                    *{margin:0;padding:0;box-sizing:border-box}
                    body{font-family:Tahoma,sans-serif;background:#f8f9fc;display:flex;align-items:center;justify-content:center;min-height:100vh}
                    .container{text-align:center;padding:40px;background:white;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,0.1);max-width:500px;width:90%}
                    .icon{font-size:80px;margin-bottom:20px}
                    h1{color:#5a5c69;font-size:24px;margin-bottom:10px}
                    p{color:#858796;font-size:16px;line-height:1.8;margin-bottom:20px}
                    .retry{color:#4e73df;font-size:14px;margin-top:10px}
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="icon">🔧</div>
                    <h1>سیستم در حال بروزرسانی</h1>
                    <p><?= htmlspecialchars($message) ?></p>
                    <p class="retry">لطفاً <?= ceil($retryAfter / 60) ?> دقیقه دیگر مجدداً تلاش کنید</p>
                </div>
            </body>
            </html>
            <?php
        }
        exit;
    }
}

// ============================================
// Create Application Instance
// ============================================
try {
    // Initialize the application
    $app = App\Core\Application::getInstance();
    
    // Load routes
    require BASE_PATH . '/routes/web.php';
    
    // Load API routes
    if (file_exists(BASE_PATH . '/routes/api.php')) {
        require BASE_PATH . '/routes/api.php';
    }
    
    // Run the application
    $app->run();
    
} catch (App\Core\Exception\HttpException $e) {
    // Handle HTTP exceptions
    $statusCode = $e->getStatusCode();
    http_response_code($statusCode);
    
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'code' => $statusCode
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Try to load custom error view
        $errorView = BASE_PATH . "/app/Views/errors/{$statusCode}.php";
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            ?>
            <!DOCTYPE html>
            <html lang="fa" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>Error <?= $statusCode ?></title>
                <style>
                    body{font-family:Tahoma,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8f9fc}
                    .error{text-align:center}
                    h1{font-size:100px;color:#e74a3b;margin:0}
                    p{color:#5a5c69;font-size:18px}
                </style>
            </head>
            <body>
                <div class="error">
                    <h1><?= $statusCode ?></h1>
                    <p><?= htmlspecialchars($e->getMessage()) ?></p>
                </div>
            </body>
            </html>
            <?php
        }
    }
    
} catch (App\Core\Exception\AuthenticationException $e) {
    // Handle authentication exceptions
    http_response_code(401);
    
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ' . ($appConfig['app_url'] ?? '/') . '/login');
    }
    
} catch (App\Core\Exception\ValidationException $e) {
    // Handle validation exceptions
    http_response_code(422);
    
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => $e->getErrors()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Redirect back with errors
        $session = $app->getSession();
        $session->flash('errors', $e->getErrors());
        $session->flash('old', $_POST);
        
        $referer = $_SERVER['HTTP_REFERER'] ?? ($appConfig['app_url'] ?? '/');
        header('Location: ' . $referer);
    }
    
} catch (App\Core\Exception\DatabaseException $e) {
    // Handle database exceptions
    http_response_code(500);
    
    if ($debug) {
        throw $e; // Show full error in debug mode
    }
    
    // Log the error
    if (isset($app)) {
        $app->getLogger()->error('Database Error: ' . $e->getMessage(), [
            'query' => $e->getQuery(),
            'params' => $e->getParams(),
            'url' => $_SERVER['REQUEST_URI'] ?? ''
        ]);
    }
    
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'خطای پایگاه داده رخ داده است. لطفاً بعداً تلاش کنید.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Show friendly error page
        $errorView = BASE_PATH . '/app/Views/errors/500.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '<h1>500 - Internal Server Error</h1>';
            echo '<p>متأسفانه مشکلی در سیستم رخ داده است. لطفاً بعداً تلاش کنید.</p>';
        }
    }
    
} catch (\Throwable $e) {
    // Catch any other unhandled exceptions
    http_response_code(500);
    
    if ($debug) {
        // Show full error details in debug mode
        echo '<!DOCTYPE html>';
        echo '<html dir="ltr"><head><meta charset="UTF-8"><title>Error</title>';
        echo '<style>body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px}';
        echo '.error{background:#2d2d2d;padding:20px;border-radius:8px;border-left:4px solid #e74a3b}';
        echo 'h1{color:#e74a3b}pre{overflow-x:auto;font-size:13px}';
        echo '.file{color:#569cd6}.line{color:#ce9178}</style>';
        echo '</head><body>';
        echo '<div class="error">';
        echo '<h1>' . get_class($e) . '</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><span class="file">File:</span> ' . htmlspecialchars($e->getFile()) . ' <span class="line">Line:</span> ' . $e->getLine() . '</p>';
        echo '<h3>Stack Trace:</h3>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
        echo '</body></html>';
    } else {
        // Log the error
        if (isset($app)) {
            $app->getLogger()->critical($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
        }
        
        // Show friendly error
        if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'خطای غیرمنتظره رخ داده است'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $errorView = BASE_PATH . '/app/Views/errors/500.php';
            if (file_exists($errorView)) {
                require $errorView;
            } else {
                echo '<h1>500 - Internal Server Error</h1>';
            }
        }
    }
}

// ============================================
// Log Request Execution Time (in development)
// ============================================
if ($debug && isset($app)) {
    $executionTime = round((microtime(true) - APP_START) * 1000, 2);
    $memoryUsage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    
    // Add as HTTP header (can be viewed in browser dev tools)
    if (!headers_sent()) {
        header("X-Execution-Time: {$executionTime}ms");
        header("X-Memory-Usage: {$memoryUsage}MB");
    }
}