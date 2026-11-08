<?php
namespace App\Helpers;

/**
 * Calendar Helper Class
 * توابع کمکی برای تقویم و رویدادها
 */
class CalendarHelper
{
    /**
     * Get days in month
     * 
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function daysInMonth($month, $year)
    {
        if ($month <= 6) return 31;
        if ($month <= 11) return 30;
        return self::isLeapYear($year) ? 30 : 29;
    }

    /**
     * Check if Jalali year is leap year
     * 
     * @param int $year
     * @return bool
     */
    public static function isLeapYear($year)
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

    /**
     * Get month name in Persian
     * 
     * @param int $month
     * @return string
     */
    public static function monthName($month)
    {
        $months = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];

        return $months[$month] ?? '';
    }

    /**
     * Get day of week name
     * 
     * @param int $day 0=Saturday, 6=Friday
     * @return string
     */
    public static function dayName($day)
    {
        $days = [
            0 => 'شنبه', 1 => 'یکشنبه', 2 => 'دوشنبه',
            3 => 'سه‌شنبه', 4 => 'چهارشنبه', 5 => 'پنج‌شنبه', 6 => 'جمعه',
        ];

        return $days[$day] ?? '';
    }

    /**
     * Get short day name
     * 
     * @param int $day
     * @return string
     */
    public static function dayShortName($day)
    {
        $days = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
        return $days[$day] ?? '';
    }

    /**
     * Generate calendar month grid
     * 
     * @param int $month
     * @param int $year
     * @param array $events Array of ['day'=>..., 'title'=>..., 'color'=>...]
     * @return array
     */
    public static function generateMonth($month, $year, $events = [])
    {
        $daysInMonth = self::daysInMonth($month, $year);
        $firstDayOfMonth = self::getFirstDayOfMonth($month, $year);

        $grid = [];
        $day = 1;

        // Fill first week
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            if ($i < $firstDayOfMonth) {
                $week[] = null;
            } else {
                $week[] = $day;
                $day++;
            }
        }
        $grid[] = $week;

        // Fill remaining weeks
        while ($day <= $daysInMonth) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                if ($day <= $daysInMonth) {
                    $week[] = $day;
                    $day++;
                } else {
                    $week[] = null;
                }
            }
            $grid[] = $week;
        }

        // Add events
        $today = self::getTodayJalali();
        $weeks = [];

        foreach ($grid as $weekIndex => $week) {
            $weekData = [];
            foreach ($week as $dayIndex => $day) {
                if ($day === null) {
                    $weekData[] = null;
                    continue;
                }

                $dayEvents = array_filter($events, function($e) use ($day, $month, $year) {
                    return $e['day'] == $day && ($e['month'] ?? $month) == $month && ($e['year'] ?? $year) == $year;
                });

                $weekData[] = [
                    'day' => $day,
                    'is_today' => ($today['day'] == $day && $today['month'] == $month && $today['year'] == $year),
                    'is_weekend' => ($dayIndex == 6),
                    'events' => array_values($dayEvents),
                ];
            }
            $weeks[] = $weekData;
        }

        return [
            'month' => $month,
            'month_name' => self::monthName($month),
            'year' => $year,
            'days_in_month' => $daysInMonth,
            'first_day' => $firstDayOfMonth,
            'weeks' => $weeks,
        ];
    }

    /**
     * Get first day of month (0=Saturday)
     * 
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function getFirstDayOfMonth($month, $year)
    {
        $timestamp = self::jalaliToGregorian($year, $month, 1);
        // Convert to Persian day (0=Saturday, 6=Friday)
        return (date('N', $timestamp) + 1) % 7;
    }

    /**
     * Convert Jalali to Gregorian timestamp
     * 
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int Unix timestamp
     */
    public static function jalaliToGregorian($year, $month, $day)
    {
        // Simplified conversion - for accurate results use a library
        $gregorianDate = self::jalaliToGregorianDate($year, $month, $day);
        return strtotime($gregorianDate);
    }

    /**
     * Get today's Jalali date
     * 
     * @return array ['year'=>..., 'month'=>..., 'day'=>...]
     */
    public static function getTodayJalali()
    {
        $timestamp = time();
        $gYear = date('Y', $timestamp);
        $gMonth = date('m', $timestamp);
        $gDay = date('d', $timestamp);

        return self::gregorianToJalali($gYear, $gMonth, $gDay);
    }

    /**
     * Convert Gregorian to Jalali
     * 
     * @param int $gYear
     * @param int $gMonth
     * @param int $gDay
     * @return array ['year'=>..., 'month'=>..., 'day'=>...]
     */
    public static function gregorianToJalali($gYear, $gMonth, $gDay)
    {
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
        for ($i = 0; $i < 11 && $jDayNo >= self::jalaliMonthDays($i + 1, $jy); $i++) {
            $jDayNo -= self::jalaliMonthDays($i + 1, $jy);
        }
        $jMonth = $i + 1;
        $jDay = $jDayNo + 1;

        return ['year' => (int)$jy, 'month' => (int)$jMonth, 'day' => (int)$jDay];
    }

    /**
     * Convert Jalali date string to Gregorian
     */
    private static function jalaliToGregorianDate($year, $month, $day)
    {
        // This is a simplified version
        // For production, use a proper Jalali calendar library
        $jd = self::jalaliToJd($year, $month, $day);
        return self::jdToGregorian($jd);
    }

    private static function gregorianMonthDays($month, $year)
    {
        $days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        if ($month == 2 && (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0)) {
            return 29;
        }
        return $days[$month - 1];
    }

    private static function jalaliMonthDays($month, $year)
    {
        if ($month <= 6) return 31;
        if ($month <= 11) return 30;
        return self::isLeapYear($year) ? 30 : 29;
    }

    private static function jalaliToJd($year, $month, $day)
    {
        // Simplified conversion
        return 0; // Placeholder
    }

    private static function jdToGregorian($jd)
    {
        // Simplified conversion
        return date('Y-m-d'); // Placeholder
    }

    /**
     * Generate academic calendar for a semester
     * 
     * @param string $startDate Jalali start date 'Y/m/d'
     * @param string $endDate Jalali end date 'Y/m/d'
     * @param array $holidays Array of holiday dates
     * @param array $weekendDays Weekend days (default Friday=6)
     * @return array
     */
    public static function academicCalendar($startDate, $endDate, $holidays = [], $weekendDays = [6])
    {
        $start = explode('/', $startDate);
        $end = explode('/', $endDate);

        $startYear = (int)$start[0];
        $startMonth = (int)$start[1];
        $startDay = (int)$start[2];

        $endYear = (int)$end[0];
        $endMonth = (int)$end[1];
        $endDay = (int)$end[2];

        $calendar = [];
        $totalDays = 0;
        $workingDays = 0;
        $holidayCount = 0;

        $currentYear = $startYear;
        $currentMonth = $startMonth;
        $currentDay = $startDay;

        // Simplified iteration
        while (true) {
            $daysInMonth = self::daysInMonth($currentMonth, $currentYear);

            while ($currentDay <= $daysInMonth) {
                if ($currentYear == $endYear && $currentMonth == $endMonth && $currentDay > $endDay) {
                    break 2;
                }

                $dayOfWeek = self::getFirstDayOfMonth($currentMonth, $currentYear) + $currentDay - 1;
                $dayOfWeek = $dayOfWeek % 7;

                $isWeekend = in_array($dayOfWeek, $weekendDays);
                $isHoliday = in_array("{$currentYear}/{$currentMonth}/{$currentDay}", $holidays);

                $calendar[] = [
                    'date' => "{$currentYear}/{$currentMonth}/{$currentDay}",
                    'day' => $currentDay,
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'day_of_week' => $dayOfWeek,
                    'day_name' => self::dayName($dayOfWeek),
                    'is_weekend' => $isWeekend,
                    'is_holiday' => $isHoliday,
                    'is_working_day' => !$isWeekend && !$isHoliday,
                ];

                $totalDays++;
                if (!$isWeekend && !$isHoliday) $workingDays++;
                if ($isHoliday) $holidayCount++;

                $currentDay++;
            }

            $currentDay = 1;
            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
        }

        return [
            'calendar' => $calendar,
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'weekend_days' => $totalDays - $workingDays - $holidayCount,
            'holidays' => $holidayCount,
        ];
    }
}