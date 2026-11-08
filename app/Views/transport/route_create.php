<?php
$title = 'ایجاد مسیر جدید';
$vehicles = $vehicles ?? [];
$drivers = $drivers ?? [];
$old = $_SESSION['old'] ?? [];

$content = function() use ($vehicles, $drivers, $old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>ایجاد مسیر جدید</h1><a href="<?= url('/transport/routes') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<div class="row"><div class="col-lg-8"><div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">اطلاعات مسیر</h6></div><div class="card-body">
    <form action="<?= url('/transport/routes') ?>" method="POST"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>">
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">نام مسیر <span class="text-danger">*</span></label><input type="text" class="form-control" name="route_name" value="<?=htmlspecialchars($old['route_name']??'')?>" required></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">کد مسیر <span class="text-danger">*</span></label><input type="text" class="form-control" name="route_code" value="<?=htmlspecialchars($old['route_code']??'')?>" required></div></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">مبدأ</label><input type="text" class="form-control" name="start_point" value="<?=htmlspecialchars($old['start_point']??'')?>"></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">مقصد</label><input type="text" class="form-control" name="end_point" value="<?=htmlspecialchars($old['end_point']??'')?>"></div></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">وسیله نقلیه</label><select class="form-select" name="vehicle_id"><option value="">انتخاب...</option><?php foreach($vehicles as $v): ?><option value="<?=$v['id']?>" <?=($old['vehicle_id']??'')==$v['id']?'selected':''?>><?=htmlspecialchars($v['vehicle_number'])?></option><?php endforeach; ?></select></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">راننده</label><select class="form-select" name="driver_id"><option value="">انتخاب...</option><?php foreach($drivers as $d): ?><option value="<?=$d['id']?>" <?=($old['driver_id']??'')==$d['id']?'selected':''?>><?=htmlspecialchars($d['first_name'].' '.$d['last_name'])?></option><?php endforeach; ?></select></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">شهریه ماهانه (تومان)</label><input type="number" class="form-control" name="monthly_fee" value="<?=htmlspecialchars($old['monthly_fee']??'0')?>"></div></div>
        <div class="row"><div class="col-md-4 mb-3"><label class="form-label fw-bold">مسافت (کیلومتر)</label><input type="number" class="form-control" name="distance_km" value="<?=htmlspecialchars($old['distance_km']??'')?>" step="0.1"></div><div class="col-md-4 mb-3"><label class="form-label fw-bold">زمان تخمینی (دقیقه)</label><input type="number" class="form-control" name="estimated_time_minutes" value="<?=htmlspecialchars($old['estimated_time_minutes']??'')?>"></div></div>
        <hr><div class="d-flex justify-content-between"><a href="<?=url('/transport/routes')?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div>
    </form>
</div></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>