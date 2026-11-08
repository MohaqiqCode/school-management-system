<?php
$title = 'ایجاد بخش جدید';
$classes = $classes ?? [];
$teachers = $teachers ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($classes, $teachers, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle me-2"></i>ایجاد بخش جدید</h1>
    <a href="<?= url('/sections') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات بخش</h6></div>
            <div class="card-body">
                <form action="<?= url('/sections') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کلاس <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['class_id']) ? 'is-invalid' : '' ?>" name="class_id" id="classSelect" required>
                                <option value="">انتخاب کلاس...</option>
                                <?php foreach ($classes as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= ($old['class_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['class_name']) ?> (پایه <?= $cl['class_level'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['class_id'])): ?><div class="invalid-feedback"><?= $errors['class_id'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کد بخش <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control <?= isset($errors['section_code']) ? 'is-invalid' : '' ?>" 
                                       name="section_code" value="<?= htmlspecialchars($old['section_code'] ?? '') ?>" 
                                       placeholder="مثال: A" required dir="ltr">
                                <?php if (isset($errors['section_code'])): ?><div class="invalid-feedback"><?= $errors['section_code'] ?></div><?php endif; ?>
                            </div>
                            <small class="text-muted">کد یکتا برای این بخش (مثلاً A، B، 1، 2)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">نام بخش <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['section_name']) ? 'is-invalid' : '' ?>" 
                                   name="section_name" value="<?= htmlspecialchars($old['section_name'] ?? '') ?>" 
                                   placeholder="مثال: الف" required>
                            <?php if (isset($errors['section_name'])): ?><div class="invalid-feedback"><?= $errors['section_name'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">ظرفیت</label>
                            <input type="number" class="form-control" name="capacity" 
                                   value="<?= htmlspecialchars($old['capacity'] ?? '40') ?>" min="1" max="500">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">شماره کلاس درس</label>
                            <input type="text" class="form-control" name="room_number" 
                                   value="<?= htmlspecialchars($old['room_number'] ?? '') ?>" placeholder="مثال: ۱۰۱">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">معلم راهنما</label>
                        <select class="form-select" name="class_teacher_id">
                            <option value="">انتخاب معلم راهنما...</option>
                            <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($old['class_teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold">بخش فعال باشد</label>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/sections') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره بخش</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">هر کلاس می‌تواند چندین بخش داشته باشد (مثلاً الف، ب، ج).</li><li class="mb-2">معلم راهنما مسئول پیگیری امور دانش‌آموزان این بخش است.</li><li>کد بخش در هر کلاس باید یکتا باشد.</li></ul></div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>