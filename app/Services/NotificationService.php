<?php
namespace App\Services;

use App\Core\Application;

/**
 * Notification Service
 * سرویس ارسال اعلان‌ها (درون برنامه، ایمیل، پیامک)
 */
class NotificationService
{
    private $db;
    private $config;
    private $smsService;
    private $logger;

    public function __construct()
    {
        $this->db = Application::getInstance()->getDB();
        $this->config = Application::getInstance()->getConfig();
        $this->smsService = new SmsService();
        $this->logger = Application::getInstance()->getLogger();
    }

    /**
     * Send notification via multiple channels
     * 
     * @param array $recipients Array of user IDs or 'all'
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type notification, announcement, reminder, alert, emergency
     * @param array $channels in_app, email, sms
     * @param int|null $senderId Sender user ID
     * @return array
     */
    public function send($recipients, $title, $message, $type = 'info', $channels = ['in_app'], $senderId = null)
    {
        $results = [];
        $userIds = $this->resolveRecipients($recipients);

        foreach ($channels as $channel) {
            switch ($channel) {
                case 'in_app':
                    $results['in_app'] = $this->sendInApp($userIds, $title, $message, $type, $senderId);
                    break;
                case 'email':
                    $results['email'] = $this->sendEmail($userIds, $title, $message, $type);
                    break;
                case 'sms':
                    $results['sms'] = $this->sendSms($userIds, $message, $type);
                    break;
            }
        }

        return [
            'success' => true,
            'message' => 'اعلان‌ها با موفقیت ارسال شدند',
            'data' => $results
        ];
    }

    /**
     * Send to all users
     */
    public function sendToAll($title, $message, $type = 'announcement', $channels = ['in_app'])
    {
        return $this->send('all', $title, $message, $type, $channels);
    }

    /**
     * Send to specific branch
     */
    public function sendToBranch($branchId, $title, $message, $type = 'info', $channels = ['in_app'])
    {
        return $this->send(['branch' => $branchId], $title, $message, $type, $channels);
    }

    /**
     * Send to specific class
     */
    public function sendToClass($classId, $title, $message, $type = 'info', $channels = ['in_app'])
    {
        return $this->send(['class' => $classId], $title, $message, $type, $channels);
    }

    /**
     * Send to specific role
     */
    public function sendToRole($roleId, $title, $message, $type = 'info', $channels = ['in_app'])
    {
        return $this->send(['role' => $roleId], $title, $message, $type, $channels);
    }

    /**
     * Send fee reminders to parents with pending fees
     */
    public function sendFeeReminders()
    {
        // Get overdue fees
        $overdueFees = $this->db->select(
            "SELECT sf.*, s.id as student_id, u_parent.id as parent_user_id,
                    u_parent.email as parent_email, u_parent.phone as parent_phone,
                    u_parent.first_name as parent_name,
                    u_student.first_name as student_name,
                    fc.category_name, sf.due_date, sf.balance_amount
             FROM student_fees sf 
             JOIN students s ON sf.student_id = s.id 
             JOIN users u_student ON s.user_id = u_student.id
             JOIN student_parent sp ON s.id = sp.student_id 
             JOIN parents p ON sp.parent_id = p.id 
             JOIN users u_parent ON p.user_id = u_parent.id
             JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
             JOIN fee_categories fc ON fs.fee_category_id = fc.id 
             WHERE sf.status IN ('pending', 'partial', 'overdue')
             AND sf.balance_amount > 0
             AND u_parent.is_active = 1"
        );

        $sentCount = 0;
        foreach ($overdueFees as $fee) {
            $daysLeft = ceil((strtotime($fee['due_date']) - time()) / 86400);
            
            if ($daysLeft <= 7 && $daysLeft >= -30) {
                // Send in-app notification
                $this->sendInApp(
                    [$fee['parent_user_id']],
                    'یادآوری پرداخت شهریه',
                    "{$fee['parent_name']} عزیز، شهریه {$fee['student_name']} برای {$fee['category_name']} به مبلغ " . number_format($fee['balance_amount']) . " تومان تا تاریخ {$fee['due_date']} سررسید شده است.",
                    'reminder'
                );

                // Send SMS if phone available
                if (!empty($fee['parent_phone'])) {
                    $this->smsService->sendFeeReminder(
                        $fee['parent_phone'],
                        $fee['parent_name'],
                        $fee['balance_amount'],
                        $fee['due_date']
                    );
                }

                $sentCount++;
            }
        }

        $this->logger->info("Fee reminders sent", ['count' => $sentCount]);

        return ['success' => true, 'message' => "{$sentCount} یادآوری شهریه ارسال شد"];
    }

