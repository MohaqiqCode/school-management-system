<?php
$title = 'ایجاد کاربر جدید';
$roles = $roles ?? [];
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($roles, $branches, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus me-2"></i>ایجاد کاربر جدید</h1>
    <a href="<?= url('/users') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات کاربر</h6></div>
            <div class="card-body">
                <form action="<?= url('/users') ?>" method="POST" enctype="multipart/form-data" id="userForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
                            <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= $errors['first_name'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام خانوادگی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
                            <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name'] ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ایمیل <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                            <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">شماره تماس</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">رمز عبور <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" name="password" id="password" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')"><i class="fas fa-eye"></i></button>
                            </div>
                            <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نقش <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" name="role_id" required>
                                <option value="">انتخاب نقش...</option>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= ($old['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['display_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['role_id'])): ?><div class="invalid-feedback"><?= $errors['role_id'] ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">شعبه <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['branch_id']) ? 'is-invalid' : '' ?>" name="branch_id" required>
                                <option value="">انتخاب شعبه...</option>
                                <?php foreach ($branches as $br): ?>
                                <option value="<?= $br['id'] ?>" <?= ($old['branch_id'] ?? '') == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['branch_id'])): ?><div class="invalid-feedback"><?= $errors['branch_id'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">جنسیت</label>
                            <select class="form-select" name="gender">
                                <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>سایر</option>
                                <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>مرد</option>
                                <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>زن</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">تاریخ تولد</label>
                            <input type="text" class="form-control persian-datepicker" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>" placeholder="انتخاب تاریخ">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تصویر پروفایل</label>
                        <input type="file" class="form-control" name="profile_photo" accept="image/*" onchange="previewImage(this)">
                        <div id="photoPreview" class="mt-2 d-none"><img src="#" class="rounded" style="max-height:100px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">آدرس</label>
                        <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">شهر</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars($old['city'] ?? '') ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">کد پستی</label><input type="text" class="form-control" name="postal_code" value="<?= htmlspecialchars($old['postal_code'] ?? '') ?>"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? '1') == '1' ? 'checked' : '' ?>><label class="form-check-label">کاربر فعال باشد</label></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="email_verified" value="1" <?= isset($old['email_verified']) ? 'checked' : '' ?>><label class="form-check-label">ایمیل تأیید شده</label></div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/users') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره کاربر</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">فیلدهای ستاره‌دار الزامی هستند.</li><li class="mb-2">رمز عبور باید حداقل ۸ کاراکتر باشد.</li><li>پس از ایجاد کاربر، اطلاعات ورود به ایمیل کاربر ارسال می‌شود.</li></ul></div></div>
    </div>
</div>

<script>
    function togglePassword(id) { const el = document.getElementById(id); el.type = el.type === 'password' ? 'text' : 'password'; }
    function previewImage(input) { if (input.files?.[0]) { const r = new FileReader(); r.onload = e => { const p = document.getElementById('photoPreview'); p.classList.remove('d-none'); p.querySelector('img').src = e.target.result; }; r.readAsDataURL(input.files[0]); } }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>