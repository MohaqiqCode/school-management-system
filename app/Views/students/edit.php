<?php
$title = 'ویرایش دانش‌آموز';
$student = $student ?? [];
$classes = $classes ?? [];
$sections = $sections ?? [];
$old = $_SESSION['old'] ?? $student;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($student, $classes, $sections, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش دانش‌آموز</h1><p class="text-muted mb-0 mt-1"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?> - <?= htmlspecialchars($student['student_code'] ?? '') ?></p></div>
    <div><a href="<?= url('/students/' . ($student['id'] ?? '')) ?>" class="btn btn-info"><i class="fas fa-eye me-1"></i>مشاهده</a><a href="<?= url('/students') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>
</div>

<form action="<?= url('/students/' . ($student['id'] ?? '')) ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="PUT">

    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-user me-2"></i>اطلاعات شخصی</h6></div><div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام</label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? $student['first_name'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی</label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? $student['last_name'] ?? '') ?>" required></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? $student['phone'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ تولد</label><input type="text" class="form-control persian-datepicker" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth'] ?? $student['date_of_birth'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تصویر</label><input type="file" class="form-control" name="profile_photo" accept="image/*"></div>
        </div>
        <?php if (!empty($student['profile_photo'])): ?><div class="mb-2"><img src="<?= asset($student['profile_photo']) ?>" class="rounded" style="max-height:60px;"></div><?php endif; ?>
    </div></div>

    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-graduation-cap me-2"></i>اطلاعات تحصیلی</h6></div><div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">کلاس</label><select class="form-select" name="class_id"><?php foreach ($classes as $cl): ?><option value="<?= $cl['id'] ?>" <?= ($old['class_id'] ?? $student['current_class_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">بخش</label><select class="form-select" name="section_id"><?php foreach ($sections as $sec): ?><option value="<?= $sec['id'] ?>" <?= ($old['section_id'] ?? $student['current_section_id'] ?? '') == $sec['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sec['section_name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">گروه خونی</label><select class="form-select" name="blood_group"><option value="">نامشخص</option><?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?><option value="<?= $bg ?>" <?= ($old['blood_group'] ?? $student['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option><?php endforeach; ?></select></div>
        </div>
    </div></div>

    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="fas fa-notes-medical me-2"></i>پزشکی و اضطراری</h6></div><div class="card-body">
        <div class="mb-2"><label class="form-label fw-bold">بیماری‌های خاص</label><textarea class="form-control" name="medical_conditions" rows="2"><?= htmlspecialchars($old['medical_conditions'] ?? $student['medical_conditions'] ?? '') ?></textarea></div>
        <div class="mb-2"><label class="form-label fw-bold">حساسیت‌ها</label><textarea class="form-control" name="allergies" rows="2"><?= htmlspecialchars($old['allergies'] ?? $student['allergies'] ?? '') ?></textarea></div>
        <div class="row">
            <div class="col-md-4 mb-2"><label class="form-label fw-bold">تماس اضطراری</label><input type="text" class="form-control" name="emergency_contact_name" value="<?= htmlspecialchars($old['emergency_contact_name'] ?? $student['emergency_contact_name'] ?? '') ?>"></div>
            <div class="col-md-4 mb-2"><label class="form-label fw-bold">شماره اضطراری</label><input type="text" class="form-control" name="emergency_contact_phone" value="<?= htmlspecialchars($old['emergency_contact_phone'] ?? $student['emergency_contact_phone'] ?? '') ?>"></div>
            <div class="col-md-4 mb-2"><label class="form-label fw-bold">نسبت</label><input type="text" class="form-control" name="emergency_contact_relation" value="<?= htmlspecialchars($old['emergency_contact_relation'] ?? $student['emergency_contact_relation'] ?? '') ?>"></div>
        </div>
    </div></div>

    <div class="d-flex justify-content-between mb-4">
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $student['id'] ?>, '<?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>')"><i class="fas fa-trash me-1"></i>حذف</button>
        <div><a href="<?= url('/students') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div>
    </div>
</form>

<script>
    function confirmDelete(id, name) { Swal.fire({ title: 'حذف دانش‌آموز', text: 'آیا از حذف "' + name + '" اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/students') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } }); }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>