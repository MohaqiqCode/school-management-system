<?php
$title = 'ارتقاء دانش‌آموز';
$student = $student ?? [];
$classes = $classes ?? [];

$content = function() use ($student, $classes) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-level-up-alt me-2"></i>ارتقاء دانش‌آموز</h1>
    <a href="<?= url('/students/' . ($student['id'] ?? '')) ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-6 col-lg-7 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-success">اطلاعات فعلی</h6></div>
            <div class="card-body">
                <div class="text-center mb-3"><img src="<?= $student['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle" width="70" height="70"><h5 class="mt-2"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h5></div>
                <table class="table table-bordered"><tr><td>کد دانش‌آموزی</td><td><code><?= htmlspecialchars($student['student_code'] ?? '') ?></code></td></tr><tr><td>کلاس فعلی</td><td><strong><?= htmlspecialchars($student['class_name'] ?? '') ?></strong> (پایه <?= $student['class_level'] ?? '' ?>)</td></tr><tr><td>بخش فعلی</td><td><?= htmlspecialchars($student['section_name'] ?? '-') ?></td></tr></table>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">ارتقاء به</h6></div>
            <div class="card-body">
                <form action="<?= url('/students/' . ($student['id'] ?? '') . '/promote') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3"><label class="form-label fw-bold">کلاس جدید <span class="text-danger">*</span></label><select class="form-select" name="to_class_id" required><option value="">انتخاب کلاس...</option><?php foreach ($classes as $cl): ?><option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['class_name']) ?> (پایه <?= $cl['class_level'] ?>)</option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label fw-bold">سال تحصیلی <span class="text-danger">*</span></label><select class="form-select" name="academic_year_id" required><option value="">انتخاب سال...</option></select></div>
                    <div class="mb-3"><label class="form-label fw-bold">تاریخ ارتقاء</label><input type="text" class="form-control persian-datepicker" name="promotion_date" value="<?= date('Y/m/d') ?>"></div>
                    <div class="mb-3"><label class="form-label fw-bold">توضیحات</label><textarea class="form-control" name="remarks" rows="2"></textarea></div>
                    <hr><button type="submit" class="btn btn-success w-100"><i class="fas fa-level-up-alt me-1"></i>ثبت ارتقاء</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>$(document).ready(function(){$('.persian-datepicker').persianDatepicker({format:'YYYY/MM/DD',autoClose:true})});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>