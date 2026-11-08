<?php
namespace App\Middleware;

/**
 * XSS Protection Middleware
 * محافظت در برابر حملات Cross-Site Scripting
 */
class XssMiddleware extends Middleware
{
    /**
     * Handle XSS protection
     * 
     * @return bool
     */
    public function handle()
    {
        // Set XSS protection headers
        $this->response->setHeader('X-XSS-Protection', '1; mode=block');
        $this->response->setHeader('X-Content-Type-Options', 'nosniff');
        $this->response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy (if configured)
        $csp = $this->config['security']['csp'] ?? null;
        if ($csp) {
            $this->response->setHeader('Content-Security-Policy', $csp);
        }
        
        // Clean input data
        if ($this->request->getMethod() !== 'GET') {
            $this->cleanInput();
        }
        
        return true;
    }
    
    /**
     * Clean input data from XSS attacks
     */
    private function cleanInput()
    {
        // Clean POST data
        if (!empty($_POST)) {
            $_POST = $this->cleanArray($_POST);
        }
        
        // Clean GET data
        if (!empty($_GET)) {
            $_GET = $this->cleanArray($_GET);
        }
        
        // Clean REQUEST data
        if (!empty($_REQUEST)) {
            $_REQUEST = $this->cleanArray($_REQUEST);
        }
    }
    
    /**
     * Recursively clean array values
     * 
     * @param array $data
     * @return array
     */
    private function cleanArray($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->cleanArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->cleanString($value);
            }
        }
        
        return $data;
    }
    
    /**
     * Clean a string from XSS
     * 
     * @param string $value
     * @return string
     */
    private function cleanString($value)
    {
        // Remove NULL characters
        $value = str_replace(chr(0), '', $value);
        
        // Remove JavaScript event handlers
        $value = preg_replace(
            '/(on\w+\s*=\s*["\'].*?["\'])|(on\w+\s*=\s*\S+)/i',
            '',
            $value
        );
        
        // Remove javascript: protocol
        $value = preg_replace(
            '/javascript\s*:/i',
            'blocked:',
            $value
        );
        
        // Remove script tags
        $value = preg_replace(
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '',
            $value
        );
        
        // Remove inline event handlers
        $value = preg_replace(
            '/<[^>]+\s+on\w+\s*=\s*"[^"]*"[^>]*>/i',
            '',
            $value
        );
        
        return $value;
    }
}