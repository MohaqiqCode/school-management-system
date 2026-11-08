<?php
$title = 'ثبت معلم جدید';
$departments = $departments ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];

$content = function() use ($departments, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus me-2"></i>ثبت معلم جدید</h1>
    <a href="<?= url('/teachers') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<form action="<?= url('/teachers') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-user me-2"></i>اطلاعات شخصی</h6></div><div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام <span class="text-danger">*</span></label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی <span class="text-danger">*</span></label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">ایمیل <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تصویر</label><input type="file" class="form-control" name="profile_photo" accept="image/*"></div>
        </div>
    </div></div>
    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-briefcase me-2"></i>اطلاعات شغلی</h6></div><div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">دپارتمان</label><select class="form-select" name="department_id"><option value="">انتخاب...</option><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تخصص</label><input type="text" class="form-control" name="specialization" value="<?= htmlspecialchars($old['specialization'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">مدرک تحصیلی</label><input type="text" class="form-control" name="qualification" value="<?= htmlspecialchars($old['qualification'] ?? '') ?>"></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">سابقه تدریس (سال)</label><input type="number" class="form-control" name="teaching_experience_years" value="<?= htmlspecialchars($old['teaching_experience_years'] ?? '0') ?>" step="0.5"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ استخدام <span class="text-danger">*</span></label><input type="text" class="form-control persian-datepicker" name="joining_date" value="<?= htmlspecialchars($old['joining_date'] ?? date('Y/m/d')) ?>" required></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">نوع قرارداد</label><select class="form-select" name="contract_type"><option value="permanent">رسمی</option><option value="contract">قراردادی</option><option value="probation">آزمایشی</option><option value="part-time">پاره وقت</option></select></div>
        </div>
    </div></div>
    <div class="d-flex justify-content-between mb-4"><a href="<?= url('/teachers') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>ذخیره</button></div>
</form>

<script>$(document).ready(function(){$('.persian-datepicker').persianDatepicker({format:'YYYY/MM/DD',autoClose:true})});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>