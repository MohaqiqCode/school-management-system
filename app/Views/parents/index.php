<?php
$title = 'مدیریت والدین';
$parents = $parents ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';

$content = function() use ($parents, $currentPage, $totalPages, $total, $search) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-friends me-2"></i>مدیریت والدین</h1>
</div>

<!-- Search -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/parents') ?>" method="GET" class="row g-3">
            <div class="col-md-10"><input type="text" class="form-control" name="search" placeholder="جستجو بر اساس نام، ایمیل یا کد..." value="<?= htmlspecialchars($search) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>جستجو</button></div>
        </form>
    </div>
</div>

<!-- Parents Cards -->
<div class="row">
    <?php if (!empty($parents)): ?>
        <?php foreach ($parents as $parent): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <img src="<?= $parent['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle mb-3" width="70" height="70">
                    <h5 class="fw-bold"><?= htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']) ?></h5>
                    <small class="text-muted d-block"><?= htmlspecialchars($parent['email'] ?? '') ?></small>
                    <small class="text-muted d-block mb-2"><?= htmlspecialchars($parent['phone'] ?? '') ?></small>
                    <span class="badge bg-info mb-2"><?= $parent['children_count'] ?? 0 ?> فرزند</span>
                    <div class="d-flex gap-2 mt-2">
                        <a href="<?= url('/parents/' . $parent['id']) ?>" class="btn btn-outline-info btn-sm flex-grow-1"><i class="fas fa-eye me-1"></i>مشاهده</a>
                        <a href="<?= url('/parents/' . $parent['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1"><i class="fas fa-edit me-1"></i>ویرایش</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-user-friends fa-4x mb-3 d-block"></i><h5>والدی یافت نشد</h5></div></div>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav><ul class="pagination justify-content-center"><?php for ($p = 1; $p <= $totalPages; $p++): ?><li class="page-item <?= $p == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a></li><?php endfor; ?></ul></nav>
<?php endif; ?>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>