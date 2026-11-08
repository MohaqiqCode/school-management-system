<?php
$content = function() use ($stats, $branchStudents, $monthlyRevenue, $expenseVsRevenue, 
                               $recentActivities, $enrollmentTrend, $topClasses, $systemHealth, $user) {
    ob_start();
?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-tachometer-alt me-2"></i>داشبورد مدیریت
    </h1>
    <div>
        <a href="<?= url('/reports') ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50 me-2"></i>گزارش کامل
        </a>
        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()">
            <i class="fas fa-print me-1"></i> چاپ
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Branches -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">کل شعب</div>
                        <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($stats['total_branches'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="<?= url('/branches') ?>" class="small text-decoration-none">
                        مشاهده جزئیات <i class="fas fa-arrow-left me-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Students -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">کل دانش‌آموزان</div>
                        <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($stats['total_students'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="<?= url('/students') ?>" class="small text-decoration-none">
                        مشاهده جزئیات <i class="fas fa-arrow-left me-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Teachers -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">کل معلمان</div>
                        <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($stats['total_teachers'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="<?= url('/teachers') ?>" class="small text-decoration-none">
                        مشاهده جزئیات <i class="fas fa-arrow-left me-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">درآمد ماه جاری</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">
                            <?= number_format($stats['monthly_revenue'] ?? 0) ?> تومان
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="<?= url('/reports/financial') ?>" class="small text-decoration-none">
                        مشاهده جزئیات <i class="fas fa-arrow-left me-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row - More Stats -->
<div class="row">
    <!-- Total Employees -->
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs fw-bold text-secondary mb-1">کارمندان</div>
                <div class="h4 mb-0"><?= number_format($stats['total_employees'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Total Parents -->
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs fw-bold text-secondary mb-1">والدین</div>
                <div class="h4 mb-0"><?= number_format($stats['total_parents'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Active Classes -->
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs fw-bold text-secondary mb-1">کلاس‌های فعال</div>
                <div class="h4 mb-0"><?= number_format($stats['active_classes'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Today's Attendance -->
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs fw-bold text-secondary mb-1">حضور امروز</div>
                <div class="h4 mb-0"><?= $stats['today_attendance_percentage'] ?? 0 ?>%</div>
            </div>
        </div>
    </div>
    
    <!-- Active Borrowings -->
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs fw-bold text-secondary mb-1">امانت‌های فعال</div>
                <div class="h4 mb-0"><?= number_format($stats['active_borrowings'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    
    <!-- Pending Fees -->
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs fw-bold text-danger mb-1">شهریه معوق</div>
                <div class="h4 mb-0"><?= number_format($stats['pending_fees'] ?? 0) ?> ت</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Revenue Chart -->
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-chart-line me-2"></i>روند درآمد ماهانه
                </h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        امسال
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">امسال</a></li>
                        <li><a class="dropdown-item" href="#">سال گذشته</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Student Distribution -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>توزیع دانش‌آموزان در شعب
                </h6>
            </div>
            <div class="card-body">
                <canvas id="studentDistributionChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Expense vs Revenue -->
<div class="row">
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-balance-scale me-2"></i>درآمد در مقابل هزینه (ماه جاری)
                </h6>
            </div>
            <div class="card-body">
                <canvas id="expenseRevenueChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Enrollment Trend -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-user-plus me-2"></i>روند ثبت نام
                </h6>
            </div>
            <div class="card-body">
                <canvas id="enrollmentChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Classes & Recent Activities -->
<div class="row">
    <!-- Top Performing Classes -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-trophy me-2"></i>کلاس‌های برتر
                </h6>
                <span class="badge bg-success">میانگین نمرات</span>
            </div>
            <div class="card-body">
                <?php if (!empty($topClasses)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>کلاس</th>
                                <th>دانش‌آموزان</th>
                                <th>میانگین نمره</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topClasses as $index => $class): ?>
                            <tr>
                                <td>
                                    <?php if ($index < 3): ?>
                                    <span class="badge bg-<?= ['warning', 'secondary', 'info'][$index] ?>">
                                        <?= $index + 1 ?>
                                    </span>
                                    <?php else: ?>
                                    <?= $index + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($class['class_name']) ?></td>
                                <td><?= $class['student_count'] ?? 0 ?> نفر</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= $class['avg_marks'] ?? 0 ?></span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= min(($class['avg_marks'] ?? 0), 100) ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center text-muted py-3">داده‌ای موجود نیست</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-history me-2"></i>فعالیت‌های اخیر
                </h6>
                <a href="<?= url('/audit-logs') ?>" class="btn btn-sm btn-outline-primary">
                    مشاهده همه
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)): ?>
                <div class="timeline">
                    <?php foreach ($recentActivities as $activity): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker 
                            <?php 
                            echo match($activity['action']) {
                                'login' => 'bg-success',
                                'logout' => 'bg-secondary',
                                'create' => 'bg-primary',
                                'update' => 'bg-info',
                                'delete' => 'bg-danger',
                                default => 'bg-warning'
                            };
                            ?>">
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <strong class="small">
                                    <?= htmlspecialchars($activity['user_first_name'] ?? 'سیستم') ?> 
                                    <?= htmlspecialchars($activity['user_last_name'] ?? '') ?>
                                </strong>
                                <small class="text-muted">
                                    <?= timeAgo($activity['created_at']) ?>
                                </small>
                            </div>
                            <p class="mb-0 small text-muted">
                                <?= htmlspecialchars($activity['description'] ?? '') ?>
                            </p>
                            <small>
                                <span class="badge bg-light text-dark">
                                    <?= htmlspecialchars($activity['module'] ?? '') ?>
                                </span>
                                <span class="badge bg-light text-dark">
                                    <?= htmlspecialchars($activity['action'] ?? '') ?>
                                </span>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-muted py-3">فعالیتی ثبت نشده است</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Health -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-heartbeat me-2"></i>وضعیت سیستم
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="mb-2">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="h5"><?= number_format($systemHealth['total_users'] ?? 0) ?></div>
                        <small class="text-muted">کل کاربران</small>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <i class="fas fa-plug fa-2x text-success"></i>
                        </div>
                        <div class="h5"><?= number_format($systemHealth['active_sessions'] ?? 0) ?></div>
                        <small class="text-muted">نشست‌های فعال</small>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <i class="fas fa-database fa-2x text-info"></i>
                        </div>
                        <div class="h5"><?= $systemHealth['database_size'] ?? 0 ?> MB</div>
                        <small class="text-muted">حجم دیتابیس</small>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <i class="fas fa-hdd fa-2x text-warning"></i>
                        </div>
                        <div class="h5"><?= $systemHealth['storage_used'] ?? 0 ?> MB</div>
                        <small class="text-muted">فضای مصرفی</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($monthlyRevenue ?? [], 'month_name')) ?>,
            datasets: [{
                label: 'درآمد (تومان)',
                data: <?= json_encode(array_map('floatval', array_column($monthlyRevenue ?? [], 'total'))) ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                pointBackgroundColor: '#4e73df',
                pointBorderColor: '#fff',
                pointHoverRadius: 5,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'درآمد: ' + new Intl.NumberFormat('fa-IR').format(context.parsed.y) + ' تومان';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fa-IR').format(value) + ' ت';
                        }
                    }
                }
            }
        }
    });
    
    // Student Distribution Pie Chart
    const studentDistCtx = document.getElementById('studentDistributionChart').getContext('2d');
    const branchNames = <?= json_encode(array_column($branchStudents ?? [], 'name')) ?>;
    const studentCounts = <?= json_encode(array_map('intval', array_column($branchStudents ?? [], 'student_count'))) ?>;
    
    const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#2e59d9'];
    
    new Chart(studentDistCtx, {
        type: 'doughnut',
        data: {
            labels: branchNames,
            datasets: [{
                data: studentCounts,
                backgroundColor: colors.slice(0, branchNames.length),
                hoverBorderColor: 'rgba(234, 236, 244, 1)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    rtl: true,
                    labels: { padding: 20, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' نفر (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    
    // Expense vs Revenue Bar Chart
    const expRevCtx = document.getElementById('expenseRevenueChart').getContext('2d');
    const expRevData = <?= json_encode($expenseVsRevenue ?? []) ?>;
    
    new Chart(expRevCtx, {
        type: 'bar',
        data: {
            labels: expRevData.map(d => d.type === 'Revenue' ? 'درآمد' : 'هزینه'),
            datasets: [{
                data: expRevData.map(d => parseFloat(d.total)),
                backgroundColor: ['#1cc88a', '#e74a3b'],
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => new Intl.NumberFormat('fa-IR').format(v) + ' ت'
                    }
                }
            }
        }
    });
    
    // Enrollment Trend Chart
    const enrollCtx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(enrollCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($enrollmentTrend ?? [], 'month')) ?>,
            datasets: [{
                label: 'ثبت نام جدید',
                data: <?= json_encode(array_map('intval', array_column($enrollmentTrend ?? [], 'count'))) ?>,
                backgroundColor: '#36b9cc',
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
</script>

<style>
.timeline {
    position: relative;
    padding-right: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    border-right: 2px solid #e3e6f0;
    padding-right: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
    border-right-color: transparent;
}

.timeline-marker {
    position: absolute;
    right: -6px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    padding-bottom: 10px;
}
</style>

<?php
    return ob_get_clean();
};

// Render in main layout
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>