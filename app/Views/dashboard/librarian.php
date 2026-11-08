<?php
$content = function() use ($stats, $recentBorrowings, $popularBooks, $user) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-book-reader me-2"></i>داشبورد کتابداری</h1>
    <a href="<?= url('/library/books/add') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>افزودن کتاب</a>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body text-center"><i class="fas fa-book fa-2x text-primary mb-2"></i><div class="h5"><?= number_format($stats['total_books'] ?? 0) ?></div><small>کل کتاب‌ها</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body text-center"><i class="fas fa-check-circle fa-2x text-success mb-2"></i><div class="h5"><?= number_format($stats['available_books'] ?? 0) ?></div><small>کتاب‌های موجود</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body text-center"><i class="fas fa-exchange-alt fa-2x text-warning mb-2"></i><div class="h5"><?= number_format($stats['books_borrowed_today'] ?? 0) ?></div><small>امانت امروز</small></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-danger shadow h-100"><div class="card-body text-center"><i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i><div class="h5"><?= number_format($stats['overdue_books'] ?? 0) ?></div><small>کتاب‌های دیرکرد</small></div></div></div>
</div>

<!-- Recent Borrowings & Popular Books -->
<div class="row">
    <div class="col-xl-7 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>آخرین امانت‌ها</h6></div><div class="card-body">
            <?php if (!empty($recentBorrowings)): ?>
            <div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>کتاب</th><th>امانت‌گیرنده</th><th>تاریخ امانت</th><th>تاریخ بازگشت</th><th>وضعیت</th></tr></thead><tbody>
                <?php foreach ($recentBorrowings as $rb): ?>
                <tr><td><strong><?= htmlspecialchars($rb['book_title']) ?></strong></td><td><?= htmlspecialchars($rb['first_name'] . ' ' . $rb['last_name']) ?></td><td><?= $rb['borrow_date'] ?></td><td><?= $rb['due_date'] ?></td><td><span class="badge bg-<?= $rb['status'] === 'returned' ? 'success' : ($rb['status'] === 'overdue' ? 'danger' : 'warning') ?>"><?= $rb['status'] === 'borrowed' ? 'امانت' : ($rb['status'] === 'returned' ? 'برگشتی' : 'دیرکرد') ?></span></td></tr>
                <?php endforeach; ?>
            </tbody></table></div>
            <?php else: ?><p class="text-center text-muted py-3">امانتی ثبت نشده</p><?php endif; ?>
        </div></div>
    </div>
    <div class="col-xl-5 mb-4">
        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-star me-2"></i>کتاب‌های محبوب</h6></div><div class="card-body">
            <?php if (!empty($popularBooks)): ?>
                <?php foreach ($popularBooks as $book): ?>
                <div class="d-flex justify-content-between p-2 border-bottom"><span><strong><?= htmlspecialchars($book['title']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($book['author']) ?></small></span><span class="badge bg-info"><?= $book['borrow_count'] ?> امانت</span></div>
                <?php endforeach; ?>
            <?php else: ?><p class="text-center text-muted py-3">داده‌ای موجود نیست</p><?php endif; ?>
        </div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>