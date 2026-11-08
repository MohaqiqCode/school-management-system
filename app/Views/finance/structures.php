<?php
$title = 'ساختار شهریه';
$structures = $structures ?? [];

$content = function() use ($structures) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-cog me-2"></i>ساختار شهریه</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#structureModal"><i class="fas fa-plus me-1"></i>ایجاد ساختار جدید</button>
</div>

<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">لیست ساختارها</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>دسته‌بندی</th><th>کلاس</th><th>سال تحصیلی</th><th>مبلغ</th><th>جریمه دیرکرد</th><th>تخفیف</th><th>روز سررسید</th><th>وضعیت</th></tr></thead><tbody>
<?php if (!empty($structures)): ?>
    <?php foreach ($structures as $i => $st): ?>
    <tr><td><?=$i+1?></td><td><?=htmlspecialchars($st['category_name'])?></td><td><?=htmlspecialchars($st['class_name'])?></td><td><?=htmlspecialchars($st['year_name'])?></td><td class="fw-bold"><?=number_format($st['amount'])?> ت</td><td><?=number_format($st['late_fee_amount'])?> ت</td><td><?=$st['discount_allowed']?>%</td><td><?=$st['due_day']?> هر ماه</td><td><?=$st['is_active']?'<span class="badge bg-success">فعال</span>':'<span class="badge bg-danger">غیرفعال</span>'?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="9" class="text-center py-4">ساختاری تعریف نشده</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<!-- Create Modal -->
<div class="modal fade" id="structureModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="<?=url('/fees/structures')?>" method="POST"><div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>ایجاد ساختار جدید</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><div class="mb-3"><label class="form-label">دسته‌بندی</label><select class="form-select" name="fee_category_id" required><option value="">انتخاب...</option></select></div><div class="mb-3"><label class="form-label">کلاس</label><select class="form-select" name="class_id" required><option value="">انتخاب...</option></select></div><div class="mb-3"><label class="form-label">سال تحصیلی</label><select class="form-select" name="academic_year_id" required><option value="">انتخاب...</option></select></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">مبلغ (تومان)</label><input type="number" class="form-control" name="amount" required></div><div class="col-md-6 mb-3"><label class="form-label">جریمه دیرکرد</label><input type="number" class="form-control" name="late_fee_amount" value="0"></div></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">تخفیف مجاز (%)</label><input type="number" class="form-control" name="discount_allowed" value="0" max="100"></div><div class="col-md-6 mb-3"><label class="form-label">روز سررسید</label><input type="number" class="form-control" name="due_day" value="10" min="1" max="30"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div></form></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>