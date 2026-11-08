<?php
$title = 'ساختار حقوق و دستمزد';
$structures = $structures ?? [];

$content = function() use ($structures) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-cog me-2"></i>ساختار حقوق و دستمزد</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#structureModal"><i class="fas fa-plus me-1"></i>ایجاد ساختار جدید</button>
</div>

<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">لیست ساختارها</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>نوع</th><th>گرید</th><th>شعبه</th><th>پایه</th><th>مسکن</th><th>درمان</th><th>ایاب و ذهاب</th><th>سایر مزایا</th><th>کسورات</th><th>جمع</th><th>وضعیت</th></tr></thead><tbody>
<?php if (!empty($structures)): ?>
    <?php foreach ($structures as $i => $st): ?>
    <tr><td><?=$i+1?></td><td><span class="badge bg-<?=$st['employee_type']==='teacher'?'info':'secondary'?>"><?=$st['employee_type']==='teacher'?'معلم':'کارمند'?></span></td><td><code><?=htmlspecialchars($st['salary_grade'])?></code></td><td><?=htmlspecialchars($st['branch_name']??'')?></td><td><?=number_format($st['basic_salary'])?></td><td><?=number_format($st['house_allowance'])?></td><td><?=number_format($st['medical_allowance'])?></td><td><?=number_format($st['transport_allowance'])?></td><td><?=number_format($st['other_allowances'])?></td><td><?=number_format($st['deductions'])?></td><td class="fw-bold"><?=number_format(($st['basic_salary']??0)+($st['house_allowance']??0)+($st['medical_allowance']??0)+($st['transport_allowance']??0)+($st['other_allowances']??0))?></td><td><?=$st['is_active']?'<span class="badge bg-success">فعال</span>':'<span class="badge bg-danger">غیرفعال</span>'?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="12" class="text-center py-4">ساختاری تعریف نشده</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<!-- Create Modal -->
<div class="modal fade" id="structureModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form action="<?=url('/payroll/structures')?>" method="POST"><div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>ساختار جدید</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">نوع</label><select class="form-select" name="employee_type" required><option value="teacher">معلم</option><option value="staff">کارمند</option></select></div><div class="col-md-6 mb-3"><label class="form-label">گرید حقوقی</label><input type="text" class="form-control" name="salary_grade" required></div></div><div class="row"><div class="col-md-4 mb-3"><label class="form-label">حقوق پایه</label><input type="number" class="form-control" name="basic_salary" required></div><div class="col-md-4 mb-3"><label class="form-label">کمک هزینه مسکن</label><input type="number" class="form-control" name="house_allowance" value="0"></div><div class="col-md-4 mb-3"><label class="form-label">کمک هزینه درمان</label><input type="number" class="form-control" name="medical_allowance" value="0"></div></div><div class="row"><div class="col-md-4 mb-3"><label class="form-label">ایاب و ذهاب</label><input type="number" class="form-control" name="transport_allowance" value="0"></div><div class="col-md-4 mb-3"><label class="form-label">سایر مزایا</label><input type="number" class="form-control" name="other_allowances" value="0"></div><div class="col-md-4 mb-3"><label class="form-label">کسورات</label><input type="number" class="form-control" name="deductions" value="0"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div></form></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>