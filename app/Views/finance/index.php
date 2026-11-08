<?php
$title = 'داشبورد مالی';
$fees = $fees ?? [];
$summary = $summary ?? [];
$classes = $classes ?? [];
$selectedStatus = $selectedStatus ?? '';
$selectedClass = $selectedClass ?? '';
$search = $search ?? '';

$content = function() use ($fees, $summary, $classes, $selectedStatus, $selectedClass, $search) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-money-bill-wave me-2"></i>داشبورد مالی</h1>
    <div>
        <a href="<?= url('/fees/collect') ?>" class="btn btn-success"><i class="fas fa-plus me-1"></i>دریافت شهریه</a>
        <a href="<?= url('/fees/structures') ?>" class="btn btn-primary ms-1"><i class="fas fa-cog me-1"></i>ساختار شهریه</a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-primary mb-1">کل شهریه</div><div class="h5 mb-0"><?= number_format($summary['total_amount'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">وصول شده</div><div class="h5 mb-0"><?= number_format($summary['collected_amount'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-danger shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-danger mb-1">معوق</div><div class="h5 mb-0"><?= number_format($summary['pending_amount'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-info mb-1">نرخ وصول</div><div class="h5 mb-0"><?= $summary['collection_rate'] ?? 0 ?>%</div></div></div></div>
</div>

<!-- Filters -->
<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/fees') ?>" method="GET" class="row g-3">
    <div class="col-md-3"><input type="text" class="form-control" name="search" placeholder="جستجوی دانش‌آموز..." value="<?= htmlspecialchars($search) ?>"></div>
    <div class="col-md-3"><select class="form-select" name="class_id"><option value="">همه کلاس‌ها</option><?php foreach ($classes as $cl): ?><option value="<?= $cl['id'] ?>" <?= $selectedClass == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><select class="form-select" name="status"><option value="">همه وضعیت‌ها</option><option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>در انتظار</option><option value="partial" <?= $selectedStatus === 'partial' ? 'selected' : '' ?>>پرداخت جزئی</option><option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : '' ?>>پرداخت شده</option><option value="overdue" <?= $selectedStatus === 'overdue' ? 'selected' : '' ?>>سررسید گذشته</option></select></div>
    <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>فیلتر</button></div>
</form></div></div>

<!-- Fees Table -->
<div class="card shadow mb-4"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold text-primary">لیست شهریه‌ها</h6><span class="badge bg-primary"><?= $summary['total_records'] ?? 0 ?> رکورد</span></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>دانش‌آموز</th><th>کلاس</th><th>دسته‌بندی</th><th>مبلغ کل</th><th>پرداختی</th><th>باقیمانده</th><th>سررسید</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
<?php if (!empty($fees)): ?>
    <?php $stColors=['pending'=>'warning','partial'=>'info','paid'=>'success','overdue'=>'danger','waived'=>'secondary']; $stTexts=['pending'=>'در انتظار','partial'=>'جزئی','paid'=>'پرداخت','overdue'=>'سررسید','waived'=>'بخشیده']; ?>
    <?php foreach ($fees as $i => $fee): ?>
    <tr><td><?= $i+1 ?></td><td><strong><?= htmlspecialchars(($fee['first_name']??'').' '.($fee['last_name']??'')) ?></strong><br><code><?= htmlspecialchars($fee['student_code']??'') ?></code></td><td><?= htmlspecialchars($fee['class_name']??'') ?></td><td><?= htmlspecialchars($fee['category_name']??'') ?></td><td><?= number_format($fee['net_payable']??0) ?></td><td class="text-success"><?= number_format($fee['paid_amount']??0) ?></td><td class="text-danger fw-bold"><?= number_format($fee['balance_amount']??0) ?></td><td><?= $fee['due_date']??'' ?></td><td><span class="badge bg-<?=$stColors[$fee['status']]??'light'?>"><?=$stTexts[$fee['status']]??$fee['status']?></span></td><td><a href="<?=url('/fees/collect?student_id='.$fee['student_id'])?>" class="btn btn-sm btn-success"><i class="fas fa-money-bill me-1"></i>دریافت</a></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="10" class="text-center py-4 text-muted">شهریه‌ای یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>