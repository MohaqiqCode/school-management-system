<?php
$title = 'گزارش دانش‌آموزان';
$students = $students ?? [];
$classes = $classes ?? [];
$stats = $stats ?? [];
$classFilter = $classFilter ?? '';
$sectionFilter = $sectionFilter ?? '';
$statusFilter = $statusFilter ?? 'active';

$content = function() use ($students, $classes, $stats, $classFilter, $sectionFilter, $statusFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-user-graduate me-2"></i>گزارش دانش‌آموزان</h1>
    <div>
        <button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button>
        <a href="<?= url('/reports/export/students') ?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>خروجی Excel</a>
    </div>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h4 text-primary"><?= number_format($stats['total'] ?? 0) ?></div><small>کل دانش‌آموزان</small></div></div></div>
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h4 text-info"><?= number_format($stats['male'] ?? 0) ?></div><small>پسر</small></div></div></div>
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h4 text-pink" style="color:#e83e8c"><?= number_format($stats['female'] ?? 0) ?></div><small>دختر</small></div></div></div>
    <div class="col-md-3 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h4 text-success"><?= number_format(count($classes ?? [])) ?></div><small>کلاس فعال</small></div></div></div>
</div>

<!-- Filters -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-4"><select class="form-select" name="class_id"><option value="">همه کلاس‌ها</option><?php foreach($classes as $cl): ?><option value="<?=$cl['id']?>" <?=$classFilter==$cl['id']?'selected':''?>><?=htmlspecialchars($cl['class_name'])?></option><?php endforeach; ?></select></div><div class="col-md-3"><select class="form-select" name="status"><option value="active" <?=$statusFilter==='active'?'selected':''?>>فعال</option><option value="inactive" <?=$statusFilter==='inactive'?'selected':''?>>غیرفعال</option><option value="all" <?=$statusFilter==='all'?'selected':''?>>همه</option></select></div><div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال</button></div><div class="col-md-2"><a href="<?=url('/reports/students')?>" class="btn btn-outline-secondary w-100">پاک کردن</a></div></form></div></div>

<!-- Table -->
<div class="card shadow mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>کد</th><th>نام</th><th>ایمیل</th><th>تلفن</th><th>جنسیت</th><th>کلاس</th><th>بخش</th><th>تاریخ ثبت</th></tr></thead><tbody>
<?php if(!empty($students)): ?><?php foreach($students as $i=>$s): ?><tr><td><?=$i+1?></td><td><code><?=htmlspecialchars($s['student_code'])?></code></td><td><?=htmlspecialchars($s['first_name'].' '.$s['last_name'])?></td><td><?=htmlspecialchars($s['email']??'')?></td><td><?=htmlspecialchars($s['phone']??'-')?></td><td><?=$s['gender']==='male'?'مرد':($s['gender']==='female'?'زن':'سایر')?></td><td><?=htmlspecialchars($s['class_name']??'')?></td><td><?=htmlspecialchars($s['section_name']??'')?></td><td><?=date('Y/m/d',strtotime($s['admission_date']??''))?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="9" class="text-center py-4">داده‌ای یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>