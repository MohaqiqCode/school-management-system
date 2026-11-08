<?php
$title = 'افزودن کالای جدید';
$categories = $categories ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($categories, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>افزودن کالای جدید</h1>
    <a href="<?= url('/inventory') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row"><div class="col-xl-8 col-lg-7"><div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">اطلاعات کالا</h6></div><div class="card-body">
    <form action="<?= url('/inventory') ?>" method="POST"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">کد کالا <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text"><i class="fas fa-barcode"></i></span><input type="text" class="form-control <?= isset($errors['item_code']) ? 'is-invalid' : '' ?>" name="item_code" value="<?= htmlspecialchars($old['item_code'] ?? '') ?>" required dir="ltr"><?php if (isset($errors['item_code'])): ?><div class="invalid-feedback"><?= $errors['item_code'] ?></div><?php endif; ?></div></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام کالا <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['item_name']) ? 'is-invalid' : '' ?>" name="item_name" value="<?= htmlspecialchars($old['item_name'] ?? '') ?>" required><?php if (isset($errors['item_name'])): ?><div class="invalid-feedback"><?= $errors['item_name'] ?></div><?php endif; ?></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">دسته‌بندی <span class="text-danger">*</span></label><select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" name="category_id" required><option value="">انتخاب...</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option><?php endforeach; ?></select><?php if (isset($errors['category_id'])): ?><div class="invalid-feedback"><?= $errors['category_id'] ?></div><?php endif; ?></div>
            <div class="col-md-2 mb-3"><label class="form-label fw-bold">واحد</label><input type="text" class="form-control" name="unit" value="<?= htmlspecialchars($old['unit'] ?? 'عدد') ?>"></div>
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">موجودی اولیه</label><input type="number" class="form-control" name="quantity_in_stock" value="<?= htmlspecialchars($old['quantity_in_stock'] ?? '0') ?>" min="0"></div>
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">حد تجدید سفارش</label><input type="number" class="form-control" name="reorder_level" value="<?= htmlspecialchars($old['reorder_level'] ?? '10') ?>" min="1"></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">قیمت واحد (تومان)</label><input type="number" class="form-control" name="unit_price" value="<?= htmlspecialchars($old['unit_price'] ?? '0') ?>" min="0"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تأمین‌کننده</label><input type="text" class="form-control" name="supplier_name" value="<?= htmlspecialchars($old['supplier_name'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">محل نگهداری</label><input type="text" class="form-control" name="location" value="<?= htmlspecialchars($old['location'] ?? '') ?>"></div>
        </div>
        <div class="mb-3"><label class="form-label fw-bold">توضیحات</label><textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($old['description'] ?? '') ?></textarea></div>
        <hr><div class="d-flex justify-content-between"><a href="<?= url('/inventory') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره کالا</button></div>
    </form>
</div></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>