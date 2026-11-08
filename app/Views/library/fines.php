<?php
$title = 'جریمه‌های کتابخانه';
$fines = $fines ?? [];

$content = function() use ($fines) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-exclamation-triangle me-2"></i>جریمه‌های کتابخانه</h1>
    <a href="<?= url('/library/borrowings') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<!-- Summary -->
<div class="row">
    <div class="col-md-4 mb-4"><div class="card border-left-danger shadow"><div class="card-body"><div class="text-xs fw-bold text-danger mb-1">کل جریمه‌ها</div><div class="h5"><?= number_format(array_sum(array_column($fines??[], 'fine_amount'))) ?> ت</div></div></div></div>
    <div class="col-md-4 mb-4"><div class="card border-left-warning shadow"><div class="card-body"><div class="text-xs fw-bold text-warning mb-1">جریمه‌های پرداخت نشده</div><div class="h5"><?= number_format(array_sum(array_column(array_filter($fines??[], fn($f)=>!($f['fine_paid']??false)), 'fine_amount'))) ?> ت</div></div></div></div>
    <div class="col-md-4 mb-4"><div class="card border-left-success shadow"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">پرداخت شده</div><div class="h5"><?= number_format(array_sum(array_column(array_filter($fines??[], fn($f)=>$f['fine_paid']??false), 'fine_amount'))) ?> ت</div></div></div></div>
</div>

<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold text-danger">لیست جریمه‌ها</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>کتاب</th><th>امانت‌گیرنده</th><th>تاریخ برگشت</th><th>تأخیر (روز)</th><th>مبلغ جریمه</th><th>وضعیت پرداخت</th><th>عملیات</th></tr></thead><tbody>
<?php if (!empty($fines)): ?>
    <?php foreach ($fines as $i => $f): $daysLate = max(0, ceil((strtotime($f['return_date']??'now') - strtotime($f['due_date']??'now')) / 86400)); ?>
    <tr><td><?=$i+1?></td><td><?=htmlspecialchars($f['book_title']??'')?></td><td><?=htmlspecialchars(($f['first_name']??'').' '.($f['last_name']??''))?></td><td><?=date('Y/m/d',strtotime($f['return_date']??''))?></td><td><?=$daysLate?> روز</td><td class="fw-bold text-danger"><?=number_format($f['fine_amount'])?> ت</td><td><?=$f['fine_paid']?'<span class="badge bg-success">پرداخت شده</span>':'<span class="badge bg-danger">پرداخت نشده</span>'?></td><td><?php if(!$f['fine_paid']): ?><button class="btn btn-sm btn-success" onclick="payFine(<?=$f['id']?>)"><i class="fas fa-check me-1"></i>پرداخت</button><?php endif; ?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="8" class="text-center py-4">جریمه‌ای ثبت نشده</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<script>function payFine(id){Swal.fire({title:'ثبت پرداخت جریمه',icon:'question',showCancelButton:true,confirmButtonText:'بله',cancelButtonText:'خیر'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/library/fines')?>/'+id+'/pay',{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else toastr.error(d.message)})}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>