<?php
$content = function() use ($childrenData, $user, $parent) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-friends me-2"></i>داشبورد والدین</h1>
    <span class="badge bg-info"><?= count($childrenData ?? []) ?> فرزند</span>
</div>

<?php if (empty($childrenData)): ?>
<div class="text-center py-5"><i class="fas fa-child fa-4x text-muted mb-3"></i><h5>هیچ دانش‌آموزی به شما متصل نیست</h5><p class="text-muted">لطفاً با مدیریت مدرسه تماس بگیرید.</p></div>
<?php else: ?>
    <?php foreach ($childrenData as $data): $child = $data['child']; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">
                <img src="<?= $child['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle me-2" width="40" height="40">
                <?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?>
            </h6>
            <div><span class="badge bg-primary me-2"><?= htmlspecialchars($child['class_name'] ?? '') ?></span><span class="badge bg-success"><?= htmlspecialchars($child['section_name'] ?? '') ?></span></div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Attendance -->
                <div class="col-md-4 mb-3"><div class="border rounded p-3 text-center"><i class="fas fa-calendar-check fa-2x text-success mb-2"></i><div class="h4"><?= $data['attendance']['percentage'] ?? 0 ?>%</div><small class="text-muted">نرخ حضور (۳۰ روز)</small><div class="mt-2 small"><span class="text-success"><?= $data['attendance']['present'] ?? 0 ?> حاضر</span> | <span class="text-danger"><?= $data['attendance']['absent'] ?? 0 ?> غایب</span></div></div></div>
                <!-- Latest Result -->
                <div class="col-md-4 mb-3"><div class="border rounded p-3 text-center"><i class="fas fa-star fa-2x text-warning mb-2"></i><div class="h4"><?= $data['results'][0]['marks_obtained'] ?? '-' ?></div><small class="text-muted">آخرین نمره (<?= htmlspecialchars($data['results'][0]['subject_name'] ?? '') ?>)</small></div></div>
                <!-- Fees -->
                <div class="col-md-4 mb-3"><div class="border rounded p-3 text-center"><i class="fas fa-money-bill fa-2x text-<?= ($data['fees'][0]['balance_amount'] ?? 0) > 0 ? 'danger' : 'success' ?> mb-2"></i><div class="h4"><?= number_format($data['fees'][0]['balance_amount'] ?? 0) ?> ت</div><small class="text-muted">شهریه معوق</small></div></div>
            </div>
            <a href="<?= url('/students/' . $child['id']) ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i>مشاهده جزئیات کامل</a>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>