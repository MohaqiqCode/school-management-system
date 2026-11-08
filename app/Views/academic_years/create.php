<?php
$title = 'ایجاد سال تحصیلی جدید';
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($branches, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-plus me-2"></i>ایجاد سال تحصیلی جدید</h1>
    <a href="<?= url('/academic-years') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات سال تحصیلی</h6></div>
            <div class="card-body">
                <form action="<?= url('/academic-years') ?>" method="POST" id="yearForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام سال تحصیلی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['year_name']) ? 'is-invalid' : '' ?>" 
                                   name="year_name" value="<?= htmlspecialchars($old['year_name'] ?? '') ?>" 
                                   placeholder="مثال: ۱۴۰۴-۱۴۰۳" required>
                            <?php if (isset($errors['year_name'])): ?><div class="invalid-feedback"><?= $errors['year_name'] ?></div><?php endif; ?>
                            <small class="text-muted">یک نام توصیفی برای سال تحصیلی وارد کنید</small>
                        </div>
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاریخ شروع <span class="text-danger">*</span></label>
                            <input type="text" class="form-control persian-datepicker <?= isset($errors['start_date']) ? 'is-invalid' : '' ?>" 
                                   name="start_date" value="<?= htmlspecialchars($old['start_date'] ?? '') ?>" 
                                   placeholder="انتخاب تاریخ شروع" required>
                            <?php if (isset($errors['start_date'])): ?><div class="invalid-feedback"><?= $errors['start_date'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاریخ پایان <span class="text-danger">*</span></label>
                            <input type="text" class="form-control persian-datepicker <?= isset($errors['end_date']) ? 'is-invalid' : '' ?>" 
                                   name="end_date" value="<?= htmlspecialchars($old['end_date'] ?? '') ?>" 
                                   placeholder="انتخاب تاریخ پایان" required>
                            <?php if (isset($errors['end_date'])): ?><div class="invalid-feedback"><?= $errors['end_date'] ?></div><?php endif; ?>
                            <small class="text-muted">تاریخ پایان باید بعد از تاریخ شروع باشد</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">وضعیت</label>
                            <select class="form-select" name="status">
                                <option value="active" <?= ($old['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>فعال</option>
                                <option value="inactive" <?= ($old['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_current" value="1" id="isCurrent" 
                                   <?= ($old['is_current'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="isCurrent">سال تحصیلی جاری باشد</label>
                        </div>
                        <small class="text-muted">در هر زمان فقط یک سال می‌تواند جاری باشد. با انتخاب این گزینه، سال جاری قبلی غیرفعال می‌شود.</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_create_semesters" value="1" id="autoSemesters" 
                                   <?= ($old['auto_create_semesters'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="autoSemesters">ایجاد خودکار ترم‌ها</label>
                        </div>
                        <small class="text-muted">دو ترم (نیمسال اول و دوم) به صورت خودکار ایجاد می‌شوند.</small>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/academic-years') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره سال تحصیلی</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">نام سال تحصیلی می‌تواند به صورت "۱۴۰۴-۱۴۰۳" باشد.</li><li class="mb-2">با فعال کردن "ایجاد خودکار ترم‌ها"، دو ترم پیش‌فرض ساخته می‌شود.</li><li class="mb-2">تاریخ شروع و پایان باید معتبر باشند.</li><li>سال جاری در لیست‌ها و گزارش‌ها به عنوان پیش‌فرض استفاده می‌شود.</li></ul></div></div>
        
        <div class="card shadow">
            <div class="card-body text-center">
                <i class="fas fa-info-circle text-info fa-2x mb-2"></i>
                <p class="small text-muted mb-0">پس از ایجاد سال تحصیلی، می‌توانید ترم‌ها، کلاس‌ها و برنامه‌های هفتگی را برای آن تعریف کنید.</p>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.persian-datepicker').persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false
        });
        
        // Validate end date > start date
        $('form').on('submit', function(e) {
            const start = $('input[name="start_date"]').val();
            const end = $('input[name="end_date"]').val();
            if (start && end) {
                const startDate = new Date(start.replace(/\//g, '-'));
                const endDate = new Date(end.replace(/\//g, '-'));
                if (endDate <= startDate) {
                    e.preventDefault();
                    toastr.error('تاریخ پایان باید بعد از تاریخ شروع باشد');
                }
            }
        });
    });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>