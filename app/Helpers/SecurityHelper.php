<?php
namespace App\Helpers;

/**
 * Security Helper Class
 * توابع کمکی امنیتی
 */
class SecurityHelper
{
    /**
     * Hash password using bcrypt
     */
    public static function hashPassword($password, $cost = 12)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehash
     */
    public static function needsRehash($hash, $cost = 12)
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken($token, $maxAge = 7200)
    {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        if (time() - $_SESSION['csrf_token_time'] > $maxAge) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate JWT token
     */
    public static function generateJwt($payload, $secret, $algorithm = 'HS256')
    {
        $header = [
            'alg' => $algorithm,
            'typ' => 'JWT'
        ];
        
        $segments = [];
        $segments[] = self::base64UrlEncode(json_encode($header));
        $segments[] = self::base64UrlEncode(json_encode($payload));
        
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = self::base64UrlEncode($signature);
        
        return implode('.', $segments);
    }
    
    /**
     * Verify JWT token
     */
    public static function verifyJwt($token, $secret)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        $validSignature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $secret, true)
        );
        
        if (!hash_equals($validSignature, $signature)) {
            return false;
        }
        
        $payload = json_decode(self::base64UrlDecode($payload), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL encode
     */
    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    public static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Encrypt data using AES-256-CBC
     */
    public static function encrypt($data, $key)
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt(
            serialize($data),
            'aes-256-cbc',
            $key,
            0,
            $iv
        );
        
        $hmac = hash_hmac('sha256', $iv . $encrypted, $key, true);
        
        return base64_encode($iv . $hmac . $encrypted);
    }
    
    /**
     * Decrypt data using AES-256-CBC
     */
    public static function decrypt($data, $key)
    {
        $data = base64_decode($data);
        
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $hmac = substr($data, $ivLength, 32);
        $encrypted = substr($data, $ivLength + 32);
        
        $calculatedHmac = hash_hmac('sha256', $iv . $encrypted, $key, true);
        
        if (!hash_equals($hmac, $calculatedHmac)) {
            return null;
        }
        
        return unserialize(
            openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv)
        );
    }
    
    /**
     * XSS clean input
     */
    public static function xssClean($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'xssClean'], $data);
        }
        
        // Remove NULL characters
        $data = str_replace(chr(0), '', $data);
        
        // Fix &entity\n
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
        
        // Remove namespaced elements
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        
        do {
            $oldData = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($oldData !== $data);
        
        return $data;
    }
    
    /**
     * Sanitize filename for security
     */
    public static function sanitizeFilename($filename)
    {
        // Remove directory traversal
        $filename = str_replace(['../', '..\\'], '', $filename);
        
        // Remove null bytes
        $filename = str_replace(chr(0), '', $filename);
        
        // Remove path info
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple dots
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        
        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'file_' . time();
        }
        
        return $filename;
    }
    
    /**
     * Generate secure random string
     */
    public static function randomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Generate secure random number
     */
    public static function randomInt($min, $max)
    {
        return random_int($min, $max);
    }
    
    /**
     * Rate limiting check
     */
    public static function rateLimit($key, $maxAttempts = 5, $decaySeconds = 60)
    {
        $attempts = $_SESSION['rate_limit_' . $key] ?? ['count' => 0, 'time' => 0];
        
        // Reset if decay time passed
        if (time() - $attempts['time'] > $decaySeconds) {
            $attempts = ['count' => 0, 'time' => time()];
        }
        
        $attempts['count']++;
        $_SESSION['rate_limit_' . $key] = $attempts;
        
        return $attempts['count'] <= $maxAttempts;
    }
    
    /**
     * Get remaining attempts
     */
    public static function remainingAttempts($key, $maxAttempts = 5)
    {
        $attempts = $_SESSION['rate_limit_' . $key]['count'] ?? 0;
        return max(0, $maxAttempts - $attempts);
    }
    
    /**
     * Clear rate limit
     */
    public static function clearRateLimit($key)
    {
        unset($_SESSION['rate_limit_' . $key]);
    }
}