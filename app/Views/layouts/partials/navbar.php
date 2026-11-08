<?php
$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
$currentUser = $currentUser ?? [];
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">
        
        <!-- Sidebar Toggle -->
        <button class="btn btn-link text-dark me-3" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        
        <!-- Page Title -->
        <h5 class="mb-0 d-none d-md-block"><?= $title ?? 'پنل مدیریت' ?></h5>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                
                <!-- Quick Actions -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (hasPermission('create_students')): ?>
                        <li>
                            <a class="dropdown-item" href="<?= url('/students/create') ?>">
                                <i class="fas fa-user-plus text-primary me-2"></i> ثبت نام دانش‌آموز
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (hasPermission('mark_attendance')): ?>
                        <li>
                            <a class="dropdown-item" href="<?= url('/attendance/students/take') ?>">
                                <i class="fas fa-calendar-check text-success me-2"></i> ثبت حضور و غیاب
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (hasPermission('collect_fees')): ?>
                        <li>
                            <a class="dropdown-item" href="<?= url('/fees/collect') ?>">
                                <i class="fas fa-money-bill text-warning me-2"></i> دریافت شهریه
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (hasPermission('send_notifications')): ?>
                        <li>
                            <a class="dropdown-item" href="<?= url('/notifications/create') ?>">
                                <i class="fas fa-bell text-info me-2"></i> ارسال اعلان
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- Search -->
                <li class="nav-item me-3 d-none d-lg-block">
                    <form class="d-flex" action="<?= url('/search') ?>" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" name="q" 
                                   placeholder="جستجو کنید..." aria-label="Search">
                            <button class="btn btn-outline-secondary btn-sm" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" 
                       role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php if ($unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                              id="notificationBadge">
                            <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                            <span class="visually-hidden">اعلان خوانده نشده</span>
                        </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow" 
                         aria-labelledby="notificationDropdown" style="width: 350px;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <h6 class="mb-0 fw-bold">اعلان‌ها</h6>
                            <div>
                                <a href="#" class="btn btn-sm btn-link text-decoration-none mark-all-read">
                                    علامت‌گذاری همه به عنوان خوانده شده
                                </a>
                                <a href="<?= url('/notifications') ?>" class="btn btn-sm btn-link text-decoration-none">
                                    مشاهده همه
                                </a>
                            </div>
                        </div>
                        <div class="notification-list" style="max-height: 400px; overflow-y: auto;" id="notificationList">
                            <?php if (empty($notifications)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                                <p>اعلانی وجود ندارد</p>
                            </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                <a href="<?= url('/notifications/' . $notif['id'] . '/read') ?>" 
                                   class="dropdown-item notification-item <?= empty($notif['read_at']) ? 'bg-light' : '' ?>">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <?php
                                            $iconClass = match($notif['notification_type'] ?? 'info') {
                                                'announcement' => 'fa-bullhorn text-primary',
                                                'reminder' => 'fa-clock text-info',
                                                'alert' => 'fa-exclamation-triangle text-warning',
                                                'emergency' => 'fa-exclamation-circle text-danger',
                                                default => 'fa-info-circle text-secondary',
                                            };
                                            ?>
                                            <i class="fas <?= $iconClass ?> fa-lg mt-1"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <div class="small fw-bold"><?= htmlspecialchars($notif['title']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars(mb_substr($notif['message'], 0, 60)) ?>...</div>
                                            <div class="small text-muted mt-1">
                                                <i class="far fa-clock me-1"></i>
                                                <?= timeAgo($notif['created_at']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                       id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?= $currentUser['profile_photo'] ?? asset('images/default-avatar.png') ?>" 
                             class="rounded-circle me-2" width="32" height="32" alt="کاربر">
                        <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['first_name'] ?? 'کاربر') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <strong><?= htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?></strong>
                            <br>
                            <small class="text-muted"><?= $currentUser['role_display'] ?? '' ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= url('/profile') ?>">
                                <i class="fas fa-user me-2"></i> پروفایل
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= url('/change-password') ?>">
                                <i class="fas fa-key me-2"></i> تغییر رمز عبور
                            </a>
                        </li>
                        <?php if ($userRole === 'super_admin'): ?>
                        <li>
                            <a class="dropdown-item" href="<?= url('/settings') ?>">
                                <i class="fas fa-cog me-2"></i> تنظیمات سیستم
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= url('/logout') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i> خروج
                            </a>
                        </li>
                    </ul>
                </li>
                
            </ul>
        </div>
    </div>
</nav>
<!-- End Top Navbar -->