<?php
namespace App\Models;

/**
 * Notification Model
 * مدیریت اعلان‌ها
 */
class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'sender_id',
        'recipient_type',
        'recipient_id',
        'title',
        'message',
        'notification_type',
        'channel',
        'is_sent',
        'sent_at',
    ];
    
    protected $casts = [
        'is_sent' => 'bool',
        'sent_at' => 'datetime',
    ];
    
    protected $appends = [
        'sender_name',
        'is_read',
        'time_ago',
    ];
    
    /**
     * Send notification
     */
    public function send($data)
    {
        $this->db->beginTransaction();
        
        try {
            $notificationId = $this->db->insert('notifications', [
                'sender_id' => $data['sender_id'] ?? null,
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
            
            $this->db->commit();
            return $notificationId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Send to multiple recipients
     */
    public function sendToMany($recipients, $data)
    {
        $this->db->beginTransaction();
        
        try {
            $ids = [];
            
            foreach ($recipients as $recipient) {
                $ids[] = $this->db->insert('notifications', [
                    'sender_id' => $data['sender_id'] ?? null,
                    'recipient_type' => $recipient['type'] ?? 'individual',
                    'recipient_id' => $recipient['id'],
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'notification_type' => $data['notification_type'] ?? 'info',
                    'channel' => $data['channel'] ?? 'in_app',
                    'is_sent' => 1,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            return $ids;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Send to a class
     */
    public function sendToClass($classId, $data)
    {
        $students = $this->db->select(
            "SELECT user_id FROM students WHERE current_class_id = ? AND is_active = 1",
            [$classId]
        );
        
        $recipients = [];
        foreach ($students as $student) {
            $recipients[] = ['type' => 'individual', 'id' => $student['user_id']];
        }
        
        return $this->sendToMany($recipients, $data);
    }
    
    /**
     * Get notifications for user
     */
    public function getForUser($userId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT n.*, nr.read_at,
                       CONCAT(u.first_name, ' ', u.last_name) as sender_name
                FROM notifications n 
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                LEFT JOIN users u ON n.sender_id = u.id
                WHERE (n.recipient_type = 'all')
                   OR (n.recipient_type = 'individual' AND n.recipient_id = ?)
                ORDER BY n.created_at DESC
                LIMIT ? OFFSET ?";
        
        return $this->db->select($sql, [$userId, $userId, $limit, $offset]);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM notifications n 
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                WHERE nr.id IS NULL 
                AND ((n.recipient_type = 'all')
                     OR (n.recipient_type = 'individual' AND n.recipient_id = ?))";
        
        $result = $this->db->selectOne($sql, [$userId, $userId]);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Mark as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $existing = $this->db->selectOne(
            "SELECT id FROM notification_reads WHERE notification_id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
        
        if (!$existing) {
            $this->db->insert('notification_reads', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return true;
    }
    
    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId)
    {
        $unread = $this->db->select(
            "SELECT n.id FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             WHERE nr.id IS NULL 
             AND ((n.recipient_type = 'all')
                  OR (n.recipient_type = 'individual' AND n.recipient_id = ?))",
            [$userId, $userId]
        );
        
        foreach ($unread as $notification) {
            $this->db->insert('notification_reads', [
                'notification_id' => $notification['id'],
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return true;
    }
    
    /**
     * Delete old notifications
     */
    public function deleteOld($days = 90)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Delete read notifications older than X days
        $this->db->delete('notification_reads', 'read_at < ?', [$date]);
        
        // Delete notifications older than X days that have been read
        $sql = "DELETE n FROM notifications n 
                LEFT JOIN notification_reads nr ON n.id = nr.notification_id 
                WHERE n.created_at < ? AND nr.id IS NOT NULL";
        
        return $this->db->query($sql, [$date]);
    }
    
    // Accessors
    public function appendSenderNameAttribute()
    {
        return $this->attributes['sender_name'] ?? 'سیستم';
    }
    
    public function appendIsReadAttribute()
    {
        return !empty($this->attributes['read_at']);
    }
    
    public function appendTimeAgoAttribute()
    {
        $timestamp = strtotime($this->attributes['created_at'] ?? 'now');
        $diff = time() - $timestamp;
        
        if ($diff < 60) return 'همین الان';
        if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
        if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
        if ($diff < 2592000) return floor($diff / 86400) . ' روز پیش';
        if ($diff < 31536000) return floor($diff / 2592000) . ' ماه پیش';
        return floor($diff / 31536000) . ' سال پیش';
    }
}