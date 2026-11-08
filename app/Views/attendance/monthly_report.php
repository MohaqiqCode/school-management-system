<?php
$title = 'گزارش ماهانه حضور و غیاب';
$report = $report ?? [];
$month = $month ?? date('m');
$year = $year ?? date('Y');

$content = function() use ($report, $month, $year) {
    ob_start();
    $monthNames=[1=>'فروردین',2=>'اردیبهشت',3=>'خرداد',4=>'تیر',5=>'مرداد',6=>'شهریور',7=>'مهر',8=>'آبان',9=>'آذر',10=>'دی',11=>'بهمن',12=>'اسفند'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0"><i class="fas fa-chart-bar me-2"></i>گزارش ماهانه - <?=$monthNames[(int)$month]?> <?=$year?></h1><div><button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button><a href="<?=url('/attendance/students')?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right me-1"></i>بازگشت</a></div></div>

<div class="card shadow mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-bordered mb-0"><thead class="table-light text-center"><tr><th>#</th><th>نام</th><th>کد</th><th>حاضر</th><th>غایب</th><th>تأخیر</th><th>موجه</th><th>نیمه وقت</th><th>کل جلسات</th><th>درصد حضور</th></tr></thead><tbody>
<?php if(!empty($report)): ?>
    <?php foreach($report as $i=>$r): ?>
    <tr class="text-center"><td><?=$i+1?></td><td class="text-start"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td><code><?=htmlspecialchars($r['student_code'])?></code></td><td class="text-success fw-bold"><?=$r['present_days']??0?></td><td class="text-danger fw-bold"><?=$r['absent_days']??0?></td><td class="text-warning"><?=$r['late_days']??0?></td><td class="text-info"><?=$r['excused_days']??0?></td><td><?=$r['half_days']??0?></td><td><?=$r['total_days']??0?></td><td><span class="badge bg-<?=($r['attendance_percentage']??0)>=80?'success':(($r['attendance_percentage']??0)>=60?'warning':'danger')?>"><?=$r['attendance_percentage']??0?>%</span></td></tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="10" class="text-center py-4">داده‌ای موجود نیست</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>