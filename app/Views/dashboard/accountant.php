<?php
$content = function() use ($stats, $feeCollectionByClass, $dailyCollection, $user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calculator me-2"></i>داشبورد حسابداری</h1>
    <a href="<?= url('/fees/collect') ?>" class="btn btn-success"><i class="fas fa-plus me-1"></i>دریافت شهریه</a>
</div>

<!-- Financial Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">درآمد امروز</div><div class="h5 mb-0"><?= number_format($stats['total_revenue_today'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-primary mb-1">درآمد ماه</div><div class="h5 mb-0"><?= number_format($stats['total_revenue_month'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-danger shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-danger mb-1">شهریه معوق</div><div class="h5 mb-0"><?= number_format($stats['pending_fees'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-warning mb-1">هزینه‌های ماه</div><div class="h5 mb-0"><?= number_format($stats['total_expenses_month'] ?? 0) ?> ت</div></div></div></div>
</div>

<!-- Fee Collection by Class -->
<div class="row">
    <div class="col-xl-7 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>نرخ وصول شهریه به تفکیک کلاس</h6></div>
            <div class="card-body">
                <?php if (!empty($feeCollectionByClass)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead><tr><th>کلاس</th><th>کل شهریه</th><th>وصول شده</th><th>معوق</th><th>درصد وصول</th></tr></thead>
                        <tbody>
                            <?php foreach ($feeCollectionByClass as $fc): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($fc['class_name']) ?></strong></td>
                                <td><?= number_format($fc['total_fees']) ?> ت</td>
                                <td class="text-success"><?= number_format($fc['collected']) ?> ت</td>
                                <td class="text-danger"><?= number_format($fc['pending']) ?> ت</td>
                                <td>
                                    <div class="d-flex align-items-center"><span class="me-2"><?= $fc['collection_rate'] ?>%</span><div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-<?= $fc['collection_rate'] >= 80 ? 'success' : ($fc['collection_rate'] >= 50 ? 'warning' : 'danger') ?>" style="width:<?= $fc['collection_rate'] ?>%"></div></div></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?><p class="text-center text-muted py-3">داده‌ای موجود نیست</p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-line me-2"></i>روند وصول روزانه (۳۰ روز)</h6></div>
            <div class="card-body"><canvas id="dailyCollectionChart" height="280"></canvas></div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow"><div class="card-body"><div class="row g-3">
            <div class="col-md-3"><a href="<?= url('/fees/structures') ?>" class="btn btn-outline-primary w-100"><i class="fas fa-cog me-1"></i>ساختار شهریه</a></div>
            <div class="col-md-3"><a href="<?= url('/expenses') ?>" class="btn btn-outline-danger w-100"><i class="fas fa-money-bill me-1"></i>ثبت هزینه</a></div>
            <div class="col-md-3"><a href="<?= url('/reports/financial') ?>" class="btn btn-outline-info w-100"><i class="fas fa-chart-bar me-1"></i>گزارش مالی</a></div>
            <div class="col-md-3"><a href="<?= url('/payroll') ?>" class="btn btn-outline-warning w-100"><i class="fas fa-file-invoice me-1"></i>حقوق و دستمزد</a></div>
        </div></div></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dates = <?= json_encode(array_column($dailyCollection ?? [], 'date')) ?>;
    const totals = <?= json_encode(array_map('floatval', array_column($dailyCollection ?? [], 'total'))) ?>;
    
    new Chart(document.getElementById('dailyCollectionChart').getContext('2d'), {
        type: 'line',
        data: { labels: dates, datasets: [{ label: 'وصول روزانه (تومان)', data: totals, borderColor: '#1cc88a', tension: 0.3, fill: true, backgroundColor: 'rgba(28,200,138,0.1)' }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { ticks: { callback: v => v.toLocaleString('fa-IR') } } } }
    });
});
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>