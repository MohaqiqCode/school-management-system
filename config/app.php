<?php
/**
 * School Management System - Application Configuration
 * 
 * تنظیمات اصلی برنامه
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'app_name' => 'School Management System',
    'app_name_short' => 'SMS',
    'app_version' => '1.0.0',
    'app_environment' => env('APP_ENV', 'production'), // production, development, staging
    
    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */
    'app_url' => env('APP_URL', 'http://localhost/school-management-system'),
    'app_url_secure' => env('APP_URL_SECURE', false),
    'asset_url' => env('ASSET_URL', '/assets'),
    
    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    */
    'app_debug' => env('APP_DEBUG', false),
    
    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    */
    'app_timezone' => 'Asia/Tehran',
    
    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    */
    'app_locale' => 'fa',
    'app_fallback_locale' => 'en',
    'app_locales' => ['fa' => 'فارسی', 'en' => 'English'],
    
    /*
    |--------------------------------------------------------------------------
    | Application Direction (RTL/LTR)
    |--------------------------------------------------------------------------
    */
    'app_direction' => 'rtl', // rtl for Persian, ltr for English
    
    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    */
    'app_key' => env('APP_KEY', 'base64:your-encryption-key-here-change-in-production'),
    
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    */
    'session' => [
        'driver' => 'file', // file, database, redis
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
        'encrypt' => true,
        'secure' => env('SESSION_SECURE_COOKIE', false),
        'httponly' => true,
        'samesite' => 'Lax', // None, Lax, Strict
        'cookie_name' => 'sms_session',
        'path' => '/',
        'domain' => env('SESSION_DOMAIN', null),
        'table' => 'sessions', // if driver is database
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cookie Configuration
    |--------------------------------------------------------------------------
    */
    'cookie' => [
        'lifetime' => 43200, // 30 days in minutes
        'path' => '/',
        'domain' => env('COOKIE_DOMAIN', null),
        'secure' => env('COOKIE_SECURE', false),
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'csrf_token_lifetime' => 7200, // 2 hours
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numeric' => true,
        'password_require_special_char' => false,
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15 minutes in seconds
        'password_reset_expiry' => 3600, // 1 hour in seconds
        'email_verification_expiry' => 86400, // 24 hours in seconds
        'jwt_expiry' => 86400, // 24 hours in seconds
        'refresh_token_expiry' => 604800, // 7 days in seconds
        'remember_me_expiry' => 2592000, // 30 days in seconds
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'password_history' => [
            'enabled' => true,
            'check_count' => 5, // Check last 5 passwords
        ],
        'two_factor_auth' => [
            'enabled' => false,
            'provider' => 'email', // email, sms, authenticator
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 25,
        'max_per_page' => 100,
        'page_name' => 'page',
        'per_page_name' => 'per_page',
        'on_each_side' => 3,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'default' => 'file', // file, redis, memcached
        'prefix' => 'sms_cache',
        'ttl' => 3600, // 1 hour default
        'file_path' => 'storage/cache/',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Date & Time Formats
    |--------------------------------------------------------------------------
    */
    'date_format' => 'Y-m-d',
    'date_format_display' => 'Y/m/d',
    'time_format' => 'H:i:s',
    'time_format_display' => 'H:i',
    'datetime_format' => 'Y-m-d H:i:s',
    'datetime_format_display' => 'Y/m/d H:i',
    'first_day_of_week' => 6, // 0=Sunday, 6=Saturday
    'weekend_days' => [5, 6], // Friday and Saturday
    
    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code' => 'IRR',
        'symbol' => 'ریال',
        'name' => 'Iranian Rial',
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'decimals' => 0,
        'position' => 'after', // before or after
    ],
    
    /*
    |--------------------------------------------------------------------------
    | School Configuration
    |--------------------------------------------------------------------------
    */
    'school' => [
        'grade_levels' => [
            1 => 'پایه اول',
            2 => 'پایه دوم',
            3 => 'پایه سوم',
            4 => 'پایه چهارم',
            5 => 'پایه پنجم',
            6 => 'پایه ششم',
            7 => 'پایه هفتم',
            8 => 'پایه هشتم',
            9 => 'پایه نهم',
            10 => 'پایه دهم',
            11 => 'پایه یازدهم',
            12 => 'پایه دوازدهم',
        ],
        'attendance_statuses' => [
            'present' => 'حاضر',
            'absent' => 'غایب',
            'late' => 'تأخیر',
            'half_day' => 'نیمه وقت',
            'excused' => 'غیبت موجه',
            'holiday' => 'تعطیل',
        ],
        'exam_types' => [
            'mid_term' => 'میان ترم',
            'final' => 'پایان ترم',
            'unit_test' => 'آزمونک',
            'quarterly' => 'فصلی',
            'half_yearly' => 'نیمسال',
            'annual' => 'سالانه',
            'practical' => 'عملی',
            'oral' => 'شفاهی',
        ],
        'fee_frequencies' => [
            'monthly' => 'ماهانه',
            'quarterly' => 'سه ماهه',
            'half_yearly' => 'شش ماهه',
            'yearly' => 'سالانه',
            'one_time' => 'یکبار',
        ],
        'payment_methods' => [
            'cash' => 'نقد',
            'check' => 'چک',
            'bank_transfer' => 'انتقال بانکی',
            'credit_card' => 'کارت اعتباری',
            'online' => 'آنلاین',
            'other' => 'سایر',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_file_size' => 5242880, // 5MB in bytes
        'photo_max_size' => 2097152, // 2MB
        'document_max_size' => 10485760, // 10MB
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'],
        'allowed_media_types' => ['mp3', 'mp4', 'avi', 'mov', 'wmv'],
        'profile_photo_path' => 'uploads/profiles/',
        'document_path' => 'uploads/documents/',
        'logo_path' => 'uploads/logos/',
        'receipt_path' => 'uploads/receipts/',
        'temp_path' => 'uploads/temp/',
        'max_profile_photo_width' => 800,
        'max_profile_photo_height' => 800,
        'thumbnail_width' => 150,
        'thumbnail_height' => 150,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => true,
        'path' => 'storage/logs/',
        'level' => 'debug', // emergency, alert, critical, error, warning, notice, info, debug
        'max_files' => 30, // Keep 30 days of logs
        'separate_levels' => false, // Separate files for each level
        'database_logging' => true, // Also log to system_logs table
        'log_queries' => false, // Log database queries (performance impact)
        'log_slow_queries' => true,
        'slow_query_threshold' => 1.0, // in seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode
    |--------------------------------------------------------------------------
    */
    'maintenance' => [
        'enabled' => false,
        'allowed_ips' => ['127.0.0.1'],
        'allowed_urls' => ['/login', '/api/health'],
        'message' => 'سیستم در حال بروزرسانی است. لطفاً چند دقیقه دیگر مراجعه کنید.',
        'retry_after' => 60, // seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'api',
        'version' => 'v1',
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'cors' => [
            'enabled' => true,
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'exposed_headers' => [],
            'max_age' => 86400,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'enabled' => true,
        'path' => 'storage/backups/',
        'database' => true,
        'files' => false,
        'schedule' => 'daily', // daily, weekly, monthly
        'keep_last' => 7, // Keep last 7 backups
        'compress' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'channels' => ['in_app', 'email', 'sms'],
        'default_channel' => 'in_app',
        'email_from_address' => 'noreply@schoolmanagement.com',
        'email_from_name' => 'سیستم مدیریت مدرسه',
        'sms_provider' => 'kavenegar', // kavenegar, ghasedak, etc.
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Report Configuration
    |--------------------------------------------------------------------------
    */
    'reports' => [
        'export_formats' => ['pdf', 'excel', 'csv', 'print'],
        'default_format' => 'pdf',
        'pdf_paper_size' => 'A4',
        'pdf_orientation' => 'portrait', // portrait, landscape
        'pdf_font' => 'tahoma',
        'excel_auto_filter' => true,
        'chart_colors' => [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
        ],
    ],
];