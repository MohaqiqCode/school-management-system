<?php
namespace App\Helpers;

/**
 * String Helper Class
 * توابع کمکی برای رشته‌ها
 */
class StringHelper
{
    /**
     * Check if string starts with
     */
    public static function startsWith($haystack, $needle)
    {
        return mb_substr($haystack, 0, mb_strlen($needle)) === $needle;
    }
    
    /**
     * Check if string ends with
     */
    public static function endsWith($haystack, $needle)
    {
        return mb_substr($haystack, -mb_strlen($needle)) === $needle;
    }
    
    /**
     * Check if string contains
     */
    public static function contains($haystack, $needle)
    {
        return mb_strpos($haystack, $needle) !== false;
    }
    
    /**
     * Get string after a substring
     */
    public static function after($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }
        
        $position = mb_strpos($subject, $search);
        
        if ($position === false) {
            return $subject;
        }
        
        return mb_substr($subject, $position + mb_strlen($search));
    }
    
    /**
     * Get string before a substring
     */
    public static function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }
        
        $position = mb_strpos($subject, $search);
        
        if ($position === false) {
            return $subject;
        }
        
        return mb_substr($subject, 0, $position);
    }
    
    /**
     * Get string between two substrings
     */
    public static function between($subject, $start, $end)
    {
        return self::before(self::after($subject, $start), $end);
    }
    
    /**
     * Convert to camelCase
     */
    public static function camelCase($string)
    {
        return lcfirst(self::studlyCase($string));
    }
    
    /**
     * Convert to StudlyCase/PascalCase
     */
    public static function studlyCase($string)
    {
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        return str_replace(' ', '', $string);
    }
    
    /**
     * Convert to snake_case
     */
    public static function snakeCase($string, $delimiter = '_')
    {
        $string = preg_replace('/\s+/u', '', $string);
        $string = preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $string);
        return mb_strtolower($string);
    }
    
    /**
     * Convert to kebab-case
     */
    public static function kebabCase($string)
    {
        return self::snakeCase($string, '-');
    }
    
    /**
     * Convert to Title Case
     */
    public static function titleCase($string)
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }
    
    /**
     * Limit string length
     */
    public static function limit($string, $limit = 100, $end = '...')
    {
        if (mb_strwidth($string, 'UTF-8') <= $limit) {
            return $string;
        }
        
        return rtrim(mb_strimwidth($string, 0, $limit, '', 'UTF-8')) . $end;
    }
    
    /**
     * Limit words
     */
    public static function words($string, $words = 100, $end = '...')
    {
        $matches = preg_split('/\s+/u', $string, $words + 1);
        
        if (count($matches) > $words) {
            array_pop($matches);
            return implode(' ', $matches) . $end;
        }
        
        return $string;
    }
    
    /**
     * Strip HTML tags
     */
    public static function stripTags($string, $allowedTags = '')
    {
        return strip_tags($string, $allowedTags);
    }
    
    /**
     * Remove extra whitespace
     */
    public static function removeExtraSpaces($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }
    
    /**
     * Extract numbers from string
     */
    public static function extractNumbers($string)
    {
        preg_match_all('/\d+/', $string, $matches);
        return implode('', $matches[0] ?? []);
    }
    
    /**
     * Check if string is valid JSON
     */
    public static function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Check if string is valid email
     */
    public static function isEmail($string)
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Check if string is valid URL
     */
    public static function isUrl($string)
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Check if string is valid IP
     */
    public static function isIp($string)
    {
        return filter_var($string, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * Check if string is valid national code (Iran)
     */
    public static function isValidNationalCode($code)
    {
        $code = str_pad(preg_replace('/[^0-9]/', '', $code), 10, '0', STR_PAD_LEFT);
        
        if (strlen($code) !== 10 || preg_match('/^(\d)\1{9}$/', $code)) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($code[$i]) * (10 - $i);
        }
        
        $remainder = $sum % 11;
        $controlDigit = intval($code[9]);
        
        return ($remainder < 2 && $controlDigit === $remainder) || 
               ($remainder >= 2 && $controlDigit === 11 - $remainder);
    }
    
    /**
     * Check if string is valid mobile number (Iran)
     */
    public static function isValidMobile($mobile)
    {
        return preg_match('/^09\d{9}$/', preg_replace('/[^0-9]/', '', $mobile)) === 1;
    }
    
    /**
     * Check if string is valid phone number (Iran)
     */
    public static function isValidPhone($phone)
    {
        return preg_match('/^0[1-9]\d{9}$/', preg_replace('/[^0-9]/', '', $phone)) === 1;
    }
    
    /**
     * Generate random password
     */
    public static function randomPassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $all = $uppercase . $lowercase . $numbers . $symbols;
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }
        
        return str_shuffle($password);
    }
    
    /**
     * Highlight search term in text
     */
    public static function highlight($text, $search, $tag = 'mark')
    {
        if (empty($search)) {
            return $text;
        }
        
        return preg_replace('/(' . preg_quote($search, '/') . ')/iu', "<{$tag}>$1</{$tag}>", $text);
    }
    
    /**
     * Convert line breaks to paragraphs
     */
    public static function nl2p($text)
    {
        $paragraphs = explode("\n\n", str_replace(["\r\n", "\r"], "\n", $text));
        
        $output = '';
        foreach ($paragraphs as $paragraph) {
            if (trim($paragraph) !== '') {
                $output .= '<p>' . trim($paragraph) . '</p>';
            }
        }
        
        return $output;
    }
    
    /**
     * Obfuscate email
     */
    public static function obfuscateEmail($email)
    {
        $parts = explode('@', $email);
        
        if (count($parts) !== 2) {
            return $email;
        }
        
        $name = $parts[0];
        $domain = $parts[1];
        
        $nameLength = strlen($name);
        $visibleChars = max(1, ceil($nameLength / 4));
        
        $obfuscated = substr($name, 0, $visibleChars) . str_repeat('*', $nameLength - $visibleChars);
        
        return $obfuscated . '@' . $domain;
    }
    
    /**
     * Pluralize word (simple English)
     */
    public static function pluralize($word, $count = 2)
    {
        if ($count === 1) {
            return $word;
        }
        
        $last = substr($word, -1);
        $lastTwo = substr($word, -2);
        
        if (in_array($lastTwo, ['ch', 'sh', 'ss', 'x', 'zz'])) {
            return $word . 'es';
        }
        
        if ($last === 'y' && !in_array(substr($word, -2, 1), ['a', 'e', 'i', 'o', 'u'])) {
            return substr($word, 0, -1) . 'ies';
        }
        
        if ($last === 's' || $last === 'x' || $last === 'z') {
            return $word . 'es';
        }
        
        return $word . 's';
    }
}