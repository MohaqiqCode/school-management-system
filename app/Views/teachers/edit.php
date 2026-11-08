<?php
$title = 'ویرایش معلم';
$teacher = $teacher ?? [];
$departments = $departments ?? [];
$old = $_SESSION['old'] ?? $teacher;

$content = function() use ($teacher, $departments, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش معلم</h1><p class="text-muted mb-0 mt-1"><?= htmlspecialchars(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) ?></p></div>
    <div><a href="<?= url('/teachers/' . ($teacher['id'] ?? '')) ?>" class="btn btn-info"><i class="fas fa-eye me-1"></i>مشاهده</a><a href="<?= url('/teachers') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>
</div>

<form action="<?= url('/teachers/' . ($teacher['id'] ?? '')) ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="PUT">
    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">اطلاعات شخصی</h6></div><div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام</label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? $teacher['first_name'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی</label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? $teacher['last_name'] ?? '') ?>" required></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? $teacher['phone'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ تولد</label><input type="text" class="form-control persian-datepicker" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth'] ?? $teacher['date_of_birth'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تصویر</label><input type="file" class="form-control" name="profile_photo"></div>
        </div>
    </div></div>
    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success">اطلاعات شغلی</h6></div><div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">دپارتمان</label><select class="form-select" name="department_id"><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= ($old['department_id'] ?? $teacher['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تخصص</label><input type="text" class="form-control" name="specialization" value="<?= htmlspecialchars($old['specialization'] ?? $teacher['specialization'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">مدرک</label><input type="text" class="form-control" name="qualification" value="<?= htmlspecialchars($old['qualification'] ?? $teacher['qualification'] ?? '') ?>"></div>
        </div>
    </div></div>
    <div class="d-flex justify-content-between mb-4"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $teacher['id'] ?>)"><i class="fas fa-trash me-1"></i>حذف</button><div><a href="<?= url('/teachers') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div></div>
</form>

<script>function confirmDelete(id){Swal.fire({title:'حذف معلم',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',cancelButtonText:'انصراف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?= url('/teachers') ?>/'+id;f.innerHTML='<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">';document.body.appendChild(f);f.submit()}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>