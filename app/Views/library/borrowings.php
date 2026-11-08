<?php
$title = 'امانت‌های کتاب';
$borrowings = $borrowings ?? [];
$statusFilter = $statusFilter ?? '';
$userIdFilter = $userIdFilter ?? '';

$content = function() use ($borrowings, $statusFilter, $userIdFilter) {
    ob_start();
    $stC = ['borrowed'=>'warning','returned'=>'success','overdue'=>'danger','lost'=>'dark'];
    $stT = ['borrowed'=>'امانت','returned'=>'برگشتی','overdue'=>'دیرکرد','lost'=>'گم شده'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-exchange-alt me-2"></i>امانت‌های کتاب</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueModal"><i class="fas fa-hand-holding me-1"></i>امانت جدید</button>
</div>

<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-6"><select class="form-select" name="status" onchange="this.form.submit()"><option value="">همه وضعیت‌ها</option><?php foreach($stT as $k=>$v): ?><option value="<?=$k?>" <?=$statusFilter===$k?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div><div class="col-md-6"><input type="text" class="form-control" name="user_id" placeholder="جستجوی کاربر..." value="<?=htmlspecialchars($userIdFilter)?>"></div></form></div></div>

<div class="card shadow mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>کتاب</th><th>امانت‌گیرنده</th><th>تاریخ امانت</th><th>موعد بازگشت</th><th>تاریخ برگشت</th><th>جریمه</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
<?php if (!empty($borrowings)): ?>
    <?php foreach ($borrowings as $i => $bb): ?>
    <tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($bb['book_title'])?></strong><br><small class="text-muted"><?=htmlspecialchars($bb['book_code']??'')?></small></td><td><?=htmlspecialchars(($bb['first_name']??'').' '.($bb['last_name']??''))?></td><td><?=date('Y/m/d',strtotime($bb['borrow_date']))?></td><td><?=date('Y/m/d',strtotime($bb['due_date']))?></td><td><?=$bb['return_date']?date('Y/m/d',strtotime($bb['return_date'])):'-'?></td><td><?=$bb['fine_amount']>0?number_format($bb['fine_amount']).' ت':'-'?></td><td><span class="badge bg-<?=$stC[$bb['status']]?>"><?=$stT[$bb['status']]?></span></td><td><?php if($bb['status']==='borrowed'||$bb['status']==='overdue'): ?><button class="btn btn-sm btn-success" onclick="returnBook(<?=$bb['id']?>)"><i class="fas fa-undo me-1"></i>برگشت</button><?php endif; ?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="9" class="text-center py-4">امانتی ثبت نشده</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<!-- Issue Modal -->
<div class="modal fade" id="issueModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="<?=url('/library/issue')?>" method="POST"><div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="fas fa-hand-holding me-2"></i>امانت کتاب</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><div class="mb-3"><label class="form-label">کتاب</label><select class="form-select" name="book_id" required><option value="">انتخاب...</option></select></div><div class="mb-3"><label class="form-label">امانت‌گیرنده</label><input type="text" class="form-control" name="user_id" placeholder="کد کاربری..." required></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">تاریخ امانت</label><input type="date" class="form-control" name="borrow_date" value="<?=date('Y-m-d')?>"></div><div class="col-md-6 mb-3"><label class="form-label">مدت امانت (روز)</label><input type="number" class="form-control" name="due_days" value="14"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>ثبت</button></div></form></div></div></div>

<script>
function returnBook(id){Swal.fire({title:'برگشت کتاب',text:'آیا کتاب برگشت داده شده است؟',icon:'question',showCancelButton:true,confirmButtonText:'بله',cancelButtonText:'خیر'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?=url('/library/return')?>';f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="borrowing_id" value="'+id+'"><input type="hidden" name="return_date" value="<?=date('Y-m-d')?>">';document.body.appendChild(f);f.submit()}})}
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>