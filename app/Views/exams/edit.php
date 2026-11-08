<?php
$title = 'ویرایش آزمون';
$exam = $exam ?? [];
$examTypes = $examTypes ?? [];
$subjects = $subjects ?? [];
$teachers = $teachers ?? [];
$old = $_SESSION['old'] ?? $exam;

$content = function() use ($exam, $examTypes, $subjects, $teachers, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>ویرایش آزمون</h1><p class="text-muted mb-0 mt-1"><?= htmlspecialchars($exam['subject_name'] ?? '') ?> - <?= htmlspecialchars($exam['class_name'] ?? '') ?></p></div>
    <div>
        <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $exam['id'] ?>)"><i class="fas fa-trash me-1"></i>حذف</button>
        <a href="<?= url('/exams') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row"><div class="col-xl-8 col-lg-7"><div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">ویرایش اطلاعات</h6></div><div class="card-body">
    <form action="<?= url('/exams/' . ($exam['id'] ?? '')) ?>" method="POST"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="PUT">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نوع آزمون</label><select class="form-select" name="exam_type_id"><?php foreach ($examTypes as $et): ?><option value="<?= $et['id'] ?>" <?= ($old['exam_type_id'] ?? $exam['exam_type_id'] ?? '') == $et['id'] ? 'selected' : '' ?>><?= htmlspecialchars($et['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">درس</label><select class="form-select" name="subject_id"><?php foreach ($subjects as $s): ?><option value="<?= $s['id'] ?>" <?= ($old['subject_id'] ?? $exam['subject_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['subject_name']) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ</label><input type="text" class="form-control persian-datepicker" name="exam_date" value="<?= htmlspecialchars($old['exam_date'] ?? $exam['exam_date'] ?? '') ?>" required></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">شروع</label><input type="time" class="form-control" name="start_time" value="<?= htmlspecialchars($old['start_time'] ?? $exam['start_time'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">پایان</label><input type="time" class="form-control" name="end_time" value="<?= htmlspecialchars($old['end_time'] ?? $exam['end_time'] ?? '') ?>"></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">نمره کل</label><input type="number" class="form-control" name="total_marks" value="<?= htmlspecialchars($old['total_marks'] ?? $exam['total_marks'] ?? '100') ?>" step="0.01" required></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">نمره قبولی</label><input type="number" class="form-control" name="passing_marks" value="<?= htmlspecialchars($old['passing_marks'] ?? $exam['passing_marks'] ?? '50') ?>" step="0.01" required></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">مراقب</label><select class="form-select" name="invigilator_id"><option value="">انتخاب...</option><?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>" <?= ($old['invigilator_id'] ?? $exam['invigilator_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">وضعیت</label><select class="form-select" name="status"><option value="scheduled" <?= ($old['status'] ?? $exam['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>برنامه‌ریزی شده</option><option value="ongoing" <?= ($old['status'] ?? '') === 'ongoing' ? 'selected' : '' ?>>در حال برگزاری</option><option value="completed" <?= ($old['status'] ?? '') === 'completed' ? 'selected' : '' ?>>تکمیل شده</option><option value="cancelled" <?= ($old['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>لغو شده</option></select></div></div>
        <hr><div class="d-flex justify-content-between"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $exam['id'] ?>)"><i class="fas fa-trash me-1"></i>حذف</button><div><a href="<?= url('/exams') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div></div>
    </form>
</div></div></div></div>

<script>function confirmDelete(id){Swal.fire({title:'حذف آزمون',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',cancelButtonText:'انصراف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?=url('/exams')?>/'+id;f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="DELETE">';document.body.appendChild(f);f.submit()}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>