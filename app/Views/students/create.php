<?php
$title = 'ثبت نام دانش‌آموز جدید';
$classes = $classes ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($classes, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus me-2"></i>ثبت نام دانش‌آموز جدید</h1>
    <a href="<?= url('/students') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<form action="<?= url('/students') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <!-- Student Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-user me-2"></i>اطلاعات دانش‌آموز</h6></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required><?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= $errors['first_name'] ?></div><?php endif; ?></div>
                <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required><?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name'] ?></div><?php endif; ?></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">ایمیل <span class="text-danger">*</span></label><input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required><?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ تولد</label><input type="text" class="form-control persian-datepicker" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">جنسیت</label><select class="form-select" name="gender"><option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>مرد</option><option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>زن</option><option value="other" <?= ($old['gender'] ?? 'other') === 'other' ? 'selected' : '' ?>>سایر</option></select></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">گروه خونی</label><select class="form-select" name="blood_group"><option value="">نامشخص</option><?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?><option value="<?= $bg ?>" <?= ($old['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">تصویر</label><input type="file" class="form-control" name="profile_photo" accept="image/*"></div>
            </div>
        </div>
    </div>

    <!-- Academic Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-graduation-cap me-2"></i>اطلاعات تحصیلی</h6></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">کلاس <span class="text-danger">*</span></label><select class="form-select <?= isset($errors['class_id']) ? 'is-invalid' : '' ?>" name="class_id" id="classSelect" required onchange="loadSections(this.value)"><option value="">انتخاب کلاس...</option><?php foreach ($classes as $cl): ?><option value="<?= $cl['id'] ?>" <?= ($old['class_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?></option><?php endforeach; ?></select><?php if (isset($errors['class_id'])): ?><div class="invalid-feedback"><?= $errors['class_id'] ?></div><?php endif; ?></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">بخش</label><select class="form-select" name="section_id" id="sectionSelect"><option value="">ابتدا کلاس را انتخاب کنید</option></select></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ ثبت نام <span class="text-danger">*</span></label><input type="text" class="form-control persian-datepicker" name="admission_date" value="<?= htmlspecialchars($old['admission_date'] ?? date('Y/m/d')) ?>" required></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">نوع پذیرش</label><select class="form-select" name="admission_type"><option value="new" <?= ($old['admission_type'] ?? 'new') === 'new' ? 'selected' : '' ?>>جدید</option><option value="transfer" <?= ($old['admission_type'] ?? '') === 'transfer' ? 'selected' : '' ?>>انتقالی</option><option value="re-admission" <?= ($old['admission_type'] ?? '') === 're-admission' ? 'selected' : '' ?>>پذیرش مجدد</option></select></div>
                <div class="col-md-8 mb-3"><label class="form-label fw-bold">مدرسه قبلی</label><input type="text" class="form-control" name="previous_school" value="<?= htmlspecialchars($old['previous_school'] ?? '') ?>"></div>
            </div>
        </div>
    </div>

    <!-- Medical Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="fas fa-notes-medical me-2"></i>اطلاعات پزشکی</h6></div>
        <div class="card-body">
            <div class="mb-3"><label class="form-label fw-bold">بیماری‌های خاص</label><textarea class="form-control" name="medical_conditions" rows="2"><?= htmlspecialchars($old['medical_conditions'] ?? '') ?></textarea></div>
            <div class="mb-3"><label class="form-label fw-bold">حساسیت‌ها</label><textarea class="form-control" name="allergies" rows="2"><?= htmlspecialchars($old['allergies'] ?? '') ?></textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">نام تماس اضطراری</label><input type="text" class="form-control" name="emergency_contact_name" value="<?= htmlspecialchars($old['emergency_contact_name'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">شماره تماس اضطراری</label><input type="text" class="form-control" name="emergency_contact_phone" value="<?= htmlspecialchars($old['emergency_contact_phone'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">نسبت</label><input type="text" class="form-control" name="emergency_contact_relation" value="<?= htmlspecialchars($old['emergency_contact_relation'] ?? '') ?>"></div>
            </div>
        </div>
    </div>

    <!-- Parent Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-user-friends me-2"></i>اطلاعات والدین (اختیاری)</h6></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">نام والد</label><input type="text" class="form-control" name="parent_first_name" value="<?= htmlspecialchars($old['parent_first_name'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">نام خانوادگی والد</label><input type="text" class="form-control" name="parent_last_name" value="<?= htmlspecialchars($old['parent_last_name'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">نسبت</label><select class="form-select" name="parent_relation"><option value="father" <?= ($old['parent_relation'] ?? 'father') === 'father' ? 'selected' : '' ?>>پدر</option><option value="mother" <?= ($old['parent_relation'] ?? '') === 'mother' ? 'selected' : '' ?>>مادر</option><option value="guardian" <?= ($old['parent_relation'] ?? '') === 'guardian' ? 'selected' : '' ?>>قیم</option></select></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">ایمیل والد</label><input type="email" class="form-control" name="parent_email" value="<?= htmlspecialchars($old['parent_email'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">شماره تماس والد</label><input type="text" class="form-control" name="parent_phone" value="<?= htmlspecialchars($old['parent_phone'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label fw-bold">شغل</label><input type="text" class="form-control" name="parent_occupation" value="<?= htmlspecialchars($old['parent_occupation'] ?? '') ?>"></div>
            </div>
        </div>
    </div>

    <!-- Address -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-secondary"><i class="fas fa-map-marker-alt me-2"></i>آدرس</h6></div>
        <div class="card-body"><div class="row"><div class="col-md-8 mb-3"><label class="form-label fw-bold">آدرس</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($old['address'] ?? '') ?></textarea></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">شهر</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars($old['city'] ?? '') ?>"></div></div></div>
    </div>

    <div class="d-flex justify-content-between mb-4">
        <a href="<?= url('/students') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>ثبت نام</button>
    </div>
</form>

<script>
    function loadSections(classId) { const sel = document.getElementById('sectionSelect'); sel.innerHTML = '<option value="">در حال بارگذاری...</option>'; if (classId) { fetch('<?= url('/api/sections') ?>/' + classId).then(r => r.json()).then(d => { sel.innerHTML = '<option value="">انتخاب بخش...</option>'; if (d.success && d.data) d.data.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.section_name}</option>`); }); } }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>