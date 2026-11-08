<?php
$title = 'ویرایش کتاب';
$book = $book ?? [];
$branches = $branches ?? [];
$old = $_SESSION['old'] ?? $book;

$content = function() use ($book, $branches, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>ویرایش کتاب</h1><p class="text-muted mb-0"><?= htmlspecialchars($book['title'] ?? '') ?></p></div>
    <div>
        <button class="btn btn-outline-danger" onclick="confirmDelete(<?=$book['id']?>)"><i class="fas fa-trash me-1"></i>حذف</button>
        <a href="<?= url('/library/books') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row"><div class="col-xl-8 col-lg-7"><div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">ویرایش اطلاعات</h6></div><div class="card-body">
    <form action="<?= url('/library/books/' . ($book['id'] ?? '')) ?>" method="POST"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="PUT">
        <div class="row"><div class="col-md-8 mb-3"><label class="form-label fw-bold">عنوان</label><input type="text" class="form-control" name="title" value="<?=htmlspecialchars($old['title']??$book['title']??'')?>" required></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">کد کتاب</label><input type="text" class="form-control" value="<?=htmlspecialchars($book['book_code']??'')?>" disabled></div></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">نویسنده</label><input type="text" class="form-control" name="author" value="<?=htmlspecialchars($old['author']??$book['author']??'')?>" required></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">ناشر</label><input type="text" class="form-control" name="publisher" value="<?=htmlspecialchars($old['publisher']??$book['publisher']??'')?>"></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">سال</label><input type="number" class="form-control" name="publication_year" value="<?=htmlspecialchars($old['publication_year']??$book['publication_year']??'')?>"></div></div>
        <div class="row"><div class="col-md-3 mb-3"><label class="form-label fw-bold">ISBN</label><input type="text" class="form-control" name="isbn" value="<?=htmlspecialchars($old['isbn']??$book['isbn']??'')?>"></div><div class="col-md-3 mb-3"><label class="form-label fw-bold">دسته‌بندی</label><input type="text" class="form-control" name="category" value="<?=htmlspecialchars($old['category']??$book['category']??'')?>"></div><div class="col-md-3 mb-3"><label class="form-label fw-bold">تعداد نسخ</label><input type="number" class="form-control" name="total_copies" value="<?=htmlspecialchars($old['total_copies']??$book['total_copies']??'1')?>" min="1"></div><div class="col-md-3 mb-3"><label class="form-label fw-bold">قیمت</label><input type="number" class="form-control" name="purchase_price" value="<?=htmlspecialchars($old['purchase_price']??$book['purchase_price']??'')?>"></div></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">محل قفسه</label><input type="text" class="form-control" name="shelf_location" value="<?=htmlspecialchars($old['shelf_location']??$book['shelf_location']??'')?>"></div><div class="col-md-6 mb-3"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?=($old['is_active']??$book['is_active']??1)?'checked':''?>><label class="form-check-label fw-bold">فعال</label></div></div></div>
        <hr><div class="d-flex justify-content-between"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?=$book['id']?>)"><i class="fas fa-trash me-1"></i>حذف</button><div><a href="<?=url('/library/books')?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div></div>
    </form>
</div></div></div></div>

<script>function confirmDelete(id){Swal.fire({title:'حذف کتاب',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',cancelButtonText:'انصراف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?=url('/library/books')?>/'+id;f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="DELETE">';document.body.appendChild(f);f.submit()}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>