<?php
namespace App\Models;

/**
 * System Log Model
 * مدیریت لاگ‌های سیستم
 */
class SystemLog extends Model
{
    protected $table = 'system_logs';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $casts = [
        'context' => 'json',
    ];
    
    /**
     * Log system event
     */
    public function log($level, $module, $message, $context = [])
    {
        return $this->db->insert('system_logs', [
            'level' => $level,
            'module' => $module,
            'message' => $message,
            'context' => !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Log debug message
     */
    public function debug($module, $message, $context = [])
    {
        return $this->log('debug', $module, $message, $context);
    }
    
    /**
     * Log info message
     */
    public function info($module, $message, $context = [])
    {
        return $this->log('info', $module, $message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning($module, $message, $context = [])
    {
        return $this->log('warning', $module, $message, $context);
    }
    
    /**
     * Log error message
     */
    public function error($module, $message, $context = [])
    {
        return $this->log('error', $module, $message, $context);
    }
    
    /**
     * Log critical message
     */
    public function critical($module, $message, $context = [])
    {
        return $this->log('critical', $module, $message, $context);
    }
    
    /**
     * Get logs with filters
     */
    public function getFiltered($filters = [], $page = 1, $perPage = 50)
    {
        $sql = "SELECT * FROM system_logs WHERE 1=1";
        $params = [];
        
        if (!empty($filters['level'])) {
            $sql .= " AND level = ?";
            $params[] = $filters['level'];
        }
        
        if (!empty($filters['module'])) {
            $sql .= " AND module = ?";
            $params[] = $filters['module'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND message LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }
        
        // Count
        $countSql = "SELECT COUNT(*) as total FROM system_logs WHERE 1=1";
        // Add same filters to count query
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                switch ($key) {
                    case 'level':
                        $countSql .= " AND level = ?";
                        break;
                    case 'module':
                        $countSql .= " AND module = ?";
                        break;
                    case 'date_from':
                        $countSql .= " AND created_at >= ?";
                        break;
                    case 'date_to':
                        $countSql .= " AND created_at <= ?";
                        break;
                }
            }
        }
        
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Data
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->select($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
        ];
    }
    
    /**
     * Get error statistics
     */
    public function getErrorStats($hours = 24)
    {
        $sql = "SELECT level, COUNT(*) as count
                FROM system_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                AND level IN ('error', 'critical', 'warning')
                GROUP BY level
                ORDER BY count DESC";
        
        return $this->db->select($sql, [$hours]);
    }
    
    /**
     * Get recent errors
     */
    public function getRecentErrors($limit = 20)
    {
        $sql = "SELECT * FROM system_logs 
                WHERE level IN ('error', 'critical')
                ORDER BY created_at DESC
                LIMIT ?";
        
        return $this->db->select($sql, [$limit]);
    }
    
    /**
     * Clean old logs
     */
    public function cleanOld($days = 90)
    {
        return $this->db->delete(
            'system_logs', 
            'created_at < DATE_SUB(NOW(), INTERVAL ? DAY)', 
            [$days]
        );
    }
    
    /**
     * Get unique levels
     */
    public function getLevels()
    {
        $sql = "SELECT DISTINCT level FROM system_logs ORDER BY level";
        return $this->db->selectColumn($sql);
    }
}