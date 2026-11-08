<?php
$title = 'ویرایش ترم';
$semester = $semester ?? [];
$academicYears = $academicYears ?? [];
$old = $_SESSION['old'] ?? $semester;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($semester, $academicYears, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش ترم</h1>
        <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($semester['semester_name'] ?? '') ?></p>
    </div>
    <div>
        <?php if (hasPermission('delete_semesters')): ?>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $semester['id'] ?>, '<?= htmlspecialchars($semester['semester_name']) ?>')">
            <i class="fas fa-trash me-1"></i>حذف
        </button>
        <?php endif; ?>
        <a href="<?= url('/semesters') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>ویرایش اطلاعات</h6>
                <?php if ($semester['is_current'] ?? false): ?>
                <span class="badge bg-success"><i class="fas fa-star me-1"></i>ترم جاری</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="<?= url('/semesters/' . ($semester['id'] ?? '')) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">سال تحصیلی</label>
                            <select class="form-select" disabled>
                                <option><?= htmlspecialchars($semester['year_name'] ?? $semester['academic_year_id'] ?? '') ?></option>
                            </select>
                            <small class="text-muted">سال تحصیلی قابل تغییر نیست</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">شماره ترم <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="semester_number" 
                                   value="<?= htmlspecialchars($old['semester_number'] ?? $semester['semester_number'] ?? '1') ?>" 
                                   min="1" max="4" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نام ترم <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="semester_name" 
                               value="<?= htmlspecialchars($old['semester_name'] ?? $semester['semester_name'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاریخ شروع <span class="text-danger">*</span></label>
                            <input type="text" class="form-control persian-datepicker" name="start_date" 
                                   value="<?= htmlspecialchars($old['start_date'] ?? $semester['start_date'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاریخ پایان <span class="text-danger">*</span></label>
                            <input type="text" class="form-control persian-datepicker" name="end_date" 
                                   value="<?= htmlspecialchars($old['end_date'] ?? $semester['end_date'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_current" value="1" id="isCurrent" 
                                   <?= ($old['is_current'] ?? $semester['is_current'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="isCurrent">ترم جاری باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $semester['id'] ?>, '<?= htmlspecialchars($semester['semester_name']) ?>')">
                            <i class="fas fa-trash me-1"></i>حذف ترم
                        </button>
                        <div>
                            <a href="<?= url('/semesters') ?>" class="btn btn-secondary">انصراف</a>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-bar me-2"></i>آمار ترم</h6></div><div class="card-body"><div class="list-group list-group-flush"><div class="list-group-item d-flex justify-content-between px-0"><span>برنامه‌های هفتگی</span><span class="badge bg-primary"><?= $semester['timetable_count'] ?? 0 ?></span></div><div class="list-group-item d-flex justify-content-between px-0"><span>آزمون‌ها</span><span class="badge bg-warning"><?= $semester['exam_count'] ?? 0 ?></span></div><div class="list-group-item d-flex justify-content-between px-0"><span>مدت (روز)</span><span class="badge bg-secondary"><?= $semester['duration_days'] ?? 0 ?></span></div></div></div></div>
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-secondary"><i class="fas fa-info-circle me-2"></i>اطلاعات ثبت</h6></div><div class="card-body"><table class="table table-sm mb-0"><tr><td class="text-muted">تاریخ ایجاد</td><td><?= date('Y/m/d', strtotime($semester['created_at'] ?? 'now')) ?></td></tr><tr><td class="text-muted">آخرین بروزرسانی</td><td><?= date('Y/m/d H:i', strtotime($semester['updated_at'] ?? 'now')) ?></td></tr></table></div></div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        Swal.fire({ title: 'حذف ترم', text: 'آیا از حذف "' + name + '" اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/semesters') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>