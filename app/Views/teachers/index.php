<?php
$title = 'مدیریت معلمان';
$teachers = $teachers ?? [];
$departments = $departments ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
$departmentFilter = $departmentFilter ?? '';
$statusFilter = $statusFilter ?? '';

$content = function() use ($teachers, $departments, $currentPage, $totalPages, $total, $search, $departmentFilter, $statusFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chalkboard-teacher me-2"></i>مدیریت معلمان</h1>
    <?php if (hasPermission('create_teachers')): ?><a href="<?= url('/teachers/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>ثبت معلم جدید</a><?php endif; ?>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/teachers') ?>" method="GET" class="row g-3">
            <div class="col-md-4"><input type="text" class="form-control" name="search" placeholder="جستجو..." value="<?= htmlspecialchars($search) ?>"></div>
            <div class="col-md-3"><select class="form-select" name="department_id"><option value="">همه دپارتمان‌ها</option><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= $departmentFilter == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><select class="form-select" name="status"><option value="">همه</option><option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>فعال</option><option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>غیرفعال</option></select></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>فیلتر</button></div>
        </form>
    </div>
</div>

<!-- Teachers Grid -->
<div class="row">
    <?php if (!empty($teachers)): ?>
        <?php foreach ($teachers as $teacher): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <img src="<?= $teacher['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle mb-3" width="75" height="75">
                    <h5 class="fw-bold"><?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></h5>
                    <small class="text-muted d-block"><?= htmlspecialchars($teacher['department_name'] ?? '') ?></small>
                    <code class="d-block mb-2"><?= htmlspecialchars($teacher['teacher_code']) ?></code>
                    <div class="d-flex justify-content-center gap-1 mb-2 flex-wrap">
                        <span class="badge bg-info"><?= $teacher['class_count'] ?? 0 ?> کلاس</span>
                        <span class="badge bg-success"><?= $teacher['subject_count'] ?? 0 ?> درس</span>
                        <?= $teacher['user_active'] ? '<span class="badge bg-success">فعال</span>' : '<span class="badge bg-danger">غیرفعال</span>' ?>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="<?= url('/teachers/' . $teacher['id']) ?>" class="btn btn-outline-info btn-sm flex-grow-1"><i class="fas fa-eye"></i></a>
                        <a href="<?= url('/teachers/' . $teacher['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1"><i class="fas fa-edit"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-chalkboard-teacher fa-4x mb-3 d-block"></i><h5>معلمی یافت نشد</h5></div></div>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?><nav><ul class="pagination justify-content-center"><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="page-item <?= $p == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&department_id=<?= $departmentFilter ?>&status=<?= $statusFilter ?>"><?= $p ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>