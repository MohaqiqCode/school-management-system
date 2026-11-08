<?php
$title = 'گزارش مالی';
$revenue = $revenue ?? [];
$expenses = $expenses ?? [];
$summary = $summary ?? [];
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-t');

$content = function() use ($revenue, $expenses, $summary, $startDate, $endDate) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-chart-pie me-2"></i>گزارش مالی</h1>
    <div><button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button><a href="<?= url('/reports/financial') ?>" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>خروجی</a></div>
</div>

<!-- Date Filter -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-4"><input type="date" class="form-control" name="start_date" value="<?=$startDate?>"></div><div class="col-md-4"><input type="date" class="form-control" name="end_date" value="<?=$endDate?>"></div><div class="col-md-4"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال</button></div></form></div></div>

<!-- Summary -->
<div class="row">
    <div class="col-xl-4 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">کل درآمد</div><div class="h4"><?= number_format($summary['total_revenue'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-4 mb-4"><div class="card border-left-danger shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-danger mb-1">کل هزینه</div><div class="h4"><?= number_format($summary['total_expenses'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-4 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-info mb-1">سود خالص</div><div class="h4"><?= number_format($summary['net_income'] ?? 0) ?> ت</div></div></div></div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-xl-7 mb-4"><div class="card shadow"><div class="card-header"><h6 class="m-0 fw-bold">روند درآمد</h6></div><div class="card-body"><canvas id="revenueChart" height="280"></canvas></div></div></div>
    <div class="col-xl-5 mb-4"><div class="card shadow"><div class="card-header"><h6 class="m-0 fw-bold">هزینه‌ها به تفکیک</h6></div><div class="card-body"><canvas id="expenseChart" height="280"></canvas></div></div></div>
</div>

<!-- Expense Table -->
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">جزئیات هزینه‌ها</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>دسته</th><th>تعداد</th><th>جمع</th></tr></thead><tbody>
<?php foreach ($expenses as $e): ?><tr><td><?= htmlspecialchars($e['expense_category']) ?></td><td><?= $e['count'] ?></td><td class="fw-bold"><?= number_format($e['total']) ?> ت</td></tr><?php endforeach; ?>
</tbody></table></div></div></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('revenueChart').getContext('2d'), { type: 'line', data: { labels: <?=json_encode(array_column($revenue??[],'date'))?>, datasets: [{ label: 'درآمد', data: <?=json_encode(array_map('floatval',array_column($revenue??[],'total')))?>, borderColor: '#1cc88a', tension: 0.3 }] }, options: { responsive: true, maintainAspectRatio: false } });
    new Chart(document.getElementById('expenseChart').getContext('2d'), { type: 'doughnut', data: { labels: <?=json_encode(array_column($expenses??[],'expense_category'))?>, datasets: [{ data: <?=json_encode(array_map('floatval',array_column($expenses??[],'total')))?>, backgroundColor: ['#e74a3b','#3498db','#2ecc71','#f39c12','#9b59b6','#1abc9c','#e67e22','#95a5a6','#34495e','#7f8c8d'] }] }, options: { responsive: true, maintainAspectRatio: false } });
});
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>