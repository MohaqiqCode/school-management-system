<?php
$content = function() use ($stats, $teacherPerformance, $user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>داشبورد مدیریت</h1>
    <div>
        <span class="badge bg-success me-2"><?= htmlspecialchars($user['branch_name'] ?? '') ?></span>
        <a href="<?= url('/reports') ?>" class="btn btn-sm btn-primary"><i class="fas fa-chart-bar me-1"></i>گزارش‌ها</a>
    </div>
</div>

<!-- Quick Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100"><div class="card-body"><div class="row g-0 align-items-center"><div class="col"><div class="text-xs fw-bold text-primary mb-1">کل دانش‌آموزان</div><div class="h5 mb-0"><?= number_format($stats['total_students'] ?? 0) ?></div></div><div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div></div></div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100"><div class="card-body"><div class="row g-0 align-items-center"><div class="col"><div class="text-xs fw-bold text-success mb-1">معلمان</div><div class="h5 mb-0"><?= number_format($stats['total_teachers'] ?? 0) ?></div></div><div class="col-auto"><i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i></div></div></div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100"><div class="card-body"><div class="row g-0 align-items-center"><div class="col"><div class="text-xs fw-bold text-info mb-1">نرخ حضور</div><div class="h5 mb-0"><?= $stats['attendance_rate'] ?? 0 ?>%</div></div><div class="col-auto"><i class="fas fa-calendar-check fa-2x text-gray-300"></i></div></div></div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100"><div class="card-body"><div class="row g-0 align-items-center"><div class="col"><div class="text-xs fw-bold text-warning mb-1">میانگین عملکرد</div><div class="h5 mb-0"><?= $stats['academic_performance'] ?? 0 ?></div></div><div class="col-auto"><i class="fas fa-star fa-2x text-gray-300"></i></div></div></div></div>
    </div>
</div>

<!-- Teacher Performance -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-trophy me-2"></i>عملکرد معلمان</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light"><tr><th>#</th><th>نام معلم</th><th>دپارتمان</th><th>کلاس‌ها</th><th>دروس</th><th>میانگین نمرات</th><th>وضعیت</th></tr></thead>
                        <tbody>
                            <?php if (!empty($teacherPerformance)): ?>
                                <?php foreach ($teacherPerformance as $i => $tp): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($tp['first_name'] . ' ' . $tp['last_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($tp['department_name'] ?? '-') ?></td>
                                    <td><span class="badge bg-primary"><?= $tp['classes_assigned'] ?? 0 ?></span></td>
                                    <td><span class="badge bg-info"><?= $tp['subjects_assigned'] ?? 0 ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 fw-bold"><?= $tp['avg_student_marks'] ?? 0 ?></span>
                                            <div class="progress flex-grow-1" style="height:6px;"><div class="progress-bar bg-<?= ($tp['avg_student_marks'] ?? 0) >= 70 ? 'success' : (($tp['avg_student_marks'] ?? 0) >= 50 ? 'warning' : 'danger') ?>" style="width:<?= min($tp['avg_student_marks'] ?? 0, 100) ?>%"></div></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (($tp['avg_student_marks'] ?? 0) >= 80): ?><span class="badge bg-success">عالی</span>
                                        <?php elseif (($tp['avg_student_marks'] ?? 0) >= 60): ?><span class="badge bg-info">خوب</span>
                                        <?php else: ?><span class="badge bg-warning">نیاز به توجه</span><?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-3 text-muted">داده‌ای موجود نیست</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>