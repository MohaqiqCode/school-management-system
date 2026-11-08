<?php
$title = 'ویرایش کاربر';
$user = $user ?? [];
$roles = $roles ?? [];
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? $user;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($user, $roles, $branches, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-edit me-2"></i>ویرایش کاربر</h1>
    <div>
        <a href="<?= url('/users/' . ($user['id'] ?? '')) ?>" class="btn btn-info"><i class="fas fa-eye me-1"></i>مشاهده</a>
        <a href="<?= url('/users') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center">
                <img src="<?= $user['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle me-2" width="40" height="40">
                <div><h6 class="m-0 fw-bold"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h6><small class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></small></div>
            </div>
            <div class="card-body">
                <form action="<?= url('/users/' . ($user['id'] ?? '')) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required><?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= $errors['first_name'] ?></div><?php endif; ?></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required><?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name'] ?></div><?php endif; ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">ایمیل <span class="text-danger">*</span></label><input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required><?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">رمز عبور جدید <small class="text-muted">(در صورت تغییر)</small></label><div class="input-group"><input type="password" class="form-control" name="password" id="editPassword"><button type="button" class="btn btn-outline-secondary" onclick="togglePassword('editPassword')"><i class="fas fa-eye"></i></button></div></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نقش <span class="text-danger">*</span></label><select class="form-select" name="role_id" required><option value="">انتخاب...</option><?php foreach ($roles as $role): ?><option value="<?= $role['id'] ?>" <?= ($old['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['display_name']) ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">شعبه <span class="text-danger">*</span></label><select class="form-select" name="branch_id" required><option value="">انتخاب...</option><?php foreach ($branches as $br): ?><option value="<?= $br['id'] ?>" <?= ($old['branch_id'] ?? '') == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3 mb-3"><label class="form-label fw-bold">جنسیت</label><select class="form-select" name="gender"><option value="other" <?= ($old['gender'] ?? 'other') === 'other' ? 'selected' : '' ?>>سایر</option><option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>مرد</option><option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>زن</option></select></div>
                        <div class="col-md-3 mb-3"><label class="form-label fw-bold">تاریخ تولد</label><input type="text" class="form-control persian-datepicker" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تصویر پروفایل</label>
                        <?php if (!empty($user['profile_photo'])): ?><div class="mb-2"><img src="<?= asset($user['profile_photo']) ?>" class="rounded" style="max-height:80px;"></div><?php endif; ?>
                        <input type="file" class="form-control" name="profile_photo" accept="image/*">
                    </div>
                    <div class="mb-3"><label class="form-label fw-bold">آدرس</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($old['address'] ?? '') ?></textarea></div>
                    <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? $user['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label">کاربر فعال باشد</label></div></div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>')"><i class="fas fa-trash me-1"></i>حذف</button>
                        <div><a href="<?= url('/users') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-history me-2"></i>اطلاعات ثبت</h6></div><div class="card-body"><p class="small mb-1"><span class="text-muted">تاریخ ایجاد:</span> <?= date('Y/m/d H:i', strtotime($user['created_at'] ?? 'now')) ?></p><p class="small mb-0"><span class="text-muted">آخرین بروزرسانی:</span> <?= date('Y/m/d H:i', strtotime($user['updated_at'] ?? 'now')) ?></p></div></div>
    </div>
</div>

<script>
    function togglePassword(id) { const el = document.getElementById(id); el.type = el.type === 'password' ? 'text' : 'password'; }
    function confirmDelete(id, name) { SwalDefault.fire({ title: 'حذف کاربر', text: 'آیا از حذف "' + name + '" اطمینان دارید؟', icon: 'warning' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/users') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } }); }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>