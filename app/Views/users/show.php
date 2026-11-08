<?php
$title = 'پروفایل کاربر';
$user = $user ?? [];
$loginHistory = $loginHistory ?? [];
$permissions = $permissions ?? [];

$content = function() use ($user, $loginHistory, $permissions) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user me-2"></i>پروفایل کاربر</h1>
    <div>
        <?php if (hasPermission('edit_users')): ?>
        <a href="<?= url('/users/' . ($user['id'] ?? '') . '/edit') ?>" class="btn btn-warning"><i class="fas fa-edit me-1"></i>ویرایش</a>
        <?php endif; ?>
        <a href="<?= url('/users') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow text-center">
            <div class="card-body pt-5 pb-4">
                <img src="<?= $user['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle mb-3 shadow" width="100" height="100">
                <h4 class="fw-bold mb-1"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h4>
                <span class="badge" style="background-color:<?= $user['color'] ?? '#858796' ?>; color:white; font-size:14px;"><?= htmlspecialchars($user['role_display'] ?? $user['role_name'] ?? '') ?></span>
                <div class="mt-2"><?= $user['is_active'] ? '<span class="badge bg-success">فعال</span>' : '<span class="badge bg-danger">غیرفعال</span>' ?></div>
            </div>
            <div class="list-group list-group-flush text-start">
                <div class="list-group-item"><small class="text-muted">ایمیل</small><br><strong><?= htmlspecialchars($user['email'] ?? '') ?></strong></div>
                <div class="list-group-item"><small class="text-muted">کد کاربری</small><br><code><?= htmlspecialchars($user['user_code'] ?? '') ?></code></div>
                <div class="list-group-item"><small class="text-muted">شعبه</small><br><strong><?= htmlspecialchars($user['branch_name'] ?? '') ?></strong></div>
                <div class="list-group-item"><small class="text-muted">شماره تماس</small><br><?= htmlspecialchars($user['phone'] ?? 'تعیین نشده') ?></div>
                <div class="list-group-item"><small class="text-muted">جنسیت</small><br><?= $user['gender'] === 'male' ? 'مرد' : ($user['gender'] === 'female' ? 'زن' : 'سایر') ?></div>
                <?php if ($user['date_of_birth']): ?><div class="list-group-item"><small class="text-muted">تاریخ تولد</small><br><?= date('Y/m/d', strtotime($user['date_of_birth'])) ?></div><?php endif; ?>
                <div class="list-group-item"><small class="text-muted">آخرین ورود</small><br><?= $user['last_login_at'] ? date('Y/m/d H:i', strtotime($user['last_login_at'])) : 'تاکنون وارد نشده' ?></div>
                <div class="list-group-item"><small class="text-muted">تاریخ عضویت</small><br><?= date('Y/m/d', strtotime($user['created_at'] ?? 'now')) ?></div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-xl-8 col-lg-7">
        <!-- Permissions -->
        <?php if (!empty($permissions)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-shield-alt me-2"></i>مجوزهای دسترسی (<?= count($permissions) ?>)</h6></div>
            <div class="card-body">
                <?php $grouped = []; foreach ($permissions as $p) { $grouped[$p['module']][] = $p; } ?>
                <?php foreach ($grouped as $module => $perms): ?>
                <div class="mb-3"><h6 class="fw-bold text-secondary"><?= htmlspecialchars($module) ?></h6><div class="row"><?php foreach ($perms as $p): ?><div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" checked disabled><label class="form-check-label small"><?= htmlspecialchars($p['display_name']) ?></label></div></div><?php endforeach; ?></div></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Login History -->
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-history me-2"></i>تاریخچه ورود</h6></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>عملیات</th><th>توضیحات</th><th>IP</th><th>تاریخ</th></tr></thead><tbody>
                <?php if (!empty($loginHistory)): ?>
                    <?php foreach ($loginHistory as $log): ?>
                    <tr><td><span class="badge bg-<?= $log['action'] === 'login' ? 'success' : ($log['action'] === 'logout' ? 'secondary' : 'danger') ?>"><?= $log['action'] === 'login' ? 'ورود' : ($log['action'] === 'logout' ? 'خروج' : 'خطا') ?></span></td><td><small><?= htmlspecialchars($log['description'] ?? '') ?></small></td><td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td><td><small><?= date('Y/m/d H:i', strtotime($log['created_at'])) ?></small></td></tr>
                    <?php endforeach; ?>
                <?php else: ?><tr><td colspan="4" class="text-center py-3 text-muted">تاریخچه‌ای موجود نیست</td></tr><?php endif; ?>
            </tbody></table></div></div>
        </div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>