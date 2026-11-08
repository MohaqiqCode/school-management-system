<?php
$title = 'افزودن وسیله نقلیه';
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

$content = function() use ($old) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>افزودن وسیله نقلیه</h1><a href="<?= url('/transport/vehicles') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div>

<div class="row"><div class="col-lg-8"><div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">اطلاعات وسیله</h6></div><div class="card-body">
    <form action="<?= url('/transport/vehicles') ?>" method="POST"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">شماره پلاک <span class="text-danger">*</span></label><input type="text" class="form-control" name="vehicle_number" value="<?= htmlspecialchars($old['vehicle_number'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">نوع</label><input type="text" class="form-control" name="vehicle_type" value="<?= htmlspecialchars($old['vehicle_type'] ?? '') ?>" placeholder="مینی‌بوس، اتوبوس، ون..."></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">مدل</label><input type="text" class="form-control" name="model" value="<?= htmlspecialchars($old['model'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">ظرفیت (نفر)</label><input type="number" class="form-control" name="capacity" value="<?= htmlspecialchars($old['capacity'] ?? '') ?>" min="1"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">نوع سوخت</label><select class="form-select" name="fuel_type"><option value="diesel">گازوئیل</option><option value="petrol">بنزین</option><option value="cng">CNG</option><option value="electric">برقی</option></select></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">تاریخ ثبت</label><input type="text" class="form-control persian-datepicker" name="registration_date" value="<?= htmlspecialchars($old['registration_date'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">انقضای بیمه</label><input type="text" class="form-control persian-datepicker" name="insurance_expiry" value="<?= htmlspecialchars($old['insurance_expiry'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label fw-bold">انقضای معاینه فنی</label><input type="text" class="form-control persian-datepicker" name="fitness_certificate_expiry" value="<?= htmlspecialchars($old['fitness_certificate_expiry'] ?? '') ?>"></div>
        </div>
        <hr><div class="d-flex justify-content-between"><a href="<?= url('/transport/vehicles') ?>" class="btn btn-secondary">انصراف</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div>
    </form>
</div></div></div></div>

<script>$(document).ready(function(){$('.persian-datepicker').persianDatepicker({format:'YYYY/MM/DD',autoClose:true})});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>