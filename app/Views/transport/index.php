<?php
$title = 'داشبورد حمل و نقل';
$stats = $stats ?? [];
$vehicles = $vehicles ?? [];
$routes = $routes ?? [];

$content = function() use ($stats, $vehicles, $routes) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-bus me-2"></i>داشبورد حمل و نقل</h1>
    <div>
        <a href="<?= url('/transport/vehicles/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>وسیله جدید</a>
        <a href="<?= url('/transport/routes/create') ?>" class="btn btn-success btn-sm ms-1"><i class="fas fa-plus me-1"></i>مسیر جدید</a>
    </div>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body text-center"><i class="fas fa-truck fa-2x text-primary mb-2"></i><div class="h5"><?= number_format($stats['total_vehicles'] ?? 0) ?></div><small>وسایل نقلیه</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body text-center"><i class="fas fa-route fa-2x text-success mb-2"></i><div class="h5"><?= number_format($stats['total_routes'] ?? 0) ?></div><small>مسیرها</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body text-center"><i class="fas fa-id-card fa-2x text-info mb-2"></i><div class="h5"><?= number_format($stats['total_drivers'] ?? 0) ?></div><small>رانندگان</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body text-center"><i class="fas fa-user-check fa-2x text-warning mb-2"></i><div class="h5"><?= number_format($stats['students_using_transport'] ?? 0) ?></div><small>دانش‌آموز سرویسی</small></div></div></div>
</div>

<!-- Vehicles & Routes -->
<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card shadow"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-truck me-2"></i>وسایل نقلیه</h6><a href="<?= url('/transport/vehicles') ?>" class="btn btn-sm btn-outline-primary">مشاهده همه</a></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>#</th><th>شماره</th><th>نوع</th><th>ظرفیت</th><th>راننده</th></tr></thead><tbody>
            <?php if(!empty($vehicles)): ?><?php foreach(array_slice($vehicles,0,5) as $i=>$v): ?><tr><td><?=$i+1?></td><td><code><?=htmlspecialchars($v['vehicle_number'])?></code></td><td><?=htmlspecialchars($v['vehicle_type']??'-')?></td><td><?=$v['capacity']??'-'?></td><td><?=htmlspecialchars($v['driver_name']??'نامشخص')?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="5" class="text-center py-3">وسیله‌ای ثبت نشده</td></tr><?php endif; ?>
        </tbody></table></div></div></div>
    </div>
    <div class="col-xl-6 mb-4">
        <div class="card shadow"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold text-success"><i class="fas fa-route me-2"></i>مسیرها</h6><a href="<?= url('/transport/routes') ?>" class="btn btn-sm btn-outline-success">مشاهده همه</a></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>#</th><th>نام مسیر</th><th>کد</th><th>وسیله</th><th>دانش‌آموز</th></tr></thead><tbody>
            <?php if(!empty($routes)): ?><?php foreach(array_slice($routes,0,5) as $i=>$r): ?><tr><td><?=$i+1?></td><td><?=htmlspecialchars($r['route_name'])?></td><td><code><?=htmlspecialchars($r['route_code'])?></code></td><td><?=htmlspecialchars($r['vehicle_number']??'-')?></td><td><?=$r['student_count']??0?> نفر</td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="5" class="text-center py-3">مسیری ثبت نشده</td></tr><?php endif; ?>
        </tbody></table></div></div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>