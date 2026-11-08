<?php
$content = function() use ($stats, $classDistribution, $todayAttendance, $recentPayments, $upcomingExams, $user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-tachometer-alt me-2"></i>داشبورد مدیریت شعبه
    </h1>
    <span class="badge bg-primary fs-6"><?= htmlspecialchars($user['branch_name'] ?? '') ?></span>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col"><div class="text-xs fw-bold text-primary text-uppercase mb-1">دانش‌آموزان</div><div class="h5 mb-0 fw-bold"><?= number_format($stats['total_students'] ?? 0) ?></div></div>
                    <div class="col-auto"><i class="fas fa-user-graduate fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col"><div class="text-xs fw-bold text-success text-uppercase mb-1">معلمان</div><div class="h5 mb-0 fw-bold"><?= number_format($stats['total_teachers'] ?? 0) ?></div></div>
                    <div class="col-auto"><i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col"><div class="text-xs fw-bold text-info text-uppercase mb-1">نرخ حضور</div><div class="h5 mb-0 fw-bold"><?= $stats['attendance_rate'] ?? 0 ?>%</div></div>
                    <div class="col-auto"><i class="fas fa-calendar-check fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col"><div class="text-xs fw-bold text-warning text-uppercase mb-1">درآمد ماه</div><div class="h5 mb-0 fw-bold"><?= number_format($stats['monthly_revenue'] ?? 0) ?> ت</div></div>
                    <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-xl-7 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i>توزیع دانش‌آموزان در کلاس‌ها</h6></div>
            <div class="card-body"><canvas id="classDistributionChart" height="300"></canvas></div>
        </div>
    </div>
    <div class="col-xl-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-calendar-day me-2"></i>حضور و غیاب امروز</h6></div>
            <div class="card-body">
                <?php if (!empty($todayAttendance)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead><tr><th>کلاس</th><th>کل</th><th>حاضر</th><th>غایب</th><th>نرخ</th></tr></thead>
                        <tbody>
                            <?php foreach ($todayAttendance as $att): 
                                $rate = $att['total_students'] > 0 ? round(($att['present'] / $att['total_students']) * 100) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($att['class_name']) ?></td>
                                <td><?= $att['total_students'] ?></td>
                                <td class="text-success"><?= $att['present'] ?></td>
                                <td class="text-danger"><?= $att['absent'] ?></td>
                                <td><span class="badge bg-<?= $rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger') ?>"><?= $rate ?>%</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center text-muted py-3">داده‌ای برای امروز ثبت نشده است</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments & Upcoming Exams -->
<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-success"><i class="fas fa-money-bill-wave me-2"></i>آخرین پرداخت‌ها</h6>
                <a href="<?= url('/fees') ?>" class="btn btn-sm btn-outline-success">مشاهده همه</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentPayments)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead><tr><th>دانش‌آموز</th><th>مبلغ</th><th>تاریخ</th><th>دسته‌بندی</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentPayments as $pay): ?>
                            <tr>
                                <td><?= htmlspecialchars($pay['student_first_name'] . ' ' . $pay['student_last_name']) ?></td>
                                <td class="text-success fw-bold"><?= number_format($pay['amount']) ?> ت</td>
                                <td><small><?= date('Y/m/d', strtotime($pay['payment_date'])) ?></small></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($pay['category_name']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center text-muted py-3">پرداختی ثبت نشده است</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-warning"><i class="fas fa-calendar-alt me-2"></i>آزمون‌های پیش رو</h6>
                <a href="<?= url('/exams') ?>" class="btn btn-sm btn-outline-warning">مشاهده همه</a>
            </div>
            <div class="card-body">
                <?php if (!empty($upcomingExams)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($upcomingExams as $exam): ?>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($exam['subject_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($exam['class_name']) ?> | <?= htmlspecialchars($exam['exam_type']) ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning text-dark"><?= date('Y/m/d', strtotime($exam['exam_date'])) ?></span>
                                <br><small class="text-muted"><?= $exam['start_time'] ?? '' ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-muted py-3">آزمونی برای آینده نزدیک برنامه‌ریزی نشده است</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classNames = <?= json_encode(array_column($classDistribution ?? [], 'class_name')) ?>;
    const studentCounts = <?= json_encode(array_map('intval', array_column($classDistribution ?? [], 'student_count'))) ?>;
    
    new Chart(document.getElementById('classDistributionChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: classNames,
            datasets: [{
                label: 'تعداد دانش‌آموزان',
                data: studentCounts,
                backgroundColor: '#4e73df',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>