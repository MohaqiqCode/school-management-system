<?php
/**
 * School Management System - Services Configuration
 * 
 * تنظیمات سرویس‌های خارجی
 */

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Service Providers
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'default' => env('SMS_PROVIDER', 'kavenegar'),
        
        'providers' => [
            'kavenegar' => [
                'api_key' => env('KAVENEGAR_API_KEY', ''),
                'sender' => env('KAVENEGAR_SENDER', '10008663'),
                'url' => 'https://api.kavenegar.com/v1/',
            ],
            'ghasedak' => [
                'api_key' => env('GHASEDAK_API_KEY', ''),
                'sender' => env('GHASEDAK_SENDER', ''),
                'url' => 'https://api.ghasedak.me/v2/',
            ],
            'melipayamak' => [
                'username' => env('MELIPAYAMAK_USERNAME', ''),
                'password' => env('MELIPAYAMAK_PASSWORD', ''),
                'from' => env('MELIPAYAMAK_FROM', ''),
                'url' => 'https://rest.payamak-panel.com/api/SendSMS/',
            ],
        ],
        
        'templates' => [
            'welcome' => [
                'text' => '{$name} عزیز، به سیستم مدیریت مدرسه خوش آمدید.',
                'provider_template_id' => '',
            ],
            'fee_reminder' => [
                'text' => '{$name} عزیز، مبلغ {$amount} تومان شهریه شما تا تاریخ {$date} سررسید می‌شود.',
                'provider_template_id' => '',
            ],
            'payment_receipt' => [
                'text' => 'پرداخت شما به مبلغ {$amount} تومان با موفقیت انجام شد. شماره پیگیری: {$tracking_code}',
                'provider_template_id' => '',
            ],
            'attendance_alert' => [
                'text' => '{$name} عزیز، فرزند شما {$student_name} امروز غایب بوده است.',
                'provider_template_id' => '',
            ],
            'result_published' => [
                'text' => 'نتایج آزمون {$exam_name} منتشر شد. برای مشاهده وارد سامانه شوید.',
                'provider_template_id' => '',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'default' => env('PAYMENT_GATEWAY', 'zarinpal'),
        
        'gateways' => [
            'zarinpal' => [
                'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
                'sandbox' => env('ZARINPAL_SANDBOX', true),
                'url' => 'https://api.zarinpal.com/pg/v4/payment/',
                'callback_url' => env('APP_URL', '') . '/payment/callback/zarinpal',
            ],
            'zibal' => [
                'merchant' => env('ZIBAL_MERCHANT', ''),
                'sandbox' => env('ZIBAL_SANDBOX', true),
                'url' => 'https://gateway.zibal.ir/v1/',
                'callback_url' => env('APP_URL', '') . '/payment/callback/zibal',
            ],
            'nextpay' => [
                'api_key' => env('NEXTPAY_API_KEY', ''),
                'sandbox' => env('NEXTPAY_SANDBOX', true),
                'url' => 'https://nextpay.org/nx/gateway/',
                'callback_url' => env('APP_URL', '') . '/payment/callback/nextpay',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Map Service (for Transport Module)
    |--------------------------------------------------------------------------
    */
    'map' => [
        'default' => 'neshan',
        
        'providers' => [
            'neshan' => [
                'api_key' => env('NESHAN_API_KEY', ''),
                'url' => 'https://api.neshan.org/v1/',
                'web_key' => env('NESHAN_WEB_KEY', ''),
            ],
            'google_maps' => [
                'api_key' => env('GOOGLE_MAPS_API_KEY', ''),
                'url' => 'https://maps.googleapis.com/maps/api/',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cloud Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'default' => 'local',
        
        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => 'storage/',
            ],
            'public' => [
                'driver' => 'local',
                'root' => 'public/assets/uploads/',
                'url' => env('APP_URL', '') . '/assets/uploads/',
                'visibility' => 'public',
            ],
            's3' => [
                'driver' => 's3',
                'key' => env('AWS_ACCESS_KEY_ID', ''),
                'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'bucket' => env('AWS_BUCKET', ''),
                'url' => env('AWS_URL', ''),
                'endpoint' => env('AWS_ENDPOINT', ''),
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | PDF Generation
    |--------------------------------------------------------------------------
    */
    'pdf' => [
        'default_font' => 'DejaVu Sans',
        'default_font_size' => 12,
        'paper_size' => 'A4',
        'orientation' => 'portrait',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 20,
        'margin_bottom' => 20,
        'header_html' => null,
        'footer_html' => null,
        'watermark_text' => null,
        'watermark_opacity' => 0.1,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Excel Export
    |--------------------------------------------------------------------------
    */
    'excel' => [
        'default_font' => 'Calibri',
        'default_font_size' => 11,
        'header_font_size' => 12,
        'header_background_color' => '4472C4',
        'header_font_color' => 'FFFFFF',
        'auto_filter' => true,
        'freeze_pane' => 'A2',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Captcha
    |--------------------------------------------------------------------------
    */
    'captcha' => [
        'enabled' => false,
        'provider' => 'google', // google, hcaptcha
        'google' => [
            'site_key' => env('GOOGLE_RECAPTCHA_SITE_KEY', ''),
            'secret_key' => env('GOOGLE_RECAPTCHA_SECRET_KEY', ''),
            'version' => 'v2', // v2, v3
        ],
        'hcaptcha' => [
            'site_key' => env('HCAPTCHA_SITE_KEY', ''),
            'secret_key' => env('HCAPTCHA_SECRET_KEY', ''),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Push Notifications
    |--------------------------------------------------------------------------
    */
    'push_notification' => [
        'enabled' => false,
        'firebase' => [
            'server_key' => env('FIREBASE_SERVER_KEY', ''),
            'sender_id' => env('FIREBASE_SENDER_ID', ''),
            'url' => 'https://fcm.googleapis.com/fcm/send',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Analytics & Tracking
    |--------------------------------------------------------------------------
    */
    'analytics' => [
        'enabled' => false,
        'google_analytics' => [
            'tracking_id' => env('GOOGLE_ANALYTICS_ID', ''),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Backup Service
    |--------------------------------------------------------------------------
    */
    'backup_service' => [
        'enabled' => true,
        'database' => [
            'enabled' => true,
            'path' => 'storage/backups/database/',
            'compress' => true,
            'retention_days' => 30,
        ],
        'files' => [
            'enabled' => false,
            'paths' => [
                'public/assets/uploads/',
            ],
            'exclude' => [
                'public/assets/uploads/temp/',
            ],
            'compress' => true,
        ],
        'cloud' => [
            'enabled' => false,
            'disk' => 's3',
            'delete_local_after_upload' => true,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Monitoring & Health Check
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => false,
        'endpoint' => '/health',
        'check_database' => true,
        'check_cache' => true,
        'check_storage' => true,
        'alert_email' => env('MONITORING_ALERT_EMAIL', ''),
    ],
];