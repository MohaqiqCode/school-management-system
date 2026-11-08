<?php
namespace App\Middleware;

/**
 * HTTPS Redirect Middleware
 * هدایت به HTTPS
 */
class HttpsMiddleware extends Middleware
{
    /**
     * Handle HTTPS redirect
     * 
     * @return bool
     */
    public function handle()
    {
        // Check if HTTPS is forced
        $forceHttps = $this->config['app_url_secure'] ?? false;
        
        if (!$forceHttps) {
            return true;
        }
        
        // Check if already HTTPS
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $isForwardedHttps = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        
        if ($isHttps || $isForwardedHttps) {
            // Set HSTS header
            $this->response->setHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
            return true;
        }
        
        // Redirect to HTTPS
        $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
        
        $this->response->redirect($url, 301);
        
        return false;
    }
}