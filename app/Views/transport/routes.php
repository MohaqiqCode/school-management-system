<?php
$title = 'مسیرهای حمل و نقل';
$routes = $routes ?? [];

$content = function() use ($routes) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-route me-2"></i>مسیرهای حمل و نقل</h1>
    <div>
        <a href="<?= url('/transport/routes/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>مسیر جدید</a>
        <a href="<?= url('/transport/student-allocation') ?>" class="btn btn-info ms-1"><i class="fas fa-user-plus me-1"></i>تخصیص دانش‌آموز</a>
    </div>
</div>

<div class="row">
    <?php if (!empty($routes)): ?>
        <?php foreach ($routes as $r): $pct = ($r['capacity'] ?? 1) > 0 ? round(($r['student_count'] / $r['capacity']) * 100) : 0; ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><h6 class="fw-bold"><?= htmlspecialchars($r['route_name']) ?></h6><span class="badge bg-<?= $r['is_active'] ? 'success' : 'danger' ?>"><?= $r['is_active'] ? 'فعال' : 'غیرفعال' ?></span></div>
                    <small class="text-muted d-block mb-2"><code><?= htmlspecialchars($r['route_code']) ?></code> | <?= htmlspecialchars($r['vehicle_number'] ?? 'بدون وسیله') ?></small>
                    <div class="small mb-1">راننده: <?= htmlspecialchars($r['driver_name'] ?? 'نامشخص') ?></div>
                    <div class="small mb-1">ایستگاه‌ها: <?= $r['stop_count'] ?? 0 ?></div>
                    <div class="small mb-2">دانش‌آموزان: <?= $r['student_count'] ?? 0 ?> / <?= $r['capacity'] ?? '?' ?></div>
                    <div class="progress mb-2" style="height:6px"><div class="progress-bar bg-<?= $pct > 80 ? 'danger' : ($pct > 50 ? 'warning' : 'success') ?>" style="width:<?=$pct?>%"></div></div>
                    <?php if ($r['monthly_fee'] > 0): ?><div class="small">شهریه ماهانه: <strong><?= number_format($r['monthly_fee']) ?> ت</strong></div><?php endif; ?>
                </div>
                <div class="card-footer bg-white"><a href="<?= url('/transport/routes/' . $r['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm w-100"><i class="fas fa-edit me-1"></i>ویرایش</a></div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?><div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-route fa-4x mb-3 d-block"></i><h5>مسیری تعریف نشده</h5></div></div><?php endif; ?>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>