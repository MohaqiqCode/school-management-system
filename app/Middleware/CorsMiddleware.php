<?php
namespace App\Middleware;

/**
 * CORS Middleware
 * مدیریت Cross-Origin Resource Sharing
 */
class CorsMiddleware extends Middleware
{
    /**
     * Handle CORS
     * 
     * @return bool
     */
    public function handle()
    {
        $corsConfig = $this->config['api']['cors'] ?? [];
        
        if (!($corsConfig['enabled'] ?? false)) {
            return true;
        }
        
        // Get allowed origins
        $allowedOrigins = $corsConfig['allowed_origins'] ?? ['*'];
        
        // Get request origin
        $origin = $this->request->header('Origin');
        
        // Check if origin is allowed
        if ($origin) {
            if (in_array('*', $allowedOrigins)) {
                $this->response->setHeader('Access-Control-Allow-Origin', '*');
            } elseif (in_array($origin, $allowedOrigins)) {
                $this->response->setHeader('Access-Control-Allow-Origin', $origin);
                $this->response->setHeader('Vary', 'Origin');
            } else {
                // Origin not allowed
                if ($this->request->wantsJson()) {
                    $this->response->json([
                        'success' => false,
                        'message' => 'Origin not allowed'
                    ], 403);
                }
                return false;
            }
        }
        
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Methods', 
            implode(', ', $corsConfig['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'])
        );
        
        $this->response->setHeader('Access-Control-Allow-Headers', 
            implode(', ', $corsConfig['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN'])
        );
        
        if (!empty($corsConfig['exposed_headers'])) {
            $this->response->setHeader('Access-Control-Expose-Headers', 
                implode(', ', $corsConfig['exposed_headers'])
            );
        }
        
        $this->response->setHeader('Access-Control-Max-Age', 
            $corsConfig['max_age'] ?? 86400
        );
        
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        
        // Handle preflight OPTIONS request
        if ($this->request->getMethod() === 'OPTIONS') {
            http_response_code(204);
            $this->response->send();
        }
        
        return true;
    }
}