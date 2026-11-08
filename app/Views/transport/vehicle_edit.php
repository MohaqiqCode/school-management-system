<?php
$title = 'ویرایش وسیله نقلیه';
$vehicle = $vehicle ?? [];
$old = $_SESSION['old'] ?? $vehicle;

$content = function() use ($vehicle, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><div><h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>ویرایش وسیله</h1><p class="text-muted mb-0"><?= htmlspecialchars($vehicle['vehicle_number'] ?? '') ?></p></div><a href="<?= url('/transport/vehicles') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<div class="row"><div class="col-lg-8"><div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">ویرایش اطلاعات</h6></div><div class="card-body">
    <form action="<?= url('/transport/vehicles/' . ($vehicle['id'] ?? '')) ?>" method="POST"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="PUT">
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">شماره پلاک</label><input type="text" class="form-control" name="vehicle_number" value="<?=htmlspecialchars($old['vehicle_number']??$vehicle['vehicle_number']??'')?>" required></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">نوع</label><input type="text" class="form-control" name="vehicle_type" value="<?=htmlspecialchars($old['vehicle_type']??$vehicle['vehicle_type']??'')?>"></div></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">مدل</label><input type="text" class="form-control" name="model" value="<?=htmlspecialchars($old['model']??$vehicle['model']??'')?>"></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">ظرفیت</label><input type="number" class="form-control" name="capacity" value="<?=htmlspecialchars($old['capacity']??$vehicle['capacity']??'')?>"></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">نوع سوخت</label><select class="form-select" name="fuel_type"><?php foreach(['diesel'=>'گازوئیل','petrol'=>'بنزین','cng'=>'CNG','electric'=>'برقی'] as $k=>$v): ?><option value="<?=$k?>" <?=($old['fuel_type']??$vehicle['fuel_type']??'')==$k?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">انقضای بیمه</label><input type="text" class="form-control persian-datepicker" name="insurance_expiry" value="<?=htmlspecialchars($old['insurance_expiry']??$vehicle['insurance_expiry']??'')?>"></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">انقضای معاینه فنی</label><input type="text" class="form-control persian-datepicker" name="fitness_certificate_expiry" value="<?=htmlspecialchars($old['fitness_certificate_expiry']??$vehicle['fitness_certificate_expiry']??'')?>"></div><div class="col-md-4 mb-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?=($old['is_active']??$vehicle['is_active']??1)?'checked':''?>><label class="form-check-label fw-bold">فعال</label></div></div></div>
        <hr><div class="d-flex justify-content-between"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?=$vehicle['id']?>)"><i class="fas fa-trash me-1"></i>حذف</button><div><a href="<?=url('/transport/vehicles')?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div></div>
    </form>
</div></div></div></div>

<script>function confirmDelete(id){Swal.fire({title:'حذف وسیله',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',cancelButtonText:'انصراف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?=url('/transport/vehicles')?>/'+id;f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="DELETE">';document.body.appendChild(f);f.submit()}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>