<?php
/**
 * School Management System - Mail Configuration
 * 
 * تنظیمات ایمیل
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */
    'default' => env('MAIL_MAILER', 'smtp'),
    
    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'timeout' => null,
            'auth_mode' => null,
        ],
        
        'ses' => [
            'transport' => 'ses',
        ],
        
        'mailgun' => [
            'transport' => 'mailgun',
            'domain' => env('MAILGUN_DOMAIN', ''),
            'secret' => env('MAILGUN_SECRET', ''),
            'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        ],
        
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => '/usr/sbin/sendmail -bs -i',
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => 'mail',
        ],
        
        'array' => [
            'transport' => 'array',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@schoolmanagement.com'),
        'name' => env('MAIL_FROM_NAME', 'سیستم مدیریت مدرسه'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    */
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            'app/Views/emails',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Templates
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'welcome' => [
            'subject' => 'خوش آمدید به سیستم مدیریت مدرسه',
            'template' => 'emails.welcome',
        ],
        'password_reset' => [
            'subject' => 'بازنشانی رمز عبور',
            'template' => 'emails.password_reset',
        ],
        'email_verification' => [
            'subject' => 'تأیید ایمیل',
            'template' => 'emails.verify_email',
        ],
        'new_student_registration' => [
            'subject' => 'ثبت نام موفق',
            'template' => 'emails.student_registration',
        ],
        'fee_reminder' => [
            'subject' => 'یادآوری پرداخت شهریه',
            'template' => 'emails.fee_reminder',
        ],
        'payment_receipt' => [
            'subject' => 'رسید پرداخت',
            'template' => 'emails.payment_receipt',
        ],
        'result_published' => [
            'subject' => 'نتایج آزمون منتشر شد',
            'template' => 'emails.result_published',
        ],
        'attendance_alert' => [
            'subject' => 'هشدار حضور و غیاب',
            'template' => 'emails.attendance_alert',
        ],
        'event_notification' => [
            'subject' => 'اطلاع‌رسانی رویداد',
            'template' => 'emails.event_notification',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Queue
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => false,
        'connection' => 'database',
        'queue' => 'emails',
        'retry_after' => 60,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'enabled' => true,
        'max_per_hour' => 100,
        'max_per_day' => 1000,
    ],
];