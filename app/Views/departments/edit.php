<?php
$title = 'ویرایش دپارتمان';
$department = $department ?? [];
$branches = $branches ?? [];
$teachers = $teachers ?? [];
$old = $_SESSION['old'] ?? $department;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($department, $branches, $teachers, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش دپارتمان</h1>
        <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($department['name'] ?? '') ?> <code>(<?= htmlspecialchars($department['department_code'] ?? '') ?>)</code></p>
    </div>
    <div>
        <?php if (hasPermission('delete_departments')): ?>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $department['id'] ?>, '<?= htmlspecialchars($department['name']) ?>')"><i class="fas fa-trash me-1"></i>حذف</button>
        <?php endif; ?>
        <a href="<?= url('/departments') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center">
                <i class="fas fa-sitemap fa-lg text-primary me-2"></i>
                <h6 class="m-0 fw-bold">ویرایش اطلاعات</h6>
            </div>
            <div class="card-body">
                <form action="<?= url('/departments/' . ($department['id'] ?? '')) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد دپارتمان</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($department['department_code'] ?? '') ?>" disabled>
                            <small class="text-muted">کد دپارتمان قابل تغییر نیست</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام دپارتمان <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   name="name" value="<?= htmlspecialchars($old['name'] ?? $department['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">شعبه</label>
                            <select class="form-select" disabled>
                                <option><?= htmlspecialchars($department['branch_name'] ?? $department['branch_id'] ?? '') ?></option>
                            </select>
                            <small class="text-muted">شعبه قابل تغییر نیست</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">مدیر دپارتمان</label>
                            <select class="form-select" name="head_teacher_id">
                                <option value="">بدون مدیر</option>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($old['head_teacher_id'] ?? $department['head_teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? $department['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? $department['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">دپارتمان فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $department['id'] ?>, '<?= htmlspecialchars($department['name']) ?>')"><i class="fas fa-trash me-1"></i>حذف دپارتمان</button>
                        <div>
                            <a href="<?= url('/departments') ?>" class="btn btn-secondary">انصراف</a>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-info-circle me-2"></i>اطلاعات</h6></div><div class="card-body"><table class="table table-sm mb-0"><tr><td class="text-muted">تاریخ ایجاد</td><td><?= date('Y/m/d', strtotime($department['created_at'] ?? 'now')) ?></td></tr><tr><td class="text-muted">آخرین بروزرسانی</td><td><?= date('Y/m/d H:i', strtotime($department['updated_at'] ?? 'now')) ?></td></tr></table></div></div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        Swal.fire({ title: 'حذف دپارتمان', text: 'آیا از حذف "' + name + '" اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/departments') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>