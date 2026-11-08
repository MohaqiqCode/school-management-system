<?php
namespace App\Core;

class Security
{
    public static function hash($value, $options = [])
    {
        $cost = $options['cost'] ?? 12;
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
    
    public static function verify($value, $hash)
    {
        return password_verify($value, $hash);
    }
    
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
    
    public static function generateRandomString($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $randomString;
    }
    
    public static function generateRandomNumber($length = 6)
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return random_int($min, $max);
    }
    
    public static function encrypt($value, $key = null)
    {
        $key = $key ?? $_ENV['APP_KEY'] ?? 'default_encryption_key_change_this';
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public static function decrypt($encrypted, $key = null)
    {
        $key = $key ?? $_ENV['APP_KEY'] ?? 'default_encryption_key_change_this';
        $data = base64_decode($encrypted);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
    
    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $data;
    }
    
    public static function sanitizeOutput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeOutput'], $data);
        }
        
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    public static function generateCsrfToken()
    {
        $token = self::generateToken();
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    public static function validateCsrfToken($token = null)
    {
        $token = $token ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (!$token || !$sessionToken) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    public static function generatePassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $all = $uppercase . $lowercase . $numbers . $special;
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }
        
        return str_shuffle($password);
    }
    
    public static function isValidPassword($password)
    {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }
        
        // At least one uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // At least one lowercase
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    public static function rateLimit($key, $maxAttempts = 5, $decaySeconds = 60)
    {
        $sessionKey = 'rate_limit_' . $key;
        $attempts = $_SESSION[$sessionKey] ?? ['count' => 0, 'time' => time()];
        
        if (time() - $attempts['time'] > $decaySeconds) {
            $attempts = ['count' => 0, 'time' => time()];
        }
        
        $attempts['count']++;
        $_SESSION[$sessionKey] = $attempts;
        
        return $attempts['count'] <= $maxAttempts;
    }
}