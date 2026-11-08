<?php
$title = 'حجم کاری معلم';
$teacher = $teacher ?? [];
$workload = $workload ?? [];
$timetable = $timetable ?? [];

$content = function() use ($teacher, $workload, $timetable) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chart-bar me-2"></i>حجم کاری</h1><p class="text-muted mb-0 mt-1"><?= htmlspecialchars(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) ?></p></div>
    <a href="<?= url('/teachers/' . ($teacher['id'] ?? '')) ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-primary mb-1">کلاس‌های هفتگی</div><div class="h5 mb-0"><?= $workload['weekly_periods'] ?? 0 ?> جلسه</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">کلاس‌های اختصاصی</div><div class="h5 mb-0"><?= $workload['assigned_classes'] ?? 0 ?> کلاس</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-info mb-1">دروس</div><div class="h5 mb-0"><?= $workload['assigned_subjects'] ?? 0 ?> درس</div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-warning mb-1">دانش‌آموزان</div><div class="h5 mb-0"><?= number_format($workload['total_students'] ?? 0) ?></div></div></div></div>
</div>

<!-- Utilization Bar -->
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">میزان استفاده از ظرفیت</h6></div><div class="card-body"><div class="d-flex justify-content-between mb-1"><span>درصد تکمیل</span><span class="fw-bold"><?= $workload['utilization_percentage'] ?? 0 ?>%</span></div><div class="progress" style="height:20px;"><div class="progress-bar bg-<?= ($workload['utilization_percentage'] ?? 0) > 80 ? 'danger' : (($workload['utilization_percentage'] ?? 0) > 50 ? 'warning' : 'success') ?>" style="width:<?= $workload['utilization_percentage'] ?? 0 ?>%;"><?= $workload['utilization_percentage'] ?? 0 ?>%</div></div><small class="text-muted mt-1">از ۴۰ جلسه هفتگی</small></div></div>

<!-- Timetable -->
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-calendar-alt me-2"></i>برنامه هفتگی کامل</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>روز</th><th>ساعت</th><th>درس</th><th>کلاس</th><th>بخش</th></tr></thead><tbody><?php if (!empty($timetable)): ?><?php foreach ($timetable as $tt): ?><tr><td><?= htmlspecialchars($tt['day_of_week']) ?></td><td><?= $tt['start_time'] ?> - <?= $tt['end_time'] ?></td><td><strong><?= htmlspecialchars($tt['subject_name']) ?></strong></td><td><?= htmlspecialchars($tt['class_name']) ?></td><td><?= htmlspecialchars($tt['section_name']) ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="5" class="text-center py-3 text-muted">برنامه‌ای ثبت نشده</td></tr><?php endif; ?></tbody></table></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>