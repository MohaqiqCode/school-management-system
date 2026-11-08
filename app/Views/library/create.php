<?php
$title = 'افزودن کتاب جدید';
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($branches, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>افزودن کتاب جدید</h1>
    <a href="<?= url('/library/books') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row"><div class="col-xl-8 col-lg-7"><div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">اطلاعات کتاب</h6></div><div class="card-body">
    <form action="<?= url('/library/books') ?>" method="POST"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="row">
            <div class="col-md-8 mb-3"><label class="form-label fw-bold">عنوان کتاب <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" required><?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= $errors['title'] ?></div><?php endif; ?></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">کد کتاب <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['book_code']) ? 'is-invalid' : '' ?>" name="book_code" value="<?= htmlspecialchars($old['book_code'] ?? '') ?>" required dir="ltr"><?php if (isset($errors['book_code'])): ?><div class="invalid-feedback"><?= $errors['book_code'] ?></div><?php endif; ?></div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نویسنده <span class="text-danger">*</span></label><input type="text" class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" name="author" value="<?= htmlspecialchars($old['author'] ?? '') ?>" required><?php if (isset($errors['author'])): ?><div class="invalid-feedback"><?= $errors['author'] ?></div><?php endif; ?></div>
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">ناشر</label><input type="text" class="form-control" name="publisher" value="<?= htmlspecialchars($old['publisher'] ?? '') ?>"></div>
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">سال انتشار</label><input type="number" class="form-control" name="publication_year" value="<?= htmlspecialchars($old['publication_year'] ?? '') ?>" min="1300" max="<?= date('Y') ?>"></div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">ISBN</label><input type="text" class="form-control" name="isbn" value="<?= htmlspecialchars($old['isbn'] ?? '') ?>" dir="ltr"></div>
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">دسته‌بندی</label><input type="text" class="form-control" name="category" value="<?= htmlspecialchars($old['category'] ?? '') ?>" list="categoryList"><datalist id="categoryList"><option>علمی</option><option>ادبیات</option><option>تاریخ</option><option>ریاضی</option><option>هنر</option><option>کامپیوتر</option></datalist></div>
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">تعداد نسخ <span class="text-danger">*</span></label><input type="number" class="form-control" name="total_copies" value="<?= htmlspecialchars($old['total_copies'] ?? '1') ?>" min="1" required></div>
            <div class="col-md-3 mb-3"><label class="form-label fw-bold">قیمت (تومان)</label><input type="number" class="form-control" name="purchase_price" value="<?= htmlspecialchars($old['purchase_price'] ?? '') ?>"></div>
        </div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">محل قفسه</label><input type="text" class="form-control" name="shelf_location" value="<?= htmlspecialchars($old['shelf_location'] ?? '') ?>"></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">تاریخ خرید</label><input type="text" class="form-control persian-datepicker" name="purchase_date" value="<?= htmlspecialchars($old['purchase_date'] ?? '') ?>"></div></div>
        <hr><div class="d-flex justify-content-between"><a href="<?= url('/library/books') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div>
    </form>
</div></div></div></div>

<script>$(document).ready(function(){$('.persian-datepicker').persianDatepicker({format:'YYYY/MM/DD',autoClose:true})});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>