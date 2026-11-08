<?php
/**
 * School Management System - Upload Configuration
 * 
 * تنظیمات آپلود فایل
 */

return [
    /*
    |--------------------------------------------------------------------------
    | General Upload Settings
    |--------------------------------------------------------------------------
    */
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    'min_file_size' => 1, // 1 byte
    'chunk_size' => 1024 * 1024, // 1MB chunks for large files
    
    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    */
    'allowed_types' => [
        'images' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'],
            'mime_types' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/bmp',
                'image/webp',
                'image/svg+xml',
            ],
            'max_size' => 5 * 1024 * 1024, // 5MB
        ],
        'documents' => [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
                'application/rtf',
            ],
            'max_size' => 10 * 1024 * 1024, // 10MB
        ],
        'audio' => [
            'extensions' => ['mp3', 'wav', 'ogg', 'm4a', 'wma'],
            'mime_types' => [
                'audio/mpeg',
                'audio/wav',
                'audio/ogg',
                'audio/mp4',
                'audio/x-ms-wma',
            ],
            'max_size' => 20 * 1024 * 1024, // 20MB
        ],
        'video' => [
            'extensions' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
            'mime_types' => [
                'video/mp4',
                'video/x-msvideo',
                'video/quicktime',
                'video/x-ms-wmv',
                'video/x-flv',
                'video/x-matroska',
                'video/webm',
            ],
            'max_size' => 50 * 1024 * 1024, // 50MB
        ],
        'archives' => [
            'extensions' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'mime_types' => [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip',
            ],
            'max_size' => 50 * 1024 * 1024, // 50MB
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Upload Paths
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'base' => 'public/assets/uploads/',
        'profiles' => 'profiles/',
        'student_photos' => 'students/photos/',
        'teacher_photos' => 'teachers/photos/',
        'employee_photos' => 'employees/photos/',
        'student_documents' => 'students/documents/',
        'teacher_documents' => 'teachers/documents/',
        'logos' => 'logos/',
        'receipts' => 'receipts/',
        'invoices' => 'invoices/',
        'expenses' => 'expenses/',
        'library' => 'library/',
        'transport' => 'transport/',
        'temp' => 'temp/',
        'backup' => 'storage/backups/',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */
    'image' => [
        'resize_enabled' => true,
        'max_width' => 1920,
        'max_height' => 1080,
        'quality' => 85,
        'thumbnails' => [
            'small' => [
                'width' => 150,
                'height' => 150,
                'quality' => 80,
                'prefix' => 'thumb_small_',
            ],
            'medium' => [
                'width' => 400,
                'height' => 400,
                'quality' => 85,
                'prefix' => 'thumb_medium_',
            ],
            'large' => [
                'width' => 800,
                'height' => 800,
                'quality' => 90,
                'prefix' => 'thumb_large_',
            ],
        ],
        'watermark' => [
            'enabled' => false,
            'image' => 'assets/images/watermark.png',
            'position' => 'bottom-right', // top-left, top-right, bottom-left, bottom-right, center
            'opacity' => 50,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | File Naming
    |--------------------------------------------------------------------------
    */
    'naming' => [
        'strategy' => 'unique', // original, unique, timestamp, hash
        'preserve_original' => false,
        'lowercase' => true,
        'replace_spaces' => '_',
        'max_length' => 100,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'scan_for_viruses' => false, // Requires antivirus software
        'check_mime_type' => true,
        'check_extension' => true,
        'block_double_extensions' => true, // e.g., file.php.jpg
        'block_executable' => true,
        'sanitize_filename' => true,
        'encrypt_filenames' => false,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cloud Storage (Optional)
    |--------------------------------------------------------------------------
    */
    'cloud' => [
        'enabled' => false,
        'provider' => 's3', // s3, google, azure
        'delete_local_after_upload' => false,
        's3' => [
            'key' => env('AWS_ACCESS_KEY_ID', ''),
            'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET', ''),
            'url' => env('AWS_URL', ''),
            'endpoint' => env('AWS_ENDPOINT', ''),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | File Validation Messages
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'file_required' => 'لطفاً یک فایل انتخاب کنید',
        'file_too_large' => 'حجم فایل بیشتر از حد مجاز است',
        'file_too_small' => 'حجم فایل کمتر از حد مجاز است',
        'file_type_not_allowed' => 'نوع فایل مجاز نیست',
        'file_extension_not_allowed' => 'پسوند فایل مجاز نیست',
        'file_already_exists' => 'فایلی با این نام قبلاً وجود دارد',
        'upload_failed' => 'خطا در آپلود فایل',
        'upload_partial' => 'فایل ناقص آپلود شد',
        'no_file_uploaded' => 'هیچ فایلی آپلود نشد',
        'no_temp_directory' => 'پوشه موقت در دسترس نیست',
        'cannot_write_file' => 'خطا در ذخیره فایل',
        'upload_stopped' => 'آپلود توسط افزونه متوقف شد',
    ],
];