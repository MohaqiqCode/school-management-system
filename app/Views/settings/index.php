<?php
$title = 'تنظیمات سیستم';

$content = function() {
    ob_start();
    $settingCards = [
        ['title'=>'تنظیمات عمومی','icon'=>'fa-cog','color'=>'primary','desc'=>'تنظیمات پایه سیستم، نام مدرسه، لوگو، منطقه زمانی','url'=>'/settings/general'],
        ['title'=>'پشتیبان‌گیری','icon'=>'fa-database','color'=>'success','desc'=>'تهیه نسخه پشتیبان از دیتابیس و فایل‌ها','url'=>'/settings/backup'],
        ['title'=>'امنیت','icon'=>'fa-shield-alt','color'=>'danger','desc'=>'تنظیمات امنیتی، قوانین رمز عبور، CSRF','url'=>'/settings/security'],
        ['title'=>'ایمیل','icon'=>'fa-envelope','color'=>'info','desc'=>'تنظیمات SMTP، قالب‌های ایمیل','url'=>'/settings/email'],
        ['title'=>'پیامک','icon'=>'fa-sms','color'=>'warning','desc'=>'تنظیمات سرویس پیامک، الگوها','url'=>'/settings/sms'],
        ['title'=>'پرداخت','icon'=>'fa-credit-card','color'=>'dark','desc'=>'تنظیمات درگاه پرداخت آنلاین','url'=>'/settings/payment'],
        ['title'=>'نقشه','icon'=>'fa-map-marked-alt','color'=>'secondary','desc'=>'تنظیمات سرویس نقشه برای حمل و نقل','url'=>'/settings/map'],
        ['title'=>'لاگ‌ها','icon'=>'fa-history','color'=>'purple','desc'=>'تنظیمات لاگینگ، پاکسازی خودکار','url'=>'/settings/logs'],
    ];
    $systemInfo = [
        ['label'=>'نسخه سیستم','value'=>config('app_version','1.0.0')],
        ['label'=>'نسخه PHP','value'=>phpversion()],
        ['label'=>'محیط','value'=>config('app_environment','production')],
        ['label'=>'Debug Mode','value'=>config('app_debug',false)?'<span class="badge bg-danger">فعال</span>':'<span class="badge bg-success">غیرفعال</span>'],
        ['label'=>'Region','value'=>config('app_timezone','Asia/Tehran')],
        ['label'=>'زبان','value'=>config('app_locale','fa')==='fa'?'فارسی':'English'],
    ];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cogs me-2"></i>تنظیمات سیستم</h1>
</div>

<!-- Settings Cards -->
<div class="row">
    <?php foreach($settingCards as $card): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-<?=$card['color']?> shadow h-100">
            <div class="card-body text-center">
                <div class="mb-3"><i class="fas <?=$card['icon']?> fa-3x text-<?=$card['color']?>"></i></div>
                <h6 class="fw-bold"><?=$card['title']?></h6>
                <p class="small text-muted"><?=$card['desc']?></p>
                <a href="<?=url($card['url'])?>" class="btn btn-outline-<?=$card['color']?> btn-sm"><i class="fas fa-arrow-left me-1"></i>تنظیم</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- System Info -->
<div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات سیستم</h6></div>
    <div class="card-body"><div class="row">
        <?php foreach($systemInfo as $info): ?>
        <div class="col-md-4 mb-2"><div class="d-flex justify-content-between bg-light rounded p-2"><span class="text-muted"><?=$info['label']?></span><strong><?=$info['value']?></strong></div></div>
        <?php endforeach; ?>
    </div></div>
</div>

<!-- Quick Actions -->
<div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-bolt me-2"></i>عملیات سریع</h6></div><div class="card-body"><div class="row g-3">
    <div class="col-md-3"><button class="btn btn-outline-warning w-100" onclick="clearCache()"><i class="fas fa-broom me-1"></i>پاکسازی کش</button></div>
    <div class="col-md-3"><button class="btn btn-outline-info w-100" onclick="clearLogs()"><i class="fas fa-trash-alt me-1"></i>پاکسازی لاگ‌ها</button></div>
    <div class="col-md-3"><a href="<?=url('/settings/backup')?>" class="btn btn-outline-success w-100"><i class="fas fa-download me-1"></i>پشتیبان‌گیری</a></div>
    <div class="col-md-3"><button class="btn btn-outline-danger w-100" onclick="maintenanceMode()"><i class="fas fa-tools me-1"></i>حالت تعمیر</button></div>
</div></div></div>

<script>
function clearCache(){Swal.fire({title:'پاکسازی کش',text:'آیا از پاکسازی کش اطمینان دارید؟',icon:'question',showCancelButton:true,confirmButtonText:'بله',cancelButtonText:'خیر'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/api/settings/clear-cache')?>',{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>toastr[d.success?'success':'error'](d.message))}})}
function clearLogs(){Swal.fire({title:'پاکسازی لاگ‌ها',text:'لاگ‌های قدیمی حذف می‌شوند.',icon:'question',showCancelButton:true,confirmButtonText:'بله'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/api/settings/clear-logs')?>',{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>toastr[d.success?'success':'error'](d.message))}})}
function maintenanceMode(){Swal.fire({title:'حالت تعمیر',text:'سیستم از دسترس خارج می‌شود.',icon:'warning',showCancelButton:true,confirmButtonText:'فعال کردن',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/api/settings/toggle-maintenance')?>',{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>toastr[d.success?'success':'error'](d.message))}})}
</script>

<style>.border-left-purple{border-left:0.25rem solid #6f42c1!important}</style>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>