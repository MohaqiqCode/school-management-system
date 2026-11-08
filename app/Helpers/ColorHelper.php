<?php
namespace App\Helpers;

/**
 * Color Helper Class
 * توابع کمکی برای مدیریت رنگ‌ها
 */
class ColorHelper
{
    /**
     * Convert hex to RGB
     */
    public static function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
    
    /**
     * Convert RGB to hex
     */
    public static function rgbToHex($r, $g, $b)
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * Convert hex to HSL
     */
    public static function hexToHsl($hex)
    {
        $rgb = self::hexToRgb($hex);
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        
        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            
            $h /= 6;
        }
        
        return [
            'h' => round($h * 360),
            's' => round($s * 100),
            'l' => round($l * 100),
        ];
    }
    
    /**
     * Convert HSL to hex
     */
    public static function hslToHex($h, $s, $l)
    {
        $h /= 360;
        $s /= 100;
        $l /= 100;
        
        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            
            $r = self::hueToRgb($p, $q, $h + 1/3);
            $g = self::hueToRgb($p, $q, $h);
            $b = self::hueToRgb($p, $q, $h - 1/3);
        }
        
        return self::rgbToHex(round($r * 255), round($g * 255), round($b * 255));
    }
    
    private static function hueToRgb($p, $q, $t)
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        
        return $p;
    }
    
    /**
     * Lighten a hex color
     */
    public static function lighten($hex, $amount = 10)
    {
        $hsl = self::hexToHsl($hex);
        $hsl['l'] = min(100, $hsl['l'] + $amount);
        return self::hslToHex($hsl['h'], $hsl['s'], $hsl['l']);
    }
    
    /**
     * Darken a hex color
     */
    public static function darken($hex, $amount = 10)
    {
        $hsl = self::hexToHsl($hex);
        $hsl['l'] = max(0, $hsl['l'] - $amount);
        return self::hslToHex($hsl['h'], $hsl['s'], $hsl['l']);
    }
    
    /**
     * Saturate a hex color
     */
    public static function saturate($hex, $amount = 10)
    {
        $hsl = self::hexToHsl($hex);
        $hsl['s'] = min(100, $hsl['s'] + $amount);
        return self::hslToHex($hsl['h'], $hsl['s'], $hsl['l']);
    }
    
    /**
     * Desaturate a hex color
     */
    public static function desaturate($hex, $amount = 10)
    {
        $hsl = self::hexToHsl($hex);
        $hsl['s'] = max(0, $hsl['s'] - $amount);
        return self::hslToHex($hsl['h'], $hsl['s'], $hsl['l']);
    }
    
    /**
     * Get contrasting text color (black or white) for background
     */
    public static function getContrastColor($hex)
    {
        $rgb = self::hexToRgb($hex);
        $luminance = (0.299 * $rgb['r'] + 0.587 * $rgb['g'] + 0.114 * $rgb['b']) / 255;
        
        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }
    
    /**
     * Generate a random hex color
     */
    public static function random()
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate a random pastel color
     */
    public static function randomPastel()
    {
        $h = mt_rand(0, 360);
        $s = mt_rand(25, 50);
        $l = mt_rand(70, 90);
        
        return self::hslToHex($h, $s, $l);
    }
    
    /**
     * Generate a color palette
     */
    public static function palette($baseColor, $count = 5)
    {
        $hsl = self::hexToHsl($baseColor);
        $colors = [];
        
        for ($i = 0; $i < $count; $i++) {
            $h = ($hsl['h'] + ($i * 360 / $count)) % 360;
            $colors[] = self::hslToHex($h, $hsl['s'], $hsl['l']);
        }
        
        return $colors;
    }
    
    /**
     * Generate gradient colors
     */
    public static function gradient($from, $to, $steps = 5)
    {
        $fromRgb = self::hexToRgb($from);
        $toRgb = self::hexToRgb($to);
        $colors = [];
        
        for ($i = 0; $i < $steps; $i++) {
            $t = $i / ($steps - 1);
            $r = round($fromRgb['r'] + ($toRgb['r'] - $fromRgb['r']) * $t);
            $g = round($fromRgb['g'] + ($toRgb['g'] - $fromRgb['g']) * $t);
            $b = round($fromRgb['b'] + ($toRgb['b'] - $fromRgb['b']) * $t);
            $colors[] = self::rgbToHex($r, $g, $b);
        }
        
        return $colors;
    }
    
    /**
     * Check if color is valid hex
     */
    public static function isValidHex($hex)
    {
        return preg_match('/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $hex) === 1;
    }
    
    /**
     * Get predefined color for status
     */
    public static function statusColor($status)
    {
        $colors = [
            'active' => '#1cc88a',
            'inactive' => '#858796',
            'pending' => '#f6c23e',
            'approved' => '#36b9cc',
            'rejected' => '#e74a3b',
            'paid' => '#1cc88a',
            'unpaid' => '#e74a3b',
            'present' => '#1cc88a',
            'absent' => '#e74a3b',
            'late' => '#f6c23e',
            'completed' => '#1cc88a',
            'cancelled' => '#e74a3b',
        ];
        
        return $colors[$status] ?? '#858796';
    }
    
    /**
     * Get Bootstrap color classes
     */
    public static function bootstrapColors()
    {
        return [
            'primary' => '#4e73df',
            'secondary' => '#858796',
            'success' => '#1cc88a',
            'danger' => '#e74a3b',
            'warning' => '#f6c23e',
            'info' => '#36b9cc',
            'light' => '#f8f9fc',
            'dark' => '#5a5c69',
        ];
    }
}