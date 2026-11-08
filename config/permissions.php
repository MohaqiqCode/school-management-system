<?php
/**
 * School Management System - Permissions Configuration
 * 
 * تنظیمات مجوزها و دسترسی‌ها
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Modules
    |--------------------------------------------------------------------------
    */
    'modules' => [
        'dashboard' => [
            'label' => 'داشبورد',
            'icon' => 'fas fa-tachometer-alt',
            'permissions' => [
                'view_admin_dashboard' => 'مشاهده داشبورد مدیریت',
                'view_branch_dashboard' => 'مشاهده داشبورد شعبه',
                'view_teacher_dashboard' => 'مشاهده داشبورد معلم',
                'view_student_dashboard' => 'مشاهده داشبورد دانش‌آموز',
                'view_parent_dashboard' => 'مشاهده داشبورد والدین',
                'view_employee_dashboard' => 'مشاهده داشبورد کارمند',
            ],
        ],
        
        'branches' => [
            'label' => 'شعب',
            'icon' => 'fas fa-building',
            'permissions' => [
                'view_branches' => 'مشاهده لیست شعب',
                'create_branches' => 'ایجاد شعبه جدید',
                'edit_branches' => 'ویرایش شعبه',
                'delete_branches' => 'حذف شعبه',
                'manage_branch_settings' => 'مدیریت تنظیمات شعبه',
            ],
        ],
        
        'users' => [
            'label' => 'کاربران',
            'icon' => 'fas fa-users',
            'permissions' => [
                'view_users' => 'مشاهده لیست کاربران',
                'create_users' => 'ایجاد کاربر جدید',
                'edit_users' => 'ویرایش کاربر',
                'delete_users' => 'حذف کاربر',
                'change_user_status' => 'تغییر وضعیت کاربر',
                'reset_user_password' => 'بازنشانی رمز عبور کاربر',
                'view_user_profile' => 'مشاهده پروفایل کاربر',
            ],
        ],
        
        'roles' => [
            'label' => 'نقش‌ها و مجوزها',
            'icon' => 'fas fa-user-shield',
            'permissions' => [
                'view_roles' => 'مشاهده لیست نقش‌ها',
                'create_roles' => 'ایجاد نقش جدید',
                'edit_roles' => 'ویرایش نقش',
                'delete_roles' => 'حذف نقش',
                'assign_permissions' => 'تخصیص مجوزها',
            ],
        ],
        
        'departments' => [
            'label' => 'دپارتمان‌ها',
            'icon' => 'fas fa-sitemap',
            'permissions' => [
                'view_departments' => 'مشاهده دپارتمان‌ها',
                'create_departments' => 'ایجاد دپارتمان',
                'edit_departments' => 'ویرایش دپارتمان',
                'delete_departments' => 'حذف دپارتمان',
            ],
        ],
        
        'academic' => [
            'label' => 'امور آموزشی',
            'icon' => 'fas fa-graduation-cap',
            'permissions' => [
                'view_academic_years' => 'مشاهده سال‌های تحصیلی',
                'create_academic_years' => 'ایجاد سال تحصیلی',
                'edit_academic_years' => 'ویرایش سال تحصیلی',
                'delete_academic_years' => 'حذف سال تحصیلی',
                'view_semesters' => 'مشاهده ترم‌ها',
                'create_semesters' => 'ایجاد ترم',
                'edit_semesters' => 'ویرایش ترم',
                'delete_semesters' => 'حذف ترم',
            ],
        ],
        
        'classes' => [
            'label' => 'کلاس‌ها',
            'icon' => 'fas fa-chalkboard',
            'permissions' => [
                'view_classes' => 'مشاهده کلاس‌ها',
                'create_classes' => 'ایجاد کلاس',
                'edit_classes' => 'ویرایش کلاس',
                'delete_classes' => 'حذف کلاس',
                'view_sections' => 'مشاهده بخش‌ها',
                'create_sections' => 'ایجاد بخش',
                'edit_sections' => 'ویرایش بخش',
                'delete_sections' => 'حذف بخش',
            ],
        ],
        
        'subjects' => [
            'label' => 'دروس',
            'icon' => 'fas fa-book',
            'permissions' => [
                'view_subjects' => 'مشاهده دروس',
                'create_subjects' => 'ایجاد درس',
                'edit_subjects' => 'ویرایش درس',
                'delete_subjects' => 'حذف درس',
                'assign_subjects' => 'تخصیص درس به کلاس',
            ],
        ],
        
        'timetable' => [
            'label' => 'برنامه هفتگی',
            'icon' => 'fas fa-calendar-alt',
            'permissions' => [
                'view_timetable' => 'مشاهده برنامه هفتگی',
                'create_timetable' => 'ایجاد برنامه',
                'edit_timetable' => 'ویرایش برنامه',
                'delete_timetable' => 'حذف برنامه',
                'manage_timetable_conflicts' => 'مدیریت تداخل‌های برنامه',
            ],
        ],
        
        'students' => [
            'label' => 'دانش‌آموزان',
            'icon' => 'fas fa-user-graduate',
            'permissions' => [
                'view_students' => 'مشاهده لیست دانش‌آموزان',
                'create_students' => 'ثبت نام دانش‌آموز',
                'edit_students' => 'ویرایش اطلاعات دانش‌آموز',
                'delete_students' => 'حذف دانش‌آموز',
                'promote_students' => 'ارتقاء دانش‌آموز',
                'transfer_students' => 'انتقال دانش‌آموز',
                'view_student_profile' => 'مشاهده پروفایل دانش‌آموز',
                'view_student_documents' => 'مشاهده مدارک دانش‌آموز',
                'manage_student_documents' => 'مدیریت مدارک دانش‌آموز',
                'view_student_results' => 'مشاهده نتایج دانش‌آموز',
                'view_student_attendance' => 'مشاهده حضور و غیاب دانش‌آموز',
                'view_student_fees' => 'مشاهده وضعیت مالی دانش‌آموز',
            ],
        ],
        
        'parents' => [
            'label' => 'والدین',
            'icon' => 'fas fa-user-friends',
            'permissions' => [
                'view_parents' => 'مشاهده لیست والدین',
                'create_parents' => 'ثبت والد',
                'edit_parents' => 'ویرایش اطلاعات والد',
                'delete_parents' => 'حذف والد',
                'link_parent_student' => 'اتصال والد به دانش‌آموز',
            ],
        ],
        
        'teachers' => [
            'label' => 'معلمان',
            'icon' => 'fas fa-chalkboard-teacher',
            'permissions' => [
                'view_teachers' => 'مشاهده لیست معلمان',
                'create_teachers' => 'ثبت معلم',
                'edit_teachers' => 'ویرایش اطلاعات معلم',
                'delete_teachers' => 'حذف معلم',
                'view_teacher_profile' => 'مشاهده پروفایل معلم',
                'view_teacher_workload' => 'مشاهده حجم کاری معلم',
                'view_teacher_timetable' => 'مشاهده برنامه معلم',
            ],
        ],
        
        'employees' => [
            'label' => 'کارمندان',
            'icon' => 'fas fa-briefcase',
            'permissions' => [
                'view_employees' => 'مشاهده لیست کارمندان',
                'create_employees' => 'ثبت کارمند',
                'edit_employees' => 'ویرایش اطلاعات کارمند',
                'delete_employees' => 'حذف کارمند',
            ],
        ],
        
        'attendance' => [
            'label' => 'حضور و غیاب',
            'icon' => 'fas fa-calendar-check',
            'permissions' => [
                'view_attendance' => 'مشاهده حضور و غیاب',
                'mark_attendance' => 'ثبت حضور و غیاب',
                'edit_attendance' => 'ویرایش حضور و غیاب',
                'bulk_attendance' => 'ثبت گروهی حضور و غیاب',
                'view_attendance_reports' => 'مشاهده گزارش حضور و غیاب',
                'export_attendance' => 'خروجی گزارش حضور و غیاب',
            ],
        ],
        
        'examinations' => [
            'label' => 'آزمون‌ها',
            'icon' => 'fas fa-file-alt',
            'permissions' => [
                'view_exams' => 'مشاهده آزمون‌ها',
                'create_exams' => 'ایجاد آزمون',
                'edit_exams' => 'ویرایش آزمون',
                'delete_exams' => 'حذف آزمون',
                'enter_marks' => 'ثبت نمرات',
                'edit_marks' => 'ویرایش نمرات',
                'verify_marks' => 'تأیید نمرات',
                'view_results' => 'مشاهده نتایج',
                'publish_results' => 'انتشار نتایج',
                'view_report_cards' => 'مشاهده کارنامه',
                'print_report_cards' => 'چاپ کارنامه',
            ],
        ],
        
        'finance' => [
            'label' => 'امور مالی',
            'icon' => 'fas fa-money-bill-wave',
            'permissions' => [
                'view_fees' => 'مشاهده شهریه‌ها',
                'create_fee_structures' => 'ایجاد ساختار شهریه',
                'edit_fee_structures' => 'ویرایش ساختار شهریه',
                'collect_fees' => 'دریافت شهریه',
                'view_payments' => 'مشاهده پرداخت‌ها',
                'manage_discounts' => 'مدیریت تخفیف‌ها',
                'manage_expenses' => 'مدیریت هزینه‌ها',
                'view_financial_reports' => 'مشاهده گزارش‌های مالی',
                'export_financial_reports' => 'خروجی گزارش مالی',
                'manage_fee_categories' => 'مدیریت دسته‌بندی شهریه',
            ],
        ],
        
        'payroll' => [
            'label' => 'حقوق و دستمزد',
            'icon' => 'fas fa-receipt',
            'permissions' => [
                'view_payroll' => 'مشاهده لیست حقوق',
                'process_payroll' => 'پردازش حقوق',
                'edit_payroll' => 'ویرایش حقوق',
                'view_payroll_reports' => 'مشاهده گزارش حقوق',
                'generate_payslips' => 'تولید فیش حقوقی',
                'manage_salary_structures' => 'مدیریت ساختار حقوق',
            ],
        ],
        
        'library' => [
            'label' => 'کتابخانه',
            'icon' => 'fas fa-book-open',
            'permissions' => [
                'view_books' => 'مشاهده کتاب‌ها',
                'add_books' => 'افزودن کتاب',
                'edit_books' => 'ویرایش کتاب',
                'delete_books' => 'حذف کتاب',
                'issue_books' => 'امانت کتاب',
                'return_books' => 'بازگشت کتاب',
                'view_fines' => 'مشاهده جریمه‌ها',
                'manage_fines' => 'مدیریت جریمه‌ها',
                'view_library_reports' => 'گزارش کتابخانه',
            ],
        ],
        
        'transport' => [
            'label' => 'حمل و نقل',
            'icon' => 'fas fa-bus',
            'permissions' => [
                'view_vehicles' => 'مشاهده وسایل نقلیه',
                'manage_vehicles' => 'مدیریت وسایل نقلیه',
                'view_routes' => 'مشاهده مسیرها',
                'manage_routes' => 'مدیریت مسیرها',
                'view_drivers' => 'مشاهده رانندگان',
                'manage_drivers' => 'مدیریت رانندگان',
                'allocate_transport' => 'تخصیص حمل و نقل',
                'view_transport_reports' => 'گزارش حمل و نقل',
            ],
        ],
        
        'inventory' => [
            'label' => 'انبار',
            'icon' => 'fas fa-boxes',
            'permissions' => [
                'view_inventory' => 'مشاهده موجودی',
                'manage_inventory' => 'مدیریت موجودی',
                'add_stock' => 'افزودن موجودی',
                'remove_stock' => 'کاهش موجودی',
                'view_stock_reports' => 'گزارش موجودی',
                'manage_categories' => 'مدیریت دسته‌بندی‌ها',
            ],
        ],
        
        'notifications' => [
            'label' => 'اعلان‌ها',
            'icon' => 'fas fa-bell',
            'permissions' => [
                'view_notifications' => 'مشاهده اعلان‌ها',
                'send_notifications' => 'ارسال اعلان',
                'manage_notification_templates' => 'مدیریت قالب‌های اعلان',
                'send_bulk_notifications' => 'ارسال اعلان گروهی',
            ],
        ],
        
        'reports' => [
            'label' => 'گزارش‌ها',
            'icon' => 'fas fa-chart-bar',
            'permissions' => [
                'view_reports' => 'مشاهده گزارش‌ها',
                'export_reports' => 'خروجی گزارش',
                'schedule_reports' => 'زمان‌بندی گزارش',
                'view_analytics' => 'مشاهده تحلیل‌ها',
            ],
        ],
        
        'audit' => [
            'label' => 'ممیزی',
            'icon' => 'fas fa-history',
            'permissions' => [
                'view_audit_logs' => 'مشاهده لاگ‌های ممیزی',
                'view_system_logs' => 'مشاهده لاگ‌های سیستم',
                'export_audit_logs' => 'خروجی لاگ‌های ممیزی',
                'clear_audit_logs' => 'پاکسازی لاگ‌ها',
            ],
        ],
        
        'settings' => [
            'label' => 'تنظیمات',
            'icon' => 'fas fa-cog',
            'permissions' => [
                'view_settings' => 'مشاهده تنظیمات',
                'manage_general_settings' => 'مدیریت تنظیمات عمومی',
                'manage_system_settings' => 'مدیریت تنظیمات سیستم',
                'manage_backup' => 'مدیریت پشتیبان‌گیری',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Special Permissions (Not tied to specific module)
    |--------------------------------------------------------------------------
    */
    'special_permissions' => [
        'super_admin_access' => 'دسترسی مدیر ارشد',
        'bypass_permission_check' => 'عبور از کنترل دسترسی',
        'impersonate_users' => 'ورود به جای کاربر دیگر',
        'view_deleted_records' => 'مشاهده رکوردهای حذف شده',
        'restore_deleted_records' => 'بازیابی رکوردهای حذف شده',
        'force_delete_records' => 'حذف دائمی رکوردها',
        'export_all_data' => 'خروجی تمام داده‌ها',
        'manage_system_config' => 'مدیریت پیکربندی سیستم',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Permission Inheritance
    |--------------------------------------------------------------------------
    */
    'inheritance' => [
        // Parent permission automatically grants child permissions
        'view_students' => ['view_student_profile', 'view_student_results', 'view_student_attendance', 'view_student_fees'],
        'manage_student_documents' => ['view_student_documents'],
        'view_financial_reports' => ['export_financial_reports'],
        'view_attendance_reports' => ['export_attendance'],
        'manage_inventory' => ['add_stock', 'remove_stock', 'view_inventory'],
        'manage_routes' => ['view_routes'],
        'manage_vehicles' => ['view_vehicles'],
    ],
];