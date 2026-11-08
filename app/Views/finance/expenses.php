<?php
$title = 'مدیریت هزینه‌ها';
$expenses = $expenses ?? [];
$summary = $summary ?? [];
$selectedCategory = $selectedCategory ?? '';
$selectedStatus = $selectedStatus ?? '';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';

$content = function() use ($expenses, $summary, $selectedCategory, $selectedStatus, $dateFrom, $dateTo) {
    ob_start();
    $cats = ['salary'=>'حقوق','utilities'=>'آب و برق','maintenance'=>'تعمیرات','supplies'=>'ملزومات','transport'=>'حمل و نقل','events'=>'رویدادها','rent'=>'اجاره','insurance'=>'بیمه','tax'=>'مالیات','other'=>'سایر'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i>مدیریت هزینه‌ها</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="fas fa-plus me-1"></i>ثبت هزینه جدید</button>
</div>

<!-- Summary -->
<div class="row"><div class="col-md-4 mb-3"><div class="card bg-light"><div class="card-body text-center"><small>کل هزینه‌ها</small><div class="h4"><?= number_format($summary['total_amount'] ?? 0) ?> ت</div></div></div></div><div class="col-md-4 mb-3"><div class="card bg-light"><div class="card-body text-center"><small>تعداد</small><div class="h4"><?= $summary['total_count'] ?? 0 ?></div></div></div></div><div class="col-md-4 mb-3"><div class="card bg-light"><div class="card-body text-center"><small>در انتظار تأیید</small><div class="h4"><?= $summary['pending_count'] ?? 0 ?></div></div></div></div></div>

<!-- Filters -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-3"><select class="form-select" name="category"><option value="">همه دسته‌ها</option><?php foreach($cats as $k=>$v): ?><option value="<?=$k?>" <?=$selectedCategory==$k?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div><div class="col-md-3"><select class="form-select" name="status"><option value="">همه وضعیت‌ها</option><option value="pending" <?=$selectedStatus==='pending'?'selected':''?>>در انتظار</option><option value="approved" <?=$selectedStatus==='approved'?'selected':''?>>تأیید شده</option><option value="paid" <?=$selectedStatus==='paid'?'selected':''?>>پرداخت شده</option></select></div><div class="col-md-2"><input type="date" class="form-control" name="date_from" value="<?=$dateFrom?>"></div><div class="col-md-2"><input type="date" class="form-control" name="date_to" value="<?=$dateTo?>"></div><div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button></div></form></div></div>

<!-- Table -->
<div class="card shadow mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>دسته</th><th>شرح</th><th>مبلغ</th><th>تاریخ</th><th>فروشنده</th><th>وضعیت</th></tr></thead><tbody>
<?php if(!empty($expenses)): ?>
    <?php $stC=['pending'=>'warning','approved'=>'info','paid'=>'success','cancelled'=>'danger']; $stT=['pending'=>'در انتظار','approved'=>'تأیید شده','paid'=>'پرداخت','cancelled'=>'لغو']; ?>
    <?php foreach($expenses as $i=>$e): ?>
    <tr><td><?=$i+1?></td><td><?=$cats[$e['expense_category']]??$e['expense_category']?></td><td><?=htmlspecialchars($e['description'])?></td><td class="fw-bold"><?=number_format($e['amount'])?> ت</td><td><?=date('Y/m/d',strtotime($e['expense_date']))?></td><td><?=htmlspecialchars($e['vendor_name']??'-')?></td><td><span class="badge bg-<?=$stC[$e['status']]??'light'?>"><?=$stT[$e['status']]??$e['status']?></span></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="7" class="text-center py-4">هزینه‌ای ثبت نشده</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<!-- Create Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="<?=url('/expenses')?>" method="POST" enctype="multipart/form-data"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>ثبت هزینه</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><div class="mb-3"><label class="form-label">دسته‌بندی</label><select class="form-select" name="expense_category" required><option value="">انتخاب...</option><?php foreach($cats as $k=>$v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">شرح</label><textarea class="form-control" name="description" rows="2" required></textarea></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">مبلغ (تومان)</label><input type="number" class="form-control" name="amount" required></div><div class="col-md-6 mb-3"><label class="form-label">تاریخ</label><input type="date" class="form-control" name="expense_date" value="<?=date('Y-m-d')?>" required></div></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">فروشنده</label><input type="text" class="form-control" name="vendor_name"></div><div class="col-md-6 mb-3"><label class="form-label">شماره فاکتور</label><input type="text" class="form-control" name="invoice_number"></div></div><div class="mb-3"><label class="form-label">رسید</label><input type="file" class="form-control" name="receipt"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>ذخیره</button></div></form></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>