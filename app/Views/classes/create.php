<?php
$title = 'ایجاد کلاس جدید';
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$gradeLevels = [
    1 => 'پایه اول', 2 => 'پایه دوم', 3 => 'پایه سوم',
    4 => 'پایه چهارم', 5 => 'پایه پنجم', 6 => 'پایه ششم',
    7 => 'پایه هفتم', 8 => 'پایه هشتم', 9 => 'پایه نهم',
    10 => 'پایه دهم', 11 => 'پایه یازدهم', 12 => 'پایه دوازدهم',
];

$content = function() use ($branches, $gradeLevels, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle me-2"></i>ایجاد کلاس جدید</h1>
    <a href="<?= url('/classes') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات کلاس</h6></div>
            <div class="card-body">
                <form action="<?= url('/classes') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد کلاس <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control <?= isset($errors['class_code']) ? 'is-invalid' : '' ?>" 
                                       name="class_code" value="<?= htmlspecialchars($old['class_code'] ?? '') ?>" 
                                       placeholder="مثال: GRADE-10-A" required dir="ltr">
                                <?php if (isset($errors['class_code'])): ?><div class="invalid-feedback"><?= $errors['class_code'] ?></div><?php endif; ?>
                            </div>
                            <small class="text-muted">کد یکتا برای کلاس</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">پایه تحصیلی <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['class_level']) ? 'is-invalid' : '' ?>" name="class_level" required>
                                <option value="">انتخاب پایه...</option>
                                <?php foreach ($gradeLevels as $level => $label): ?>
                                <option value="<?= $level ?>" <?= ($old['class_level'] ?? '') == $level ? 'selected' : '' ?>>
                                    <?= $label ?> (پایه <?= $level ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['class_level'])): ?><div class="invalid-feedback"><?= $errors['class_level'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">نام کلاس <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['class_name']) ? 'is-invalid' : '' ?>" 
                                   name="class_name" value="<?= htmlspecialchars($old['class_name'] ?? '') ?>" 
                                   placeholder="مثال: دهم ریاضی" required>
                            <?php if (isset($errors['class_name'])): ?><div class="invalid-feedback"><?= $errors['class_name'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">ظرفیت</label>
                            <input type="number" class="form-control" name="capacity" 
                                   value="<?= htmlspecialchars($old['capacity'] ?? '40') ?>" min="1" max="500">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">شعبه <span class="text-danger">*</span></label>
                        <select class="form-select <?= isset($errors['branch_id']) ? 'is-invalid' : '' ?>" name="branch_id" required>
                            <option value="">انتخاب شعبه...</option>
                            <?php foreach ($branches as $br): ?>
                            <option value="<?= $br['id'] ?>" <?= ($old['branch_id'] ?? '') == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['branch_id'])): ?><div class="invalid-feedback"><?= $errors['branch_id'] ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="توضیحات اختیاری..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">کلاس فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/classes') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره کلاس</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">کد کلاس باید یکتا باشد و در سیستم استفاده می‌شود.</li><li class="mb-2">هر کلاس می‌تواند چندین بخش (Section) داشته باشد.</li><li>پس از ایجاد کلاس، بخش‌ها و دروس را به آن اختصاص دهید.</li></ul></div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>