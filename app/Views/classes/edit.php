<?php
$title = 'ویرایش کلاس';
$class = $class ?? [];
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? $class;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$gradeLevels = [
    1 => 'پایه اول', 2 => 'پایه دوم', 3 => 'پایه سوم',
    4 => 'پایه چهارم', 5 => 'پایه پنجم', 6 => 'پایه ششم',
    7 => 'پایه هفتم', 8 => 'پایه هشتم', 9 => 'پایه نهم',
    10 => 'پایه دهم', 11 => 'پایه یازدهم', 12 => 'پایه دوازدهم',
];

$content = function() use ($class, $branches, $gradeLevels, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش کلاس</h1>
        <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($class['class_name'] ?? '') ?> <code>(<?= htmlspecialchars($class['class_code'] ?? '') ?>)</code></p>
    </div>
    <div>
        <?php if (hasPermission('delete_classes')): ?>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $class['id'] ?>, '<?= htmlspecialchars($class['class_name']) ?>')">
            <i class="fas fa-trash me-1"></i>حذف
        </button>
        <?php endif; ?>
        <a href="<?= url('/classes') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>ویرایش اطلاعات</h6></div>
            <div class="card-body">
                <form action="<?= url('/classes/' . ($class['id'] ?? '')) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد کلاس</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($class['class_code'] ?? '') ?>" disabled>
                            <small class="text-muted">کد کلاس قابل تغییر نیست</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">پایه تحصیلی <span class="text-danger">*</span></label>
                            <select class="form-select" name="class_level" required>
                                <?php foreach ($gradeLevels as $level => $label): ?>
                                <option value="<?= $level ?>" <?= ($old['class_level'] ?? $class['class_level'] ?? '') == $level ? 'selected' : '' ?>>
                                    <?= $label ?> (پایه <?= $level ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">نام کلاس <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['class_name']) ? 'is-invalid' : '' ?>" 
                                   name="class_name" value="<?= htmlspecialchars($old['class_name'] ?? $class['class_name'] ?? '') ?>" required>
                            <?php if (isset($errors['class_name'])): ?><div class="invalid-feedback"><?= $errors['class_name'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">ظرفیت</label>
                            <input type="number" class="form-control" name="capacity" 
                                   value="<?= htmlspecialchars($old['capacity'] ?? $class['capacity'] ?? '40') ?>" min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">شعبه</label>
                        <select class="form-select" disabled>
                            <option><?= htmlspecialchars($class['branch_name'] ?? $class['branch_id'] ?? '') ?></option>
                        </select>
                        <small class="text-muted">شعبه قابل تغییر نیست</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($old['description'] ?? $class['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? $class['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">کلاس فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $class['id'] ?>, '<?= htmlspecialchars($class['class_name']) ?>')">
                            <i class="fas fa-trash me-1"></i>حذف کلاس
                        </button>
                        <div>
                            <a href="<?= url('/classes') ?>" class="btn btn-secondary">انصراف</a>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-bar me-2"></i>آمار کلاس</h6></div><div class="card-body"><div class="list-group list-group-flush"><div class="list-group-item d-flex justify-content-between px-0"><span>بخش‌ها</span><span class="badge bg-info"><?= $class['section_count'] ?? 0 ?></span></div><div class="list-group-item d-flex justify-content-between px-0"><span>دانش‌آموزان</span><span class="badge bg-success"><?= $class['student_count'] ?? 0 ?></span></div><div class="list-group-item d-flex justify-content-between px-0"><span>نرخ تکمیل</span><span class="badge bg-<?= ($class['occupancy_rate'] ?? 0) > 80 ? 'danger' : 'success' ?>"><?= $class['occupancy_rate'] ?? 0 ?>%</span></div></div></div></div>
        <div class="card shadow"><div class="card-body"><a href="<?= url('/sections/create?class_id=' . ($class['id'] ?? '')) ?>" class="btn btn-outline-success w-100 mb-2"><i class="fas fa-plus me-1"></i>افزودن بخش جدید</a><a href="<?= url('/sections?class_id=' . ($class['id'] ?? '')) ?>" class="btn btn-outline-info w-100"><i class="fas fa-layer-group me-1"></i>مشاهده بخش‌ها</a></div></div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        Swal.fire({ title: 'حذف کلاس', text: 'آیا از حذف "' + name + '" اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/classes') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>