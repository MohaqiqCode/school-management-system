<?php
$title = 'ویرایش مسیر';
$route = $route ?? [];
$vehicles = $vehicles ?? [];
$drivers = $drivers ?? [];
$old = $_SESSION['old'] ?? $route;

$content = function() use ($route, $vehicles, $drivers, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><div><h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>ویرایش مسیر</h1><p class="text-muted mb-0"><?=htmlspecialchars($route['route_name']??'')?></p></div><a href="<?=url('/transport/routes')?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<div class="row"><div class="col-lg-8"><div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">ویرایش اطلاعات</h6></div><div class="card-body">
    <form action="<?=url('/transport/routes/'.($route['id']??''))?>" method="POST"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="PUT">
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">نام مسیر</label><input type="text" class="form-control" name="route_name" value="<?=htmlspecialchars($old['route_name']??$route['route_name']??'')?>" required></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">کد مسیر</label><input type="text" class="form-control" value="<?=htmlspecialchars($route['route_code']??'')?>" disabled></div></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">مبدأ</label><input type="text" class="form-control" name="start_point" value="<?=htmlspecialchars($old['start_point']??$route['start_point']??'')?>"></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">مقصد</label><input type="text" class="form-control" name="end_point" value="<?=htmlspecialchars($old['end_point']??$route['end_point']??'')?>"></div></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">وسیله</label><select class="form-select" name="vehicle_id"><option value="">انتخاب...</option><?php foreach($vehicles as $v): ?><option value="<?=$v['id']?>" <?=($old['vehicle_id']??$route['vehicle_id']??'')==$v['id']?'selected':''?>><?=htmlspecialchars($v['vehicle_number'])?></option><?php endforeach; ?></select></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">راننده</label><select class="form-select" name="driver_id"><option value="">انتخاب...</option><?php foreach($drivers as $d): ?><option value="<?=$d['id']?>" <?=($old['driver_id']??$route['driver_id']??'')==$d['id']?'selected':''?>><?=htmlspecialchars($d['first_name'].' '.$d['last_name'])?></option><?php endforeach; ?></select></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">شهریه (تومان)</label><input type="number" class="form-control" name="monthly_fee" value="<?=htmlspecialchars($old['monthly_fee']??$route['monthly_fee']??'0')?>"></div></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">مسافت (km)</label><input type="number" class="form-control" name="distance_km" value="<?=htmlspecialchars($old['distance_km']??$route['distance_km']??'')?>" step="0.1"></div><div class="col-md-6 mb-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?=($old['is_active']??$route['is_active']??1)?'checked':''?>><label class="form-check-label fw-bold">فعال</label></div></div></div>
        <hr><div class="d-flex justify-content-between"><button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?=$route['id']?>)"><i class="fas fa-trash me-1"></i>حذف</button><div><a href="<?=url('/transport/routes')?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary ms-2"><i class="fas fa-save me-1"></i>ذخیره</button></div></div>
    </form>
</div></div></div></div>

<script>function confirmDelete(id){Swal.fire({title:'حذف مسیر',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',cancelButtonText:'انصراف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){const f=document.createElement('form');f.method='POST';f.action='<?=url('/transport/routes')?>/'+id;f.innerHTML='<input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="_method" value="DELETE">';document.body.appendChild(f);f.submit()}})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>