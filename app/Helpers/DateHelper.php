<?php
namespace App\Helpers;

/**
 * Date Helper Class
 * توابع کمکی برای تاریخ و زمان
 */
class DateHelper
{
    /**
     * Format date
     */
    public static function format($date, $format = 'Y-m-d')
    {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        
        if ($timestamp === false) {
            return $date;
        }
        
        return date($format, $timestamp);
    }
    
    /**
     * Format datetime for display
     */
    public static function formatDisplay($date, $includeTime = false)
    {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = strtotime($date);
        $format = $includeTime ? 'Y/m/d H:i' : 'Y/m/d';
        
        return self::toJalali($timestamp, $format);
    }
    
    /**
     * Get relative time (time ago)
     */
    public static function timeAgo($datetime)
    {
        $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 0) {
            return 'در آینده';
        }
        
        if ($diff < 60) {
            return 'همین الان';
        }
        
        $intervals = [
            31536000 => ['سال', 'سال'],
            2592000 => ['ماه', 'ماه'],
            604800 => ['هفته', 'هفته'],
            86400 => ['روز', 'روز'],
            3600 => ['ساعت', 'ساعت'],
            60 => ['دقیقه', 'دقیقه'],
        ];
        
        foreach ($intervals as $seconds => $labels) {
            $count = floor($diff / $seconds);
            
            if ($count > 0) {
                return $count . ' ' . ($count > 1 ? $labels[1] : $labels[0]) . ' پیش';
            }
        }
        
