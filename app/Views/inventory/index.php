<?php
$title = 'داشبورد انبار';
$items = $items ?? [];
$categories = $categories ?? [];
$lowStock = $lowStock ?? [];
$categoryFilter = $categoryFilter ?? '';

$content = function() use ($items, $categories, $lowStock, $categoryFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-boxes me-2"></i>داشبورد انبار</h1>
    <div>
        <a href="<?= url('/inventory/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>افزودن کالا</a>
        <a href="<?= url('/inventory/categories') ?>" class="btn btn-info ms-1"><i class="fas fa-tags me-1"></i>دسته‌بندی‌ها</a>
    </div>
</div>

<!-- Low Stock Alert -->
<?php if (!empty($lowStock)): ?>
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
    <div><strong>هشدار کمبود موجودی!</strong> <?= count($lowStock) ?> کالا زیر حد مجاز است. <a href="#lowStockSection" class="alert-link">مشاهده</a></div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-primary mb-1">کل کالاها</div><div class="h5 mb-0"><?= number_format(count($items ?? [])) ?></div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-warning mb-1">کمبود موجودی</div><div class="h5 mb-0"><?= number_format(count($lowStock ?? [])) ?></div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-danger shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-danger mb-1">ناموجود</div><div class="h5 mb-0"><?= number_format(count(array_filter($items ?? [], fn($i) => ($i['quantity_in_stock'] ?? 0) == 0))) ?></div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">ارزش کل</div><div class="h5 mb-0"><?= number_format(array_sum(array_map(fn($i) => ($i['quantity_in_stock'] ?? 0) * ($i['unit_price'] ?? 0), $items ?? []))) ?> ت</div></div></div></div>
</div>

<!-- Filter -->
<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/inventory') ?>" method="GET" class="row g-3"><div class="col-md-8"><select class="form-select" name="category_id" onchange="this.form.submit()"><option value="">همه دسته‌بندی‌ها</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-4"><a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary w-100">پاک کردن فیلتر</a></div></form></div></div>

<!-- Items Table -->
<div class="card shadow mb-4"><div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold text-primary">لیست کالاها</h6><span class="badge bg-primary"><?= count($items ?? []) ?> کالا</span></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>کد</th><th>نام کالا</th><th>دسته‌بندی</th><th>موجودی</th><th>حد تجدید</th><th>قیمت واحد</th><th>ارزش کل</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
<?php if (!empty($items)): ?>
    <?php foreach ($items as $i => $item): $qty = $item['quantity_in_stock'] ?? 0; $reorder = $item['reorder_level'] ?? 10; $status = $qty == 0 ? 'out' : ($qty <= $reorder ? 'low' : 'ok'); ?>
    <tr class="<?= $status === 'out' ? 'table-danger' : ($status === 'low' ? 'table-warning' : '') ?>">
        <td><?= $i + 1 ?></td>
        <td><code><?= htmlspecialchars($item['item_code']) ?></code></td>
        <td><strong><?= htmlspecialchars($item['item_name']) ?></strong><?php if ($item['description']): ?><br><small class="text-muted"><?= htmlspecialchars(mb_substr($item['description'], 0, 40)) ?></small><?php endif; ?></td>
        <td><?= htmlspecialchars($item['category_name'] ?? '-') ?></td>
        <td class="fw-bold <?= $status === 'out' ? 'text-danger' : ($status === 'low' ? 'text-warning' : 'text-success') ?>"><?= $qty ?> <?= htmlspecialchars($item['unit'] ?? 'عدد') ?></td>
        <td><?= $reorder ?></td>
        <td><?= number_format($item['unit_price'] ?? 0) ?> ت</td>
        <td><?= number_format($qty * ($item['unit_price'] ?? 0)) ?> ت</td>
        <td><span class="badge bg-<?= $status === 'ok' ? 'success' : ($status === 'low' ? 'warning' : 'danger') ?>"><?= $status === 'ok' ? 'موجود' : ($status === 'low' ? 'کمبود' : 'ناموجود') ?></span></td>
        <td>
            <div class="btn-group btn-group-sm">
                <a href="<?= url('/inventory/' . $item['id'] . '/edit') ?>" class="btn btn-warning" title="ویرایش"><i class="fas fa-edit"></i></a>
                <button class="btn btn-success" onclick="stockMovement(<?= $item['id'] ?>, 'in')" title="ورود"><i class="fas fa-plus"></i></button>
                <button class="btn btn-danger" onclick="stockMovement(<?= $item['id'] ?>, 'out')" title="خروج" <?= $qty <= 0 ? 'disabled' : '' ?>><i class="fas fa-minus"></i></button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="10" class="text-center py-4 text-muted">کالایی یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<!-- Low Stock Section -->
<?php if (!empty($lowStock)): ?>
<div class="card shadow mb-4 border-warning" id="lowStockSection"><div class="card-header bg-warning"><h6 class="m-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>کالاهای با موجودی کم</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>کالا</th><th>موجودی</th><th>حد تجدید</th><th>تأمین‌کننده</th><th>عملیات</th></tr></thead><tbody>
    <?php foreach ($lowStock as $ls): ?>
    <tr><td><strong><?= htmlspecialchars($ls['item_name']) ?></strong></td><td class="text-danger fw-bold"><?= $ls['quantity_in_stock'] ?></td><td><?= $ls['reorder_level'] ?></td><td><?= htmlspecialchars($ls['supplier_name'] ?? '-') ?></td><td><button class="btn btn-sm btn-success" onclick="stockMovement(<?= $ls['id'] ?>, 'in')"><i class="fas fa-plus me-1"></i>ورود</button></td></tr>
    <?php endforeach; ?>
</tbody></table></div></div></div>
<?php endif; ?>

<!-- Stock Movement Modal -->
<div class="modal fade" id="stockModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><form id="stockForm"><div class="modal-header" id="stockModalHeader"><h5 class="modal-title" id="stockModalTitle"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="item_id" id="stockItemId"><input type="hidden" name="type" id="stockType"><div class="mb-3"><label class="form-label">تعداد</label><input type="number" class="form-control" name="quantity" id="stockQuantity" min="1" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn" id="stockSubmitBtn"></button></div></form></div></div></div>

<script>
function stockMovement(id, type) {
    document.getElementById('stockItemId').value = id;
    document.getElementById('stockType').value = type;
    document.getElementById('stockQuantity').value = 1;
    const isIn = type === 'in';
    document.getElementById('stockModalHeader').className = `modal-header ${isIn ? 'bg-success' : 'bg-danger'} text-white`;
    document.getElementById('stockModalTitle').textContent = isIn ? 'ورود کالا' : 'خروج کالا';
    document.getElementById('stockSubmitBtn').className = `btn ${isIn ? 'btn-success' : 'btn-danger'}`;
    document.getElementById('stockSubmitBtn').innerHTML = `<i class="fas fa-${isIn ? 'plus' : 'minus'} me-1"></i>${isIn ? 'ثبت ورود' : 'ثبت خروج'}`;
    new bootstrap.Modal(document.getElementById('stockModal')).show();
}
document.getElementById('stockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('stockItemId').value;
    const type = document.getElementById('stockType').value;
    const url = type === 'in' ? '<?= url('/inventory') ?>/' + id + '/stock-in' : '<?= url('/inventory') ?>/' + id + '/stock-out';
    const fd = new FormData(this);
    fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>' } })
        .then(r => r.json()).then(d => { if (d.success) { toastr.success(d.message); setTimeout(() => location.reload(), 800); } else toastr.error(d.message); });
});
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>