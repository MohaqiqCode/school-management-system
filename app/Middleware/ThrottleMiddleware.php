<?php
namespace App\Middleware;

/**
 * Throttle/Rate Limiting Middleware
 * محدود کردن تعداد درخواست‌ها
 * 
 * Usage: Throttle:60,1 (60 requests per minute)
 * Usage: Throttle:100,1 (100 requests per minute)
 */
class ThrottleMiddleware extends Middleware
{
    /**
     * Handle rate limiting
     * 
     * @param int $maxAttempts Maximum number of attempts
     * @param int $decayMinutes Minutes until reset
     * @return bool
     */
    public function handle($maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->getRateLimitKey();
        $maxAttempts = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;
        
        // Get current attempts
        $attempts = $this->getAttempts($key);
        
        // Check if over limit
        if ($attempts['count'] >= $maxAttempts) {
            $retryAfter = ($attempts['time'] + ($decayMinutes * 60)) - time();
            
            if ($retryAfter > 0) {
                $this->log('rate_limit_exceeded', 
                    "Rate limit exceeded for {$key}: {$attempts['count']}/{$maxAttempts}"
                );
                
                if ($this->request->wantsJson()) {
                    $this->response->setHeader('Retry-After', $retryAfter);
                    $this->response->setHeader('X-RateLimit-Limit', $maxAttempts);
                    $this->response->setHeader('X-RateLimit-Remaining', 0);
                    $this->response->json([
                        'success' => false,
                        'message' => 'تعداد درخواست‌ها بیش از حد مجاز است. لطفاً ' . ceil($retryAfter / 60) . ' دقیقه دیگر تلاش کنید.',
                        'retry_after' => $retryAfter
                    ], 429);
                } else {
                    $this->response->setHeader('Retry-After', $retryAfter);
                    http_response_code(429);
                    echo '<h1>429 - Too Many Requests</h1>';
                    echo '<p>تعداد درخواست‌ها بیش از حد مجاز است.</p>';
                    echo '<p>لطفاً ' . ceil($retryAfter / 60) . ' دقیقه دیگر تلاش کنید.</p>';
                }
                
                return false;
            }
            
            // Reset if decay time passed
            $this->resetAttempts($key);
        }
        
        // Increment attempts
        $this->incrementAttempts($key);
        
        // Add rate limit headers
        $remaining = $maxAttempts - ($attempts['count'] + 1);
        $this->response->setHeader('X-RateLimit-Limit', $maxAttempts);
        $this->response->setHeader('X-RateLimit-Remaining', max(0, $remaining));
        
        return true;
    }
    
    /**
     * Get rate limit key
     * 
     * @return string
     */
    private function getRateLimitKey()
    {
        $ip = $this->getClientIp();
        $route = $this->request->getUri();
        $userId = $this->session->get('user_id', 'guest');
        
        return 'rate_limit:' . md5($ip . '|' . $route . '|' . $userId);
    }
    
    /**
     * Get current attempts
     * 
     * @param string $key
     * @return array
     */
    private function getAttempts($key)
    {
        $data = $this->session->get($key);
        
        if (!$data || !is_array($data)) {
            return ['count' => 0, 'time' => time()];
        }
        
        return $data;
    }
    
    /**
     * Increment attempts
     * 
     * @param string $key
     */
    private function incrementAttempts($key)
    {
        $attempts = $this->getAttempts($key);
        $attempts['count']++;
        $this->session->set($key, $attempts);
    }
    
    /**
     * Reset attempts
     * 
     * @param string $key
     */
    private function resetAttempts($key)
    {
        $this->session->set($key, ['count' => 0, 'time' => time()]);
    }
}