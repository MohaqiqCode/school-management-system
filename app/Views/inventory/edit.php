<?php
$title = 'ویرایش کالا';
$item = $item ?? [];
$categories = $categories ?? [];
$old = $_SESSION['old'] ?? $item;

$content = function() use ($item, $categories, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>ویرایش کالا</h1><p class="text-muted mb-0"><?= htmlspecialchars($item['item_name'] ?? '') ?> <code>(<?= htmlspecialchars($item['item_code'] ?? '') ?>)</code></p></div>
    <div>
        <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $item['id'] ?>)"><i class="fas fa-trash me-1"></i>حذف</button>
        <a href="<?= url('/inventory') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row"><div class="col-xl-8 col-lg-7"><div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">ویرایش اطلاعات</h6></div><div class="card-body">
    <form action="<?= url('/inventory/' . ($item['id'] ?? '')) ?>" method="POST"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="PUT">
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">کد کالا</label><input type="text" class="form-control" value="<?= htmlspecialchars($item['item_code'] ?? '') ?>" disabled></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">نام کالا</label><input type="text" class="form-control" name="item_name" value="<?= htmlspecialchars($old['item_name'] ?? $item['item_name'] ?? '') ?>" required></div></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">دسته‌بندی</label><select class="form-select" name="category_id"><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? $item['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2 mb-3"><label class="form-label fw-bold">واحد</label><input type="text" class="form-control" name="unit" value="<?= htmlspecialchars($old['unit'] ?? $item['unit'] ?? 'عدد') ?>"></div><div class="col-md-3 mb-3"><label class="form-label fw-bold">حد تجدید</label><input type="number" class="form-control" name="reorder_level" value="<?= htmlspecialchars($old['reorder_level'] ?? $item['reorder_level'] ?? '10') ?>"></div><div class="col-md-3 mb-3"><label class="form-label fw-bold">قیمت واحد</label><input type="number" class="form-control" name="unit_price" value="<?= htmlspecialchars($old['unit_price'] ?? $item['unit_price'] ?? '0') ?>"></div></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">تأمین‌کننده</label><input type="text" class="form-control" name="supplier_name" value="<?= htmlspecialchars($old['supplier_name'] ?? $item['supplier_name'] ?? '') ?>"></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">محل نگهداری</label><input type="text" class="form-control" name="location" value="<?= htmlspecialchars($old['location'] ?? $item['location'] ?? '') ?>"></div></div>
        <div class="mb-3"><label class="form-label fw-bold">توضیحات</label><textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($old['description'] ?? $item['description'] ?? '') ?></textarea></div>
        <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($old['is_active'] ?? $item['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label fw-bold">فعال</label></div></div>
        <hr><div class="d-flex justify-content-between"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $item['id'] ?>)"><i class="fas fa-trash me-1"></i>حذف</button><div><a href="<?= url('/inventory') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div></div>
    </form>
</div></div></div></div>

<script>function confirmDelete(id){Swal.fire({title:'حذف کالا',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',cancelButtonText:'انصراف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?=url('/inventory')?>/'+id;f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="DELETE">';document.body.appendChild(f);f.submit()}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>