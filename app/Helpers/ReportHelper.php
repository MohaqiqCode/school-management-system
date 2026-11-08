<?php
namespace App\Helpers;

use App\Core\Application;

/**
 * Report Helper Class
 * توابع کمکی برای تولید گزارش‌ها
 */
class ReportHelper
{
    /**
     * Calculate percentage
     * 
     * @param float $value
     * @param float $total
     * @param int $decimals
     * @return float
     */
    public static function percentage($value, $total, $decimals = 2)
    {
        if ($total == 0) return 0;
        return round(($value / $total) * 100, $decimals);
    }

    /**
     * Calculate average
     * 
     * @param array $values
     * @param int $decimals
     * @return float
     */
    public static function average($values, $decimals = 2)
    {
        $values = array_filter($values, 'is_numeric');
        if (empty($values)) return 0;

        return round(array_sum($values) / count($values), $decimals);
    }

    /**
     * Calculate median
     * 
     * @param array $values
     * @return float
     */
    public static function median($values)
    {
        $values = array_filter($values, 'is_numeric');
        if (empty($values)) return 0;

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
     * 
     * @param array $values
     * @return mixed
     */
    public static function mode($values)
    {
        $values = array_filter($values, 'is_numeric');
        if (empty($values)) return 0;

        $counts = array_count_values($values);
        arsort($counts);
        return key($counts);
    }

    /**
     * Calculate standard deviation
     * 
     * @param array $values
     * @param int $decimals
     * @return float
     */
    public static function standardDeviation($values, $decimals = 2)
    {
        $values = array_filter($values, 'is_numeric');
        if (count($values) < 2) return 0;

        $mean = self::average($values);
        $sumSquaredDiff = 0;

        foreach ($values as $value) {
            $sumSquaredDiff += pow($value - $mean, 2);
        }

        return round(sqrt($sumSquaredDiff / (count($values) - 1)), $decimals);
    }

    /**
     * Calculate GPA
     * 
     * @param array $grades Array of grade points
     * @param int $decimals
     * @return float
     */
    public static function calculateGpa($grades, $decimals = 2)
    {
        $grades = array_filter($grades, 'is_numeric');
        if (empty($grades)) return 0;

        return round(array_sum($grades) / count($grades), $decimals);
    }

    /**
     * Calculate pass rate
     * 
     * @param int $passed
     * @param int $total
     * @return float
     */
    public static function passRate($passed, $total)
    {
        return self::percentage($passed, $total);
    }

    /**
     * Calculate attendance rate
     * 
     * @param int $present
     * @param int $total
     * @return float
     */
    public static function attendanceRate($present, $total)
    {
        return self::percentage($present, $total);
    }

    /**
     * Calculate collection rate
     * 
     * @param float $collected
     * @param float $total
     * @return float
     */
    public static function collectionRate($collected, $total)
    {
        return self::percentage($collected, $total);
    }

    /**
     * Calculate occupancy rate
     * 
     * @param int $occupied
     * @param int $capacity
     * @return float
     */
    public static function occupancyRate($occupied, $capacity)
    {
        return self::percentage($occupied, $capacity);
    }

    /**
     * Group data by key for reporting
     * 
     * @param array $data
     * @param string $key
     * @return array
     */
    public static function groupBy($data, $key)
    {
        $result = [];
        foreach ($data as $item) {
            $groupKey = is_array($item) ? ($item[$key] ?? 'other') : (is_object($item) ? ($item->$key ?? 'other') : 'other');
            $result[$groupKey][] = $item;
        }
        return $result;
    }

    /**
     * Aggregate data for reporting
     * 
     * @param array $data
     * @param string $groupBy Group by this key
     * @param string $sumKey Sum this key
     * @param string $countKey Count distinct values of this key
     * @return array
     */
    public static function aggregate($data, $groupBy, $sumKey = null, $countKey = null)
    {
        $result = [];

        foreach ($data as $item) {
            $group = $item[$groupBy] ?? 'other';

            if (!isset($result[$group])) {
                $result[$group] = [
                    'group' => $group,
                    'count' => 0,
                    'sum' => 0,
                    'distinct' => [],
                ];
            }

            $result[$group]['count']++;

            if ($sumKey && isset($item[$sumKey])) {
                $result[$group]['sum'] += $item[$sumKey];
            }

            if ($countKey && isset($item[$countKey])) {
                $result[$group]['distinct'][$item[$countKey]] = true;
            }
        }

        // Calculate distinct counts
        foreach ($result as &$group) {
            $group['distinct_count'] = count($group['distinct']);
            unset($group['distinct']);
        }

        return array_values($result);
    }

    /**
     * Generate summary statistics
     * 
     * @param array $data
     * @param string $valueKey
     * @return array
     */
    public static function summary($data, $valueKey)
    {
        $values = array_column($data, $valueKey);
        $values = array_filter($values, 'is_numeric');

        if (empty($values)) {
            return [
                'count' => 0, 'sum' => 0, 'avg' => 0, 'min' => 0,
                'max' => 0, 'median' => 0, 'std_dev' => 0,
            ];
        }

        return [
            'count' => count($values),
            'sum' => array_sum($values),
            'avg' => self::average($values),
            'min' => min($values),
            'max' => max($values),
            'median' => self::median($values),
            'std_dev' => self::standardDeviation($values),
        ];
    }

    /**
     * Generate trend data (for charts)
     * 
     * @param array $data
     * @param string $dateKey Date field
     * @param string $valueKey Value field
     * @param string $period daily, weekly, monthly, yearly
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function trend($data, $dateKey, $valueKey, $period = 'daily', $startDate = null, $endDate = null)
    {
        $trend = [];

        foreach ($data as $item) {
            $date = $item[$dateKey] ?? null;
            $value = $item[$valueKey] ?? 0;

            if (!$date) continue;

            $timestamp = strtotime($date);

            switch ($period) {
                case 'weekly':
                    $key = date('Y-W', $timestamp);
                    $label = 'هفته ' . date('W', $timestamp) . ' ' . date('Y', $timestamp);
                    break;
                case 'monthly':
                    $key = date('Y-m', $timestamp);
                    $label = DateHelper::getJalaliMonthName((int)date('m', $timestamp)) . ' ' . date('Y', $timestamp);
                    break;
                case 'yearly':
                    $key = date('Y', $timestamp);
                    $label = date('Y', $timestamp);
                    break;
                case 'daily':
                default:
                    $key = date('Y-m-d', $timestamp);
                    $label = date('Y/m/d', $timestamp);
                    break;
            }

            if (!isset($trend[$key])) {
                $trend[$key] = ['label' => $label, 'value' => 0, 'count' => 0];
            }

            $trend[$key]['value'] += $value;
            $trend[$key]['count']++;
        }

        ksort($trend);

        return array_values($trend);
    }

    /**
     * Generate ranking report
     * 
     * @param array $data
     * @param string $labelKey Label field
     * @param string $valueKey Value field to rank by
     * @param string $order desc or asc
     * @param int $limit Limit results
     * @return array
     */
    public static function ranking($data, $labelKey, $valueKey, $order = 'desc', $limit = 10)
    {
        // Sort by value
        usort($data, function($a, $b) use ($valueKey, $order) {
            $aVal = $a[$valueKey] ?? 0;
            $bVal = $b[$valueKey] ?? 0;
            return $order === 'desc' ? $bVal <=> $aVal : $aVal <=> $bVal;
        });

        // Add rank
        $ranked = [];
        $rank = 1;
        $prevValue = null;
        $actualRank = 0;

        foreach ($data as $item) {
            $actualRank++;
            $value = $item[$valueKey] ?? 0;

            if ($prevValue !== null && $value !== $prevValue) {
                $rank = $actualRank;
            }

            $ranked[] = array_merge($item, [
                'rank' => $rank,
                'label' => $item[$labelKey] ?? '',
                'value' => $value,
            ]);

            $prevValue = $value;

            if (count($ranked) >= $limit) break;
        }

        return $ranked;
    }

    /**
     * Generate distribution report (for pie charts)
     * 
     * @param array $data
     * @param string $labelKey
     * @return array
     */
    public static function distribution($data, $labelKey)
    {
        $distribution = [];

        foreach ($data as $item) {
            $label = $item[$labelKey] ?? 'other';

            if (!isset($distribution[$label])) {
                $distribution[$label] = ['label' => $label, 'count' => 0, 'percentage' => 0];
            }

            $distribution[$label]['count']++;
        }

        $total = count($data);
        foreach ($distribution as &$item) {
            $item['percentage'] = $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0;
        }

        return array_values($distribution);
    }

    /**
     * Generate comparison report
     * 
     * @param array $currentPeriod
     * @param array $previousPeriod
     * @param string $valueKey
     * @return array
     */
    public static function comparison($currentPeriod, $previousPeriod, $valueKey)
    {
        $currentValue = array_sum(array_column($currentPeriod, $valueKey));
        $previousValue = array_sum(array_column($previousPeriod, $valueKey));

        $change = $currentValue - $previousValue;
        $changePercent = $previousValue != 0 ? round(($change / $previousValue) * 100, 2) : 0;

        return [
            'current' => $currentValue,
            'previous' => $previousValue,
            'change' => $change,
            'change_percent' => $changePercent,
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'),
        ];
    }
}