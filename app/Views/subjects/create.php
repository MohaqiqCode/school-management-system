<?php
$title = 'ایجاد درس جدید';
$departments = $departments ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($departments, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle me-2"></i>ایجاد درس جدید</h1>
    <a href="<?= url('/subjects') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات درس</h6></div>
            <div class="card-body">
                <form action="<?= url('/subjects') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد درس <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control <?= isset($errors['subject_code']) ? 'is-invalid' : '' ?>" 
                                       name="subject_code" value="<?= htmlspecialchars($old['subject_code'] ?? '') ?>" 
                                       placeholder="مثال: MATH-101" required dir="ltr">
                                <?php if (isset($errors['subject_code'])): ?><div class="invalid-feedback"><?= $errors['subject_code'] ?></div><?php endif; ?>
                            </div>
                            <small class="text-muted">کد یکتا برای درس</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام درس <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['subject_name']) ? 'is-invalid' : '' ?>" 
                                   name="subject_name" value="<?= htmlspecialchars($old['subject_name'] ?? '') ?>" 
                                   placeholder="مثال: ریاضیات پایه" required>
                            <?php if (isset($errors['subject_name'])): ?><div class="invalid-feedback"><?= $errors['subject_name'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">دپارتمان <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['department_id']) ? 'is-invalid' : '' ?>" name="department_id" required>
                                <option value="">انتخاب دپارتمان...</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= ($old['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['department_id'])): ?><div class="invalid-feedback"><?= $errors['department_id'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">تعداد واحد</label>
                            <input type="number" class="form-control" name="credit_hours" 
                                   value="<?= htmlspecialchars($old['credit_hours'] ?? '1') ?>" 
                                   min="0.5" max="20" step="0.5">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_core" value="1" <?= ($old['is_core'] ?? '1') == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold">درس اصلی</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="توضیحات اختیاری درباره این درس..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">درس فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/subjects') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره درس</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">کد درس باید یکتا باشد و معمولاً شامل حروف اختصاری دپارتمان و شماره است.</li><li class="mb-2">درس‌های اصلی برای همه دانش‌آموزان آن پایه اجباری هستند.</li><li>پس از ایجاد درس، می‌توانید آن را به کلاس‌ها اختصاص دهید.</li></ul></div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>