<?php
$content = function() use ($stats, $routeUtilization, $user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-bus-alt me-2"></i>داشبورد مدیریت حمل و نقل</h1>
    <a href="<?= url('/transport/routes/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>مسیر جدید</a>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body text-center"><i class="fas fa-truck fa-2x text-primary mb-2"></i><div class="h5"><?= number_format($stats['total_vehicles'] ?? 0) ?></div><small>وسایل نقلیه</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body text-center"><i class="fas fa-route fa-2x text-success mb-2"></i><div class="h5"><?= number_format($stats['total_routes'] ?? 0) ?></div><small>مسیرهای فعال</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body text-center"><i class="fas fa-id-card fa-2x text-info mb-2"></i><div class="h5"><?= number_format($stats['total_drivers'] ?? 0) ?></div><small>رانندگان</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body text-center"><i class="fas fa-user-check fa-2x text-warning mb-2"></i><div class="h5"><?= number_format($stats['students_using_transport'] ?? 0) ?></div><small>دانش‌آموز سرویسی</small></div></div></div>
</div>

<!-- Route Utilization -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i>میزان استفاده از مسیرها</h6></div><div class="card-body">
            <?php if (!empty($routeUtilization)): ?>
            <div class="table-responsive"><table class="table table-hover"><thead><tr><th>مسیر</th><th>کد</th><th>وسیله نقلیه</th><th>ظرفیت</th><th>دانش‌آموزان</th><th>درصد تکمیل</th></tr></thead><tbody>
                <?php foreach ($routeUtilization as $route): 
                    $cap = $route['capacity'] ?? 30;
                    $rate = $cap > 0 ? round(($route['students_count'] / $cap) * 100) : 0;
                ?>
                <tr><td><strong><?= htmlspecialchars($route['route_name']) ?></strong></td><td><code><?= htmlspecialchars($route['route_code']) ?></code></td><td><?= htmlspecialchars($route['vehicle_number'] ?? '-') ?></td><td><?= $cap ?></td><td><?= $route['students_count'] ?></td><td><div class="d-flex align-items-center"><span class="me-2"><?= $rate ?>%</span><div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-<?= $rate >= 80 ? 'success' : ($rate >= 40 ? 'warning' : 'info') ?>" style="width:<?= $rate ?>%"></div></div></div></td></tr>
                <?php endforeach; ?>
            </tbody></table></div>
            <?php else: ?><p class="text-center text-muted py-3">مسیری تعریف نشده است</p><?php endif; ?>
        </div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>