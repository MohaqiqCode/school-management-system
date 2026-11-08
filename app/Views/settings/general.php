<?php
$title = 'تنظیمات عمومی';
$settings = $settings ?? [];
$old = $_SESSION['old'] ?? $settings;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($settings, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-cog me-2"></i>تنظیمات عمومی</h1>
    <a href="<?= url('/settings') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<form action="<?= url('/settings/general') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <!-- School Info -->
    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-school me-2"></i>اطلاعات مدرسه</h6></div><div class="card-body"><div class="row">
        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام سیستم</label><input type="text" class="form-control" name="app_name" value="<?=htmlspecialchars($old['app_name']??$settings['app_name']??config('app_name'))?>"></div>
        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نام اختصاری</label><input type="text" class="form-control" name="app_name_short" value="<?=htmlspecialchars($old['app_name_short']??$settings['app_name_short']??config('app_name_short','SMS'))?>"></div>
    </div><div class="row">
        <div class="col-md-6 mb-3"><label class="form-label fw-bold">آدرس وب‌سایت</label><input type="url" class="form-control" name="app_url" value="<?=htmlspecialchars($old['app_url']??$settings['app_url']??config('app_url'))?>" dir="ltr"></div>
        <div class="col-md-6 mb-3"><label class="form-label fw-bold">ایمیل اصلی</label><input type="email" class="form-control" name="email" value="<?=htmlspecialchars($old['email']??$settings['email']??config('email.from_address'))?>" dir="ltr"></div>
    </div><div class="mb-3"><label class="form-label fw-bold">لوگوی سیستم</label><?php if(!empty($settings['logo'])): ?><div class="mb-2"><img src="<?=asset($settings['logo'])?>" class="rounded" style="max-height:60px;"></div><?php endif; ?><input type="file" class="form-control" name="logo" accept="image/*"></div></div></div>

    <!-- Localization -->
    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-globe me-2"></i>محلی‌سازی</h6></div><div class="card-body"><div class="row">
        <div class="col-md-4 mb-3"><label class="form-label fw-bold">منطقه زمانی</label><select class="form-select" name="app_timezone"><?php foreach(timezone_identifiers_list() as $tz): ?><option value="<?=$tz?>" <?=($old['app_timezone']??$settings['app_timezone']??config('app_timezone','Asia/Tehran'))===$tz?'selected':''?>><?=$tz?></option><?php endforeach; ?></select></div>
        <div class="col-md-4 mb-3"><label class="form-label fw-bold">زبان</label><select class="form-select" name="app_locale"><option value="fa" <?=($old['app_locale']??$settings['app_locale']??'fa')==='fa'?'selected':''?>>فارسی</option><option value="en" <?=($old['app_locale']??'')==='en'?'selected':''?>>English</option></select></div>
        <div class="col-md-4 mb-3"><label class="form-label fw-bold">جهت</label><select class="form-select" name="app_direction"><option value="rtl" <?=($old['app_direction']??$settings['app_direction']??'rtl')==='rtl'?'selected':''?>>راست به چپ</option><option value="ltr" <?=($old['app_direction']??'')==='ltr'?'selected':''?>>چپ به راست</option></select></div>
    </div><div class="row">
        <div class="col-md-4 mb-3"><label class="form-label fw-bold">فرمت تاریخ</label><input type="text" class="form-control" name="date_format" value="<?=htmlspecialchars($old['date_format']??$settings['date_format']??'Y/m/d')?>"></div>
        <div class="col-md-4 mb-3"><label class="form-label fw-bold">فرمت زمان</label><input type="text" class="form-control" name="time_format" value="<?=htmlspecialchars($old['time_format']??$settings['time_format']??'H:i')?>"></div>
        <div class="col-md-4 mb-3"><label class="form-label fw-bold">واحد پول</label><input type="text" class="form-control" name="currency" value="<?=htmlspecialchars($old['currency']??$settings['currency']??'تومان')?>"></div>
    </div></div></div>

    <!-- Pagination & Security -->
    <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="fas fa-sliders-h me-2"></i>تنظیمات پیشرفته</h6></div><div class="card-body"><div class="row">
        <div class="col-md-3 mb-3"><label class="form-label fw-bold">تعداد در صفحه</label><input type="number" class="form-control" name="per_page" value="<?=htmlspecialchars($old['per_page']??$settings['per_page']??'25')?>" min="5" max="100"></div>
        <div class="col-md-3 mb-3"><label class="form-label fw-bold">حداکثر آپلود (MB)</label><input type="number" class="form-control" name="max_upload_size" value="<?=htmlspecialchars($old['max_upload_size']??$settings['max_upload_size']??'5')?>" min="1"></div>
        <div class="col-md-3 mb-3"><label class="form-label fw-bold">حداقل رمز عبور</label><input type="number" class="form-control" name="password_min_length" value="<?=htmlspecialchars($old['password_min_length']??$settings['password_min_length']??'8')?>" min="6"></div>
        <div class="col-md-3 mb-3"><label class="form-label fw-bold">مدت نشست (دقیقه)</label><input type="number" class="form-control" name="session_lifetime" value="<?=htmlspecialchars($old['session_lifetime']??$settings['session_lifetime']??'120')?>" min="5"></div>
    </div><div class="row">
        <div class="col-md-6 mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="app_debug" value="1" <?=($old['app_debug']??$settings['app_debug']??config('app_debug',false))?'checked':''?>><label class="form-check-label fw-bold">حالت اشکال‌زدایی (Debug)</label></div></div>
        <div class="col-md-6 mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="maintenance" value="1" <?=($old['maintenance']??$settings['maintenance']??false)?'checked':''?>><label class="form-check-label fw-bold">حالت تعمیر و نگهداری</label></div></div>
    </div></div></div>

    <div class="d-flex justify-content-end mb-4">
        <a href="<?= url('/settings') ?>" class="btn btn-secondary px-4 me-2">انصراف</a>
        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>ذخیره تنظیمات</button>
    </div>
</form>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>