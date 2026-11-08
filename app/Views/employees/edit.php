<?php
$title = 'ویرایش کارمند';
$employee = $employee ?? [];
$departments = $departments ?? [];
$employees = $employees ?? [];
$old = $_SESSION['old'] ?? $employee;

$content = function() use ($employee, $departments, $employees, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><div><h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>ویرایش کارمند</h1><p class="text-muted mb-0"><?= htmlspecialchars(($employee['first_name']??'').' '.($employee['last_name']??'')) ?></p></div><a href="<?= url('/employees') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<form action="<?= url('/employees/'.($employee['id']??'')) ?>" method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="PUT">
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold text-primary">اطلاعات شخصی</h6></div><div class="card-body"><div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">نام</label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($old['first_name']??$employee['first_name']??'') ?>" required></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی</label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($old['last_name']??$employee['last_name']??'') ?>" required></div></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone']??$employee['phone']??'') ?>"></div></div></div></div>
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold text-success">اطلاعات شغلی</h6></div><div class="card-body"><div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">سمت</label><input type="text" class="form-control" name="designation" value="<?= htmlspecialchars($old['designation']??$employee['designation']??'') ?>" required></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">نوع</label><select class="form-select" name="employee_type"><?php foreach(['administrative'=>'اداری','support'=>'پشتیبانی','maintenance'=>'تاسیسات','security'=>'حراست','other'=>'سایر'] as $k=>$v): ?><option value="<?=$k?>" <?=($old['employee_type']??$employee['employee_type']??'')==$k?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">دپارتمان</label><select class="form-select" name="department_id"><option value="">انتخاب...</option><?php foreach($departments as $d): ?><option value="<?=$d['id']?>" <?=($old['department_id']??$employee['department_id']??'')==$d['id']?'selected':''?>><?=htmlspecialchars($d['name'])?></option><?php endforeach; ?></select></div></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">سرپرست</label><select class="form-select" name="supervisor_id"><option value="">بدون سرپرست</option><?php foreach($employees as $e): ?><option value="<?=$e['id']?>" <?=($old['supervisor_id']??$employee['supervisor_id']??'')==$e['id']?'selected':''?>><?=htmlspecialchars($e['first_name'].' '.$e['last_name'])?></option><?php endforeach; ?></select></div></div></div></div>
<div class="d-flex justify-content-between mb-4"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?=$employee['id']?>)"><i class="fas fa-trash me-1"></i>حذف</button><div><a href="<?= url('/employees') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div></div>
</form>

<script>function confirmDelete(id){Swal.fire({title:'حذف کارمند',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',cancelButtonText:'انصراف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?=url('/employees')?>/'+id;f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="DELETE">';document.body.appendChild(f);f.submit()}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>