<?php
$title = 'مدیریت کتابخانه';
$books = $books ?? [];
$categories = $categories ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
$categoryFilter = $categoryFilter ?? '';

$content = function() use ($books, $categories, $currentPage, $totalPages, $total, $search, $categoryFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-book-open me-2"></i>مدیریت کتابخانه</h1>
    <div>
        <a href="<?= url('/library/books/add') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>افزودن کتاب</a>
        <a href="<?= url('/library/borrowings') ?>" class="btn btn-info ms-1"><i class="fas fa-exchange-alt me-1"></i>امانت‌ها</a>
    </div>
</div>

<!-- Search & Filter -->
<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/library/books') ?>" method="GET" class="row g-3">
    <div class="col-md-6"><input type="text" class="form-control" name="search" placeholder="جستجو بر اساس عنوان، نویسنده، ISBN یا کد کتاب..." value="<?= htmlspecialchars($search) ?>"></div>
    <div class="col-md-3"><select class="form-select" name="category"><option value="">همه دسته‌ها</option><?php foreach ($categories as $cat): ?><option value="<?= htmlspecialchars($cat['category']) ?>" <?= $categoryFilter === $cat['category'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>جستجو</button></div>
</form></div></div>

<!-- Books Grid -->
<div class="row">
    <?php if (!empty($books)): ?>
        <?php foreach ($books as $book): ?>
        <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="fas fa-book fa-3x <?= ($book['available_copies'] ?? 0) > 0 ? 'text-primary' : 'text-danger' ?>"></i></div>
                    <h6 class="fw-bold"><?= htmlspecialchars(mb_substr($book['title'], 0, 40)) ?><?= mb_strlen($book['title']) > 40 ? '...' : '' ?></h6>
                    <small class="text-muted d-block"><?= htmlspecialchars($book['author'] ?? 'نامشخص') ?></small>
                    <code class="d-block my-1"><?= htmlspecialchars($book['book_code']) ?></code>
                    <?php if ($book['isbn']): ?><small class="text-muted d-block">ISBN: <?= htmlspecialchars($book['isbn']) ?></small><?php endif; ?>
                    <div class="mt-2">
                        <span class="badge bg-<?= ($book['available_copies'] ?? 0) > 0 ? 'success' : 'danger' ?>"><?= $book['available_copies'] ?? 0 ?> موجود</span>
                        <span class="badge bg-secondary"><?= $book['total_copies'] ?? 0 ?> کل</span>
                        <?php if (($book['borrowed_count'] ?? 0) > 0): ?><span class="badge bg-warning"><?= $book['borrowed_count'] ?> امانت</span><?php endif; ?>
                    </div>
                    <div class="d-flex gap-1 mt-3">
                        <?php if (hasPermission('edit_books')): ?><a href="<?= url('/library/books/' . $book['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1"><i class="fas fa-edit"></i></a><?php endif; ?>
                        <?php if ($book['available_copies'] > 0): ?><button class="btn btn-outline-success btn-sm flex-grow-1" onclick="issueBook(<?= $book['id'] ?>)"><i class="fas fa-hand-holding"></i></button><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-book-open fa-4x mb-3 d-block"></i><h5>کتابی یافت نشد</h5></div></div>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?><nav><ul class="pagination justify-content-center"><?php for($p=1;$p<=$totalPages;$p++): ?><li class="page-item <?=$p==$currentPage?'active':''?>"><a class="page-link" href="?page=<?=$p?>&search=<?=urlencode($search)?>&category=<?=urlencode($categoryFilter)?>"><?=$p?></a></li><?php endfor; ?></ul></nav><?php endif; ?>

<script>
function issueBook(bookId) { Swal.fire({ title: 'امانت کتاب', html: '<input id="userId" class="form-control" placeholder="کد کاربری یا نام امانت‌گیرنده...">', showCancelButton: true, confirmButtonText: 'ثبت امانت', cancelButtonText: 'انصراف' }).then(r => { if (r.isConfirmed) { /* submit borrow */ } }); }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>