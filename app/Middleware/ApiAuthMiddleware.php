<?php
namespace App\Middleware;

/**
 * API Authentication Middleware
 * احراز هویت برای API
 */
class ApiAuthMiddleware extends Middleware
{
    /**
     * Handle API authentication
     * 
     * @return bool
     */
    public function handle()
    {
        // Check for API key in header
        $apiKey = $this->request->header('X-API-Key');
        
        if ($apiKey) {
            return $this->validateApiKey($apiKey);
        }
        
        // Check for Bearer token
        $authHeader = $this->request->header('Authorization');
        
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            return $this->validateBearerToken($token);
        }
        
        // No authentication provided
        $this->response->json([
            'success' => false,
            'message' => 'API authentication required',
            'error_code' => 'AUTH_REQUIRED'
        ], 401);
        
        return false;
    }
    
    /**
     * Validate API key
     * 
     * @param string $apiKey
     * @return bool
     */
    private function validateApiKey($apiKey)
    {
        // Get valid API keys from config
        $validKeys = $this->config['api']['keys'] ?? [];
        
        if (empty($validKeys)) {
            // Check in database
            $keyData = $this->db->selectOne(
                "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1 AND expires_at > NOW()",
                [$apiKey]
            );
            
            if (!$keyData) {
                $this->response->json([
                    'success' => false,
                    'message' => 'Invalid API key',
                    'error_code' => 'INVALID_API_KEY'
                ], 401);
                return false;
            }
            
            // Update last used
            $this->db->update('api_keys', [
                'last_used_at' => date('Y-m-d H:i:s'),
                'request_count' => ($keyData['request_count'] ?? 0) + 1
            ], 'id = ?', [$keyData['id']]);
        } else {
            if (!in_array($apiKey, $validKeys)) {
                $this->response->json([
                    'success' => false,
                    'message' => 'Invalid API key',
                    'error_code' => 'INVALID_API_KEY'
                ], 401);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate Bearer token
     * 
     * @param string $token
     * @return bool
     */
    private function validateBearerToken($token)
    {
        try {
            // Decode JWT
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                throw new \Exception('Invalid token format');
            }
            
            list($header, $payload, $signature) = $parts;
            
            $secret = $this->config['security']['jwt_secret'] ?? 'default-secret';
            
            $validSignature = rtrim(strtr(base64_encode(
                hash_hmac('sha256', "{$header}.{$payload}", $secret, true)
            ), '+/', '-_'), '=');
            
            if (!hash_equals($validSignature, $signature)) {
                throw new \Exception('Invalid signature');
            }
            
            $payload = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
            
            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                $this->response->json([
                    'success' => false,
                    'message' => 'Token expired',
                    'error_code' => 'TOKEN_EXPIRED'
                ], 401);
                return false;
            }
            
            // Check if token is revoked
            $isRevoked = $this->db->selectOne(
                "SELECT id FROM jwt_tokens WHERE token = ? AND is_revoked = 1",
                [$token]
            );
            
            if ($isRevoked) {
                $this->response->json([
                    'success' => false,
                    'message' => 'Token revoked',
                    'error_code' => 'TOKEN_REVOKED'
                ], 401);
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->response->json([
                'success' => false,
                'message' => 'Invalid token: ' . $e->getMessage(),
                'error_code' => 'INVALID_TOKEN'
            ], 401);
            return false;
        }
    }
}