<?php
/**
 * School Management System - Menu Configuration
 * 
 * تنظیمات منوهای سیستم
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Structure
    |--------------------------------------------------------------------------
    */
    'sidebar' => [
        [
            'title' => 'داشبورد',
            'icon' => 'fas fa-tachometer-alt',
            'route' => '/dashboard',
            'permission' => null, // All authenticated users
            'badge' => null,
            'order' => 1,
        ],
        [
            'title' => 'مدیریت',
            'icon' => 'fas fa-cogs',
            'route' => null,
            'permission' => null,
            'order' => 2,
            'children' => [
                [
                    'title' => 'شعب',
                    'icon' => 'fas fa-building',
                    'route' => '/branches',
                    'permission' => 'view_branches',
                ],
                [
                    'title' => 'دپارتمان‌ها',
                    'icon' => 'fas fa-sitemap',
                    'route' => '/departments',
                    'permission' => 'view_departments',
                ],
                [
                    'title' => 'کاربران',
                    'icon' => 'fas fa-users',
                    'route' => '/users',
                    'permission' => 'view_users',
                ],
                [
                    'title' => 'نقش‌ها',
                    'icon' => 'fas fa-user-shield',
                    'route' => '/roles',
                    'permission' => 'view_roles',
                ],
                [
                    'title' => 'کارمندان',
                    'icon' => 'fas fa-briefcase',
                    'route' => '/employees',
                    'permission' => 'view_employees',
                ],
            ],
        ],
        [
            'title' => 'آموزش',
            'icon' => 'fas fa-graduation-cap',
            'route' => null,
            'permission' => null,
            'order' => 3,
            'children' => [
                [
                    'title' => 'سال‌های تحصیلی',
                    'icon' => 'fas fa-calendar',
                    'route' => '/academic-years',
                    'permission' => 'view_academic_years',
                ],
                [
                    'title' => 'ترم‌ها',
                    'icon' => 'fas fa-calendar-week',
                    'route' => '/semesters',
                    'permission' => 'view_semesters',
                ],
                [
                    'title' => 'کلاس‌ها',
                    'icon' => 'fas fa-chalkboard',
                    'route' => '/classes',
                    'permission' => 'view_classes',
                ],
                [
                    'title' => 'بخش‌ها',
                    'icon' => 'fas fa-layer-group',
                    'route' => '/sections',
                    'permission' => 'view_sections',
                ],
                [
                    'title' => 'دروس',
                    'icon' => 'fas fa-book',
                    'route' => '/subjects',
                    'permission' => 'view_subjects',
                ],
                [
                    'title' => 'برنامه هفتگی',
                    'icon' => 'fas fa-calendar-alt',
                    'route' => '/timetable',
                    'permission' => 'view_timetable',
                ],
            ],
        ],
        [
            'title' => 'افراد',
            'icon' => 'fas fa-users',
            'route' => null,
            'permission' => null,
            'order' => 4,
            'children' => [
                [
                    'title' => 'دانش‌آموزان',
                    'icon' => 'fas fa-user-graduate',
                    'route' => '/students',
                    'permission' => 'view_students',
                ],
                [
                    'title' => 'والدین',
                    'icon' => 'fas fa-user-friends',
                    'route' => '/parents',
                    'permission' => 'view_parents',
                ],
                [
                    'title' => 'معلمان',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'route' => '/teachers',
                    'permission' => 'view_teachers',
                ],
            ],
        ],
        [
            'title' => 'عملیات روزانه',
            'icon' => 'fas fa-tasks',
            'route' => null,
            'permission' => null,
            'order' => 5,
            'children' => [
                [
                    'title' => 'حضور و غیاب دانش‌آموزان',
                    'icon' => 'fas fa-calendar-check',
                    'route' => '/attendance/students',
                    'permission' => 'view_attendance',
                ],
                [
                    'title' => 'حضور و غیاب معلمان',
                    'icon' => 'fas fa-calendar-times',
                    'route' => '/attendance/teachers',
                    'permission' => 'view_attendance',
                ],
                [
                    'title' => 'ثبت حضور و غیاب',
                    'icon' => 'fas fa-clipboard-check',
                    'route' => '/attendance/students/take',
                    'permission' => 'mark_attendance',
                ],
            ],
        ],
        [
            'title' => 'آزمون‌ها',
            'icon' => 'fas fa-file-alt',
            'route' => null,
            'permission' => null,
            'order' => 6,
            'children' => [
                [
                    'title' => 'لیست آزمون‌ها',
                    'icon' => 'fas fa-list-alt',
                    'route' => '/exams',
                    'permission' => 'view_exams',
                ],
                [
                    'title' => 'ایجاد آزمون',
                    'icon' => 'fas fa-plus-circle',
                    'route' => '/exams/create',
                    'permission' => 'create_exams',
                ],
                [
                    'title' => 'نتایج',
                    'icon' => 'fas fa-chart-bar',
                    'route' => '/results',
                    'permission' => 'view_results',
                ],
                [
                    'title' => 'کارنامه',
                    'icon' => 'fas fa-file-pdf',
                    'route' => '/results/report-card',
                    'permission' => 'view_report_cards',
                ],
            ],
        ],
        [
            'title' => 'امور مالی',
            'icon' => 'fas fa-money-bill-wave',
            'route' => null,
            'permission' => null,
            'order' => 7,
            'children' => [
                [
                    'title' => 'شهریه‌ها',
                    'icon' => 'fas fa-receipt',
                    'route' => '/fees',
                    'permission' => 'view_fees',
                ],
                [
                    'title' => 'ساختار شهریه',
                    'icon' => 'fas fa-cogs',
                    'route' => '/fees/structures',
                    'permission' => 'create_fee_structures',
                ],
                [
                    'title' => 'پرداخت‌ها',
                    'icon' => 'fas fa-credit-card',
                    'route' => '/fees/collect',
                    'permission' => 'collect_fees',
                ],
                [
                    'title' => 'هزینه‌ها',
                    'icon' => 'fas fa-money-bill',
                    'route' => '/expenses',
                    'permission' => 'manage_expenses',
                ],
                [
                    'title' => 'حقوق و دستمزد',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'route' => '/payroll',
                    'permission' => 'view_payroll',
                ],
            ],
        ],
        [
            'title' => 'کتابخانه',
            'icon' => 'fas fa-book-open',
            'route' => null,
            'permission' => null,
            'order' => 8,
            'children' => [
                [
                    'title' => 'لیست کتاب‌ها',
                    'icon' => 'fas fa-list',
                    'route' => '/library/books',
                    'permission' => 'view_books',
                ],
                [
                    'title' => 'افزودن کتاب',
                    'icon' => 'fas fa-plus',
                    'route' => '/library/books/add',
                    'permission' => 'add_books',
                ],
                [
                    'title' => 'امانت‌ها',
                    'icon' => 'fas fa-exchange-alt',
                    'route' => '/library/borrowings',
                    'permission' => 'issue_books',
                ],
            ],
        ],
        [
            'title' => 'حمل و نقل',
            'icon' => 'fas fa-bus',
            'route' => null,
            'permission' => null,
            'order' => 9,
            'children' => [
                [
                    'title' => 'وسایل نقلیه',
                    'icon' => 'fas fa-truck',
                    'route' => '/transport/vehicles',
                    'permission' => 'view_vehicles',
                ],
                [
                    'title' => 'مسیرها',
                    'icon' => 'fas fa-route',
                    'route' => '/transport/routes',
                    'permission' => 'view_routes',
                ],
                [
                    'title' => 'تخصیص دانش‌آموزان',
                    'icon' => 'fas fa-user-plus',
                    'route' => '/transport/student-allocation',
                    'permission' => 'allocate_transport',
                ],
            ],
        ],
        [
            'title' => 'انبار',
            'icon' => 'fas fa-boxes',
            'route' => '/inventory',
            'permission' => 'view_inventory',
            'order' => 10,
        ],
        [
            'title' => 'اعلان‌ها',
            'icon' => 'fas fa-bell',
            'route' => '/notifications',
            'permission' => 'view_notifications',
            'order' => 11,
        ],
        [
            'title' => 'گزارش‌ها',
            'icon' => 'fas fa-chart-pie',
            'route' => '/reports',
            'permission' => 'view_reports',
            'order' => 12,
            'children' => [
                [
                    'title' => 'گزارش دانش‌آموزان',
                    'icon' => 'fas fa-users',
                    'route' => '/reports/students',
                    'permission' => 'view_reports',
                ],
                [
                    'title' => 'گزارش حضور و غیاب',
                    'icon' => 'fas fa-calendar',
                    'route' => '/reports/attendance',
                    'permission' => 'view_reports',
                ],
                [
                    'title' => 'گزارش مالی',
                    'icon' => 'fas fa-chart-line',
                    'route' => '/reports/financial',
                    'permission' => 'view_financial_reports',
                ],
                [
                    'title' => 'گزارش آزمون‌ها',
                    'icon' => 'fas fa-file-signature',
                    'route' => '/reports/exam',
                    'permission' => 'view_reports',
                ],
            ],
        ],
        [
            'title' => 'ممیزی',
            'icon' => 'fas fa-history',
            'route' => null,
            'permission' => null,
            'order' => 13,
            'children' => [
                [
                    'title' => 'لاگ‌های فعالیت',
                    'icon' => 'fas fa-clipboard-list',
                    'route' => '/audit-logs',
                    'permission' => 'view_audit_logs',
                ],
                [
                    'title' => 'لاگ‌های سیستم',
                    'icon' => 'fas fa-server',
                    'route' => '/system-logs',
                    'permission' => 'view_system_logs',
                ],
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Top Navigation Menu
    |--------------------------------------------------------------------------
    */
    'top_nav' => [
        [
            'title' => 'پروفایل',
            'icon' => 'fas fa-user',
            'route' => '/profile',
            'order' => 1,
        ],
        [
            'title' => 'تنظیمات',
            'icon' => 'fas fa-cog',
            'route' => '/settings',
            'permission' => 'view_settings',
            'order' => 2,
        ],
        [
            'title' => 'خروج',
            'icon' => 'fas fa-sign-out-alt',
            'route' => '/logout',
            'order' => 99,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Quick Actions Menu
    |--------------------------------------------------------------------------
    */
    'quick_actions' => [
        [
            'title' => 'ثبت نام دانش‌آموز جدید',
            'icon' => 'fas fa-user-plus',
            'route' => '/students/create',
            'permission' => 'create_students',
            'color' => 'primary',
        ],
        [
            'title' => 'ثبت حضور و غیاب',
            'icon' => 'fas fa-calendar-check',
            'route' => '/attendance/students/take',
            'permission' => 'mark_attendance',
            'color' => 'success',
        ],
        [
            'title' => 'ثبت نمرات',
            'icon' => 'fas fa-pen',
            'route' => '/exams',
            'permission' => 'enter_marks',
            'color' => 'info',
        ],
        [
            'title' => 'دریافت شهریه',
            'icon' => 'fas fa-money-bill',
            'route' => '/fees/collect',
            'permission' => 'collect_fees',
            'color' => 'warning',
        ],
        [
            'title' => 'ارسال اعلان',
            'icon' => 'fas fa-bell',
            'route' => '/notifications/create',
            'permission' => 'send_notifications',
            'color' => 'danger',
        ],
    ],
];