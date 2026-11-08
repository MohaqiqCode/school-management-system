<?php
$title = 'ایجاد دپارتمان جدید';
$branches = $branches ?? [];
$teachers = $teachers ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($branches, $teachers, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-sitemap me-2"></i>ایجاد دپارتمان جدید</h1>
    <a href="<?= url('/departments') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات دپارتمان</h6></div>
            <div class="card-body">
                <form action="<?= url('/departments') ?>" method="POST" id="deptForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد دپارتمان <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control <?= isset($errors['department_code']) ? 'is-invalid' : '' ?>" 
                                       name="department_code" value="<?= htmlspecialchars($old['department_code'] ?? '') ?>" 
                                       placeholder="مثال: MATH" required dir="ltr">
                                <?php if (isset($errors['department_code'])): ?><div class="invalid-feedback"><?= $errors['department_code'] ?></div><?php endif; ?>
                            </div>
                            <small class="text-muted">فقط حروف انگلیسی و اعداد</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نام دپارتمان <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" 
                                   placeholder="مثال: گروه ریاضی" required>
                            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">مدیر دپارتمان</label>
                            <select class="form-select" name="head_teacher_id">
                                <option value="">انتخاب مدیر...</option>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($old['head_teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="توضیحات اختیاری..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">دپارتمان فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/departments') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره دپارتمان</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">کد دپارتمان باید یکتا باشد و در کل سیستم استفاده می‌شود.</li><li class="mb-2">مدیر دپارتمان می‌تواند بعداً نیز تعیین شود.</li><li>پس از ایجاد، می‌توانید معلمان و دروس را به این دپارتمان اختصاص دهید.</li></ul></div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>