        return 'همین الان';
    }
    
    /**
     * Get time remaining
     */
    public static function timeRemaining($datetime)
    {
        $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
        $diff = $timestamp - time();
        
        if ($diff <= 0) {
            return 'منقضی شده';
        }
        
        $days = floor($diff / 86400);
        $hours = floor(($diff % 86400) / 3600);
        $minutes = floor(($diff % 3600) / 60);
        
        $parts = [];
        
        if ($days > 0) {
            $parts[] = $days . ' روز';
        }
        if ($hours > 0) {
            $parts[] = $hours . ' ساعت';
        }
        if ($minutes > 0 && $days === 0) {
            $parts[] = $minutes . ' دقیقه';
        }
        
        return implode(' و ', $parts) . ' باقی مانده';
    }
    
    /**
     * Get date range
     */
    public static function dateRange($startDate, $endDate, $format = 'Y-m-d')
    {
        $dates = [];
        $current = strtotime($startDate);
        $end = strtotime($endDate);
        
        while ($current <= $end) {
            $dates[] = date($format, $current);
            $current = strtotime('+1 day', $current);
        }
        
        return $dates;
    }
    
    /**
     * Get number of days between two dates
     */
    public static function daysBetween($startDate, $endDate)
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        
        return abs(ceil(($end - $start) / 86400));
    }
    
    /**
     * Get number of weekdays between two dates
     */
    public static function weekdaysBetween($startDate, $endDate, $weekendDays = [5, 6])
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        $count = 0;
        
        while ($start <= $end) {
            $dayOfWeek = date('N', $start); // 1=Monday, 7=Sunday
            
            // Convert to 0=Saturday, 6=Friday
            $dayOfWeek = ($dayOfWeek + 1) % 7;
            
            if (!in_array($dayOfWeek, $weekendDays)) {
                $count++;
            }
            
            $start = strtotime('+1 day', $start);
        }
        
        return $count;
    }
    
    /**
     * Add days to date
     */
    public static function addDays($date, $days, $format = 'Y-m-d')
    {
        return date($format, strtotime($date . ' + ' . $days . ' days'));
    }
    
    /**
     * Subtract days from date
     */
    public static function subDays($date, $days, $format = 'Y-m-d')
    {
        return date($format, strtotime($date . ' - ' . $days . ' days'));
    }
    
    /**
     * Add months to date
     */
    public static function addMonths($date, $months, $format = 'Y-m-d')
    {
        return date($format, strtotime($date . ' + ' . $months . ' months'));
    }
    
    /**
     * Get start of month
     */
    public static function startOfMonth($date = null)
    {
        $timestamp = $date ? strtotime($date) : time();
        return date('Y-m-01', $timestamp);
    }
    
    /**
     * Get end of month
     */
    public static function endOfMonth($date = null)
    {
        $timestamp = $date ? strtotime($date) : time();
        return date('Y-m-t', $timestamp);
    }
    
    /**
     * Get start of week (Saturday by default)
     */
    public static function startOfWeek($date = null)
    {
        $timestamp = $date ? strtotime($date) : time();
        $dayOfWeek = date('N', $timestamp); // 1=Monday, 7=Sunday
        $saturdayOffset = ($dayOfWeek + 1) % 7;
        
        return date('Y-m-d', strtotime("-{$saturdayOffset} days", $timestamp));
    }
    
    /**
     * Get end of week (Friday by default)
     */
    public static function endOfWeek($date = null)
    {
        $timestamp = $date ? strtotime($date) : time();
        $dayOfWeek = date('N', $timestamp);
        $fridayOffset = 5 - (($dayOfWeek + 1) % 7);
        
        return date('Y-m-d', strtotime("+{$fridayOffset} days", $timestamp));
    }
    
    /**
     * Get current financial year
     */
    public static function getFinancialYear()
    {
        $month = date('n');
        $year = date('Y');
        
        if ($month >= 4) { // April onwards
            return $year . '-' . ($year + 1);
        }
        
        return ($year - 1) . '-' . $year;
    }
    
    /**
     * Convert Gregorian to Jalali (Persian/Shamsi)
     */
    public static function toJalali($timestamp = null, $format = 'Y/m/d')
    {
        if ($timestamp === null) {
            $timestamp = time();
        } elseif (!is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        
        $gYear = date('Y', $timestamp);
        $gMonth = date('n', $timestamp);
        $gDay = date('j', $timestamp);
        
        $gy = $gYear - 1600;
        $gm = $gMonth - 1;
        $gd = $gDay - 1;
        
        $gDayNo = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);
        
        for ($i = 0; $i < $gm; $i++) {
            $gDayNo += self::gregorianMonthDays($i + 1, $gYear);
        }
        
        $gDayNo += $gd;
        
        $jDayNo = $gDayNo - 79;
        $jNp = floor($jDayNo / 12053);
        $jDayNo %= 12053;
        $jy = 979 + 33 * $jNp + 4 * floor($jDayNo / 1461);
        $jDayNo %= 1461;
        
        if ($jDayNo >= 366) {
            $jy += floor(($jDayNo - 1) / 365);
            $jDayNo = ($jDayNo - 1) % 365;
        }
        
        $jMonth = 0;
        $jDay = 0;
        
        for ($i = 0; $i < 11 && $jDayNo >= self::jalaliMonthDays($i + 1, $jy); $i++) {
            $jDayNo -= self::jalaliMonthDays($i + 1, $jy);
        }
        
        $jMonth = $i + 1;
        $jDay = $jDayNo + 1;
        
        $replacements = [
            'Y' => $jy,
            'y' => substr($jy, 2),
            'm' => str_pad($jMonth, 2, '0', STR_PAD_LEFT),
            'n' => $jMonth,
            'd' => str_pad($jDay, 2, '0', STR_PAD_LEFT),
            'j' => $jDay,
            'H' => date('H', $timestamp),
            'i' => date('i', $timestamp),
            's' => date('s', $timestamp),
        ];
        
        return strtr($format, $replacements);
    }
    
    /**
     * Get Jalali month name
     */
    public static function getJalaliMonthName($month)
    {
        $months = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];
        
        return $months[$month] ?? '';
    }
    
    /**
     * Get Jalali day of week name
     */
    public static function getJalaliDayName($dayOfWeek)
    {
        $days = [
            0 => 'شنبه',
            1 => 'یکشنبه',
            2 => 'دوشنبه',
            3 => 'سه‌شنبه',
            4 => 'چهارشنبه',
            5 => 'پنج‌شنبه',
            6 => 'جمعه',
        ];
        
        return $days[$dayOfWeek] ?? '';
    }
    
    /**
     * Get age from birthdate
     */
    public static function getAge($birthDate)
    {
        $birth = new \DateTime($birthDate);
        $now = new \DateTime();
        $diff = $now->diff($birth);
        
        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'total_months' => ($diff->y * 12) + $diff->m,
            'total_days' => $diff->days,
        ];
    }
    
    /**
     * Check if date is today
     */
    public static function isToday($date)
    {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d');
    }
    
    /**
     * Check if date is in the past
     */
    public static function isPast($date)
    {
        return strtotime($date) < time();
    }
    
    /**
     * Check if date is in the future
     */
    public static function isFuture($date)
    {
        return strtotime($date) > time();
    }
    
    /**
     * Check if date is a weekend
     */
    public static function isWeekend($date, $weekendDays = [5, 6])
    {
        $dayOfWeek = (date('N', strtotime($date)) + 1) % 7;
        return in_array($dayOfWeek, $weekendDays);
    }
    
    /**
     * Check if date is a holiday
     */
    public static function isHoliday($date, $holidays = [])
    {
        $date = date('Y-m-d', strtotime($date));
        return in_array($date, $holidays);
    }
    
    /**
     * Get remaining days until date
     */
    public static function daysUntil($date)
    {
        $target = strtotime($date);
        $now = time();
        
        if ($target <= $now) {
            return 0;
        }
        
        return ceil(($target - $now) / 86400);
    }
    
    private static function gregorianMonthDays($month, $year)
    {
        $days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        
        if ($month === 2 && self::isGregorianLeapYear($year)) {
            return 29;
        }
        
        return $days[$month - 1];
    }
    
    private static function jalaliMonthDays($month, $year)
    {
        if ($month <= 6) {
            return 31;
        }
        
        if ($month <= 11) {
            return 30;
        }
        
        return self::isJalaliLeapYear($year) ? 30 : 29;
    }
    
    private static function isGregorianLeapYear($year)
    {
        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }
    
    private static function isJalaliLeapYear($year)
    {
        $a = 0.025;
        $b = 266;
        
        if ($year > 0) {
            $leapDays0 = floor(($year + 38) * $a + $b + 0.5);
            $leapDays1 = floor(($year + 39) * $a + $b + 0.5);
        } else {
            $leapDays0 = floor(($year + 39) * $a + $b + 0.5);
            $leapDays1 = floor(($year + 40) * $a + $b + 0.5);
        }
        
        return $leapDays0 !== $leapDays1;
    }
}