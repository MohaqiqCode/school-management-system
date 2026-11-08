<?php
namespace App\Middleware;

/**
 * Maintenance Mode Middleware
 * مدیریت حالت نگهداری سیستم
 */
class MaintenanceMiddleware extends Middleware
{
    /**
     * Handle maintenance mode
     * 
     * @return bool
     */
    public function handle()
    {
        $maintenanceConfig = $this->config['maintenance'] ?? [];
        
        // Check if maintenance mode is enabled
        if (!($maintenanceConfig['enabled'] ?? false)) {
            return true;
        }
        
        // Check if current IP is in allowed list
        $clientIp = $this->getClientIp();
        $allowedIps = $maintenanceConfig['allowed_ips'] ?? ['127.0.0.1'];
        
        if (in_array($clientIp, $allowedIps)) {
            return true;
        }
        
        // Check if current URL is in allowed list
        $currentUri = $this->request->getUri();
        $allowedUrls = $maintenanceConfig['allowed_urls'] ?? ['/login', '/api/health'];
        
        foreach ($allowedUrls as $url) {
            if (strpos($currentUri, $url) === 0) {
                return true;
            }
        }
        
        // Check if user is super admin
        $user = $this->getCurrentUser();
        if ($user && $user['role_name'] === 'super_admin') {
            return true;
        }
        
        // Show maintenance page
        $message = $maintenanceConfig['message'] ?? 'سیستم در حال بروزرسانی است';
        $retryAfter = $maintenanceConfig['retry_after'] ?? 60;
        
        if ($this->request->wantsJson()) {
            $this->response->setHeader('Retry-After', $retryAfter);
            $this->response->json([
                'success' => false,
                'message' => $message,
                'retry_after' => $retryAfter
            ], 503);
        } else {
            http_response_code(503);
            $this->response->setHeader('Retry-After', $retryAfter);
            
            echo '<!DOCTYPE html>
            <html dir="rtl" lang="fa">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>در حال بروزرسانی - ' . ($this->config['app_name'] ?? '') . '</title>
                <style>
                    body { font-family: Tahoma, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f8f9fc; }
                    .container { text-align: center; padding: 40px; }
                    .icon { font-size: 80px; color: #f6c23e; margin-bottom: 20px; }
                    h1 { color: #5a5c69; font-size: 28px; margin-bottom: 10px; }
                    p { color: #858796; font-size: 16px; }
                    .retry { color: #4e73df; font-size: 14px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="icon">🔧</div>
                    <h1>سیستم در حال بروزرسانی</h1>
                    <p>' . htmlspecialchars($message) . '</p>
                    <p class="retry">لطفاً ' . ceil($retryAfter / 60) . ' دقیقه دیگر مجدداً تلاش کنید.</p>
                </div>
            </body>
            </html>';
        }
        
        return false;
    }
}