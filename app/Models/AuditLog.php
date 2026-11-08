<?php
namespace App\Models;

/**
 * Audit Log Model
 * مدیریت لاگ‌های ممیزی
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];
    
    /**
     * Log an activity
     */
    public function log($userId, $action, $module, $description, $metadata = [])
    {
        return $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_values' => !empty($metadata['old_values']) ? json_encode($metadata['old_values'], JSON_UNESCAPED_UNICODE) : null,
            'new_values' => !empty($metadata['new_values']) ? json_encode($metadata['new_values'], JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get logs with filters
     */
    public function getFiltered($filters = [], $page = 1, $perPage = 50)
    {
        $sql = "SELECT al.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       u.email as user_email
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['module'])) {
            $sql .= " AND al.module = ?";
            $params[] = $filters['module'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Count total
        $countSql = str_replace(
            "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email as user_email",
            "SELECT COUNT(*) as total",
            $sql
        );
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY al.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
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
     * Get unique actions
     */
    public function getActions()
    {
        $sql = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
        return $this->db->selectColumn($sql);
    }
    
    /**
     * Get unique modules
     */
    public function getModules()
    {
        $sql = "SELECT DISTINCT module FROM audit_logs ORDER BY module";
        return $this->db->selectColumn($sql);
    }
    
    /**
     * Get login history
     */
    public function getLoginHistory($userId = null, $limit = 50)
    {
        $sql = "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.action IN ('login', 'logout', 'login_failed')";
        
        $params = [];
        
        if ($userId) {
            $sql .= " AND al.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $days = 30)
    {
        $sql = "SELECT action, COUNT(*) as count
                FROM audit_logs 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY action
                ORDER BY count DESC";
        
        return $this->db->select($sql, [$userId, $days]);
    }
    
    /**
     * Clean old logs
     */
    public function cleanOld($days = 90)
    {
        return $this->db->delete(
            'audit_logs', 
            'created_at < DATE_SUB(NOW(), INTERVAL ? DAY)', 
            [$days]
        );
    }
    
    /**
     * Export logs
     */
    public function export($filters = [])
    {
        $result = $this->getFiltered($filters, 1, 10000);
        return $result['data'];
    }
}