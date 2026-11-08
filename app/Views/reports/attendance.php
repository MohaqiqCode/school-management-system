<?php
$title = 'گزارش حضور و غیاب';
$report = $report ?? [];
$classes = $classes ?? [];
$classFilter = $classFilter ?? '';
$sectionFilter = $sectionFilter ?? '';
$startDate = $startDate ?? date('Y-m-01');
$endDate = $endDate ?? date('Y-m-t');

$content = function() use ($report, $classes, $classFilter, $sectionFilter, $startDate, $endDate) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-calendar-check me-2"></i>گزارش حضور و غیاب</h1>
    <div>
        <button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button>
        <a href="<?= url('/reports/export/attendance') ?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>خروجی Excel</a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-3"><select class="form-select" name="class_id"><option value="">کلاس</option><?php foreach($classes as $cl): ?><option value="<?=$cl['id']?>" <?=$classFilter==$cl['id']?'selected':''?>><?=htmlspecialchars($cl['class_name'])?></option><?php endforeach; ?></select></div><div class="col-md-2"><input type="date" class="form-control" name="start_date" value="<?=$startDate?>"></div><div class="col-md-2"><input type="date" class="form-control" name="end_date" value="<?=$endDate?>"></div><div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال</button></div></form></div></div>

<!-- Report Table -->
<div class="card shadow mb-4"><div class="card-header"><h6 class="m-0 fw-bold">بازه: <?=date('Y/m/d',strtotime($startDate))?> تا <?=date('Y/m/d',strtotime($endDate))?></h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-bordered mb-0 text-center"><thead class="table-light"><tr><th>#</th><th>نام</th><th>کد</th><th>حاضر</th><th>غایب</th><th>تأخیر</th><th>موجه</th><th>کل</th><th>درصد</th></tr></thead><tbody>
<?php if(!empty($report)): ?><?php foreach($report as $i=>$r): ?><tr><td><?=$i+1?></td><td class="text-start"><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td><code><?=htmlspecialchars($r['student_code'])?></code></td><td class="text-success fw-bold"><?=$r['present_days']??0?></td><td class="text-danger fw-bold"><?=$r['absent_days']??0?></td><td class="text-warning"><?=$r['late_days']??0?></td><td class="text-info"><?=$r['excused_days']??0?></td><td><?=$r['total_days']??0?></td><td><span class="badge bg-<?=($r['attendance_percentage']??0)>=80?'success':(($r['attendance_percentage']??0)>=60?'warning':'danger')?>"><?=$r['attendance_percentage']??0?>%</span></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="9" class="py-4">داده‌ای یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>