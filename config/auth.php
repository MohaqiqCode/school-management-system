<?php
/**
 * School Management System - Authentication Configuration
 * 
 * تنظیمات احراز هویت
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Authentication Guard
    |--------------------------------------------------------------------------
    */
    'default_guard' => 'web',
    
    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
            'remember' => true,
        ],
        
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
        
        'parent' => [
            'driver' => 'session',
            'provider' => 'parents',
            'remember' => true,
        ],
        
        'student' => [
            'driver' => 'session',
            'provider' => 'students',
            'remember' => false,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => 'App\Models\User',
            'table' => 'users',
        ],
        
        'parents' => [
            'driver' => 'eloquent',
            'model' => 'App\Models\Parent',
            'table' => 'parents',
        ],
        
        'students' => [
            'driver' => 'eloquent',
            'model' => 'App\Models\Student',
            'table' => 'students',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Password Reset Configuration
    |--------------------------------------------------------------------------
    */
    'password_reset' => [
        'token_expiry' => 60, // minutes
        'table' => 'jwt_tokens',
        'throttle' => 60, // seconds between reset attempts
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */
    'verification' => [
        'enabled' => true,
        'expire' => 1440, // 24 hours in minutes
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Password Validation Rules
    |--------------------------------------------------------------------------
    */
    'password_rules' => [
        'min_length' => 8,
        'max_length' => 32,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numeric' => true,
        'require_special_char' => false,
        'prevent_common_passwords' => true,
        'prevent_personal_info' => true, // Prevent using name, email in password
        'password_history_count' => 5, // Remember last N passwords
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Login Configuration
    |--------------------------------------------------------------------------
    */
    'login' => [
        'max_attempts' => 5,
        'lockout_minutes' => 15,
        'throttle_key' => 'email|ip',
        'remember_me_days' => 30,
        'session_lifetime' => 120, // minutes
        'single_session' => false, // Allow only one session per user
        'logout_other_devices' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enabled' => false,
        'required_for_roles' => ['super_admin', 'branch_admin'],
        'providers' => [
            'email' => [
                'enabled' => true,
                'code_length' => 6,
                'code_expiry' => 10, // minutes
            ],
            'sms' => [
                'enabled' => false,
                'code_length' => 6,
                'code_expiry' => 5, // minutes
            ],
            'authenticator_app' => [
                'enabled' => false,
                'issuer' => 'School Management System',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | OAuth / Social Login
    |--------------------------------------------------------------------------
    */
    'social_login' => [
        'enabled' => false,
        'providers' => [
            'google' => [
                'client_id' => env('GOOGLE_CLIENT_ID', ''),
                'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
                'redirect' => env('GOOGLE_REDIRECT_URI', ''),
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Authentication (JWT)
    |--------------------------------------------------------------------------
    */
    'jwt' => [
        'secret' => env('JWT_SECRET', 'your-jwt-secret-key'),
        'algorithm' => 'HS256',
        'ttl' => 1440, // 24 hours in minutes
        'refresh_ttl' => 20160, // 14 days in minutes
        'blacklist_enabled' => true,
        'blacklist_grace_period' => 10, // seconds
    ],
];