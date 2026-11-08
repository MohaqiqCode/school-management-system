<?php
/**
 * School Management System - Modules Configuration
 * 
 * تنظیمات ماژول‌های سیستم
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled Modules
    |--------------------------------------------------------------------------
    */
    'enabled' => [
        'authentication' => true,
        'dashboard' => true,
        'branches' => true,
        'users' => true,
        'roles' => true,
        'departments' => true,
        'academic_years' => true,
        'semesters' => true,
        'classes' => true,
        'sections' => true,
        'subjects' => true,
        'timetable' => true,
        'students' => true,
        'parents' => true,
        'teachers' => true,
        'employees' => true,
        'attendance' => true,
        'examinations' => true,
        'finance' => true,
        'payroll' => true,
        'library' => true,
        'transport' => true,
        'inventory' => true,
        'notifications' => true,
        'reports' => true,
        'audit' => true,
        'settings' => true,
        'api' => true,
        'backup' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Module Dependencies
    |--------------------------------------------------------------------------
    */
    'dependencies' => [
        'students' => ['classes', 'sections', 'users'],
        'teachers' => ['departments', 'users'],
        'employees' => ['departments', 'users'],
        'attendance' => ['students', 'teachers', 'classes', 'sections'],
        'examinations' => ['students', 'classes', 'subjects', 'academic_years'],
        'finance' => ['students', 'classes', 'academic_years'],
        'payroll' => ['teachers', 'employees'],
        'timetable' => ['classes', 'sections', 'subjects', 'teachers', 'semesters'],
        'transport' => ['students', 'employees'],
        'library' => ['students', 'users'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Module Settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'attendance' => [
            'default_status' => 'present',
            'allow_bulk_mark' => true,
            'mark_future_dates' => false,
            'mark_past_days' => 30, // Allow marking attendance for past 30 days
            'auto_mark_absent' => false,
            'auto_mark_time' => '10:00:00',
            'late_mark_time' => '08:30:00',
            'half_day_hours' => 4,
        ],
        
        'examinations' => [
            'default_passing_percentage' => 50,
            'auto_calculate_grades' => true,
            'allow_marks_override' => true,
            'marks_decimal_places' => 2,
            'show_rank' => true,
            'publish_automatically' => false,
            'allow_revaluation' => false,
        ],
        
        'finance' => [
            'default_due_day' => 10,
            'late_fee_percentage' => 5,
            'late_fee_fixed_amount' => 0,
            'allow_partial_payment' => true,
            'auto_generate_invoice' => true,
            'invoice_prefix' => 'INV-',
            'receipt_prefix' => 'RCP-',
            'payment_reminder_days' => [3, 1, 0], // Days before due date
            'overdue_after_days' => 30,
        ],
        
        'library' => [
            'default_borrow_days' => 14,
            'max_books_per_user' => 5,
            'fine_per_day' => 1000, // Amount in currency
            'max_fine_amount' => 50000,
            'allow_renewal' => true,
            'max_renewals' => 2,
            'reservation_days' => 3,
        ],
        
        'transport' => [
            'default_vehicle_capacity' => 30,
            'max_stops_per_route' => 20,
            'allow_route_changes' => true,
            'route_change_notice_days' => 3,
        ],
        
        'inventory' => [
            'low_stock_threshold' => 10,
            'critical_stock_threshold' => 5,
            'auto_reorder' => false,
            'stock_alert_enabled' => true,
        ],
        
        'notifications' => [
            'max_inapp_notifications' => 100,
            'cleanup_after_days' => 90,
            'email_footer' => 'این ایمیل توسط سیستم مدیریت مدرسه ارسال شده است',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Module Display Configuration
    |--------------------------------------------------------------------------
    */
    'display' => [
        'sidebar_menu_groups' => [
            'main' => [
                'label' => 'اصلی',
                'icon' => 'fas fa-home',
                'order' => 1,
                'modules' => ['dashboard'],
            ],
            'management' => [
                'label' => 'مدیریت',
                'icon' => 'fas fa-cogs',
                'order' => 2,
                'modules' => ['branches', 'departments', 'users', 'roles', 'employees'],
            ],
            'academic' => [
                'label' => 'آموزشی',
                'icon' => 'fas fa-graduation-cap',
                'order' => 3,
                'modules' => ['academic_years', 'semesters', 'classes', 'sections', 'subjects', 'timetable'],
            ],
            'people' => [
                'label' => 'افراد',
                'icon' => 'fas fa-users',
                'order' => 4,
                'modules' => ['students', 'parents', 'teachers'],
            ],
            'operations' => [
                'label' => 'عملیات',
                'icon' => 'fas fa-tasks',
                'order' => 5,
                'modules' => ['attendance', 'examinations'],
            ],
            'finance_group' => [
                'label' => 'مالی',
                'icon' => 'fas fa-money-bill',
                'order' => 6,
                'modules' => ['finance', 'payroll'],
            ],
            'facilities' => [
                'label' => 'امکانات',
                'icon' => 'fas fa-warehouse',
                'order' => 7,
                'modules' => ['library', 'transport', 'inventory'],
            ],
            'communication' => [
                'label' => 'ارتباطات',
                'icon' => 'fas fa-comments',
                'order' => 8,
                'modules' => ['notifications'],
            ],
            'analytics' => [
                'label' => 'گزارشات',
                'icon' => 'fas fa-chart-pie',
                'order' => 9,
                'modules' => ['reports', 'audit'],
            ],
        ],
    ],
];