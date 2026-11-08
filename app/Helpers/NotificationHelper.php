<?php
namespace App\Helpers;

use App\Core\Application;

/**
 * Notification Helper Class
 * توابع کمکی برای ارسال اعلان‌ها
 */
class NotificationHelper
{
    /**
     * Send in-app notification
     * 
     * @param int|array $userIds User ID(s)
     * @param string $title
     * @param string $message
     * @param string $type info, announcement, reminder, alert, emergency
     * @param int|null $senderId
     * @return int|false Notification ID
     */
    public static function sendInApp($userIds, $title, $message, $type = 'info', $senderId = null)
    {
        $db = Application::getInstance()->getDB();
        $userIds = (array)$userIds;

        $notificationId = null;
        foreach ($userIds as $userId) {
            $id = $db->insert('notifications', [
                'sender_id' => $senderId,
                'recipient_type' => 'individual',
                'recipient_id' => $userId,
                'title' => $title,
                'message' => $message,
                'notification_type' => $type,
                'channel' => 'in_app',
                'is_sent' => 1,
                'sent_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            if ($notificationId === null) $notificationId = $id;
        }

        return $notificationId;
    }

    /**
     * Send notification to all users
     * 
     * @param string $title
     * @param string $message
     * @param string $type
     * @param int|null $senderId
     * @return int|false
     */
    public static function sendToAll($title, $message, $type = 'announcement', $senderId = null)
    {
        $db = Application::getInstance()->getDB();

        return $db->insert('notifications', [
            'sender_id' => $senderId,
            'recipient_type' => 'all',
            'recipient_id' => null,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'channel' => 'in_app',
            'is_sent' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send notification to specific branch
     * 
     * @param int $branchId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param int|null $senderId
     * @return int|false
     */
    public static function sendToBranch($branchId, $title, $message, $type = 'info', $senderId = null)
    {
        $db = Application::getInstance()->getDB();

        return $db->insert('notifications', [
            'sender_id' => $senderId,
            'recipient_type' => 'branch',
            'recipient_id' => $branchId,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'channel' => 'in_app',
            'is_sent' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send notification to specific class
     * 
     * @param int $classId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param int|null $senderId
     * @return int|false
     */
    public static function sendToClass($classId, $title, $message, $type = 'info', $senderId = null)
    {
        $db = Application::getInstance()->getDB();

        return $db->insert('notifications', [
            'sender_id' => $senderId,
            'recipient_type' => 'class',
            'recipient_id' => $classId,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'channel' => 'in_app',
            'is_sent' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send notification to specific role
     * 
     * @param int $roleId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param int|null $senderId
     * @return int|false
     */
    public static function sendToRole($roleId, $title, $message, $type = 'info', $senderId = null)
    {
        $db = Application::getInstance()->getDB();

        return $db->insert('notifications', [
            'sender_id' => $senderId,
            'recipient_type' => 'role',
            'recipient_id' => $roleId,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'channel' => 'in_app',
            'is_sent' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send email notification
     * 
     * @param string|array $emails
     * @param string $subject
     * @param string $body HTML body
     * @param array $attachments Array of file paths
     * @return bool
     */
    public static function sendEmail($emails, $subject, $body, $attachments = [])
    {
        $config = Application::getInstance()->getConfig();
        $fromAddress = $config['notifications']['email_from_address'] ?? 'noreply@school.com';
        $fromName = $config['notifications']['email_from_name'] ?? 'سیستم مدیریت مدرسه';

        $emails = (array)$emails;

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromAddress}>\r\n";

        $success = true;
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
            if (!mail($email, $subject, $body, $headers)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Send SMS notification
     * 
     * @param string|array $mobiles
     * @param string $message
     * @return array
     */
    public static function sendSms($mobiles, $message)
    {
        $smsService = new \App\Services\SmsService();
        return $smsService->sendBulk((array)$mobiles, $message);
    }

    /**
     * Get unread notification count for user
     * 
     * @param int $userId
     * @return int
     */
    public static function getUnreadCount($userId)
    {
        $db = Application::getInstance()->getDB();

        $result = $db->selectOne(
            "SELECT COUNT(*) as count
             FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             WHERE nr.id IS NULL 
             AND ((n.recipient_type = 'all')
                  OR (n.recipient_type = 'individual' AND n.recipient_id = ?))",
            [$userId, $userId]
        );

        return (int)($result['count'] ?? 0);
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public static function markAsRead($notificationId, $userId)
    {
        $db = Application::getInstance()->getDB();

        $existing = $db->selectOne(
            "SELECT id FROM notification_reads WHERE notification_id = ? AND user_id = ?",
            [$notificationId, $userId]
        );

        if (!$existing) {
            $db->insert('notification_reads', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }

        return true;
    }

    /**
     * Mark all notifications as read for user
     * 
     * @param int $userId
     * @return int Number marked as read
     */
    public static function markAllAsRead($userId)
    {
        $db = Application::getInstance()->getDB();

        $unread = $db->select(
            "SELECT n.id FROM notifications n 
             LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
             WHERE nr.id IS NULL 
             AND ((n.recipient_type = 'all')
                  OR (n.recipient_type = 'individual' AND n.recipient_id = ?))",
            [$userId, $userId]
        );

        $count = 0;
        foreach ($unread as $n) {
            $db->insert('notification_reads', [
                'notification_id' => $n['id'],
                'user_id' => $userId,
                'read_at' => date('Y-m-d H:i:s')
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Delete old notifications
     * 
     * @param int $days Days to keep
     * @return int Number deleted
     */
    public static function cleanOld($days = 90)
    {
        $db = Application::getInstance()->getDB();
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $deleted = $db->delete('notifications', 'created_at < ?', [$date]);
        return $deleted;
    }

    /**
     * Build notification message from template
     * 
     * @param string $template Template name
     * @param array $params Parameters to replace
     * @return string
     */
    public static function buildMessage($template, $params = [])
    {
        $templates = [
            'welcome' => '{$name} عزیز، به سیستم مدیریت مدرسه خوش آمدید.',
            'fee_reminder' => '{$name} عزیز، مبلغ {$amount} تومان شهریه {$student_name} تا تاریخ {$date} سررسید می‌شود.',
            'payment_success' => 'پرداخت شما به مبلغ {$amount} تومان با موفقیت انجام شد. شماره پیگیری: {$ref_id}',
            'attendance_alert' => '{$name} عزیز، {$student_name} امروز در مدرسه غایب بوده است.',
            'result_published' => 'نتایج آزمون {$exam_name} برای {$student_name} منتشر شد.',
            'event_reminder' => 'یادآوری: {$event_name} در تاریخ {$date} برگزار می‌شود.',
        ];

        $message = $templates[$template] ?? $template;

        foreach ($params as $key => $value) {
            $message = str_replace('{$' . $key . '}', $value, $message);
        }

        return $message;
    }
}