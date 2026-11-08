<?php
namespace App\Helpers;

/**
 * Math Helper Class
 * توابع کمکی ریاضی
 */
class MathHelper
{
    /**
     * Calculate percentage
     */
    public static function percentage($value, $total, $decimals = 2)
    {
        if ($total == 0) {
            return 0;
        }
        
        return round(($value / $total) * 100, $decimals);
    }
    
    /**
     * Calculate percentage value
     */
    public static function percentageOf($percentage, $total)
    {
        return ($percentage / 100) * $total;
    }
    
    /**
     * Calculate average
     */
    public static function average(...$values)
    {
        $values = is_array($values[0] ?? null) ? $values[0] : $values;
        $values = array_filter($values, 'is_numeric');
        
        if (empty($values)) {
            return 0;
        }
        
        return array_sum($values) / count($values);
    }
    
    /**
     * Calculate median
     */
    public static function median(...$values)
    {
        $values = is_array($values[0] ?? null) ? $values[0] : $values;
        $values = array_filter($values, 'is_numeric');
        
        if (empty($values)) {
            return 0;
        }
        
        sort($values);
        $count = count($values);
        $middle = floor(($count - 1) / 2);
        
        if ($count % 2) {
            return $values[$middle];
        }
        
        return ($values[$middle] + $values[$middle + 1]) / 2;
    }
    
    /**
     * Calculate mode
     */
    public static function mode(...$values)
    {
        $values = is_array($values[0] ?? null) ? $values[0] : $values;
        $values = array_filter($values, 'is_numeric');
        
        if (empty($values)) {
            return 0;
        }
        
        $counts = array_count_values($values);
        arsort($counts);
        
        return key($counts);
    }
    
    /**
     * Calculate range
     */
    public static function range(...$values)
    {
        $values = is_array($values[0] ?? null) ? $values[0] : $values;
        $values = array_filter($values, 'is_numeric');
        
        if (empty($values)) {
            return 0;
        }
        
        return max($values) - min($values);
    }
    
    /**
     * Calculate standard deviation
     */
    public static function standardDeviation(...$values)
    {
        $values = is_array($values[0] ?? null) ? $values[0] : $values;
        $values = array_filter($values, 'is_numeric');
        
        if (count($values) < 2) {
            return 0;
        }
        
        $mean = self::average($values);
        $sum = 0;
        
        foreach ($values as $value) {
            $sum += pow($value - $mean, 2);
        }
        
        return sqrt($sum / (count($values) - 1));
    }
    
    /**
     * Calculate variance
     */
    public static function variance(...$values)
    {
        $values = is_array($values[0] ?? null) ? $values[0] : $values;
        $values = array_filter($values, 'is_numeric');
        
        if (count($values) < 2) {
            return 0;
        }
        
        $mean = self::average($values);
        $sum = 0;
        
        foreach ($values as $value) {
            $sum += pow($value - $mean, 2);
        }
        
        return $sum / (count($values) - 1);
    }
    
    /**
     * Round to nearest
     */
    public static function roundTo($value, $nearest = 1)
    {
        return round($value / $nearest) * $nearest;
    }
    
    /**
     * Round up to nearest
     */
    public static function ceilTo($value, $nearest = 1)
    {
        return ceil($value / $nearest) * $nearest;
    }
    
    /**
     * Round down to nearest
     */
    public static function floorTo($value, $nearest = 1)
    {
        return floor($value / $nearest) * $nearest;
    }
    
    /**
     * Clamp value between min and max
     */
    public static function clamp($value, $min, $max)
    {
        return max($min, min($max, $value));
    }
    
    /**
     * Linear interpolation
     */
    public static function lerp($a, $b, $t)
    {
        return $a + ($b - $a) * self::clamp($t, 0, 1);
    }
    
    /**
     * Map value from one range to another
     */
    public static function map($value, $fromMin, $fromMax, $toMin, $toMax)
    {
        if ($fromMax == $fromMin) {
            return $toMin;
        }
        
        $normalized = ($value - $fromMin) / ($fromMax - $fromMin);
        return $toMin + $normalized * ($toMax - $toMin);
    }
    
    /**
     * Calculate factorial
     */
    public static function factorial($n)
    {
        if ($n < 0) {
            return 0;
        }
        
        if ($n <= 1) {
            return 1;
        }
        
        $result = 1;
        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }
        
        return $result;
    }
    
    /**
     * Calculate GCD (Greatest Common Divisor)
     */
    public static function gcd($a, $b)
    {
        $a = abs($a);
        $b = abs($b);
        
        while ($b != 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }
        
        return $a;
    }
    
    /**
     * Calculate LCM (Least Common Multiple)
     */
    public static function lcm($a, $b)
    {
        return abs($a * $b) / self::gcd($a, $b);
    }
    
    /**
     * Check if number is prime
     */
    public static function isPrime($n)
    {
        if ($n <= 1) return false;
        if ($n <= 3) return true;
        if ($n % 2 == 0 || $n % 3 == 0) return false;
        
        for ($i = 5; $i * $i <= $n; $i += 6) {
            if ($n % $i == 0 || $n % ($i + 2) == 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Format number with ordinal suffix
     */
    public static function ordinal($number)
    {
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
        
        return $number . $suffixes[$number % 10];
    }
    
    /**
     * Convert number to words (English, up to millions)
     */
    public static function numberToWords($number)
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        if ($number == 0) {
            return 'Zero';
        }
        
        if ($number < 20) {
            return $ones[$number];
        }
        
        if ($number < 100) {
            return $tens[floor($number / 10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
        }
        
        if ($number < 1000) {
            return $ones[floor($number / 100)] . ' Hundred' . ($number % 100 ? ' and ' . self::numberToWords($number % 100) : '');
        }
        
        if ($number < 1000000) {
            return self::numberToWords(floor($number / 1000)) . ' Thousand' . ($number % 1000 ? ' ' . self::numberToWords($number % 1000) : '');
        }
        
        return self::numberToWords(floor($number / 1000000)) . ' Million' . ($number % 1000000 ? ' ' . self::numberToWords($number % 1000000) : '');
    }
}