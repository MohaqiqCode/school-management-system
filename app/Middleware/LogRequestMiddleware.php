<?php
namespace App\Middleware;

/**
 * Request Logging Middleware
 * ثبت لاگ تمام درخواست‌ها
 */
class LogRequestMiddleware extends Middleware
{
    /**
     * Handle request logging
     * 
     * @return bool
     */
    public function handle()
    {
        $startTime = microtime(true);
        
        // Register shutdown function to log after response
        register_shutdown_function(function () use ($startTime) {
            $this->logRequest($startTime);
        });
        
        return true;
    }
    
    /**
     * Log the request details
     * 
     * @param float $startTime
     */
    private function logRequest($startTime)
    {
        try {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $logData = [
                'method' => $this->request->getMethod(),
                'uri' => $this->request->getUri(),
                'ip' => $this->getClientIp(),
                'user_agent' => $this->request->getUserAgent(),
                'user_id' => $this->session->get('user_id'),
                'status_code' => http_response_code(),
                'execution_time_ms' => $executionTime,
                'memory_usage' => Helper::formatBytes(memory_get_peak_usage(true)),
                'timestamp' => date('Y-m-d H:i:s'),
            ];
            
            // Don't log certain paths
            $excludedPaths = ['/assets/', '/api/health', '/favicon.ico'];
            foreach ($excludedPaths as $path) {
                if (strpos($logData['uri'], $path) === 0) {
                    return;
                }
            }
            
            // Store in system logs
            $this->db->insert('system_logs', [
                'level' => 'info',
                'module' => 'request',
                'message' => "{$logData['method']} {$logData['uri']} - {$logData['status_code']} - {$executionTime}ms",
                'context' => json_encode($logData, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}