    /**
     * Send attendance alerts to parents
     */
    public function sendAttendanceAlerts()
    {
        // Get today's absences
        $absences = $this->db->select(
            "SELECT sa.*, u_student.first_name as student_name,
                    u_parent.id as parent_user_id,
                    u_parent.phone as parent_phone,
                    u_parent.first_name as parent_name
             FROM student_attendance sa 
             JOIN students s ON sa.student_id = s.id 
             JOIN users u_student ON s.user_id = u_student.id
             JOIN student_parent sp ON s.id = sp.student_id 
             JOIN parents p ON sp.parent_id = p.id 
             JOIN users u_parent ON p.user_id = u_parent.id
             WHERE sa.attendance_date = CURDATE()
             AND sa.status = 'absent'
             AND sp.is_emergency_contact = 1
             AND u_parent.is_active = 1"
        );

        $sentCount = 0;
        foreach ($absences as $absence) {
            if (!empty($absence['parent_user_id'])) {
                $this->sendInApp(
                    [$absence['parent_user_id']],
                    'غیبت دانش‌آموز',
                    "{$absence['parent_name']} عزیز، فرزند شما {$absence['student_name']} امروز در مدرسه غایب بوده است.",
                    'alert'
                );
            }

            if (!empty($absence['parent_phone'])) {
                $this->smsService->sendAttendanceAlert(
                    $absence['parent_phone'],
                    $absence['parent_name'],
                    $absence['student_name']
                );
            }

            $sentCount++;
        }

        return ['success' => true, 'message' => "{$sentCount} هشدار غیبت ارسال شد"];
    }

    /**
     * Send exam results notification
     */
    public function sendResultNotifications($examId)
    {
        $exam = $this->db->selectOne(
            "SELECT e.*, et.name as exam_type, s.subject_name, c.class_name
             FROM exams e 
             JOIN exam_types et ON e.exam_type_id = et.id 
             JOIN subjects s ON e.subject_id = s.id 
             JOIN classes c ON e.class_id = c.id 
             WHERE e.id = ? AND e.status = 'completed'",
            [$examId]
        );

        if (!$exam) {
            return ['success' => false, 'message' => 'آزمون یافت نشد'];
        }

        // Get students in this class
        $students = $this->db->select(
            "SELECT s.id, u_student.first_name as student_name,
                    u_parent.id as parent_user_id,
                    u_parent.first_name as parent_name
             FROM students s 
             JOIN users u_student ON s.user_id = u_student.id
             JOIN student_parent sp ON s.id = sp.student_id 
             JOIN parents p ON sp.parent_id = p.id 
             JOIN users u_parent ON p.user_id = u_parent.id
             WHERE s.current_class_id = ? AND s.is_active = 1",
            [$exam['class_id']]
        );

        foreach ($students as $student) {
            if ($student['parent_user_id']) {
                $this->sendInApp(
                    [$student['parent_user_id']],
                    'نتایج آزمون منتشر شد',
                    "نتایج آزمون {$exam['exam_type']} {$exam['subject_name']} برای {$student['student_name']} منتشر شد.",
                    'announcement'
                );
            }
        }

        $this->logger->info("Result notifications sent for exam {$examId}");

        return ['success' => true, 'message' => 'اعلان نتایج آزمون ارسال شد'];
    }

    // ============================================
    // Channel Implementations
    // ============================================

