<?php
$title = 'ویرایش درس';
$subject = $subject ?? [];
$departments = $departments ?? [];
$old = $_SESSION['old'] ?? $subject;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($subject, $departments, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش درس</h1>
        <p class="text-muted mb-0 mt-1">
            <?= htmlspecialchars($subject['subject_name'] ?? '') ?> 
            <code>(<?= htmlspecialchars($subject['subject_code'] ?? '') ?>)</code>
        </p>
    </div>
    <div>
        <?php if (hasPermission('delete_subjects')): ?>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $subject['id'] ?>, '<?= htmlspecialchars($subject['subject_name']) ?>')">
            <i class="fas fa-trash me-1"></i>حذف
        </button>
        <?php endif; ?>
        <a href="<?= url('/subjects') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>ویرایش اطلاعات</h6>
            </div>
            <div class="card-body">
                <form action="<?= url('/subjects/' . ($subject['id'] ?? '')) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد درس</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($subject['subject_code'] ?? '') ?>" disabled>
                            <small class="text-muted">کد درس قابل تغییر نیست</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام درس <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['subject_name']) ? 'is-invalid' : '' ?>" 
                                   name="subject_name" value="<?= htmlspecialchars($old['subject_name'] ?? $subject['subject_name'] ?? '') ?>" required>
                            <?php if (isset($errors['subject_name'])): ?><div class="invalid-feedback"><?= $errors['subject_name'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">دپارتمان <span class="text-danger">*</span></label>
                            <select class="form-select" name="department_id" required>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= ($old['department_id'] ?? $subject['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">تعداد واحد</label>
                            <input type="number" class="form-control" name="credit_hours" 
                                   value="<?= htmlspecialchars($old['credit_hours'] ?? $subject['credit_hours'] ?? '1') ?>" 
                                   min="0.5" max="20" step="0.5">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_core" value="1" <?= ($old['is_core'] ?? $subject['is_core'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold">درس اصلی</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? $subject['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? $subject['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">درس فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $subject['id'] ?>, '<?= htmlspecialchars($subject['subject_name']) ?>')">
                            <i class="fas fa-trash me-1"></i>حذف درس
                        </button>
                        <div>
                            <a href="<?= url('/subjects') ?>" class="btn btn-secondary">انصراف</a>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-bar me-2"></i>آمار درس</h6></div><div class="card-body"><div class="list-group list-group-flush"><div class="list-group-item d-flex justify-content-between px-0"><span>کلاس‌های فعال</span><span class="badge bg-primary"><?= $subject['class_count'] ?? 0 ?></span></div><div class="list-group-item d-flex justify-content-between px-0"><span>نوع درس</span><span class="badge bg-<?= ($subject['is_core'] ?? true) ? 'primary' : 'info' ?>"><?= ($subject['is_core'] ?? true) ? 'اصلی' : 'اختیاری' ?></span></div><div class="list-group-item d-flex justify-content-between px-0"><span>تعداد واحد</span><span class="badge bg-secondary"><?= $subject['credit_hours'] ?? 1 ?></span></div></div></div></div>
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-secondary"><i class="fas fa-info-circle me-2"></i>اطلاعات ثبت</h6></div><div class="card-body"><table class="table table-sm mb-0"><tr><td class="text-muted">تاریخ ایجاد</td><td><?= date('Y/m/d', strtotime($subject['created_at'] ?? 'now')) ?></td></tr><tr><td class="text-muted">آخرین بروزرسانی</td><td><?= date('Y/m/d H:i', strtotime($subject['updated_at'] ?? 'now')) ?></td></tr></table></div></div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        Swal.fire({ title: 'حذف درس', text: 'آیا از حذف "' + name + '" اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/subjects') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>