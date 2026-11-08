<?php
$title = 'ویرایش برنامه';
$timetable = $timetable ?? [];
$sections = $sections ?? [];
$subjects = $subjects ?? [];
$teachers = $teachers ?? [];
$semesters = $semesters ?? [];
$old = $_SESSION['old'] ?? $timetable;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$days = ['Saturday' => 'شنبه', 'Sunday' => 'یکشنبه', 'Monday' => 'دوشنبه', 'Tuesday' => 'سه‌شنبه', 
         'Wednesday' => 'چهارشنبه', 'Thursday' => 'پنج‌شنبه', 'Friday' => 'جمعه'];

$content = function() use ($timetable, $sections, $subjects, $teachers, $semesters, $days, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش برنامه</h1>
        <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($timetable['subject_name'] ?? '') ?> - <?= htmlspecialchars($timetable['class_name'] ?? '') ?></p>
    </div>
    <div>
        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $timetable['id'] ?>)"><i class="fas fa-trash me-1"></i>حذف</button>
        <a href="<?= url('/timetable') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>ویرایش اطلاعات</h6></div>
            <div class="card-body">
                <form action="<?= url('/timetable/' . ($timetable['id'] ?? '')) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">بخش</label>
                            <select class="form-select" name="section_id" required>
                                <?php foreach ($sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= ($old['section_id'] ?? $timetable['section_id'] ?? '') == $sec['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sec['section_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">درس</label>
                            <select class="form-select" name="subject_id" required>
                                <?php foreach ($subjects as $sub): ?>
                                <option value="<?= $sub['id'] ?>" <?= ($old['subject_id'] ?? $timetable['subject_id'] ?? '') == $sub['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sub['subject_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">معلم <span class="text-danger">*</span></label>
                            <select class="form-select" name="teacher_id" required>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($old['teacher_id'] ?? $timetable['teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">روز هفته <span class="text-danger">*</span></label>
                            <select class="form-select" name="day_of_week" required>
                                <?php foreach ($days as $en => $fa): ?>
                                <option value="<?= $en ?>" <?= ($old['day_of_week'] ?? $timetable['day_of_week'] ?? '') === $en ? 'selected' : '' ?>><?= $fa ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label fw-bold">ساعت شروع</label><input type="time" class="form-control" name="start_time" value="<?= htmlspecialchars($old['start_time'] ?? $timetable['start_time'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label fw-bold">ساعت پایان</label><input type="time" class="form-control" name="end_time" value="<?= htmlspecialchars($old['end_time'] ?? $timetable['end_time'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label fw-bold">شماره کلاس</label><input type="text" class="form-control" name="room_number" value="<?= htmlspecialchars($old['room_number'] ?? $timetable['room_number'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label class="form-label fw-bold">ترم</label><select class="form-select" name="semester_id" required><?php foreach ($semesters as $sem): ?><option value="<?= $sem['id'] ?>" <?= ($old['semester_id'] ?? $timetable['semester_id'] ?? '') == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['semester_name']) ?></option><?php endforeach; ?></select></div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $timetable['id'] ?>)"><i class="fas fa-trash me-1"></i>حذف</button>
                        <div><a href="<?= url('/timetable') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({ title: 'حذف برنامه', text: 'آیا از حذف این برنامه اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/timetable') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>