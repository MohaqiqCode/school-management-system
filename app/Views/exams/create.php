<?php
$title = 'ایجاد آزمون جدید';
$examTypes = $examTypes ?? [];
$academicYears = $academicYears ?? [];
$classes = $classes ?? [];
$teachers = $teachers ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($examTypes, $academicYears, $classes, $teachers, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle me-2"></i>ایجاد آزمون جدید</h1>
    <a href="<?= url('/exams') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات آزمون</h6></div>
            <div class="card-body">
                <form action="<?= url('/exams') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نوع آزمون <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['exam_type_id']) ? 'is-invalid' : '' ?>" name="exam_type_id" required>
                                <option value="">انتخاب کنید...</option>
                                <?php foreach ($examTypes as $et): ?>
                                <option value="<?= $et['id'] ?>" <?= ($old['exam_type_id'] ?? '') == $et['id'] ? 'selected' : '' ?>><?= htmlspecialchars($et['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['exam_type_id'])): ?><div class="invalid-feedback"><?= $errors['exam_type_id'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">سال تحصیلی <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['academic_year_id']) ? 'is-invalid' : '' ?>" name="academic_year_id" required>
                                <option value="">انتخاب کنید...</option>
                                <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= ($old['academic_year_id'] ?? '') == $ay['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ay['year_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['academic_year_id'])): ?><div class="invalid-feedback"><?= $errors['academic_year_id'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">کلاس <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['class_id']) ? 'is-invalid' : '' ?>" name="class_id" id="classSelect" required onchange="loadSubjects(this.value)">
                                <option value="">انتخاب کلاس...</option>
                                <?php foreach ($classes as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= ($old['class_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['class_id'])): ?><div class="invalid-feedback"><?= $errors['class_id'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">درس <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['subject_id']) ? 'is-invalid' : '' ?>" name="subject_id" id="subjectSelect" required>
                                <option value="">ابتدا کلاس را انتخاب کنید...</option>
                            </select>
                            <?php if (isset($errors['subject_id'])): ?><div class="invalid-feedback"><?= $errors['subject_id'] ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">تاریخ آزمون <span class="text-danger">*</span></label>
                            <input type="text" class="form-control persian-datepicker <?= isset($errors['exam_date']) ? 'is-invalid' : '' ?>" name="exam_date" value="<?= htmlspecialchars($old['exam_date'] ?? '') ?>" required>
                            <?php if (isset($errors['exam_date'])): ?><div class="invalid-feedback"><?= $errors['exam_date'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">ساعت شروع</label>
                            <input type="time" class="form-control" name="start_time" value="<?= htmlspecialchars($old['start_time'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">ساعت پایان</label>
                            <input type="time" class="form-control" name="end_time" value="<?= htmlspecialchars($old['end_time'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">شماره کلاس</label>
                            <input type="text" class="form-control" name="room_number" value="<?= htmlspecialchars($old['room_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">نمره کل <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['total_marks']) ? 'is-invalid' : '' ?>" name="total_marks" value="<?= htmlspecialchars($old['total_marks'] ?? '100') ?>" step="0.01" required>
                            <?php if (isset($errors['total_marks'])): ?><div class="invalid-feedback"><?= $errors['total_marks'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">نمره قبولی <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['passing_marks']) ? 'is-invalid' : '' ?>" name="passing_marks" value="<?= htmlspecialchars($old['passing_marks'] ?? '50') ?>" step="0.01" required>
                            <?php if (isset($errors['passing_marks'])): ?><div class="invalid-feedback"><?= $errors['passing_marks'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">مراقب</label>
                            <select class="form-select" name="invigilator_id">
                                <option value="">انتخاب مراقب...</option>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($old['invigilator_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/exams') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره آزمون</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>راهنما</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">نمره قبولی باید کمتر از نمره کل باشد.</li><li class="mb-2">پس از ایجاد آزمون می‌توانید نمرات را ثبت کنید.</li><li>مراقب آزمون اختیاری است.</li></ul></div></div>
    </div>
</div>

<script>
    function loadSubjects(classId) {
        const sel = document.getElementById('subjectSelect');
        sel.innerHTML = '<option value="">در حال بارگذاری...</option>';
        if (classId) {
            fetch('<?= url('/api/subjects') ?>/' + classId).then(r => r.json()).then(d => {
                sel.innerHTML = '<option value="">انتخاب درس...</option>';
                if (d.success && d.data) d.data.forEach(s => sel.innerHTML += `<option value="${s.id}">${s.subject_name}</option>`);
            });
        }
    }
    $(document).ready(function() { $('.persian-datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>