<?php
$title = 'ایجاد برنامه جدید';
$classes = $classes ?? [];
$teachers = $teachers ?? [];
$semesters = $semesters ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$days = ['Saturday' => 'شنبه', 'Sunday' => 'یکشنبه', 'Monday' => 'دوشنبه', 'Tuesday' => 'سه‌شنبه', 
         'Wednesday' => 'چهارشنبه', 'Thursday' => 'پنج‌شنبه', 'Friday' => 'جمعه'];

$content = function() use ($classes, $teachers, $semesters, $days, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle me-2"></i>ایجاد برنامه جدید</h1>
    <a href="<?= url('/timetable') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات برنامه</h6></div>
            <div class="card-body">
                <form action="<?= url('/timetable') ?>" method="POST" id="timetableForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کلاس <span class="text-danger">*</span></label>
                            <select class="form-select" name="class_id" id="classSelect" required onchange="loadSectionsForForm(this.value)">
                                <option value="">انتخاب کلاس...</option>
                                <?php foreach ($classes as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= ($old['class_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?> (پایه <?= $cl['class_level'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">بخش <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['section_id']) ? 'is-invalid' : '' ?>" name="section_id" id="sectionSelect" required>
                                <option value="">ابتدا کلاس را انتخاب کنید...</option>
                            </select>
                            <?php if (isset($errors['section_id'])): ?><div class="invalid-feedback"><?= $errors['section_id'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">درس <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['subject_id']) ? 'is-invalid' : '' ?>" name="subject_id" id="subjectSelect" required>
                                <option value="">ابتدا کلاس را انتخاب کنید...</option>
                            </select>
                            <?php if (isset($errors['subject_id'])): ?><div class="invalid-feedback"><?= $errors['subject_id'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">معلم <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['teacher_id']) ? 'is-invalid' : '' ?>" name="teacher_id" required>
                                <option value="">انتخاب معلم...</option>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($old['teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['teacher_id'])): ?><div class="invalid-feedback"><?= $errors['teacher_id'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">روز هفته <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['day_of_week']) ? 'is-invalid' : '' ?>" name="day_of_week" required>
                                <option value="">انتخاب روز...</option>
                                <?php foreach ($days as $en => $fa): ?>
                                <option value="<?= $en ?>" <?= ($old['day_of_week'] ?? '') === $en ? 'selected' : '' ?>><?= $fa ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['day_of_week'])): ?><div class="invalid-feedback"><?= $errors['day_of_week'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">ساعت شروع <span class="text-danger">*</span></label>
                            <input type="time" class="form-control <?= isset($errors['start_time']) ? 'is-invalid' : '' ?>" 
                                   name="start_time" value="<?= htmlspecialchars($old['start_time'] ?? '') ?>" required>
                            <?php if (isset($errors['start_time'])): ?><div class="invalid-feedback"><?= $errors['start_time'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">ساعت پایان <span class="text-danger">*</span></label>
                            <input type="time" class="form-control <?= isset($errors['end_time']) ? 'is-invalid' : '' ?>" 
                                   name="end_time" value="<?= htmlspecialchars($old['end_time'] ?? '') ?>" required>
                            <?php if (isset($errors['end_time'])): ?><div class="invalid-feedback"><?= $errors['end_time'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">شماره کلاس</label>
                            <input type="text" class="form-control" name="room_number" value="<?= htmlspecialchars($old['room_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ترم <span class="text-danger">*</span></label>
                        <select class="form-select" name="semester_id" required>
                            <option value="">انتخاب ترم...</option>
                            <?php foreach ($semesters as $sem): ?>
                            <option value="<?= $sem['id'] ?>" <?= ($old['semester_id'] ?? '') == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['semester_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/timetable') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره برنامه</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">سیستم به صورت خودکار تداخل‌های زمانی را بررسی می‌کند.</li><li class="mb-2">هر معلم نمی‌تواند همزمان در دو کلاس باشد.</li><li>شماره کلاس درس اختیاری است.</li></ul></div></div>
    </div>
</div>

<script>
    function loadSectionsForForm(classId) {
        const sectionSelect = document.getElementById('sectionSelect');
        const subjectSelect = document.getElementById('subjectSelect');
        sectionSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
        subjectSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
        
        if (classId) {
            fetch('<?= url('/api/sections') ?>/' + classId)
                .then(r => r.json()).then(d => {
                    sectionSelect.innerHTML = '<option value="">انتخاب بخش...</option>';
                    if (d.success && d.data) d.data.forEach(s => sectionSelect.innerHTML += `<option value="${s.id}">${s.section_name}</option>`);
                });
            fetch('<?= url('/api/subjects') ?>/' + classId)
                .then(r => r.json()).then(d => {
                    subjectSelect.innerHTML = '<option value="">انتخاب درس...</option>';
                    if (d.success && d.data) d.data.forEach(s => subjectSelect.innerHTML += `<option value="${s.id}">${s.subject_name}</option>`);
                });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const classId = document.getElementById('classSelect').value;
        if (classId) loadSectionsForForm(classId);
    });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>