<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class NotificationController extends Controller
{
    /**
     * List notifications for current user
     * GET /api/v1/notifications
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $page = $this->request->get('page', 1);
        $perPage = min(50, $this->request->get('per_page', 20));

        $sql = "SELECT n.*, nr.read_at,
                       CONCAT(u.first_name, ' ', u.last_name) as sender_name
                FROM notifications n 
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                LEFT JOIN users u ON n.sender_id = u.id
                WHERE (n.recipient_type = 'all')
                   OR (n.recipient_type = 'individual' AND n.recipient_id = ?)
                ORDER BY n.created_at DESC
                LIMIT ? OFFSET ?";

        $offset = ($page - 1) * $perPage;
        $notifications = $this->db->select($sql, [$userId, $userId, $perPage, $offset]);

        // Count total
        $total = $this->db->selectOne(
            "SELECT COUNT(*) as count
             FROM notifications n 
             WHERE (n.recipient_type = 'all')
                OR (n.recipient_type = 'individual' AND n.recipient_id = ?)",
            [$userId]
        )['count'] ?? 0;

        $this->json([
            'success' => true,
            'data' => $notifications,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Get unread notifications
     * GET /api/v1/notifications/unread
     */
    public function unread()
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $limit = min(20, $this->request->get('limit', 10));

        $notifications = $this->db->select(
            "SELECT n.*, nr.read_at,
                    CONCAT(u.first_name, ' ', u.last_name) as sender_name
             FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             LEFT JOIN users u ON n.sender_id = u.id
             WHERE nr.id IS NULL
             AND ((n.recipient_type = 'all')
                  OR (n.recipient_type = 'individual' AND n.recipient_id = ?))
             ORDER BY n.created_at DESC
             LIMIT ?",
            [$userId, $userId, $limit]
        );

        $this->json(['success' => true, 'data' => ['notifications' => $notifications]]);
    }

    /**
     * Get unread count
     * GET /api/v1/notifications/count
     */
    public function count()
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
             AND ((n.recipient_type = 'all')
                  OR (n.recipient_type = 'individual' AND n.recipient_id = ?))",
            [$userId, $userId]
        )['count'] ?? 0;

        $this->json(['success' => true, 'count' => (int)$count]);
    }

    /**
     * Mark notification as read
     * POST /api/v1/notifications/{id}/read
     */
    public function markAsRead($id)
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

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

        $this->json(['success' => true, 'message' => 'علامت‌گذاری شد']);
    }

    /**
     * Mark all notifications as read
     * POST /api/v1/notifications/mark-all-read
     */
    public function markAllAsRead()
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $unread = $this->db->select(
            "SELECT n.id FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             WHERE nr.id IS NULL 
             AND ((n.recipient_type = 'all')
                  OR (n.recipient_type = 'individual' AND n.recipient_id = ?))",
            [$userId, $userId]
        );

        foreach ($unread as $n) {
            $this->db->insert('notification_reads', [
                'notification_id' => $n['id'],
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->json(['success' => true, 'message' => 'همه اعلان‌ها خوانده شدند']);
    }

    /**
     * Create notification
     * POST /api/v1/notifications
     */
    public function store()
    {
        $data = $this->request->getJson();

        if (empty($data['title']) || empty($data['message'])) {
            $this->json(['success' => false, 'message' => 'عنوان و متن الزامی است'], 422);
            return;
        }

        $notificationId = $this->db->insert('notifications', [
            'sender_id' => $this->session->get('user_id'),
            'recipient_type' => $data['recipient_type'] ?? 'all',
            'recipient_id' => $data['recipient_id'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'notification_type' => $data['notification_type'] ?? 'info',
            'channel' => $data['channel'] ?? 'in_app',
            'is_sent' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->json([
            'success' => true,
            'message' => 'اعلان با موفقیت ارسال شد',
            'data' => ['id' => $notificationId]
        ], 201);
    }
}