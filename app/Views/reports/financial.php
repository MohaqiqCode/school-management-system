<?php
$title = 'گزارش مالی';
$feeSummary = $feeSummary ?? [];
$outstandingFees = $outstandingFees ?? [];
$expenseSummary = $expenseSummary ?? [];
$totals = $totals ?? [];
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-t');

$content = function() use ($feeSummary, $outstandingFees, $expenseSummary, $totals, $startDate, $endDate) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>گزارش مالی</h1>
    <div><button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button><a href="<?=url('/reports/export/financial')?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>Excel</a></div>
</div>

<!-- Date Filter -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-4"><input type="date" class="form-control" name="start_date" value="<?=$startDate?>"></div><div class="col-md-4"><input type="date" class="form-control" name="end_date" value="<?=$endDate?>"></div><div class="col-md-4"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال</button></div></form></div></div>

<!-- Totals -->
<div class="row">
    <div class="col-md-4 mb-4"><div class="card border-left-success shadow"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">کل وصولی</div><div class="h4"><?=number_format($totals['total_collected']??0)?> ت</div></div></div></div>
    <div class="col-md-4 mb-4"><div class="card border-left-danger shadow"><div class="card-body"><div class="text-xs fw-bold text-danger mb-1">کل معوق</div><div class="h4"><?=number_format($totals['total_outstanding']??0)?> ت</div></div></div></div>
    <div class="col-md-4 mb-4"><div class="card border-left-warning shadow"><div class="card-body"><div class="text-xs fw-bold text-warning mb-1">کل هزینه‌ها</div><div class="h4"><?=number_format($totals['total_expenses']??0)?> ت</div></div></div></div>
</div>

<div class="row">
    <!-- Fee Collection -->
    <div class="col-xl-8 mb-4"><div class="card shadow"><div class="card-header"><h6 class="m-0 fw-bold">وصولی روزانه</h6></div><div class="card-body"><canvas id="revenueChart" height="250"></canvas></div></div></div>
    <!-- Outstanding -->
    <div class="col-xl-4 mb-4"><div class="card shadow"><div class="card-header"><h6 class="m-0 fw-bold">شهریه معوق به تفکیک کلاس</h6></div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>کلاس</th><th>دانش‌آموز</th><th>مبلغ</th></tr></thead><tbody><?php foreach($outstandingFees as $of): ?><tr><td><?=htmlspecialchars($of['class_name'])?></td><td><?=$of['student_count']?></td><td class="text-danger"><?=number_format($of['total_outstanding'])?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
</div>

<!-- Expenses -->
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">هزینه‌ها به تفکیک</h6></div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>دسته</th><th>تعداد</th><th>جمع</th></tr></thead><tbody><?php foreach($expenseSummary as $es): ?><tr><td><?=htmlspecialchars($es['expense_category'])?></td><td><?=$es['count']?></td><td><?=number_format($es['total'])?> ت</td></tr><?php endforeach; ?></tbody></table></div></div>

<script>new Chart(document.getElementById('revenueChart').getContext('2d'),{type:'bar',data:{labels:<?=json_encode(array_column($feeSummary??[],'date'))?>,datasets:[{label:'وصولی',data:<?=json_encode(array_map('floatval',array_column($feeSummary??[],'total_collected')))?>,backgroundColor:'#1cc88a'}]},options:{responsive:true}});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>