<?php
namespace App\Models;

/**
 * Notification Read Model
 * مدیریت وضعیت خواندن اعلان‌ها
 */
class NotificationRead extends Model
{
    protected $table = 'notification_reads';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $existing = $this->db->selectOne(
            "SELECT id FROM notification_reads WHERE notification_id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
        
        if ($existing) return $existing['id'];
        
        return $this->db->insert('notification_reads', [
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId)
    {
        // Get all unread notifications for user
        $unreadIds = $this->db->select(
            "SELECT n.id FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             WHERE nr.id IS NULL 
             AND (n.recipient_type = 'all' OR (n.recipient_type = 'individual' AND n.recipient_id = ?))",
            [$userId, $userId]
        );
        
        $ids = [];
        foreach ($unreadIds as $row) {
            $ids[] = $this->markAsRead($row['id'], $userId);
        }
        
        return $ids;
    }
    
    /**
     * Check if notification is read
     */
    public function isRead($notificationId, $userId)
    {
        $result = $this->db->selectOne(
            "SELECT id FROM notification_reads WHERE notification_id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
        
        return !empty($result);
    }
    
    /**
     * Get read status for multiple notifications
     */
    public function getReadStatus($notificationIds, $userId)
    {
        if (empty($notificationIds)) return [];
        
        $placeholders = implode(',', array_fill(0, count($notificationIds), '?'));
        $params = array_merge([$userId], $notificationIds);
        
        $sql = "SELECT notification_id FROM notification_reads 
                WHERE user_id = ? AND notification_id IN ({$placeholders})";
        
        $results = $this->db->selectColumn($sql, $params, 0);
        
        $status = [];
        foreach ($notificationIds as $id) {
            $status[$id] = in_array($id, $results);
        }
        
        return $status;
    }
    
    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM notifications n 
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                WHERE nr.id IS NULL 
                AND (n.recipient_type = 'all' OR (n.recipient_type = 'individual' AND n.recipient_id = ?))";
        
        $result = $this->db->selectOne($sql, [$userId, $userId]);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Clean old read records
     */
    public function cleanOld($days = 90)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->db->delete('notification_reads', 'read_at < ?', [$date]);
    }
}