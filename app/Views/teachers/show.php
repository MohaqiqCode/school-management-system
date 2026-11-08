<?php
$title = 'پروفایل معلم';
$teacher = $teacher ?? [];
$assignments = $assignments ?? [];
$timetable = $timetable ?? [];
$attendanceSummary = $attendanceSummary ?? [];
$recentPayroll = $recentPayroll ?? [];

$content = function() use ($teacher, $assignments, $timetable, $attendanceSummary, $recentPayroll) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chalkboard-teacher me-2"></i>پروفایل معلم</h1>
    <div>
        <a href="<?= url('/teachers/' . ($teacher['id'] ?? '') . '/edit') ?>" class="btn btn-warning"><i class="fas fa-edit me-1"></i>ویرایش</a>
        <a href="<?= url('/teachers/' . ($teacher['id'] ?? '') . '/workload') ?>" class="btn btn-info"><i class="fas fa-chart-bar me-1"></i>حجم کاری</a>
        <a href="<?= url('/teachers') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow text-center"><div class="card-body pt-5 pb-4">
            <img src="<?= $teacher['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle shadow mb-3" width="100" height="100">
            <h4 class="fw-bold"><?= htmlspecialchars(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) ?></h4>
            <span class="badge bg-primary"><?= htmlspecialchars($teacher['department_name'] ?? '') ?></span>
            <div class="mt-2"><code><?= htmlspecialchars($teacher['teacher_code']) ?></code></div>
            <div class="mt-2 small text-muted">سابقه: <?= $teacher['teaching_experience_years'] ?? 0 ?> سال | <?= htmlspecialchars($teacher['qualification'] ?? '') ?></div>
        </div></div>

        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-calendar-check me-2"></i>حضور (ماه جاری)</h6></div><div class="card-body text-center">
            <div class="display-4 fw-bold text-success"><?= $attendanceSummary['attendance_rate'] ?? 0 ?>%</div>
            <small class="text-muted">نرخ حضور</small>
            <div class="row mt-3"><div class="col-4"><span class="text-success"><?= $attendanceSummary['current_month']['present'] ?? 0 ?></span><br><small>حاضر</small></div><div class="col-4"><span class="text-danger"><?= $attendanceSummary['current_month']['absent'] ?? 0 ?></span><br><small>غایب</small></div><div class="col-4"><span class="text-info"><?= $attendanceSummary['current_month']['on_leave'] ?? 0 ?></span><br><small>مرخصی</small></div></div>
        </div></div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-book me-2"></i>کلاس‌ها و دروس</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>کلاس</th><th>درس</th><th>سال</th><th>واحد</th></tr></thead><tbody><?php if (!empty($assignments)): ?><?php foreach ($assignments as $a): ?><tr><td><?= htmlspecialchars($a['class_name']) ?></td><td><strong><?= htmlspecialchars($a['subject_name']) ?></strong></td><td><?= htmlspecialchars($a['year_name'] ?? '') ?></td><td><?= $a['periods_per_week'] ?? 0 ?> جلسه</td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="4" class="text-center text-muted py-3">کلاسی اختصاص داده نشده</td></tr><?php endif; ?></tbody></table></div></div></div>

        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-calendar-alt me-2"></i>برنامه هفتگی</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>روز</th><th>ساعت</th><th>درس</th><th>بخش</th></tr></thead><tbody><?php if (!empty($timetable)): ?><?php foreach ($timetable as $tt): ?><tr><td><?= htmlspecialchars($tt['day_of_week']) ?></td><td><?= $tt['start_time'] ?>-<?= $tt['end_time'] ?></td><td><?= htmlspecialchars($tt['subject_name']) ?></td><td><?= htmlspecialchars($tt['section_name']) ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="4" class="text-center text-muted py-3">برنامه‌ای ثبت نشده</td></tr><?php endif; ?></tbody></table></div></div></div>

        <?php if (!empty($recentPayroll)): ?>
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-receipt me-2"></i>آخرین حقوق‌ها</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>دوره</th><th>ناخالص</th><th>خالص</th><th>وضعیت</th></tr></thead><tbody><?php foreach ($recentPayroll as $p): ?><tr><td><?= $p['pay_period_start'] ?> تا <?= $p['pay_period_end'] ?></td><td><?= number_format($p['gross_salary'] ?? 0) ?></td><td><?= number_format($p['net_salary'] ?? 0) ?></td><td><span class="badge bg-<?= $p['status'] === 'paid' ? 'success' : 'warning' ?>"><?= $p['status'] === 'paid' ? 'پرداخت شده' : 'در انتظار' ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
        <?php endif; ?>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>