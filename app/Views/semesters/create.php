<?php
$title = 'ایجاد ترم جدید';
$academicYears = $academicYears ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($academicYears, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-plus me-2"></i>ایجاد ترم جدید</h1>
    <a href="<?= url('/semesters') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات ترم</h6></div>
            <div class="card-body">
                <form action="<?= url('/semesters') ?>" method="POST" id="semesterForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">سال تحصیلی <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['academic_year_id']) ? 'is-invalid' : '' ?>" name="academic_year_id" required>
                                <option value="">انتخاب سال تحصیلی...</option>
                                <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= ($old['academic_year_id'] ?? '') == $ay['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ay['year_name']) ?> <?= $ay['is_current'] ? '(جاری)' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['academic_year_id'])): ?><div class="invalid-feedback"><?= $errors['academic_year_id'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">شماره ترم <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['semester_number']) ? 'is-invalid' : '' ?>" 
                                   name="semester_number" value="<?= htmlspecialchars($old['semester_number'] ?? '1') ?>" 
                                   min="1" max="4" required>
                            <?php if (isset($errors['semester_number'])): ?><div class="invalid-feedback"><?= $errors['semester_number'] ?></div><?php endif; ?>
                            <small class="text-muted">معمولاً ۱ برای نیمسال اول و ۲ برای نیمسال دوم</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نام ترم <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errors['semester_name']) ? 'is-invalid' : '' ?>" 
                               name="semester_name" value="<?= htmlspecialchars($old['semester_name'] ?? '') ?>" 
                               placeholder="مثال: نیمسال اول ۱۴۰۴-۱۴۰۳" required>
                        <?php if (isset($errors['semester_name'])): ?><div class="invalid-feedback"><?= $errors['semester_name'] ?></div><?php endif; ?>
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
                        </div>
                    </div>

                    <!-- Calculated Duration -->
                    <div class="alert alert-info small mb-3" id="durationInfo" style="display:none;">
                        <i class="fas fa-clock me-1"></i>
                        مدت ترم: <strong id="durationDays">0</strong> روز
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_current" value="1" id="isCurrent" 
                                   <?= ($old['is_current'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="isCurrent">ترم جاری باشد</label>
                        </div>
                        <small class="text-muted">در هر سال تحصیلی فقط یک ترم می‌تواند جاری باشد.</small>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/semesters') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره ترم</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">تاریخ‌های ترم باید در بازه سال تحصیلی انتخاب شده باشند.</li><li class="mb-2">شماره ترم برای ترتیب نمایش استفاده می‌شود.</li><li>پس از ایجاد ترم، می‌توانید برنامه هفتگی و آزمون‌ها را برای آن تعریف کنید.</li></ul></div></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true, initialValue: false });
        
        // Calculate duration
        $('input[name="start_date"], input[name="end_date"]').on('change', function() {
            const start = $('input[name="start_date"]').val();
            const end = $('input[name="end_date"]').val();
            if (start && end) {
                const s = new Date(start.replace(/\//g, '-'));
                const e = new Date(end.replace(/\//g, '-'));
                const days = Math.ceil((e - s) / 86400000);
                if (days > 0) {
                    $('#durationDays').text(days);
                    $('#durationInfo').show();
                } else {
                    $('#durationInfo').hide();
                }
            }
        });
        
        $('form').on('submit', function(e) {
            const start = $('input[name="start_date"]').val();
            const end = $('input[name="end_date"]').val();
            if (start && end) {
                const s = new Date(start.replace(/\//g, '-'));
                const e = new Date(end.replace(/\//g, '-'));
                if (e <= s) {
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