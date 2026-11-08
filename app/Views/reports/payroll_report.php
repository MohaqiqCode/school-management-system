<?php
$title = 'گزارش حقوق و دستمزد';
$payrolls = $payrolls ?? [];
$summary = $summary ?? [];
$monthFilter = $monthFilter ?? date('m');
$yearFilter = $yearFilter ?? date('Y');

$content = function() use ($payrolls, $summary, $monthFilter, $yearFilter) {
    ob_start();
    $monthNames = [1=>'فروردین',2=>'اردیبهشت',3=>'خرداد',4=>'تیر',5=>'مرداد',6=>'شهریور',7=>'مهر',8=>'آبان',9=>'آذر',10=>'دی',11=>'بهمن',12=>'اسفند'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>گزارش حقوق - <?=$monthNames[(int)$monthFilter]?> <?=$yearFilter?></h1>
    <div><button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button><a href="<?=url('/reports/export/payroll')?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>Excel</a></div>
</div>

<!-- Filters -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-4"><select class="form-select" name="month"><?php for($m=1;$m<=12;$m++): ?><option value="<?=$m?>" <?=$monthFilter==$m?'selected':''?>><?=$monthNames[$m]?></option><?php endfor; ?></select></div><div class="col-md-4"><select class="form-select" name="year"><?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?><option value="<?=$y?>" <?=$yearFilter==$y?'selected':''?>><?=$y?></option><?php endfor; ?></select></div><div class="col-md-4"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال</button></div></form></div></div>

<!-- Summary -->
<div class="row"><div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=number_format($summary['total_employees']??0)?></div><small>نفر</small></div></div></div><div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=number_format($summary['total_gross']??0)?> ت</div><small>ناخالص</small></div></div></div><div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=number_format($summary['total_deductions']??0)?> ت</div><small>کسورات</small></div></div></div><div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=number_format($summary['total_net']??0)?> ت</div><small>خالص</small></div></div></div></div>

<!-- Table -->
<div class="card shadow mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>نام</th><th>گرید</th><th>پایه</th><th>مزایا</th><th>کسورات</th><th>ناخالص</th><th>خالص</th><th>وضعیت</th></tr></thead><tbody><?php if(!empty($payrolls)): ?><?php foreach($payrolls as $i=>$p): ?><tr><td><?=$i+1?></td><td><?=htmlspecialchars($p['first_name'].' '.$p['last_name'])?></td><td><code><?=htmlspecialchars($p['salary_grade']??'-')?></code></td><td><?=number_format($p['basic_salary'])?></td><td><?=number_format($p['allowances'])?></td><td><?=number_format($p['deductions'])?></td><td><?=number_format($p['gross_salary'])?></td><td class="fw-bold"><?=number_format($p['net_salary'])?></td><td><span class="badge bg-<?=$p['status']==='paid'?'success':'warning'?>"><?=$p['status']==='paid'?'پرداخت':'در انتظار'?></span></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="9" class="text-center py-4">داده‌ای یافت نشد</td></tr><?php endif; ?></tbody></table></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>