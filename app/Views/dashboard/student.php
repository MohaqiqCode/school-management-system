<?php
$content = function() use ($student, $attendanceStats, $recentResults, $todayTimetable, $feeStatus, $upcomingExams, $notifications, $user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-graduate me-2"></i>داشبورد دانش‌آموز</h1>
    <span class="badge bg-success fs-6"><?= htmlspecialchars($student['class_name'] ?? '') ?> - <?= htmlspecialchars($student['section_name'] ?? '') ?></span>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body text-center"><i class="fas fa-calendar-check fa-2x text-primary mb-2"></i><div class="h5"><?= $attendanceStats['percentage'] ?? 0 ?>%</div><small>حضور (۳۰ روز)</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body text-center"><i class="fas fa-star fa-2x text-success mb-2"></i><div class="h5"><?= $recentResults[0]['marks_obtained'] ?? '-' ?></div><small>آخرین نمره</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body text-center"><i class="fas fa-book fa-2x text-info mb-2"></i><div class="h5"><?= count($todayTimetable ?? []) ?></div><small>کلاس امروز</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body text-center"><i class="fas fa-money-bill fa-2x text-warning mb-2"></i><div class="h5"><?= number_format(array_sum(array_column($feeStatus ?? [], 'balance_amount'))) ?> ت</div><small>شهریه معوق</small></div></div></div>
</div>

<!-- Today Timetable & Recent Results -->
<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-calendar-day me-2"></i>برنامه امروز</h6></div>
            <div class="card-body">
                <?php if (!empty($todayTimetable)): ?>
                    <?php foreach ($todayTimetable as $tt): ?>
                    <div class="d-flex justify-content-between p-2 border-bottom"><span><strong><?= htmlspecialchars($tt['subject_name']) ?></strong></span><span class="badge bg-light text-dark"><?= $tt['start_time'] ?> - <?= $tt['end_time'] ?></span></div>
                    <?php endforeach; ?>
                <?php else: ?><p class="text-center text-muted py-3">امروز تعطیل است</p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-6 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-chart-line me-2"></i>نتایج اخیر</h6></div>
            <div class="card-body">
                <?php if (!empty($recentResults)): ?>
                    <?php foreach (array_slice($recentResults, 0, 5) as $res): ?>
                    <div class="d-flex justify-content-between p-2 border-bottom"><span><?= htmlspecialchars($res['subject_name']) ?></span><span class="fw-bold <?= $res['marks_obtained'] >= ($res['passing_marks'] ?? 50) ? 'text-success' : 'text-danger' ?>"><?= $res['marks_obtained'] ?></span></div>
                    <?php endforeach; ?>
                <?php else: ?><p class="text-center text-muted py-3">نتیجه‌ای ثبت نشده</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Exams & Notifications -->
<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="fas fa-bell me-2"></i>آزمون‌های پیش رو</h6></div>
            <div class="card-body">
                <?php if (!empty($upcomingExams)): ?>
                    <?php foreach ($upcomingExams as $exam): ?>
                    <div class="alert alert-warning py-2 mb-2"><strong><?= htmlspecialchars($exam['subject_name']) ?></strong> | <?= date('Y/m/d', strtotime($exam['exam_date'])) ?></div>
                    <?php endforeach; ?>
                <?php else: ?><p class="text-center text-muted">آزمونی برنامه‌ریزی نشده</p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-6 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-bullhorn me-2"></i>اعلان‌ها</h6></div>
            <div class="card-body">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                    <div class="p-2 border-bottom <?= empty($notif['read_at']) ? 'bg-light' : '' ?>"><strong><?= htmlspecialchars($notif['title']) ?></strong><br><small class="text-muted"><?= htmlspecialchars(mb_substr($notif['message'], 0, 80)) ?></small></div>
                    <?php endforeach; ?>
                <?php else: ?><p class="text-center text-muted">اعلانی ندارید</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>