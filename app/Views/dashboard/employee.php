<?php
$content = function() use ($user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-briefcase me-2"></i>داشبورد کارمند</h1>
    <span class="badge bg-secondary"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
</div>

<!-- Welcome Card -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow"><div class="card-body text-center py-5">
            <i class="fas fa-briefcase fa-4x text-primary mb-3"></i>
            <h4>به پنل مدیریت خوش آمدید</h4>
            <p class="text-muted">از منوی سمت راست می‌توانید به بخش‌های مختلف دسترسی داشته باشید.</p>
        </div></div>
    </div>
</div>

<!-- Quick Access -->
<div class="row">
    <div class="col-md-4 mb-4"><a href="<?= url('/attendance/students') ?>" class="card border-left-primary shadow h-100 text-decoration-none"><div class="card-body text-center"><i class="fas fa-calendar-check fa-3x text-primary mb-3"></i><h6 class="text-dark">حضور و غیاب</h6></div></a></div>
    <div class="col-md-4 mb-4"><a href="<?= url('/inventory') ?>" class="card border-left-success shadow h-100 text-decoration-none"><div class="card-body text-center"><i class="fas fa-boxes fa-3x text-success mb-3"></i><h6 class="text-dark">مدیریت انبار</h6></div></a></div>
    <div class="col-md-4 mb-4"><a href="<?= url('/students') ?>" class="card border-left-info shadow h-100 text-decoration-none"><div class="card-body text-center"><i class="fas fa-user-graduate fa-3x text-info mb-3"></i><h6 class="text-dark">دانش‌آموزان</h6></div></a></div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>