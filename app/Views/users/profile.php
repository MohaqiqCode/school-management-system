<?php
$title = 'پروفایل من';
$user = $user ?? [];
$additionalDetails = $additionalDetails ?? [];
$old = $_SESSION['old'] ?? $user;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($user, $additionalDetails, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-id-card me-2"></i>پروفایل من</h1>
    <a href="<?= url('/change-password') ?>" class="btn btn-warning"><i class="fas fa-key me-1"></i>تغییر رمز عبور</a>
</div>

<div class="row">
    <!-- Avatar & Basic Info -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow text-center">
            <div class="card-body pt-5">
                <div class="position-relative d-inline-block">
                    <img src="<?= $user['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle shadow mb-3" width="120" height="120" id="profileAvatar">
                    <label for="avatarUpload" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2" style="cursor:pointer; width:35px; height:35px;"><i class="fas fa-camera"></i></label>
                </div>
                <h4 class="fw-bold"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h4>
                <span class="badge bg-primary fs-6"><?= htmlspecialchars($user['role_display'] ?? $user['role_name'] ?? '') ?></span>
                <p class="text-muted mt-2"><?= htmlspecialchars($user['branch_name'] ?? '') ?></p>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-edit me-2"></i>ویرایش اطلاعات شخصی</h6></div>
            <div class="card-body">
                <form action="<?= url('/profile') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="file" id="avatarUpload" name="profile_photo" accept="image/*" class="d-none" onchange="updateAvatar(this)">

                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? $user['first_name'] ?? '') ?>" required><?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= $errors['first_name'] ?></div><?php endif; ?></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? $user['last_name'] ?? '') ?>" required><?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name'] ?></div><?php endif; ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">ایمیل</label><input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled><small class="text-muted">برای تغییر ایمیل با مدیر سیستم تماس بگیرید</small></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">شماره تماس <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" name="phone" value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>" required><?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone'] ?></div><?php endif; ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">جنسیت</label><select class="form-select" name="gender"><option value="other" <?= ($user['gender'] ?? 'other') === 'other' ? 'selected' : '' ?>>سایر</option><option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>مرد</option><option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>زن</option></select></div>
                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ تولد</label><input type="text" class="form-control persian-datepicker" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth'] ?? $user['date_of_birth'] ?? '') ?>"></div>
                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">شهر</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars($old['city'] ?? $user['city'] ?? '') ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-bold">آدرس</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($old['address'] ?? $user['address'] ?? '') ?></textarea></div>
                    <hr>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>ذخیره تغییرات</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateAvatar(input) { if (input.files?.[0]) { const r = new FileReader(); r.onload = e => document.getElementById('profileAvatar').src = e.target.result; r.readAsDataURL(input.files[0]); } }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>