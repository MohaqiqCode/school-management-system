<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class SystemController extends Controller
{
    /**
     * Health check endpoint
     * GET /api/v1/health
     */
    public function health()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'uptime' => $this->getUptime(),
            'checks' => [
                'database' => $this->checkDatabase(),
                'storage' => $this->checkStorage(),
                'cache' => $this->checkCache(),
            ],
            'info' => [
                'app_name' => $this->config['app_name'] ?? 'SMS',
                'app_version' => $this->config['app_version'] ?? '1.0.0',
                'app_environment' => $this->config['app_environment'] ?? 'production',
                'php_version' => PHP_VERSION,
                'memory_usage' => $this->getMemoryUsage(),
                'disk_usage' => $this->getDiskUsage(),
            ]
        ];

        $statusCode = 200;
        if (!$health['checks']['database'] || !$health['checks']['storage']) {
            $health['status'] = 'degraded';
            $statusCode = 503;
        }

        $this->json(['success' => true, 'data' => $health], $statusCode);
    }

    /**
     * Ping endpoint
     * GET /api/v1/ping
     */
    public function ping()
    {
        $this->json([
            'success' => true,
            'message' => 'pong',
            'timestamp' => date('Y-m-d H:i:s'),
            'response_time_ms' => round((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000, 2)
        ]);
    }

    /**
     * Get system logs (for admin)
     * GET /api/v1/system/logs
     */
    public function logs()
    {
        $level = $this->request->get('level', '');
        $limit = min(100, $this->request->get('limit', 50));

        $sql = "SELECT * FROM system_logs WHERE 1=1";
        $params = [];

        if ($level) {
            $sql .= " AND level = ?";
            $params[] = $level;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $logs = $this->db->select($sql, $params);

        $this->json(['success' => true, 'data' => $logs]);
    }

    /**
     * Clear cache
     * POST /api/v1/system/clear-cache
     */
    public function clearCache()
    {
        $cachePath = __DIR__ . '/../../../storage/cache/';
        $this->clearDirectory($cachePath);

        $this->logActivity('system', 'clear_cache', 'Cache cleared via API');

        $this->json(['success' => true, 'message' => 'کش با موفقیت پاکسازی شد']);
    }

    /**
     * Clear logs
     * POST /api/v1/system/clear-logs
     */
    public function clearLogs()
    {
        $days = $this->request->get('days', 90);

        $this->db->delete('system_logs', 'created_at < DATE_SUB(NOW(), INTERVAL ? DAY)', [$days]);
        $this->db->delete('audit_logs', 'created_at < DATE_SUB(NOW(), INTERVAL ? DAY)', [$days]);

        $this->logActivity('system', 'clear_logs', "Logs older than {$days} days cleared via API");

        $this->json(['success' => true, 'message' => "لاگ‌های قدیمی‌تر از {$days} روز پاکسازی شدند"]);
    }

    /**
     * Toggle maintenance mode
     * POST /api/v1/system/toggle-maintenance
     */
    public function toggleMaintenance()
    {
        $maintenanceFile = __DIR__ . '/../../../storage/maintenance.lock';

        if (file_exists($maintenanceFile)) {
            unlink($maintenanceFile);
            $message = 'حالت تعمیر غیرفعال شد';
        } else {
            file_put_contents($maintenanceFile, date('Y-m-d H:i:s'));
            $message = 'حالت تعمیر فعال شد';
        }

        $this->logActivity('system', 'toggle_maintenance', $message);

        $this->json(['success' => true, 'message' => $message]);
    }

    private function checkDatabase()
    {
        try {
            $this->db->selectOne("SELECT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkStorage()
    {
        $storagePath = __DIR__ . '/../../../storage/';
        return is_writable($storagePath);
    }

    private function checkCache()
    {
        $cachePath = __DIR__ . '/../../../storage/cache/';
        return is_writable($cachePath);
    }

    private function getUptime()
    {
        $startTime = $_SERVER['REQUEST_TIME'] ?? time();
        $uptime = time() - $startTime;
        return $this->formatUptime($uptime);
    }

    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "{$days}d {$hours}h {$minutes}m";
    }

    private function getMemoryUsage()
    {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        return [
            'current' => round($usage / 1024 / 1024, 2) . ' MB',
            'peak' => round($peak / 1024 / 1024, 2) . ' MB'
        ];
    }

    private function getDiskUsage()
    {
        $path = __DIR__ . '/../../../';
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return [
            'total' => round($total / 1024 / 1024 / 1024, 2) . ' GB',
            'used' => round($used / 1024 / 1024 / 1024, 2) . ' GB',
            'free' => round($free / 1024 / 1024 / 1024, 2) . ' GB',
            'percentage' => $total > 0 ? round(($used / $total) * 100, 1) . '%' : '0%'
        ];
    }

    private function clearDirectory($path)
    {
        if (!is_dir($path)) return;
        $files = glob($path . '*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    private function logActivity($module, $action, $description)
    {
        try {
            $this->db->insert('audit_logs', [
                'user_id' => $this->session->get('user_id'),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => $this->request->getIpAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {}
    }
}