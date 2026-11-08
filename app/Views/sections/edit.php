<?php
$title = 'ویرایش بخش';
$section = $section ?? [];
$classes = $classes ?? [];
$teachers = $teachers ?? [];
$old = $_SESSION['old'] ?? $section;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($section, $classes, $teachers, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش بخش</h1>
        <p class="text-muted mb-0 mt-1">
            بخش <?= htmlspecialchars($section['section_name'] ?? '') ?> | 
            <?= htmlspecialchars($section['class_name'] ?? '') ?>
        </p>
    </div>
    <div>
        <?php if (hasPermission('delete_sections')): ?>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $section['id'] ?>, 'بخش <?= htmlspecialchars($section['section_name']) ?>')">
            <i class="fas fa-trash me-1"></i>حذف
        </button>
        <?php endif; ?>
        <a href="<?= url('/sections') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>ویرایش اطلاعات</h6></div>
            <div class="card-body">
                <form action="<?= url('/sections/' . ($section['id'] ?? '')) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کلاس</label>
                            <select class="form-select" disabled>
                                <option><?= htmlspecialchars($section['class_name'] ?? $section['class_id'] ?? '') ?></option>
                            </select>
                            <small class="text-muted">کلاس قابل تغییر نیست</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد بخش</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($section['section_code'] ?? '') ?>" disabled>
                            <small class="text-muted">کد بخش قابل تغییر نیست</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">نام بخش <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="section_name" 
                                   value="<?= htmlspecialchars($old['section_name'] ?? $section['section_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">ظرفیت</label>
                            <input type="number" class="form-control" name="capacity" 
                                   value="<?= htmlspecialchars($old['capacity'] ?? $section['capacity'] ?? '40') ?>" min="1">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">شماره کلاس درس</label>
                            <input type="text" class="form-control" name="room_number" 
                                   value="<?= htmlspecialchars($old['room_number'] ?? $section['room_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">معلم راهنما</label>
                        <select class="form-select" name="class_teacher_id">
                            <option value="">انتخاب معلم راهنما...</option>
                            <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($old['class_teacher_id'] ?? $section['class_teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? $section['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">بخش فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $section['id'] ?>, 'بخش <?= htmlspecialchars($section['section_name']) ?>')">
                            <i class="fas fa-trash me-1"></i>حذف بخش
                        </button>
                        <div>
                            <a href="<?= url('/sections') ?>" class="btn btn-secondary">انصراف</a>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-bar me-2"></i>آمار بخش</h6></div><div class="card-body"><div class="list-group list-group-flush"><div class="list-group-item d-flex justify-content-between px-0"><span>دانش‌آموزان</span><span class="badge bg-success"><?= $section['student_count'] ?? 0 ?></span></div><div class="list-group-item d-flex justify-content-between px-0"><span>نرخ تکمیل</span><span class="badge bg-<?= ($section['occupancy_rate'] ?? 0) > 80 ? 'danger' : 'success' ?>"><?= $section['occupancy_rate'] ?? 0 ?>%</span></div></div></div></div>
        <div class="card shadow"><div class="card-body"><a href="<?= url('/students?section_id=' . ($section['id'] ?? '')) ?>" class="btn btn-outline-info w-100 mb-2"><i class="fas fa-users me-1"></i>مشاهده دانش‌آموزان</a><a href="<?= url('/timetable?section_id=' . ($section['id'] ?? '')) ?>" class="btn btn-outline-primary w-100"><i class="fas fa-calendar-alt me-1"></i>برنامه هفتگی</a></div></div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        Swal.fire({ title: 'حذف بخش', text: 'آیا از حذف ' + name + ' اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/sections') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>