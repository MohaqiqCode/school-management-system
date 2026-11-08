<?php
$title = 'گزارش آزمون‌ها';
$results = $results ?? [];
$statistics = $statistics ?? [];
$exams = $exams ?? [];
$selectedExam = $selectedExam ?? '';

$content = function() use ($results, $statistics, $exams, $selectedExam) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-file-alt me-2"></i>گزارش آزمون‌ها</h1>
    <div><button class="btn btn-outline-primary btn-sm me-1" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button><a href="<?=url('/reports/export/exam')?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>Excel</a></div>
</div>

<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-8"><select class="form-select" name="exam_id" onchange="this.form.submit()"><option value="">انتخاب آزمون...</option><?php foreach($exams as $ex): ?><option value="<?=$ex['id']?>" <?=$selectedExam==$ex['id']?'selected':''?>><?=htmlspecialchars($ex['subject_name'])?> - <?=htmlspecialchars($ex['class_name'])?> (<?=date('Y/m/d',strtotime($ex['exam_date']))?>)</option><?php endforeach; ?></select></div></form></div></div>

<?php if($selectedExam): ?>
<!-- Statistics -->
<div class="row">
    <div class="col-md-2 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5"><?=$statistics['total_students']??0?></div><small>کل</small></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5 text-success"><?=$statistics['highest']??0?></div><small>بیشترین</small></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5 text-danger"><?=$statistics['lowest']??0?></div><small>کمترین</small></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5 text-info"><?=$statistics['average']??0?></div><small>میانگین</small></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5 text-success"><?=$statistics['passed']??0?></div><small>قبول</small></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h5 text-danger"><?=$statistics['failed']??0?></div><small>مردود</small></div></div></div>
</div>

<!-- Pass Rate -->
<div class="card shadow mb-4"><div class="card-body text-center"><div class="h3">نرخ قبولی: <span class="text-<?=($statistics['pass_percentage']??0)>=50?'success':'danger'?>"><?=$statistics['pass_percentage']??0?>%</span></div><div class="progress" style="height:20px"><div class="progress-bar bg-<?=($statistics['pass_percentage']??0)>=50?'success':'danger'?>" style="width:<?=$statistics['pass_percentage']??0?>%"><?=$statistics['pass_percentage']??0?>%</div></div></div></div>

<!-- Results Table -->
<div class="card shadow mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>دانش‌آموز</th><th>کد</th><th>نمره</th><th>درصد</th><th>وضعیت</th></tr></thead><tbody><?php foreach($results as $i=>$r): $pct=($r['total_marks']??1)>0?round(($r['marks_obtained']/$r['total_marks'])*100):0; ?><tr><td><?=$i+1?></td><td><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td><td><code><?=htmlspecialchars($r['student_code'])?></code></td><td class="fw-bold"><?=$r['marks_obtained']?> / <?=$r['total_marks']?></td><td><?=$pct?>%</td><td><span class="badge bg-<?=$r['marks_obtained']>=($r['passing_marks']??50)?'success':'danger'?>"><?=$r['marks_obtained']>=($r['passing_marks']??50)?'قبول':'مردود'?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php endif; ?>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>