    /**
     * Send in-app notification
     */
    private function sendInApp($userIds, $title, $message, $type = 'info', $senderId = null)
    {
        $count = 0;
        
        foreach ($userIds as $userId) {
            $this->db->insert('notifications', [
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
            $count++;
        }

        return ['sent' => $count, 'channel' => 'in_app'];
    }

    /**
     * Send email notification
     */
    private function sendEmail($userIds, $title, $message, $type)
    {
        $count = 0;
        
        $users = $this->db->select(
            "SELECT id, email, first_name FROM users WHERE id IN (" . implode(',', array_fill(0, count($userIds), '?')) . ") AND is_active = 1",
            $userIds
        );

        foreach ($users as $user) {
            if (!empty($user['email'])) {
                $this->sendEmailMessage($user['email'], $title, $message, $user['first_name']);
                $count++;
            }
        }

        return ['sent' => $count, 'channel' => 'email'];
    }

    /**
     * Send SMS notification
     */
    private function sendSms($userIds, $message, $type)
    {
        $count = 0;
        
        $users = $this->db->select(
            "SELECT id, phone, first_name FROM users WHERE id IN (" . implode(',', array_fill(0, count($userIds), '?')) . ") AND is_active = 1",
            $userIds
        );

        foreach ($users as $user) {
            if (!empty($user['phone'])) {
                $result = $this->smsService->send($user['phone'], $message);
                if ($result['success']) $count++;
            }
        }

        return ['sent' => $count, 'channel' => 'sms'];
    }

    /**
     * Resolve recipients to user IDs
     */
    private function resolveRecipients($recipients)
    {
        if ($recipients === 'all') {
            $users = $this->db->select("SELECT id FROM users WHERE is_active = 1 AND deleted_at IS NULL");
            return array_column($users, 'id');
        }

        if (is_array($recipients)) {
            // Check if it's a simple array of user IDs
            if (isset($recipients[0]) && is_numeric($recipients[0])) {
                return $recipients;
            }

            // Check for special recipient types
            if (isset($recipients['branch'])) {
                $users = $this->db->select(
                    "SELECT id FROM users WHERE branch_id = ? AND is_active = 1 AND deleted_at IS NULL",
                    [$recipients['branch']]
                );
                return array_column($users, 'id');
            }

            if (isset($recipients['class'])) {
                $users = $this->db->select(
                    "SELECT u.id FROM users u 
                     JOIN students s ON u.id = s.user_id 
                     WHERE s.current_class_id = ? AND s.is_active = 1",
                    [$recipients['class']]
                );
                return array_column($users, 'id');
            }

            if (isset($recipients['role'])) {
                $users = $this->db->select(
                    "SELECT id FROM users WHERE role_id = ? AND is_active = 1 AND deleted_at IS NULL",
                    [$recipients['role']]
                );
                return array_column($users, 'id');
            }
        }

        return [];
    }

    /**
     * Send email message
     */
    private function sendEmailMessage($to, $subject, $body, $name = '')
    {
        $fromAddress = $this->config['notifications']['email_from_address'] ?? 'noreply@school.com';
        $fromName = $this->config['notifications']['email_from_name'] ?? 'سیستم مدیریت مدرسه';
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromAddress}>\r\n";
        
        $htmlBody = $this->buildEmailTemplate($subject, $body, $name);

        return mail($to, $subject, $htmlBody, $headers);
    }

    /**
     * Build email HTML template
     */
    private function buildEmailTemplate($title, $body, $name = '')
    {
        return <<<HTML
        <!DOCTYPE html>
        <html dir="rtl">
        <head><meta charset="UTF-8"></head>
        <body style="font-family:Tahoma,sans-serif;background:#f4f4f4;margin:0;padding:0;">
            <div style="max-width:600px;margin:20px auto;background:#fff;border-radius:10px;overflow:hidden;">
                <div style="background:#4e73df;padding:25px;text-align:center;">
                    <h2 style="color:#fff;margin:0;">{$title}</h2>
                </div>
                <div style="padding:30px;">
                    {$body}
                </div>
                <div style="background:#f8f9fc;padding:15px;text-align:center;border-top:1px solid #e3e6f0;">
                    <small style="color:#888;">© {$this->config['app_name']}</small>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}