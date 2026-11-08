<?php
$content = function() use ($stats, $todayTimetable, $attendanceSummary, $upcomingExams, $user, $teacher) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chalkboard-teacher me-2"></i>داشبورد معلم</h1>
    <span class="badge bg-info fs-6"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body text-center"><i class="fas fa-chalkboard fa-2x text-primary mb-2"></i><div class="h5"><?= $stats['assigned_classes'] ?? 0 ?></div><small>کلاس‌های من</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body text-center"><i class="fas fa-book fa-2x text-success mb-2"></i><div class="h5"><?= $stats['assigned_subjects'] ?? 0 ?></div><small>دروس من</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body text-center"><i class="fas fa-users fa-2x text-info mb-2"></i><div class="h5"><?= number_format($stats['total_students'] ?? 0) ?></div><small>دانش‌آموزان</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body text-center"><i class="fas fa-clock fa-2x text-warning mb-2"></i><div class="h5"><?= $stats['today_periods'] ?? 0 ?></div><small>کلاس امروز</small></div></div></div>
</div>

<!-- Today's Timetable & Attendance -->
<div class="row">
    <div class="col-xl-7 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-calendar-day me-2"></i>برنامه امروز</h6></div>
            <div class="card-body">
                <?php if (!empty($todayTimetable)): ?>
                <div class="table-responsive"><table class="table table-hover"><thead><tr><th>ساعت</th><th>درس</th><th>کلاس</th><th>بخش</th></tr></thead><tbody>
                    <?php foreach ($todayTimetable as $tt): ?>
                    <tr><td><span class="badge bg-light text-dark"><?= $tt['start_time'] ?> - <?= $tt['end_time'] ?></span></td><td><strong><?= htmlspecialchars($tt['subject_name']) ?></strong></td><td><?= htmlspecialchars($tt['class_name']) ?></td><td><?= htmlspecialchars($tt['section_name']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>
                <?php else: ?><p class="text-center text-muted py-3">امروز کلاسی ندارید</p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-clipboard-check me-2"></i>خلاصه حضور و غیاب امروز</h6></div>
            <div class="card-body">
                <?php if (!empty($attendanceSummary)): ?>
                    <?php foreach ($attendanceSummary as $summ): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                        <div><strong><?= htmlspecialchars($summ['class_name']) ?> - <?= htmlspecialchars($summ['section_name']) ?></strong></div>
                        <div><span class="text-success"><?= $summ['present'] ?> حاضر</span> / <span class="text-danger"><?= $summ['absent'] ?> غایب</span></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?><p class="text-center text-muted py-3">داده‌ای موجود نیست</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Exams & Quick Actions -->
<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="fas fa-file-alt me-2"></i>آزمون‌های پیش رو</h6></div>
            <div class="card-body">
                <?php if (!empty($upcomingExams)): ?>
                    <?php foreach ($upcomingExams as $exam): ?>
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom"><div><strong><?= htmlspecialchars($exam['subject_name']) ?></strong><br><small><?= htmlspecialchars($exam['class_name']) ?></small></div><span class="badge bg-warning text-dark"><?= date('Y/m/d', strtotime($exam['exam_date'])) ?></span></div>
                    <?php endforeach; ?>
                <?php else: ?><p class="text-center text-muted py-3">آزمونی برنامه‌ریزی نشده</p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-6 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-link me-2"></i>دسترسی سریع</h6></div><div class="card-body"><div class="row g-3">
            <div class="col-6"><a href="<?= url('/attendance/students/take') ?>" class="btn btn-outline-success w-100"><i class="fas fa-clipboard-check me-1"></i>ثبت حضور و غیاب</a></div>
            <div class="col-6"><a href="<?= url('/exams') ?>" class="btn btn-outline-warning w-100"><i class="fas fa-pen me-1"></i>ثبت نمرات</a></div>
            <div class="col-6"><a href="<?= url('/timetable') ?>" class="btn btn-outline-info w-100"><i class="fas fa-calendar-alt me-1"></i>برنامه هفتگی</a></div>
            <div class="col-6"><a href="<?= url('/students') ?>" class="btn btn-outline-primary w-100"><i class="fas fa-users me-1"></i>دانش‌آموزان</a></div>
        </div></div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>