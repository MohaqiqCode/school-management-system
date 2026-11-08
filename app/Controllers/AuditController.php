<?php
namespace App\Controllers;

use App\Core\Controller;

class AuditController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $userIdFilter = $_GET['user_id'] ?? '';
        $actionFilter = $_GET['action'] ?? '';
        $moduleFilter = $_GET['module'] ?? '';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $sql = "SELECT al.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       u.email as user_email
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.created_at BETWEEN ? AND ?";
        
        $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
        
        if ($userIdFilter) {
            $sql .= " AND al.user_id = ?";
            $params[] = $userIdFilter;
        }
        
        if ($actionFilter) {
            $sql .= " AND al.action = ?";
            $params[] = $actionFilter;
        }
        
        if ($moduleFilter) {
            $sql .= " AND al.module = ?";
            $params[] = $moduleFilter;
        }
        
        $countSql = str_replace("SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email as user_email", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY al.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $logs = $this->db->select($sql, $params);
        
        // Get unique actions and modules for filters
        $actions = $this->db->select("SELECT DISTINCT action FROM audit_logs ORDER BY action");
        $modules = $this->db->select("SELECT DISTINCT module FROM audit_logs ORDER BY module");
        $users = $this->db->select("SELECT id, first_name, last_name, email FROM users WHERE deleted_at IS NULL ORDER BY first_name");
        
        $this->view('audit/index', [
            'title' => 'گزارش‌های ممیزی',
            'logs' => $logs,
            'actions' => $actions,
            'modules' => $modules,
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'userIdFilter' => $userIdFilter,
            'actionFilter' => $actionFilter,
            'moduleFilter' => $moduleFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
    }
    
    public function systemLogs()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $levelFilter = $_GET['level'] ?? '';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $sql = "SELECT * FROM system_logs WHERE created_at BETWEEN ? AND ?";
        $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
        
        if ($levelFilter) {
            $sql .= " AND level = ?";
            $params[] = $levelFilter;
        }
        
        $countSql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $logs = $this->db->select($sql, $params);
        
        $this->view('audit/system_logs', [
            'title' => 'لاگ‌های سیستم',
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'levelFilter' => $levelFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
    }
    
    public function loginHistory()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $userIdFilter = $_GET['user_id'] ?? '';
        
        $sql = "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.action IN ('login', 'logout', 'login_failed')
                AND al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $params = [];
        
        if ($userIdFilter) {
            $sql .= " AND al.user_id = ?";
            $params[] = $userIdFilter;
        }
        
        $countSql = str_replace("SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY al.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $logs = $this->db->select($sql, $params);
        
        $users = $this->db->select("SELECT id, first_name, last_name FROM users WHERE deleted_at IS NULL ORDER BY first_name");
        
        $this->view('audit/login_history', [
            'title' => 'تاریخچه ورود و خروج',
            'logs' => $logs,
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'userIdFilter' => $userIdFilter
        ]);
    }
    
    public function export()
    {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $logs = $this->db->select(
            "SELECT al.action, al.module, al.description, al.ip_address,
                    al.created_at, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM audit_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE al.created_at BETWEEN ? AND ?
             ORDER BY al.created_at DESC
             LIMIT 10000",
            [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        );
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($logs)) {
            fputcsv($output, ['Action', 'Module', 'Description', 'User', 'IP Address', 'Date/Time']);
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['action'],
                    $log['module'],
                    $log['description'],
                    $log['user_name'],
                    $log['ip_address'],
                    $log['created_at']
                ]);
            }
        }
        
        fclose($output);
        exit;
    }
    
    public function clearOldLogs()
    {
        // Clear logs older than 90 days
        $this->db->delete('audit_logs', 'created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)');
        $this->db->delete('system_logs', 'created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)');
        
        $this->logActivity('audit', 'clear_logs', 'لاگ‌های قدیمی پاکسازی شدند');
        
        $this->session->flash('success', 'لاگ‌های قدیمی با موفقیت پاکسازی شدند');
        $this->redirect('/audit-logs');
    }
    
    private function logActivity($module, $action, $description)
    {
        $userId = $this->session->get('user_id');
        
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}