<?php
$title = 'گزارش‌های سیستم';

$content = function() {
    ob_start();
    $reportCards = [
        ['title'=>'دانش‌آموزان','icon'=>'fa-user-graduate','color'=>'primary','desc'=>'لیست دانش‌آموزان، آمار ثبت نام، توزیع کلاسی','url'=>'/reports/students'],
        ['title'=>'حضور و غیاب','icon'=>'fa-calendar-check','color'=>'success','desc'=>'گزارش روزانه و ماهانه، آمار غیبت و تأخیر','url'=>'/reports/attendance'],
        ['title'=>'مالی','icon'=>'fa-money-bill-wave','color'=>'warning','desc'=>'درآمدها، هزینه‌ها، شهریه معوق، پرداخت‌ها','url'=>'/reports/financial'],
        ['title'=>'آزمون‌ها','icon'=>'fa-file-alt','color'=>'info','desc'=>'نتایج آزمون‌ها، نمودار تحلیلی، رتبه‌بندی','url'=>'/reports/exam'],
        ['title'=>'حقوق و دستمزد','icon'=>'fa-file-invoice-dollar','color'=>'secondary','desc'=>'گزارش پرداخت حقوق، فیش حقوقی','url'=>'/reports/payroll'],
        ['title'=>'کتابخانه','icon'=>'fa-book-open','color'=>'dark','desc'=>'کتاب‌های امانتی، آمار امانت، جریمه‌ها','url'=>'/reports/library'],
        ['title'=>'حمل و نقل','icon'=>'fa-bus','color'=>'danger','desc'=>'استفاده از سرویس، مسیرها، ظرفیت','url'=>'/reports/transport'],
        ['title'=>'انبار','icon'=>'fa-boxes','color'=>'purple','desc'=>'موجودی کالاها، گردش انبار','url'=>'/reports/inventory'],
    ];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chart-bar me-2"></i>گزارش‌های سیستم</h1>
</div>

<div class="row">
    <?php foreach ($reportCards as $card): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-<?= $card['color'] ?> shadow h-100">
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas <?= $card['icon'] ?> fa-3x text-<?= $card['color'] ?>"></i>
                </div>
                <h5 class="card-title text-center fw-bold"><?= $card['title'] ?></h5>
                <p class="card-text text-muted small text-center"><?= $card['desc'] ?></p>
                <div class="d-grid">
                    <a href="<?= url($card['url']) ?>" class="btn btn-outline-<?= $card['color'] ?>">
                        <i class="fas fa-eye me-1"></i> مشاهده گزارش
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick Export -->
<div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-download me-2"></i>خروجی سریع</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><a href="<?= url('/reports/export/students') ?>" class="btn btn-outline-primary w-100"><i class="fas fa-file-excel me-1"></i>Excel دانش‌آموزان</a></div>
            <div class="col-md-3"><a href="<?= url('/reports/export/attendance') ?>" class="btn btn-outline-success w-100"><i class="fas fa-file-excel me-1"></i>Excel حضور و غیاب</a></div>
            <div class="col-md-3"><a href="<?= url('/reports/export/financial') ?>" class="btn btn-outline-warning w-100"><i class="fas fa-file-excel me-1"></i>Excel مالی</a></div>
            <div class="col-md-3"><a href="<?= url('/reports/export/exam') ?>" class="btn btn-outline-info w-100"><i class="fas fa-file-excel me-1"></i>Excel آزمون‌ها</a></div>
        </div>
    </div>
</div>

<style>.border-left-purple{border-left:0.25rem solid #6f42c1!important}</style>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>