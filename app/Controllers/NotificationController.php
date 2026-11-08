<?php
namespace App\Controllers;

use App\Core\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 25;
        $typeFilter = $_GET['type'] ?? '';
        
        $sql = "SELECT n.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as sender_name
                FROM notifications n 
                LEFT JOIN users u ON n.sender_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($typeFilter) {
            $sql .= " AND n.notification_type = ?";
            $params[] = $typeFilter;
        }
        
        $countSql = str_replace("SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as sender_name", "SELECT COUNT(*) as total", $sql);
        $totalResult = $this->db->selectOne($countSql, $params);
        $total = $totalResult['total'] ?? 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sql .= " ORDER BY n.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $notifications = $this->db->select($sql, $params);
        
        $this->view('notifications/index', [
            'title' => 'مدیریت اعلان‌ها',
            'notifications' => $notifications,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'typeFilter' => $typeFilter
        ]);
    }
    
    public function create()
    {
        $branches = $this->db->select("SELECT id, name FROM branches WHERE is_active = 1");
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE is_active = 1 ORDER BY class_level");
        $roles = $this->db->select("SELECT id, display_name FROM roles ORDER BY display_name");
        
        $this->view('notifications/create', [
            'title' => 'ارسال اعلان جدید',
            'branches' => $branches,
            'classes' => $classes,
            'roles' => $roles
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        if (empty($data['title']) || empty($data['message'])) {
            $this->session->flash('error', 'عنوان و متن اعلان الزامی است');
            $this->redirect('/notifications/create');
            return;
        }
        
        $recipientType = $data['recipient_type'] ?? 'all';
        $recipientId = $data['recipient_id'] ?? null;
        
        // If sending to a class, get all sections
        if ($recipientType === 'class' && $recipientId) {
            // Also send to individual students in that class
            $this->sendNotificationToClass($data, $recipientId);
        } else {
            // Send single notification
            $this->db->insert('notifications', [
                'sender_id' => $this->session->get('user_id'),
                'recipient_type' => $recipientType,
                'recipient_id' => $recipientId,
                'title' => $data['title'],
                'message' => $data['message'],
                'notification_type' => $data['notification_type'] ?? 'info',
                'channel' => $data['channel'] ?? 'in_app',
                'is_sent' => 1,
                'sent_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $this->logActivity('notifications', 'send', "اعلان '{$data['title']}' ارسال شد");
        
        $this->session->flash('success', 'اعلان با موفقیت ارسال شد');
        $this->redirect('/notifications');
    }
    
    public function markAsRead($id)
    {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        // Check if already read
        $existing = $this->db->selectOne(
            "SELECT id FROM notification_reads WHERE notification_id = ? AND user_id = ?",
            [$id, $userId]
        );
        
        if (!$existing) {
            $this->db->insert('notification_reads', [
                'notification_id' => $id,
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $this->json(['success' => true, 'message' => 'اعلان خوانده شد']);
    }
    
    public function markAllAsRead()
    {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        // Get all unread notifications for this user
        $unreadNotifications = $this->db->select(
            "SELECT n.id FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             WHERE nr.id IS NULL AND (n.recipient_type = 'all' OR n.recipient_id = ?)",
            [$userId, $userId]
        );
        
        foreach ($unreadNotifications as $notification) {
            $this->db->insert('notification_reads', [
                'notification_id' => $notification['id'],
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $this->json(['success' => true, 'message' => 'همه اعلان‌ها خوانده شدند']);
    }
    
    public function delete($id)
    {
        $notification = $this->db->selectOne("SELECT * FROM notifications WHERE id = ?", [$id]);
        
        if (!$notification) {
            $this->json(['success' => false, 'message' => 'اعلان یافت نشد'], 404);
            return;
        }
        
        $this->db->delete('notification_reads', 'notification_id = ?', [$id]);
        $this->db->delete('notifications', 'id = ?', [$id]);
        
        $this->json(['success' => true, 'message' => 'اعلان حذف شد']);
    }
    
    public function getUnreadCount()
    {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            $this->json(['success' => false, 'count' => 0]);
            return;
        }
        
        $count = $this->db->selectOne(
            "SELECT COUNT(*) as count 
             FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             WHERE nr.id IS NULL 
             AND (n.recipient_type = 'all' 
                  OR (n.recipient_type = 'individual' AND n.recipient_id = ?))",
            [$userId, $userId]
        )['count'];
        
        $this->json(['success' => true, 'count' => $count]);
    }
    
    private function sendNotificationToClass($data, $classId)
    {
        // Get all students in this class
        $students = $this->db->select(
            "SELECT s.user_id FROM students s WHERE s.current_class_id = ? AND s.is_active = 1",
            [$classId]
        );
        
        foreach ($students as $student) {
            $this->db->insert('notifications', [
                'sender_id' => $this->session->get('user_id'),
                'recipient_type' => 'individual',
                'recipient_id' => $student['user_id'],
                'title' => $data['title'],
                'message' => $data['message'],
                'notification_type' => $data['notification_type'] ?? 'info',
                'channel' => $data['channel'] ?? 'in_app',
                'is_sent' => 1,
                'sent_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
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