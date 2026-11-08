<?php
namespace App\Middleware;

/**
 * CSRF Protection Middleware
 * محافظت در برابر حملات Cross-Site Request Forgery
 */
class CsrfMiddleware extends Middleware
{
    /**
     * Methods that require CSRF check
     */
    private $protectedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
    
    /**
     * URIs excluded from CSRF check
     */
    private $excludedUris = [
        '/api/*',
        '/webhook/*',
        '/payment/callback/*',
    ];
    
    /**
     * Handle CSRF check
     * 
     * @return bool
     */
    public function handle()
    {
        $method = $this->request->getMethod();
        
        // Only check protected methods
        if (!in_array(strtoupper($method), $this->protectedMethods)) {
            return true;
        }
        
        // Check if URI is excluded
        $uri = $this->request->getUri();
        if ($this->isExcluded($uri)) {
            return true;
        }
        
        // Get token from request
        $token = $this->getTokenFromRequest();
        
        // Get token from session
        $sessionToken = $this->session->get('csrf_token');
        
        // Validate token
        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            // Check token expiry
            $tokenTime = $this->session->get('csrf_token_time', 0);
            $maxAge = $this->config['security']['csrf_token_lifetime'] ?? 7200;
            
            if (time() - $tokenTime > $maxAge) {
                $message = 'توکن امنیتی منقضی شده است. لطفاً صفحه را رفرش کنید.';
            } else {
                $message = 'توکن امنیتی نامعتبر است';
            }
            
            $this->log('csrf_failed', "CSRF validation failed for {$uri}");
            
            if ($this->request->wantsJson()) {
                $this->response->json([
                    'success' => false,
                    'message' => $message
                ], 419);
            } else {
                $this->session->flash('error', $message);
                $this->response->back();
            }
            
            return false;
        }
        
        // Generate new token after successful validation (optional)
        // $this->generateNewToken();
        
        return true;
    }
    
    /**
     * Generate new CSRF token
     */
    public function generateNewToken()
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        $this->session->set('csrf_token_time', time());
        return $token;
    }
    
    /**
     * Get token from request
     * 
     * @return string|null
     */
    private function getTokenFromRequest()
    {
        // Check POST data
        $token = $this->request->post('csrf_token');
        if ($token) {
            return $token;
        }
        
        // Check X-CSRF-TOKEN header
        $token = $this->request->header('X-CSRF-TOKEN');
        if ($token) {
            return $token;
        }
        
        // Check X-XSRF-TOKEN header
        $token = $this->request->header('X-XSRF-TOKEN');
        if ($token) {
            return $token;
        }
        
        return null;
    }
    
    /**
     * Check if URI is excluded from CSRF protection
     * 
     * @param string $uri
     * @return bool
     */
    private function isExcluded($uri)
    {
        foreach ($this->excludedUris as $pattern) {
            $pattern = str_replace('*', '.*', $pattern);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }
        
        return false;
    }
}