<?php
$title = 'ویرایش والد';
$parent = $parent ?? [];
$old = $_SESSION['old'] ?? $parent;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($parent, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>ویرایش والد</h1><p class="text-muted mb-0 mt-1"><?= htmlspecialchars(($parent['first_name'] ?? '') . ' ' . ($parent['last_name'] ?? '')) ?></p></div>
    <a href="<?= url('/parents') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>ویرایش اطلاعات</h6></div>
            <div class="card-body">
                <form action="<?= url('/parents/' . ($parent['id'] ?? '')) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="PUT">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام</label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? $parent['first_name'] ?? '') ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام خانوادگی</label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? $parent['last_name'] ?? '') ?>" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">شماره تماس</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? $parent['phone'] ?? '') ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">تصویر</label><input type="file" class="form-control" name="profile_photo" accept="image/*"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">شغل</label><input type="text" class="form-control" name="occupation" value="<?= htmlspecialchars($old['occupation'] ?? $parent['occupation'] ?? '') ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">محل کار</label><input type="text" class="form-control" name="workplace" value="<?= htmlspecialchars($old['workplace'] ?? $parent['workplace'] ?? '') ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-bold">آدرس</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($old['address'] ?? $parent['address'] ?? '') ?></textarea></div>
                    <hr>
                    <div class="d-flex justify-content-between"><a href="<?= url('/parents') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>