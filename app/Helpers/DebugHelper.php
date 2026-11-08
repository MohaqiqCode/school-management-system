<?php
namespace App\Helpers;

/**
 * Debug Helper Class
 * توابع کمکی برای اشکال‌زدایی
 */
class DebugHelper
{
    /**
     * Dump variable and die
     * 
     * @param mixed ...$args
     * @return void
     */
    public static function dd(...$args)
    {
        self::dump(...$args);
        die();
    }

    /**
     * Dump variable with pretty formatting
     * 
     * @param mixed ...$args
     * @return void
     */
    public static function dump(...$args)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $caller = $backtrace[0];

        echo '<div style="background:#1e1e1e;color:#d4d4d4;padding:20px;margin:10px 0;border-radius:8px;font-family:monospace;font-size:13px;direction:ltr;overflow-x:auto;">';
        echo '<div style="color:#569cd6;margin-bottom:10px;">';
        echo '📁 ' . htmlspecialchars($caller['file'] ?? 'unknown') . ' : ' . ($caller['line'] ?? '');
        echo '</div>';

        foreach ($args as $arg) {
            echo '<pre style="background:#2d2d2d;padding:15px;border-radius:6px;margin:5px 0;white-space:pre-wrap;word-break:break-all;">';
            echo self::varExport($arg);
            echo '</pre>';
        }

        echo '</div>';
    }

    /**
     * Log variable to file
     * 
     * @param mixed $data
     * @param string $label
     * @param string $logFile
     * @return void
     */
    public static function log($data, $label = 'DEBUG', $logFile = null)
    {
        $logFile = $logFile ?? (__DIR__ . '/../../storage/logs/debug.log');
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $output = "[{$timestamp}] {$label}\n";
        $output .= print_r($data, true) . "\n";
        $output .= str_repeat('-', 80) . "\n";

        file_put_contents($logFile, $output, FILE_APPEND);
    }

    /**
     * Measure execution time of callback
     * 
     * @param callable $callback
     * @param int $iterations
     * @return array
     */
    public static function benchmark($callback, $iterations = 1)
    {
        $start = microtime(true);
        $startMemory = memory_get_usage();

        for ($i = 0; $i < $iterations; $i++) {
            $callback();
        }

        $end = microtime(true);
        $endMemory = memory_get_usage();

        return [
            'time' => round(($end - $start) * 1000, 4) . ' ms',
            'time_per_iteration' => round((($end - $start) / $iterations) * 1000, 4) . ' ms',
            'memory' => self::formatBytes($endMemory - $startMemory),
            'iterations' => $iterations,
        ];
    }

    /**
     * Get query log
     * 
     * @return array
     */
    public static function getQueryLog()
    {
        $db = \App\Core\Application::getInstance()->getDB();
        return $db->getQueries();
    }

    /**
     * Print query log
     * 
     * @return void
     */
    public static function printQueryLog()
    {
        $queries = self::getQueryLog();

        echo '<div style="background:#1e1e1e;color:#d4d4d4;padding:20px;margin:10px 0;border-radius:8px;font-family:monospace;font-size:12px;direction:ltr;">';
        echo '<h3 style="color:#569cd6;">Query Log (' . count($queries) . ' queries)</h3>';

        foreach ($queries as $i => $query) {
            echo '<div style="background:#2d2d2d;padding:10px;margin:5px 0;border-radius:4px;">';
            echo '<span style="color:#888;">#' . ($i + 1) . '</span> ';
            echo '<span style="color:#ce9178;">' . htmlspecialchars($query['query']) . '</span>';
            echo '<br><span style="color:#6a9955;">Params: ' . htmlspecialchars(json_encode($query['params'])) . '</span>';
            echo '<br><span style="color:#569cd6;">Time: ' . $query['time'] . ' ms</span>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Get memory usage info
     * 
     * @return array
     */
    public static function memoryUsage()
    {
        return [
            'current' => self::formatBytes(memory_get_usage(true)),
            'peak' => self::formatBytes(memory_get_peak_usage(true)),
            'limit' => ini_get('memory_limit'),
        ];
    }

    /**
     * Get loaded PHP extensions
     * 
     * @return array
     */
    public static function loadedExtensions()
    {
        return get_loaded_extensions();
    }

    /**
     * Check if extension is loaded
     * 
     * @param string $extension
     * @return bool
     */
    public static function hasExtension($extension)
    {
        return extension_loaded($extension);
    }

    /**
     * Get PHP info summary
     * 
     * @return array
     */
    public static function phpInfo()
    {
        return [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'display_errors' => ini_get('display_errors'),
            'error_reporting' => ini_get('error_reporting'),
        ];
    }

    /**
     * Get server info
     * 
     * @return array
     */
    public static function serverInfo()
    {
        return [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
            'host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
            'port' => $_SERVER['SERVER_PORT'] ?? '80',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        ];
    }

    /**
     * Format variable for display
     * 
     * @param mixed $var
     * @return string
     */
    private static function varExport($var)
    {
        if (is_null($var)) return '<span style="color:#569cd6;">null</span>';
        if (is_bool($var)) return '<span style="color:#569cd6;">' . ($var ? 'true' : 'false') . '</span>';
        if (is_int($var) || is_float($var)) return '<span style="color:#b5cea8;">' . $var . '</span>';
        if (is_string($var)) return '<span style="color:#ce9178;">"' . htmlspecialchars($var) . '"</span>';
        if (is_array($var)) return htmlspecialchars(print_r($var, true));
        if (is_object($var)) return htmlspecialchars(print_r($var, true));

        return htmlspecialchars((string)$var);
    }

    /**
     * Format bytes
     * 
     * @param int $bytes
     * @return string
     */
    private static function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}