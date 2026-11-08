<?php
$title = 'ثبت کارمند جدید';
$departments = $departments ?? [];
$employees = $employees ?? [];
$old = $_SESSION['old'] ?? [];

$content = function() use ($departments, $employees, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-user-plus me-2"></i>ثبت کارمند جدید</h1><a href="<?= url('/employees') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<form action="<?= url('/employees') ?>" method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold text-primary">اطلاعات شخصی</h6></div><div class="card-body">
    <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">نام <span class="text-danger">*</span></label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($old['first_name']??'') ?>" required></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی <span class="text-danger">*</span></label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($old['last_name']??'') ?>" required></div></div>
    <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">ایمیل <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($old['email']??'') ?>" required></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone']??'') ?>"></div></div>
</div></div>
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold text-success">اطلاعات شغلی</h6></div><div class="card-body">
    <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">سمت <span class="text-danger">*</span></label><input type="text" class="form-control" name="designation" value="<?= htmlspecialchars($old['designation']??'') ?>" required></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">نوع</label><select class="form-select" name="employee_type"><option value="administrative">اداری</option><option value="support">پشتیبانی</option><option value="maintenance">تاسیسات</option><option value="security">حراست</option><option value="other">سایر</option></select></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">دپارتمان</label><select class="form-select" name="department_id"><option value="">انتخاب...</option><?php foreach($departments as $d): ?><option value="<?=$d['id']?>"><?=htmlspecialchars($d['name'])?></option><?php endforeach; ?></select></div></div>
    <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">تاریخ استخدام</label><input type="text" class="form-control persian-datepicker" name="joining_date" value="<?= date('Y/m/d') ?>"></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">سرپرست</label><select class="form-select" name="supervisor_id"><option value="">بدون سرپرست</option><?php foreach($employees as $e): ?><option value="<?=$e['id']?>"><?=htmlspecialchars($e['first_name'].' '.$e['last_name'])?></option><?php endforeach; ?></select></div></div>
</div></div>
<div class="d-flex justify-content-between mb-4"><a href="<?= url('/employees') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>ذخیره</button></div>
</form>

<script>$(document).ready(function(){$('.persian-datepicker').persianDatepicker({format:'YYYY/MM/DD',autoClose:true})});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>