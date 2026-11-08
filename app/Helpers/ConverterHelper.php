<?php
namespace App\Helpers;

/**
 * Converter Helper Class
 * توابع کمکی برای تبدیل واحدها
 */
class ConverterHelper
{
    /**
     * Convert bytes to human readable
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function bytesToHuman($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Convert seconds to human readable duration
     * 
     * @param int $seconds
     * @return string
     */
    public static function secondsToHuman($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . ' روز';
        if ($hours > 0) $parts[] = $hours . ' ساعت';
        if ($minutes > 0) $parts[] = $minutes . ' دقیقه';
        if ($secs > 0 && empty($parts)) $parts[] = $secs . ' ثانیه';

        return implode(' و ', $parts);
    }

    /**
     * Convert minutes to human readable
     * 
     * @param int $minutes
     * @return string
     */
    public static function minutesToHuman($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($hours > 0) $parts[] = $hours . ' ساعت';
        if ($mins > 0) $parts[] = $mins . ' دقیقه';

        return implode(' و ', $parts);
    }

    /**
     * Convert Persian numbers to English
     * 
     * @param string $text
     * @return string
     */
    public static function persianToEnglishNumbers($text)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($persian, $english, $text);
    }

    /**
     * Convert English numbers to Persian
     * 
     * @param string $text
     * @return string
     */
    public static function englishToPersianNumbers($text)
    {
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        return str_replace($english, $persian, $text);
    }

    /**
     * Convert Arabic characters to Persian
     * 
     * @param string $text
     * @return string
     */
    public static function arabicToPersian($text)
    {
        $arabic = ['ي', 'ك', 'ة', 'ؤ', 'ئ', 'ء', 'أ', 'إ', 'آ', 'ٱ'];
        $persian = ['ی', 'ک', 'ه', 'و', 'ی', '', 'ا', 'ا', 'آ', 'ا'];

        return str_replace($arabic, $persian, $text);
    }

    /**
     * Convert number to Persian words
     * 
     * @param int $number
     * @return string
     */
    public static function numberToPersianWords($number)
    {
        if ($number == 0) return 'صفر';

        $ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
        $tens = ['', 'ده', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
        $teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
        $hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
        $scales = ['', 'هزار', 'میلیون', 'میلیارد'];

        $words = [];
        $scaleIndex = 0;

        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk > 0) {
                $chunkWords = [];
                $h = floor($chunk / 100);
                $t = floor(($chunk % 100) / 10);
                $o = $chunk % 10;

                if ($h > 0) $chunkWords[] = $hundreds[$h];
                if ($t == 1) {
                    $chunkWords[] = $teens[$o];
                } else {
                    if ($t > 0) $chunkWords[] = $tens[$t];
                    if ($o > 0) $chunkWords[] = $ones[$o];
                }

                if ($scaleIndex > 0) $chunkWords[] = $scales[$scaleIndex];
                $words[] = implode(' و ', $chunkWords);
            }
            $number = floor($number / 1000);
            $scaleIndex++;
        }

        return implode(' و ', array_reverse($words));
    }

    /**
     * Convert Gregorian date to Jalali
     * 
     * @param string $gregorianDate Y-m-d format
     * @param string $format Output format
     * @return string
     */
    public static function gregorianToJalali($gregorianDate, $format = 'Y/m/d')
    {
        $timestamp = strtotime($gregorianDate);
        return DateHelper::toJalali($timestamp, $format);
    }

    /**
     * Convert Jalali date to Gregorian
     * 
     * @param string $jalaliDate Y/m/d format
     * @param string $format Output format
     * @return string
     */
    public static function jalaliToGregorian($jalaliDate, $format = 'Y-m-d')
    {
        // Requires Jalali calendar library for accurate conversion
        // This is a simplified placeholder
        $parts = explode('/', $jalaliDate);
        if (count($parts) !== 3) return '';

        $year = (int)$parts[0];
        $month = (int)$parts[1];
        $day = (int)$parts[2];

        $timestamp = CalendarHelper::jalaliToGregorian($year, $month, $day);
        return date($format, $timestamp);
    }

    /**
     * Convert temperature units
     * 
     * @param float $value
     * @param string $from celsius, fahrenheit, kelvin
     * @param string $to celsius, fahrenheit, kelvin
     * @return float
     */
    public static function temperature($value, $from = 'celsius', $to = 'fahrenheit')
    {
        // Convert to Celsius first
        switch ($from) {
            case 'celsius':
                $celsius = $value;
                break;
            case 'fahrenheit':
                $celsius = ($value - 32) * 5 / 9;
                break;
            case 'kelvin':
                $celsius = $value - 273.15;
                break;
            default:
                return $value;
        }

        // Convert from Celsius to target
        switch ($to) {
            case 'celsius':
                return $celsius;
            case 'fahrenheit':
                return ($celsius * 9 / 5) + 32;
            case 'kelvin':
                return $celsius + 273.15;
            default:
                return $celsius;
        }
    }

    /**
     * Convert length units
     * 
     * @param float $value
     * @param string $from m, km, cm, mm, ft, in, yd, mi
     * @param string $to
     * @return float
     */
    public static function length($value, $from = 'm', $to = 'km')
    {
        // Convert to meters first
        $meters = match($from) {
            'km' => $value * 1000,
            'cm' => $value / 100,
            'mm' => $value / 1000,
            'ft' => $value * 0.3048,
            'in' => $value * 0.0254,
            'yd' => $value * 0.9144,
            'mi' => $value * 1609.344,
            default => $value, // m
        };

        // Convert from meters to target
        return match($to) {
            'km' => $meters / 1000,
            'cm' => $meters * 100,
            'mm' => $meters * 1000,
            'ft' => $meters / 0.3048,
            'in' => $meters / 0.0254,
            'yd' => $meters / 0.9144,
            'mi' => $meters / 1609.344,
            default => $meters, // m
        };
    }

    /**
     * Convert weight units
     * 
     * @param float $value
     * @param string $from kg, g, mg, ton, lb, oz
     * @param string $to
     * @return float
     */
    public static function weight($value, $from = 'kg', $to = 'g')
    {
        // Convert to grams first
        $grams = match($from) {
            'kg' => $value * 1000,
            'mg' => $value / 1000,
            'ton' => $value * 1000000,
            'lb' => $value * 453.592,
            'oz' => $value * 28.3495,
            default => $value, // g
        };

        // Convert from grams to target
        return match($to) {
            'kg' => $grams / 1000,
            'mg' => $grams * 1000,
            'ton' => $grams / 1000000,
            'lb' => $grams / 453.592,
            'oz' => $grams / 28.3495,
            default => $grams, // g
        };
    }

    /**
     * Convert area units
     * 
     * @param float $value
     * @param string $from sqm, sqkm, hectare, sqft, acre
     * @param string $to
     * @return float
     */
    public static function area($value, $from = 'sqm', $to = 'hectare')
    {
        $sqm = match($from) {
            'sqkm' => $value * 1000000,
            'hectare' => $value * 10000,
            'sqft' => $value * 0.092903,
            'acre' => $value * 4046.86,
            default => $value, // sqm
        };

        return match($to) {
            'sqkm' => $sqm / 1000000,
            'hectare' => $sqm / 10000,
            'sqft' => $sqm / 0.092903,
            'acre' => $sqm / 4046.86,
            default => $sqm, // sqm
        };
    }

    /**
     * Convert volume units
     * 
     * @param float $value
     * @param string $from l, ml, gal, qt, pt, cup, floz
     * @param string $to
     * @return float
     */
    public static function volume($value, $from = 'l', $to = 'ml')
    {
        $liters = match($from) {
            'ml' => $value / 1000,
            'gal' => $value * 3.78541,
            'qt' => $value * 0.946353,
            'pt' => $value * 0.473176,
            'cup' => $value * 0.236588,
            'floz' => $value * 0.0295735,
            default => $value, // l
        };

        return match($to) {
            'ml' => $liters * 1000,
            'gal' => $liters / 3.78541,
            'qt' => $liters / 0.946353,
            'pt' => $liters / 0.473176,
            'cup' => $liters / 0.236588,
            'floz' => $liters / 0.0295735,
            default => $liters, // l
        };
    }
}