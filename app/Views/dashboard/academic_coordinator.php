<?php
$content = function() use ($stats, $timetableConflicts, $user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-clipboard-list me-2"></i>داشبورد هماهنگی آموزشی</h1>
    <a href="<?= url('/timetable') ?>" class="btn btn-primary"><i class="fas fa-calendar-alt me-1"></i>برنامه هفتگی</a>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body text-center"><i class="fas fa-calendar fa-2x text-primary mb-2"></i><div class="h5"><?= htmlspecialchars($stats['active_academic_year']['year_name'] ?? 'تعیین نشده') ?></div><small class="text-muted">سال تحصیلی جاری</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body text-center"><i class="fas fa-chalkboard fa-2x text-success mb-2"></i><div class="h5"><?= number_format($stats['total_classes'] ?? 0) ?></div><small class="text-muted">کلاس‌های فعال</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100"><div class="card-body text-center"><i class="fas fa-book fa-2x text-info mb-2"></i><div class="h5"><?= number_format($stats['total_subjects'] ?? 0) ?></div><small class="text-muted">دروس فعال</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body text-center"><i class="fas fa-clock fa-2x text-warning mb-2"></i><div class="h5"><?= number_format($stats['total_timetable_entries'] ?? 0) ?></div><small class="text-muted">برنامه‌های هفتگی</small></div></div></div>
</div>

<!-- Timetable Conflicts -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold <?= !empty($timetableConflicts) ? 'text-danger' : 'text-success' ?>">
                    <i class="fas <?= !empty($timetableConflicts) ? 'fa-exclamation-triangle' : 'fa-check-circle' ?> me-2"></i>
                    تداخل‌های برنامه هفتگی
                </h6>
                <?php if (!empty($timetableConflicts)): ?>
                <span class="badge bg-danger"><?= count($timetableConflicts) ?> تداخل</span>
                <?php else: ?>
                <span class="badge bg-success">بدون تداخل</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($timetableConflicts)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light"><tr><th>روز</th><th>ساعت</th><th>معلم ۱</th><th>معلم ۲</th><th>بخش ۱</th><th>بخش ۲</th></tr></thead>
                        <tbody>
                            <?php foreach ($timetableConflicts as $conflict): ?>
                            <tr>
                                <td><?= htmlspecialchars($conflict['day_of_week']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= $conflict['start_time'] ?> - <?= $conflict['end_time'] ?></span></td>
                                <td><?= htmlspecialchars($conflict['teacher1_name']) ?></td>
                                <td><?= htmlspecialchars($conflict['teacher2_name']) ?></td>
                                <td><?= htmlspecialchars($conflict['section1']) ?></td>
                                <td><?= htmlspecialchars($conflict['section2']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5>هیچ تداخلی در برنامه هفتگی وجود ندارد</h5>
                    <p class="text-muted">همه برنامه‌ها به درستی تنظیم شده‌اند.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-link me-2"></i>دسترسی سریع</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><a href="<?= url('/timetable/create') ?>" class="btn btn-outline-primary w-100"><i class="fas fa-plus me-1"></i>ایجاد برنامه جدید</a></div>
                    <div class="col-md-3"><a href="<?= url('/classes') ?>" class="btn btn-outline-success w-100"><i class="fas fa-chalkboard me-1"></i>مدیریت کلاس‌ها</a></div>
                    <div class="col-md-3"><a href="<?= url('/subjects') ?>" class="btn btn-outline-info w-100"><i class="fas fa-book me-1"></i>مدیریت دروس</a></div>
                    <div class="col-md-3"><a href="<?= url('/teachers') ?>" class="btn btn-outline-warning w-100"><i class="fas fa-user-tie me-1"></i>معلمان</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>