<?php
namespace App\Helpers;

/**
 * Validation Helper Class
 * توابع کمکی اعتبارسنجی
 */
class ValidationHelper
{
    /**
     * Check if value is valid email
     */
    public static function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Check if value is valid URL
     */
    public static function isUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Check if value is valid IP address
     */
    public static function isIp($value, $version = null)
    {
        $flags = 0;
        
        if ($version === 4) {
            $flags = FILTER_FLAG_IPV4;
        } elseif ($version === 6) {
            $flags = FILTER_FLAG_IPV6;
        }
        
        return filter_var($value, FILTER_VALIDATE_IP, $flags) !== false;
    }
    
    /**
     * Check if value is valid MAC address
     */
    public static function isMacAddress($value)
    {
        return filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }
    
    /**
     * Check if value is integer
     */
    public static function isInt($value, $min = null, $max = null)
    {
        $options = [];
        
        if ($min !== null) {
            $options['min_range'] = $min;
        }
        
        if ($max !== null) {
            $options['max_range'] = $max;
        }
        
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => $options]) !== false;
    }
    
    /**
     * Check if value is float
     */
    public static function isFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }
    
    /**
     * Check if value is boolean
     */
    public static function isBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }
    
    /**
     * Check if value is alphabetic
     */
    public static function isAlpha($value)
    {
        return preg_match('/^[\pL\s]+$/u', $value) === 1;
    }
    
    /**
     * Check if value is alphanumeric
     */
    public static function isAlphanumeric($value)
    {
        return preg_match('/^[\pL\pN\s]+$/u', $value) === 1;
    }
    
    /**
     * Check if value is numeric
     */
    public static function isNumeric($value)
    {
        return is_numeric($value);
    }
    
    /**
     * Check if value is digits only
     */
    public static function isDigits($value)
    {
        return ctype_digit((string) $value);
    }
    
    /**
     * Check if value matches pattern
     */
    public static function matches($value, $pattern)
    {
        return preg_match($pattern, $value) === 1;
    }
    
    /**
     * Check if value is in array
     */
    public static function inArray($value, $array)
    {
        return in_array($value, $array, true);
    }
    
    /**
     * Check if value is not in array
     */
    public static function notInArray($value, $array)
    {
        return !in_array($value, $array, true);
    }
    
    /**
     * Check if string length is between
     */
    public static function lengthBetween($value, $min, $max)
    {
        $length = mb_strlen($value);
        return $length >= $min && $length <= $max;
    }
    
    /**
     * Check if string length is min
     */
    public static function minLength($value, $min)
    {
        return mb_strlen($value) >= $min;
    }
    
    /**
     * Check if string length is max
     */
    public static function maxLength($value, $max)
    {
        return mb_strlen($value) <= $max;
    }
    
    /**
     * Check if value is between
     */
    public static function between($value, $min, $max)
    {
        return $value >= $min && $value <= $max;
    }
    
    /**
     * Check if value is min
     */
    public static function min($value, $min)
    {
        return $value >= $min;
    }
    
    /**
     * Check if value is max
     */
    public static function max($value, $max)
    {
        return $value <= $max;
    }
    
    /**
     * Check if date is valid
     */
    public static function isDate($value, $format = 'Y-m-d')
    {
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }
    
    /**
     * Check if date is before
     */
    public static function dateBefore($value, $before, $format = 'Y-m-d')
    {
        $date = \DateTime::createFromFormat($format, $value);
        $beforeDate = \DateTime::createFromFormat($format, $before);
        
        return $date && $beforeDate && $date < $beforeDate;
    }
    
    /**
     * Check if date is after
     */
    public static function dateAfter($value, $after, $format = 'Y-m-d')
    {
        $date = \DateTime::createFromFormat($format, $value);
        $afterDate = \DateTime::createFromFormat($format, $after);
        
        return $date && $afterDate && $date > $afterDate;
    }
    
    /**
     * Check if time is valid
     */
    public static function isTime($value, $format = 'H:i:s')
    {
        $time = \DateTime::createFromFormat($format, $value);
        return $time && $time->format($format) === $value;
    }
    
    /**
     * Check if value is valid JSON
     */
    public static function isJson($value)
    {
        if (!is_string($value)) {
            return false;
        }
        
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Check if value is valid credit card
     */
    public static function isCreditCard($value)
    {
        // Remove non-digits
        $value = preg_replace('/\D/', '', $value);
        
        $length = strlen($value);
        
        if ($length < 13 || $length > 19) {
            return false;
        }
        
        // Luhn algorithm
        $sum = 0;
        $alt = false;
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = intval($value[$i]);
            
            if ($alt) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $alt = !$alt;
        }
        
        return ($sum % 10) === 0;
    }
    
    /**
     * Check if value is valid hex color
     */
    public static function isHexColor($value)
    {
        return preg_match('/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $value) === 1;
    }
    
    /**
     * Check if value is valid UUID
     */
    public static function isUuid($value)
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
    
    /**
     * Check if value is valid file (uploaded)
     */
    public static function isUploadedFile($value, $allowedTypes = [], $maxSize = null)
    {
        if (!isset($value['tmp_name']) || !is_uploaded_file($value['tmp_name'])) {
            return false;
        }
        
        if (!empty($allowedTypes)) {
            $extension = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedTypes)) {
                return false;
            }
        }
        
        if ($maxSize !== null && $value['size'] > $maxSize) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if value is valid phone (Iran)
     */
    public static function isPhone($value)
    {
        return StringHelper::isValidPhone($value);
    }
    
    /**
     * Check if value is valid mobile (Iran)
     */
    public static function isMobile($value)
    {
        return StringHelper::isValidMobile($value);
    }
    
    /**
     * Check if value is valid national code (Iran)
     */
    public static function isNationalCode($value)
    {
        return StringHelper::isValidNationalCode($value);
    }
    
    /**
     * Check if value is valid postal code (Iran)
     */
    public static function isPostalCode($value)
    {
        return preg_match('/^\d{10}$/', preg_replace('/[^0-9]/', '', $value)) === 1;
    }
    
    /**
     * Check if value is valid IBAN (Iran Sheba)
     */
    public static function isSheba($value)
    {
        $value = strtoupper(str_replace(' ', '', $value));
        return preg_match('/^IR\d{24}$/', $value) === 1;
    }
    
    /**
     * Check if required fields are present
     */
    public static function required($data, $fields)
    {
        $missing = [];
        
        foreach ((array) $fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
}