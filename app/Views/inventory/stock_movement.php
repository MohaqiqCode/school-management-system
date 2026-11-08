<?php
$title = 'گردش موجودی';
$item = $item ?? null;
$movements = $movements ?? [];

$content = function() use ($item, $movements) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-exchange-alt me-2"></i>گردش موجودی<?= $item ? ' - ' . htmlspecialchars($item['item_name']) : '' ?></h1>
    <a href="<?= url('/inventory') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<?php if ($item): ?>
<div class="card shadow mb-4"><div class="card-body"><div class="row text-center">
    <div class="col-md-4"><small class="text-muted">کد کالا</small><div class="h5"><code><?= htmlspecialchars($item['item_code']) ?></code></div></div>
    <div class="col-md-4"><small class="text-muted">موجودی فعلی</small><div class="h5 text-<?= ($item['quantity_in_stock'] ?? 0) > ($item['reorder_level'] ?? 10) ? 'success' : 'danger' ?>"><?= $item['quantity_in_stock'] ?? 0 ?> <?= htmlspecialchars($item['unit'] ?? 'عدد') ?></div></div>
    <div class="col-md-4"><small class="text-muted">حد تجدید</small><div class="h5"><?= $item['reorder_level'] ?? 10 ?></div></div>
</div></div></div>
<?php endif; ?>

<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">تاریخچه گردش</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>تاریخ</th><th>نوع</th><th>تعداد</th><th>موجودی قبل</th><th>موجودی بعد</th><th>کاربر</th><th>توضیحات</th></tr></thead><tbody>
<?php if (!empty($movements)): ?>
    <?php foreach ($movements as $i => $m): ?>
    <tr><td><?= $i + 1 ?></td><td><?= date('Y/m/d H:i', strtotime($m['created_at'] ?? '')) ?></td><td><span class="badge bg-<?= $m['type'] === 'in' ? 'success' : 'danger' ?>"><?= $m['type'] === 'in' ? 'ورود' : 'خروج' ?></span></td><td class="fw-bold"><?= $m['quantity'] ?> <?= htmlspecialchars($item['unit'] ?? 'عدد') ?></td><td><?= $m['before_quantity'] ?></td><td><?= $m['after_quantity'] ?></td><td><?= htmlspecialchars($m['user_name'] ?? '-') ?></td><td><?= htmlspecialchars($m['notes'] ?? '-') ?></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="8" class="text-center py-4 text-muted">گردشی ثبت نشده است</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>