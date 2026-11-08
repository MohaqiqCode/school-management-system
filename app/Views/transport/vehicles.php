<?php
$title = 'وسایل نقلیه';
$vehicles = $vehicles ?? [];

$content = function() use ($vehicles) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-truck me-2"></i>وسایل نقلیه</h1>
    <a href="<?= url('/transport/vehicles/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>افزودن وسیله</a>
</div>

<div class="row">
    <?php if (!empty($vehicles)): ?>
        <?php foreach ($vehicles as $v): 
            $insExp = strtotime($v['insurance_expiry'] ?? '');
            $fitExp = strtotime($v['fitness_certificate_expiry'] ?? '');
            $insStatus = !$insExp ? 'unknown' : ($insExp < time() ? 'expired' : ($insExp < strtotime('+30 days') ? 'expiring' : 'valid'));
            $fitStatus = !$fitExp ? 'unknown' : ($fitExp < time() ? 'expired' : ($fitExp < strtotime('+30 days') ? 'expiring' : 'valid'));
        ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div><h6 class="fw-bold mb-0"><?= htmlspecialchars($v['vehicle_number']) ?></h6><small class="text-muted"><?= htmlspecialchars($v['vehicle_type'] ?? '') ?> | <?= htmlspecialchars($v['model'] ?? '') ?></small></div>
                        <span class="badge bg-<?= $v['is_active'] ? 'success' : 'danger' ?>"><?= $v['is_active'] ? 'فعال' : 'غیرفعال' ?></span>
                    </div>
                    <div class="small mb-2"><i class="fas fa-user me-1"></i>راننده: <strong><?= htmlspecialchars($v['driver_name'] ?? 'نامشخص') ?></strong></div>
                    <div class="small mb-1">ظرفیت: <?= $v['capacity'] ?? '-' ?> نفر</div>
                    <div class="small mb-1">بیمه: <span class="badge bg-<?= $insStatus==='valid'?'success':($insStatus==='expiring'?'warning':'danger') ?>"><?= $v['insurance_expiry'] ? date('Y/m/d', strtotime($v['insurance_expiry'])) : 'نامشخص' ?></span></div>
                    <div class="small">معاینه فنی: <span class="badge bg-<?= $fitStatus==='valid'?'success':($fitStatus==='expiring'?'warning':'danger') ?>"><?= $v['fitness_certificate_expiry'] ? date('Y/m/d', strtotime($v['fitness_certificate_expiry'])) : 'نامشخص' ?></span></div>
                </div>
                <div class="card-footer bg-white"><a href="<?= url('/transport/vehicles/' . $v['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm w-100"><i class="fas fa-edit me-1"></i>ویرایش</a></div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?><div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-truck fa-4x mb-3 d-block"></i><h5>وسیله نقلیه‌ای ثبت نشده</h5></div></div><?php endif; ?>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>