<?php
$title = 'مدیریت حقوق و دستمزد';
$payrolls = $payrolls ?? [];
$summary = $summary ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$statusFilter = $statusFilter ?? '';
$monthFilter = $monthFilter ?? date('m');
$yearFilter = $yearFilter ?? date('Y');

$content = function() use ($payrolls, $summary, $currentPage, $totalPages, $total, $statusFilter, $monthFilter, $yearFilter) {
    ob_start();
    $monthNames = [1=>'فروردین',2=>'اردیبهشت',3=>'خرداد',4=>'تیر',5=>'مرداد',6=>'شهریور',7=>'مهر',8=>'آبان',9=>'آذر',10=>'دی',11=>'بهمن',12=>'اسفند'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-invoice-dollar me-2"></i>مدیریت حقوق و دستمزد</h1>
    <div>
        <a href="<?= url('/payroll/process') ?>" class="btn btn-success"><i class="fas fa-cogs me-1"></i>پردازش حقوق</a>
        <a href="<?= url('/payroll/structures') ?>" class="btn btn-primary ms-1"><i class="fas fa-cog me-1"></i>ساختار حقوق</a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-primary mb-1">تعداد کارکنان</div><div class="h5 mb-0"><?= number_format($summary['total_employees'] ?? 0) ?> نفر</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">جمع ناخالص</div><div class="h5 mb-0"><?= number_format($summary['total_gross'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-danger shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-danger mb-1">کسورات</div><div class="h5 mb-0"><?= number_format($summary['total_deductions'] ?? 0) ?> ت</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-info mb-1">خالص پرداختی</div><div class="h5 mb-0"><?= number_format($summary['total_net'] ?? 0) ?> ت</div></div></div></div>
</div>

<!-- Filters -->
<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/payroll') ?>" method="GET" class="row g-3">
    <div class="col-md-3">
        <label class="form-label small">ماه</label>
        <select class="form-select" name="month"><?php for($m=1;$m<=12;$m++): ?><option value="<?=$m?>" <?=$monthFilter==$m?'selected':''?>><?=$monthNames[$m]?></option><?php endfor; ?></select>
    </div>
    <div class="col-md-3">
        <label class="form-label small">سال</label>
        <select class="form-select" name="year"><?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?><option value="<?=$y?>" <?=$yearFilter==$y?'selected':''?>><?=$y?></option><?php endfor; ?></select>
    </div>
    <div class="col-md-3">
        <label class="form-label small">وضعیت</label>
        <select class="form-select" name="status"><option value="">همه</option><option value="pending" <?=$statusFilter==='pending'?'selected':''?>>در انتظار</option><option value="processed" <?=$statusFilter==='processed'?'selected':''?>>پردازش شده</option><option value="paid" <?=$statusFilter==='paid'?'selected':''?>>پرداخت شده</option></select>
    </div>
    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال</button></div>
</form></div></div>

<!-- Payroll Table -->
<div class="card shadow mb-4"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold text-primary">لیست حقوق - <?=$monthNames[(int)$monthFilter]?> <?=$yearFilter?></h6><span class="badge bg-primary"><?=number_format($total)?> رکورد</span></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>نام</th><th>ایمیل</th><th>گرید</th><th>پایه</th><th>مزایا</th><th>کسورات</th><th>ناخالص</th><th>خالص</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
<?php if (!empty($payrolls)): ?>
    <?php $stC=['pending'=>'warning','processed'=>'info','paid'=>'success']; $stT=['pending'=>'در انتظار','processed'=>'پردازش','paid'=>'پرداخت']; ?>
    <?php foreach ($payrolls as $i => $p): ?>
    <tr><td><?=($currentPage-1)*25+$i+1?></td><td><strong><?=htmlspecialchars($p['first_name'].' '.$p['last_name'])?></strong></td><td><small><?=htmlspecialchars($p['email'])?></small></td><td><code><?=htmlspecialchars($p['salary_grade']??'-')?></code></td><td><?=number_format($p['basic_salary'])?></td><td><?=number_format($p['allowances'])?></td><td><?=number_format($p['deductions'])?></td><td><?=number_format($p['gross_salary'])?></td><td class="fw-bold"><?=number_format($p['net_salary'])?></td><td><span class="badge bg-<?=$stC[$p['status']]?>"><?=$stT[$p['status']]?></span></td><td><div class="btn-group btn-group-sm"><a href="<?=url('/payroll/'.$p['id'].'/payslip')?>" class="btn btn-info" title="فیش"><i class="fas fa-file-alt"></i></a><?php if($p['status']!=='paid'): ?><button class="btn btn-success" onclick="markPaid(<?=$p['id']?>)" title="پرداخت"><i class="fas fa-check"></i></button><?php endif; ?></div></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="11" class="text-center py-4 text-muted">رکوردی یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php if($totalPages>1): ?><div class="card-footer"><nav><ul class="pagination justify-content-center mb-0"><?php for($p=1;$p<=$totalPages;$p++): ?><li class="page-item <?=$p==$currentPage?'active':''?>"><a class="page-link" href="?page=<?=$p?>&month=<?=$monthFilter?>&year=<?=$yearFilter?>&status=<?=$statusFilter?>"><?=$p?></a></li><?php endfor; ?></ul></nav></div><?php endif; ?>
</div>

<script>
function markPaid(id){Swal.fire({title:'تأیید پرداخت',icon:'question',showCancelButton:true,confirmButtonText:'بله',cancelButtonText:'خیر'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/payroll')?>/'+id+'/mark-paid',{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else toastr.error(d.message)})}})}
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>