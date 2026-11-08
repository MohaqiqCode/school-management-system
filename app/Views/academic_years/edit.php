<?php
$title = 'ویرایش سال تحصیلی';
$academicYear = $academicYear ?? [];
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? $academicYear;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($academicYear, $branches, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش سال تحصیلی</h1>
        <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($academicYear['year_name'] ?? '') ?></p>
    </div>
    <div>
        <?php if (hasPermission('delete_academic_years')): ?>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $academicYear['id'] ?>, '<?= htmlspecialchars($academicYear['year_name']) ?>')">
            <i class="fas fa-trash me-1"></i>حذف
        </button>
        <?php endif; ?>
        <a href="<?= url('/academic-years') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>ویرایش اطلاعات</h6>
                <div>
                    <?php if ($academicYear['is_current'] ?? false): ?>
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>سال جاری</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= url('/academic-years/' . ($academicYear['id'] ?? '')) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام سال تحصیلی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['year_name']) ? 'is-invalid' : '' ?>" 
                                   name="year_name" value="<?= htmlspecialchars($old['year_name'] ?? $academicYear['year_name'] ?? '') ?>" required>
                            <?php if (isset($errors['year_name'])): ?><div class="invalid-feedback"><?= $errors['year_name'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">شعبه</label>
                            <select class="form-select" disabled>
                                <option><?= htmlspecialchars($academicYear['branch_name'] ?? $academicYear['branch_id'] ?? '') ?></option>
                            </select>
                            <small class="text-muted">شعبه قابل تغییر نیست</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاریخ شروع <span class="text-danger">*</span></label>
                            <input type="text" class="form-control persian-datepicker" 
                                   name="start_date" value="<?= htmlspecialchars($old['start_date'] ?? $academicYear['start_date'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاریخ پایان <span class="text-danger">*</span></label>
                            <input type="text" class="form-control persian-datepicker" 
                                   name="end_date" value="<?= htmlspecialchars($old['end_date'] ?? $academicYear['end_date'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">وضعیت</label>
                            <select class="form-select" name="status">
                                <option value="active" <?= ($old['status'] ?? $academicYear['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>فعال</option>
                                <option value="inactive" <?= ($old['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                                <option value="archived" <?= ($old['status'] ?? '') === 'archived' ? 'selected' : '' ?>>آرشیو شده</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_current" value="1" id="isCurrent" 
                                   <?= ($old['is_current'] ?? $academicYear['is_current'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="isCurrent">سال تحصیلی جاری باشد</label>
                        </div>
                        <small class="text-muted">با انتخاب این گزینه، سال جاری قبلی غیرفعال می‌شود.</small>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $academicYear['id'] ?>, '<?= htmlspecialchars($academicYear['year_name']) ?>')">
                            <i class="fas fa-trash me-1"></i>حذف سال تحصیلی
                        </button>
                        <div>
                            <a href="<?= url('/academic-years') ?>" class="btn btn-secondary">انصراف</a>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره تغییرات</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <!-- Stats -->
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-bar me-2"></i>آمار</h6></div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between px-0"><span>تعداد ترم‌ها</span><span class="badge bg-primary"><?= $academicYear['semester_count'] ?? 0 ?></span></div>
                    <div class="list-group-item d-flex justify-content-between px-0"><span>تعداد ارتقاء</span><span class="badge bg-info"><?= $academicYear['promotion_count'] ?? 0 ?></span></div>
                    <div class="list-group-item d-flex justify-content-between px-0"><span>مدت (روز)</span><span class="badge bg-secondary"><?= $academicYear['duration_days'] ?? 0 ?></span></div>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-secondary"><i class="fas fa-info-circle me-2"></i>اطلاعات ثبت</h6></div><div class="card-body"><table class="table table-sm mb-0"><tr><td class="text-muted">تاریخ ایجاد</td><td><?= date('Y/m/d', strtotime($academicYear['created_at'] ?? 'now')) ?></td></tr><tr><td class="text-muted">آخرین بروزرسانی</td><td><?= date('Y/m/d H:i', strtotime($academicYear['updated_at'] ?? 'now')) ?></td></tr></table></div></div>

        <!-- Quick Links -->
        <div class="card shadow">
            <div class="card-body">
                <a href="<?= url('/semesters/create?academic_year_id=' . ($academicYear['id'] ?? '')) ?>" class="btn btn-outline-success w-100 mb-2"><i class="fas fa-plus me-1"></i>افزودن ترم جدید</a>
                <a href="<?= url('/semesters?academic_year_id=' . ($academicYear['id'] ?? '')) ?>" class="btn btn-outline-info w-100"><i class="fas fa-list me-1"></i>مشاهده ترم‌ها</a>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        Swal.fire({ title: 'حذف سال تحصیلی', text: 'آیا از حذف "' + name + '" اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/academic-years') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>