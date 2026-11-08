<?php
namespace App\Helpers;

/**
 * Helper Class
 * توابع کمکی عمومی
 */
class Helper
{
    /**
     * Generate random string
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $max)];
        }
        
        return $randomString;
    }
    
    /**
     * Generate random number
     */
    public static function generateRandomNumber($length = 6)
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return random_int($min, $max);
    }
    
    /**
     * Generate unique code with prefix
     */
    public static function generateCode($prefix = '', $length = 8)
    {
        $prefix = strtoupper($prefix);
        $random = strtoupper(self::generateRandomString($length));
        return $prefix . '_' . $random;
    }
    
    /**
     * Generate unique token
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Generate UUID v4
     */
    public static function generateUuid()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Generate slug from text
     */
    public static function slugify($text)
    {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        
        // Lowercase
        $text = strtolower($text);
        
        return $text ?: 'n-a';
    }
    
    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $suffix = '...')
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Format bytes to human readable
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Format phone number
     */
    public static function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '09') {
            return substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7, 4);
        }
        
        if (strlen($phone) === 10) {
            return substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6, 4);
        }
        
        return $phone;
    }
    
    /**
     * Format national code
     */
    public static function formatNationalCode($code)
    {
        $code = str_pad($code, 10, '0', STR_PAD_LEFT);
        return substr($code, 0, 3) . '-' . substr($code, 3, 6) . '-' . substr($code, 9, 1);
    }
    
    /**
     * Mask sensitive data
     */
    public static function mask($string, $start = 3, $length = 4, $maskChar = '*')
    {
        $mask = str_repeat($maskChar, $length);
        return substr($string, 0, $start) . $mask . substr($string, $start + $length);
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIp()
    {
        $ipAddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ipAddress;
    }
    
    /**
     * Get user agent
     */
    public static function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    /**
     * Check if running from CLI
     */
    public static function isCli()
    {
        return php_sapi_name() === 'cli' || defined('STDIN');
    }
    
    /**
     * Check if request is AJAX
     */
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if request wants JSON
     */
    public static function wantsJson()
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, '/json') !== false || strpos($accept, '+json') !== false;
    }
    
    /**
     * Get environment variable with default
     */
    public static function env($key, $default = null)
    {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
    
    /**
     * Retry a callback with delay
     */
    public static function retry($times, $callback, $sleep = 0)
    {
        $attempts = 0;
        
        beginning:
        $attempts++;
        
        try {
            return $callback($attempts);
        } catch (\Exception $e) {
            if ($attempts >= $times) {
                throw $e;
            }
            
            if ($sleep > 0) {
                usleep($sleep * 1000);
            }
            
            goto beginning;
        }
    }
    
    /**
     * Measure execution time of callback
     */
    public static function benchmark($callback, $iterations = 1)
    {
        $start = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $callback();
        }
        
        $end = microtime(true);
        $totalTime = ($end - $start) * 1000; // Convert to milliseconds
        
        return [
            'total_time_ms' => round($totalTime, 4),
            'average_time_ms' => round($totalTime / $iterations, 4),
            'iterations' => $iterations,
        ];
    }
    
    /**
     * Convert Persian/Arabic numbers to English
     */
    public static function toEnglishNumbers($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        $string = str_replace($persian, $english, $string);
        $string = str_replace($arabic, $english, $string);
        
        return $string;
    }
    
    /**
     * Convert English numbers to Persian
     */
    public static function toPersianNumbers($string)
    {
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        
        return str_replace($english, $persian, $string);
    }
    
    /**
     * Clean Persian text (remove diacritics, standardize)
     */
    public static function cleanPersianText($text)
    {
        // Replace Arabic characters with Persian
        $text = str_replace('ي', 'ی', $text);
        $text = str_replace('ك', 'ک', $text);
        $text = str_replace('ة', 'ه', $text);
        $text = str_replace('ؤ', 'و', $text);
        $text = str_replace('ئ', 'ی', $text);
        
        // Remove diacritics
        $text = preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);
        
        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Get file extension
     */
    public static function getFileExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Get MIME type from extension
     */
    public static function getMimeType($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    
    /**
     * Check if value is empty (including whitespace)
     */
    public static function isEmpty($value)
    {
        return $value === null || $value === '' || (is_string($value) && trim($value) === '');
    }
    
    /**
     * Get first non-empty value
     */
    public static function coalesce(...$values)
    {
        foreach ($values as $value) {
            if (!self::isEmpty($value)) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Safe divide (avoid division by zero)
     */
    public static function safeDivide($numerator, $denominator, $default = 0)
    {
        return $denominator != 0 ? $numerator / $denominator : $default;
    }
}