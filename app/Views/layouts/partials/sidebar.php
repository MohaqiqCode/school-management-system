<?php
$currentUser = $currentUser ?? null;
$userRole = $currentUser['role_name'] ?? '';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPath = '/' . trim($currentPath, '/');

function isActive($path, $exact = false) {
    global $currentPath;
    if ($exact) {
        return $currentPath === $path;
    }
    return strpos($currentPath, $path) === 0;
}

function hasSubmenuActive($items) {
    foreach ($items as $item) {
        if (isActive($item['route'] ?? '')) {
            return true;
        }
    }
    return false;
}
?>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= url('/dashboard') ?>" class="sidebar-brand">
            <i class="fas fa-graduation-cap fa-2x"></i>
            <div class="sidebar-brand-text">
                <h6 class="mb-0">سیستم مدیریت</h6>
                <small>مدرسه هوشمند</small>
            </div>
        </a>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="<?= $currentUser['profile_photo'] ?? asset('images/default-avatar.png') ?>" 
                 alt="کاربر" class="rounded-circle" width="45" height="45">
        </div>
        <div class="user-info">
            <h6 class="mb-0"><?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['first_name'] . ' ' . $currentUser['last_name'] ?? 'کاربر') ?></h6>
            <small class="text-muted"><?= $currentUser['role_display'] ?? $currentUser['role_name'] ?? '' ?></small>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav flex-column" id="sidebarMenu">
            
            <!-- داشبورد -->
            <li class="nav-item">
                <a href="<?= url('/dashboard') ?>" 
                   class="nav-link <?= isActive('/dashboard', true) ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>داشبورد</span>
                </a>
            </li>
            
            <?php if (hasPermission('view_branches')): ?>
            <!-- مدیریت شعب -->
            <li class="nav-item">
                <a href="#submenu-branches" 
                   class="nav-link <?= isActive('/branches') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-building"></i>
                    <span>مدیریت شعب</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/branches') ? 'show' : '' ?>" 
                    id="submenu-branches" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/branches') ?>" 
                           class="nav-link <?= isActive('/branches', true) ? 'active' : '' ?>">
                            <span>لیست شعب</span>
                        </a>
                    </li>
                    <?php if (hasPermission('create_branches')): ?>
                    <li>
                        <a href="<?= url('/branches/create') ?>" 
                           class="nav-link <?= isActive('/branches/create') ? 'active' : '' ?>">
                            <span>ایجاد شعبه</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_users')): ?>
            <!-- مدیریت کاربران -->
            <li class="nav-item">
                <a href="#submenu-users" 
                   class="nav-link <?= isActive('/users') || isActive('/roles') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-users-cog"></i>
                    <span>کاربران و نقش‌ها</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/users') || isActive('/roles') ? 'show' : '' ?>" 
                    id="submenu-users" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/users') ?>" 
                           class="nav-link <?= isActive('/users', true) ? 'active' : '' ?>">
                            <span>کاربران</span>
                        </a>
                    </li>
                    <?php if (hasPermission('view_roles')): ?>
                    <li>
                        <a href="<?= url('/roles') ?>" 
                           class="nav-link <?= isActive('/roles') ? 'active' : '' ?>">
                            <span>نقش‌ها و مجوزها</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- آموزش -->
            <li class="nav-item">
                <a href="#submenu-academic" 
                   class="nav-link <?= isActive('/academic') || isActive('/classes') || isActive('/subjects') || isActive('/timetable') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-graduation-cap"></i>
                    <span>امور آموزشی</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/academic') || isActive('/classes') || isActive('/subjects') || isActive('/timetable') ? 'show' : '' ?>" 
                    id="submenu-academic" data-bs-parent="#sidebarMenu">
                    
                    <?php if (hasPermission('view_academic_years')): ?>
                    <li>
                        <a href="<?= url('/academic-years') ?>" 
                           class="nav-link <?= isActive('/academic-years') ? 'active' : '' ?>">
                            <span>سال‌های تحصیلی</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('view_semesters')): ?>
                    <li>
                        <a href="<?= url('/semesters') ?>" 
                           class="nav-link <?= isActive('/semesters') ? 'active' : '' ?>">
                            <span>ترم‌ها</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('view_departments')): ?>
                    <li>
                        <a href="<?= url('/departments') ?>" 
                           class="nav-link <?= isActive('/departments') ? 'active' : '' ?>">
                            <span>دپارتمان‌ها</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('view_classes')): ?>
                    <li>
                        <a href="<?= url('/classes') ?>" 
                           class="nav-link <?= isActive('/classes') ? 'active' : '' ?>">
                            <span>کلاس‌ها</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('view_sections')): ?>
                    <li>
                        <a href="<?= url('/sections') ?>" 
                           class="nav-link <?= isActive('/sections') ? 'active' : '' ?>">
                            <span>بخش‌ها</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('view_subjects')): ?>
                    <li>
                        <a href="<?= url('/subjects') ?>" 
                           class="nav-link <?= isActive('/subjects') ? 'active' : '' ?>">
                            <span>دروس</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('view_timetable')): ?>
                    <li>
                        <a href="<?= url('/timetable') ?>" 
                           class="nav-link <?= isActive('/timetable') ? 'active' : '' ?>">
                            <span>برنامه هفتگی</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            
            <?php if (hasPermission('view_students')): ?>
            <!-- دانش‌آموزان -->
            <li class="nav-item">
                <a href="#submenu-students" 
                   class="nav-link <?= isActive('/students') || isActive('/parents') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-user-graduate"></i>
                    <span>دانش‌آموزان</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/students') || isActive('/parents') ? 'show' : '' ?>" 
                    id="submenu-students" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/students') ?>" 
                           class="nav-link <?= isActive('/students', true) ? 'active' : '' ?>">
                            <span>لیست دانش‌آموزان</span>
                        </a>
                    </li>
                    <?php if (hasPermission('create_students')): ?>
                    <li>
                        <a href="<?= url('/students/create') ?>" 
                           class="nav-link <?= isActive('/students/create') ? 'active' : '' ?>">
                            <span>ثبت نام جدید</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission('view_parents')): ?>
                    <li>
                        <a href="<?= url('/parents') ?>" 
                           class="nav-link <?= isActive('/parents') ? 'active' : '' ?>">
                            <span>والدین</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_teachers')): ?>
            <!-- معلمان -->
            <li class="nav-item">
                <a href="<?= url('/teachers') ?>" 
                   class="nav-link <?= isActive('/teachers') ? 'active' : '' ?>">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>معلمان</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_employees')): ?>
            <!-- کارمندان -->
            <li class="nav-item">
                <a href="<?= url('/employees') ?>" 
                   class="nav-link <?= isActive('/employees') ? 'active' : '' ?>">
                    <i class="fas fa-briefcase"></i>
                    <span>کارمندان</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_attendance')): ?>
            <!-- حضور و غیاب -->
            <li class="nav-item">
                <a href="#submenu-attendance" 
                   class="nav-link <?= isActive('/attendance') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-calendar-check"></i>
                    <span>حضور و غیاب</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/attendance') ? 'show' : '' ?>" 
                    id="submenu-attendance" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/attendance/students') ?>" 
                           class="nav-link <?= isActive('/attendance/students') ? 'active' : '' ?>">
                            <span>دانش‌آموزان</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/attendance/teachers') ?>" 
                           class="nav-link <?= isActive('/attendance/teachers') ? 'active' : '' ?>">
                            <span>معلمان</span>
                        </a>
                    </li>
                    <?php if (hasPermission('mark_attendance')): ?>
                    <li>
                        <a href="<?= url('/attendance/students/take') ?>" 
                           class="nav-link <?= isActive('/attendance/students/take') ? 'active' : '' ?>">
                            <span>ثبت حضور و غیاب</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_exams')): ?>
            <!-- آزمون‌ها -->
            <li class="nav-item">
                <a href="#submenu-exams" 
                   class="nav-link <?= isActive('/exams') || isActive('/results') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-file-alt"></i>
                    <span>آزمون‌ها</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/exams') || isActive('/results') ? 'show' : '' ?>" 
                    id="submenu-exams" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/exams') ?>" 
                           class="nav-link <?= isActive('/exams', true) ? 'active' : '' ?>">
                            <span>لیست آزمون‌ها</span>
                        </a>
                    </li>
                    <?php if (hasPermission('create_exams')): ?>
                    <li>
                        <a href="<?= url('/exams/create') ?>" 
                           class="nav-link <?= isActive('/exams/create') ? 'active' : '' ?>">
                            <span>ایجاد آزمون</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?= url('/results') ?>" 
                           class="nav-link <?= isActive('/results') ? 'active' : '' ?>">
                            <span>نتایج</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_fees')): ?>
            <!-- امور مالی -->
            <li class="nav-item">
                <a href="#submenu-finance" 
                   class="nav-link <?= isActive('/fees') || isActive('/expenses') || isActive('/payroll') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>امور مالی</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/fees') || isActive('/expenses') || isActive('/payroll') ? 'show' : '' ?>" 
                    id="submenu-finance" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/fees') ?>" 
                           class="nav-link <?= isActive('/fees', true) ? 'active' : '' ?>">
                            <span>شهریه‌ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/expenses') ?>" 
                           class="nav-link <?= isActive('/expenses') ? 'active' : '' ?>">
                            <span>هزینه‌ها</span>
                        </a>
                    </li>
                    <?php if (hasPermission('view_payroll')): ?>
                    <li>
                        <a href="<?= url('/payroll') ?>" 
                           class="nav-link <?= isActive('/payroll') ? 'active' : '' ?>">
                            <span>حقوق و دستمزد</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_books')): ?>
            <!-- کتابخانه -->
            <li class="nav-item">
                <a href="<?= url('/library/books') ?>" 
                   class="nav-link <?= isActive('/library') ? 'active' : '' ?>">
                    <i class="fas fa-book-open"></i>
                    <span>کتابخانه</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_vehicles')): ?>
            <!-- حمل و نقل -->
            <li class="nav-item">
                <a href="#submenu-transport" 
                   class="nav-link <?= isActive('/transport') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-bus"></i>
                    <span>حمل و نقل</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/transport') ? 'show' : '' ?>" 
                    id="submenu-transport" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/transport/vehicles') ?>" 
                           class="nav-link <?= isActive('/transport/vehicles') ? 'active' : '' ?>">
                            <span>وسایل نقلیه</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/transport/routes') ?>" 
                           class="nav-link <?= isActive('/transport/routes') ? 'active' : '' ?>">
                            <span>مسیرها</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/transport/student-allocation') ?>" 
                           class="nav-link <?= isActive('/transport/student-allocation') ? 'active' : '' ?>">
                            <span>تخصیص دانش‌آموزان</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_inventory')): ?>
            <!-- انبار -->
            <li class="nav-item">
                <a href="<?= url('/inventory') ?>" 
                   class="nav-link <?= isActive('/inventory') ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i>
                    <span>انبار</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- گزارش‌ها -->
            <li class="nav-item">
                <a href="#submenu-reports" 
                   class="nav-link <?= isActive('/reports') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-chart-bar"></i>
                    <span>گزارش‌ها</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/reports') ? 'show' : '' ?>" 
                    id="submenu-reports" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/reports/students') ?>" 
                           class="nav-link <?= isActive('/reports/students') ? 'active' : '' ?>">
                            <span>دانش‌آموزان</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/reports/attendance') ?>" 
                           class="nav-link <?= isActive('/reports/attendance') ? 'active' : '' ?>">
                            <span>حضور و غیاب</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/reports/financial') ?>" 
                           class="nav-link <?= isActive('/reports/financial') ? 'active' : '' ?>">
                            <span>مالی</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/reports/exam') ?>" 
                           class="nav-link <?= isActive('/reports/exam') ? 'active' : '' ?>">
                            <span>آزمون‌ها</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <?php if (hasPermission('send_notifications')): ?>
            <!-- اعلان‌ها -->
            <li class="nav-item">
                <a href="<?= url('/notifications') ?>" 
                   class="nav-link <?= isActive('/notifications') ? 'active' : '' ?>">
                    <i class="fas fa-bell"></i>
                    <span>اعلان‌ها</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('view_audit_logs')): ?>
            <!-- ممیزی -->
            <li class="nav-item">
                <a href="#submenu-audit" 
                   class="nav-link <?= isActive('/audit') || isActive('/system') ? '' : 'collapsed' ?>" 
                   data-bs-toggle="collapse">
                    <i class="fas fa-history"></i>
                    <span>ممیزی و لاگ</span>
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="collapse <?= isActive('/audit') || isActive('/system') ? 'show' : '' ?>" 
                    id="submenu-audit" data-bs-parent="#sidebarMenu">
                    <li>
                        <a href="<?= url('/audit-logs') ?>" 
                           class="nav-link <?= isActive('/audit-logs') ? 'active' : '' ?>">
                            <span>لاگ‌های فعالیت</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('/system-logs') ?>" 
                           class="nav-link <?= isActive('/system-logs') ? 'active' : '' ?>">
                            <span>لاگ‌های سیستم</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
        </ul>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="d-flex justify-content-between align-items-center px-3">
            <small class="text-muted">نسخه <?= config('app_version', '1.0.0') ?></small>
            <button class="btn btn-sm btn-link text-muted" id="sidebarCollapse">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</nav>
<!-- End Sidebar -->