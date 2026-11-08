<?php
$title = 'دسته‌بندی‌های انبار';
$categories = $categories ?? [];

$content = function() use ($categories) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-tags me-2"></i>دسته‌بندی‌های انبار</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="fas fa-plus me-1"></i>دسته‌بندی جدید</button>
</div>

<div class="row">
    <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="fw-bold"><?= htmlspecialchars($cat['category_name']) ?></h6>
                    <?php if ($cat['description']): ?><p class="small text-muted"><?= htmlspecialchars($cat['description']) ?></p><?php endif; ?>
                    <div class="d-flex gap-2"><span class="badge bg-info"><?= $cat['item_count'] ?? 0 ?> کالا</span><span class="badge bg-success"><?= number_format($cat['total_value'] ?? 0) ?> ت</span></div>
                    <?php if (($cat['low_stock_count'] ?? 0) > 0): ?><span class="badge bg-warning mt-1"><?= $cat['low_stock_count'] ?> کمبود</span><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?><div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-tags fa-4x mb-3 d-block"></i><h5>دسته‌بندی تعریف نشده</h5></div></div><?php endif; ?>
</div>

<!-- Create Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="<?= url('/inventory/categories') ?>" method="POST"><div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>دسته‌بندی جدید</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><div class="mb-3"><label class="form-label">نام دسته‌بندی</label><input type="text" class="form-control" name="category_name" required></div><div class="mb-3"><label class="form-label">توضیحات</label><textarea class="form-control" name="description" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div></form></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>