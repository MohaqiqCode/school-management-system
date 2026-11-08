<?php
$title = 'گزارش کتابخانه';
$stats = $stats ?? [];
$popularBooks = $popularBooks ?? [];
$overdueBooks = $overdueBooks ?? [];
$borrowingTrend = $borrowingTrend ?? [];

$content = function() use ($stats, $popularBooks, $overdueBooks, $borrowingTrend) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-book-open me-2"></i>گزارش کتابخانه</h1>
    <div><button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button><a href="<?=url('/reports/export/library')?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>Excel</a></div>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=number_format($stats['total_books']??0)?></div><small>کل کتاب‌ها</small></div></div></div>
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=number_format($stats['available_books']??0)?></div><small>موجود</small></div></div></div>
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=number_format($stats['borrowed_books']??0)?></div><small>امانت فعال</small></div></div></div>
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5 text-danger"><?=number_format($stats['overdue_books']??0)?></div><small>دیرکرد</small></div></div></div>
</div>

<div class="row">
    <!-- Popular Books -->
    <div class="col-xl-7 mb-4"><div class="card shadow"><div class="card-header"><h6 class="m-0 fw-bold">کتاب‌های پرامانت</h6></div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>عنوان</th><th>نویسنده</th><th>ISBN</th><th>تعداد امانت</th></tr></thead><tbody><?php if(!empty($popularBooks)): ?><?php foreach($popularBooks as $i=>$pb): ?><tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($pb['title'])?></strong></td><td><?=htmlspecialchars($pb['author'])?></td><td><?=htmlspecialchars($pb['isbn']??'-')?></td><td><span class="badge bg-info"><?=$pb['borrow_count']?></span></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="5" class="text-center py-3">داده‌ای نیست</td></tr><?php endif; ?></tbody></table></div></div></div>
    
    <!-- Overdue -->
    <div class="col-xl-5 mb-4"><div class="card shadow"><div class="card-header"><h6 class="m-0 fw-bold text-danger">کتاب‌های با تأخیر</h6></div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>کتاب</th><th>امانت‌گیرنده</th><th>تأخیر</th><th>جریمه</th></tr></thead><tbody><?php if(!empty($overdueBooks)): ?><?php foreach($overdueBooks as $od): $days=ceil((time()-strtotime($od['due_date']))/86400); ?><tr><td><?=htmlspecialchars($od['book_title'])?></td><td><?=htmlspecialchars($od['first_name'].' '.$od['last_name'])?></td><td class="text-danger"><?=$days?> روز</td><td><?=number_format($od['fine_amount'])?> ت</td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="4" class="text-center py-3">کتاب دارای تأخیر نیست</td></tr><?php endif; ?></tbody></table></div></div></div>
</div>

<!-- Borrowing Trend -->
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">روند امانت ماهانه</h6></div><div class="card-body"><canvas id="borrowingChart" height="250"></canvas></div></div>

<script>new Chart(document.getElementById('borrowingChart').getContext('2d'),{type:'line',data:{labels:<?=json_encode(array_column($borrowingTrend??[],'month'))?>,datasets:[{label:'امانت',data:<?=json_encode(array_map('intval',array_column($borrowingTrend??[],'count')))?>,borderColor:'#4e73df',tension:0.3}]},options:{responsive:true}});</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>