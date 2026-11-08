<?php
$title = 'جزئیات شعبه';
$branch = $branch ?? [];
$stats = $stats ?? [];
$recentStudents = $recentStudents ?? [];
$classDistribution = $classDistribution ?? [];
$monthlyRevenue = $monthlyRevenue ?? [];

$content = function() use ($branch, $stats, $recentStudents, $classDistribution, $monthlyRevenue) {
    ob_start();
?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building me-2"></i><?= htmlspecialchars($branch['name'] ?? 'جزئیات شعبه') ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 mt-2">
                <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/branches') ?>">شعب</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($branch['name'] ?? '') ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('edit_branches')): ?>
        <a href="<?= url('/branches/' . ($branch['id'] ?? '') . '/edit') ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i> ویرایش
        </a>
        <?php endif; ?>
        <?php if (hasPermission('manage_branch_settings')): ?>
        <a href="<?= url('/branches/' . ($branch['id'] ?? '') . '/settings') ?>" class="btn btn-secondary ms-1">
            <i class="fas fa-cog me-1"></i> تنظیمات
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Branch Info & Stats -->
<div class="row">
    <!-- Branch Details Card -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات شعبه</h6>
                <?php if ($branch['is_active'] ?? false): ?>
                <span class="badge bg-success">فعال</span>
                <?php else: ?>
                <span class="badge bg-danger">غیرفعال</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Logo -->
                <?php if (!empty($branch['logo_path'])): ?>
                <div class="text-center mb-3">
                    <img src="<?= asset($branch['logo_path']) ?>" alt="Logo" class="img-fluid rounded" style="max-height: 120px;">
                </div>
                <?php endif; ?>

                <table class="table table-sm">
                    <tr><td class="text-muted" width="40%">کد شعبه</td><td><span class="badge bg-secondary"><?= htmlspecialchars($branch['branch_code'] ?? '') ?></span></td></tr>
                    <tr><td class="text-muted">نام شعبه</td><td><strong><?= htmlspecialchars($branch['name'] ?? '') ?></strong></td></tr>
                    <tr><td class="text-muted">مدیر</td><td><?= htmlspecialchars($branch['principal_name'] ?? 'تعیین نشده') ?></td></tr>
                    <tr><td class="text-muted">ایمیل</td><td><?= htmlspecialchars($branch['email'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">تلفن</td><td><?= htmlspecialchars($branch['phone'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">شهر</td><td><?= htmlspecialchars($branch['city'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">استان</td><td><?= htmlspecialchars($branch['state'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">کد پستی</td><td><?= htmlspecialchars($branch['postal_code'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">تاریخ تأسیس</td><td><?= $branch['established_date'] ? date('Y/m/d', strtotime($branch['established_date'])) : '-' ?></td></tr>
                    <tr><td class="text-muted">ظرفیت</td><td><?= number_format($branch['total_capacity'] ?? 0) ?> نفر</td></tr>
                </table>

                <?php if (!empty($branch['address'])): ?>
                <div class="mt-3">
                    <h6 class="fw-bold"><i class="fas fa-map-marker-alt text-danger me-1"></i>آدرس</h6>
                    <p class="text-muted small"><?= nl2br(htmlspecialchars($branch['address'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats & Charts -->
    <div class="col-xl-8 col-lg-7">
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card border-left-primary shadow h-100"><div class="card-body text-center"><div class="h4 mb-0"><?= number_format($branch['student_count'] ?? 0) ?></div><small class="text-muted">دانش‌آموز</small></div></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-success shadow h-100"><div class="card-body text-center"><div class="h4 mb-0"><?= number_format($branch['teacher_count'] ?? 0) ?></div><small class="text-muted">معلم</small></div></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-info shadow h-100"><div class="card-body text-center"><div class="h4 mb-0"><?= number_format($branch['class_count'] ?? 0) ?></div><small class="text-muted">کلاس</small></div></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-left-warning shadow h-100"><div class="card-body text-center"><div class="h4 mb-0"><?= number_format($stats['attendance_rate'] ?? 0) ?>%</div><small class="text-muted">نرخ حضور</small></div></div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card bg-light"><div class="card-body text-center py-2"><small class="text-muted">نرخ وصول شهریه</small><div class="h5 mb-0 text-success"><?= $stats['fee_collection_rate'] ?? 0 ?>%</div></div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-light"><div class="card-body text-center py-2"><small class="text-muted">نرخ قبولی</small><div class="h5 mb-0 text-info"><?= $stats['exam_pass_rate'] ?? 0 ?>%</div></div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bg-light"><div class="card-body text-center py-2"><small class="text-muted">درآمد سالانه</small><div class="h5 mb-0 text-primary"><?= number_format($branch['yearly_revenue'] ?? 0) ?> ت</div></div></div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>روند درآمد ماهانه</h6></div>
            <div class="card-body"><canvas id="revenueChart" height="250"></canvas></div>
        </div>
    </div>
</div>

<!-- Class Distribution & Recent Students -->
<div class="row">
    <!-- Class Distribution -->
    <div class="col-xl-7 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>توزیع کلاسی</h6></div>
            <div class="card-body">
                <?php if (!empty($classDistribution)): ?>
                <div class="table-responsive"><table class="table table-hover"><thead><tr><th>کلاس</th><th>ظرفیت</th><th>دانش‌آموز</th><th>نرخ تکمیل</th></tr></thead><tbody>
                    <?php foreach ($classDistribution as $cd): ?>
                    <tr><td><strong><?= htmlspecialchars($cd['class_name']) ?></strong></td><td><?= $cd['capacity'] ?></td><td><?= $cd['student_count'] ?></td><td><div class="d-flex align-items-center"><span class="me-2 small"><?= $cd['occupancy_rate'] ?>%</span><div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-<?= $cd['occupancy_rate'] > 80 ? 'danger' : ($cd['occupancy_rate'] > 50 ? 'warning' : 'success') ?>" style="width:<?= $cd['occupancy_rate'] ?>%"></div></div></div></td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>
                <?php else: ?><p class="text-center text-muted py-3">کلاسی تعریف نشده است</p><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Students -->
    <div class="col-xl-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-success"><i class="fas fa-user-plus me-2"></i>آخرین ثبت نام‌ها</h6>
                <a href="<?= url('/students?branch_id=' . ($branch['id'] ?? '')) ?>" class="btn btn-sm btn-outline-success">همه</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentStudents)): ?>
                    <?php foreach ($recentStudents as $student): ?>
                    <div class="d-flex align-items-center mb-3 p-2 border-bottom">
                        <img src="<?= $student['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle me-2" width="40" height="40">
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($student['class_name'] ?? '') ?> | <?= date('Y/m/d', strtotime($student['admission_date'] ?? '')) ?></small>
                        </div>
                        <a href="<?= url('/students/' . $student['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?><p class="text-center text-muted py-3">دانش‌آموزی ثبت نام نشده</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const months = <?= json_encode(array_column($monthlyRevenue ?? [], 'month_name')) ?>;
    const totals = <?= json_encode(array_map('floatval', array_column($monthlyRevenue ?? [], 'total'))) ?>;
    
    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'line',
        data: { labels: months, datasets: [{ label: 'درآمد (تومان)', data: totals, borderColor: '#4e73df', backgroundColor: 'rgba(78,115,223,0.05)', tension: 0.3, fill: true }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { ticks: { callback: v => v.toLocaleString('fa-IR') } } } }
    });
});
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>