<?php
$title = 'پردازش حقوق و دستمزد';
$allStaff = $allStaff ?? [];

$content = function() use ($allStaff) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-cogs me-2"></i>پردازش حقوق</h1>
    <a href="<?= url('/payroll') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>حقوق تمام کارکنان فعال برای دوره جاری محاسبه و ثبت می‌شود. کارکنانی که قبلاً برای این دوره پردازش شده‌اند، مجدداً محاسبه نمی‌شوند.</div>

<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold">لیست کارکنان برای پردازش (<?=count($allStaff??[])?> نفر)</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>نام</th><th>نوع</th><th>گرید</th><th>حقوق پایه</th><th>مزایا</th><th>کسورات</th><th>خالص</th></tr></thead><tbody>
<?php if (!empty($allStaff)): ?>
    <?php foreach ($allStaff as $i => $s): $allowances = ($s['house_allowance']??0)+($s['medical_allowance']??0)+($s['transport_allowance']??0)+($s['other_allowances']??0); $net = ($s['basic_salary']??0) + $allowances - ($s['deductions']??0); ?>
    <tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($s['first_name'].' '.$s['last_name'])?></strong></td><td><span class="badge bg-<?=($s['type']??'')==='teacher'?'info':'secondary'?>"><?=($s['type']??'')==='teacher'?'معلم':'کارمند'?></span></td><td><code><?=htmlspecialchars($s['salary_grade']??'-')?></code></td><td><?=number_format($s['basic_salary']??0)?></td><td><?=number_format($allowances)?></td><td><?=number_format($s['deductions']??0)?></td><td class="fw-bold"><?=number_format($net)?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="8" class="text-center py-4">کارمندی یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<form action="<?= url('/payroll/process') ?>" method="POST"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><div class="row mb-4"><div class="col-md-4"><label class="form-label">شروع دوره</label><input type="date" class="form-control" name="pay_period_start" value="<?=date('Y-m-01')?>" required></div><div class="col-md-4"><label class="form-label">پایان دوره</label><input type="date" class="form-control" name="pay_period_end" value="<?=date('Y-m-t')?>" required></div><div class="col-md-4"><label class="form-label">تاریخ پرداخت</label><input type="date" class="form-control" name="payment_date" value="<?=date('Y-m-t')?>"></div></div>
<?php if (!empty($allStaff)): ?><button type="submit" class="btn btn-success btn-lg w-100"><i class="fas fa-check-double me-2"></i>پردازش حقوق همه کارکنان</button><?php endif; ?>
</form>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>