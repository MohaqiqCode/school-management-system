<?php
$title = 'نتایج آزمون‌ها';
$results = $results ?? [];
$exams = $exams ?? [];
$selectedExam = $selectedExam ?? '';

$content = function() use ($results, $exams, $selectedExam) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-chart-bar me-2"></i>نتایج آزمون‌ها</h1>
</div>

<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/results') ?>" method="GET" class="row g-3"><div class="col-md-8"><select class="form-select" name="exam_id" onchange="this.form.submit()"><option value="">انتخاب آزمون...</option><?php foreach ($exams as $ex): ?><option value="<?= $ex['id'] ?>" <?= $selectedExam == $ex['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ex['subject_name']) ?> - <?= htmlspecialchars($ex['class_name']) ?> (<?= date('Y/m/d', strtotime($ex['exam_date'] ?? '')) ?>)</option><?php endforeach; ?></select></div><div class="col-md-4"><?php if ($selectedExam): ?><a href="<?= url('/results') ?>" class="btn btn-outline-secondary w-100">پاک کردن</a><?php endif; ?></div></form></div></div>

<?php if (!empty($results)): ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold">نتایج (<?= count($results) ?> دانش‌آموز)</h6><button class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>نام</th><th>کد</th><th>نمره</th><th>درصد</th><th>نمره</th><th>وضعیت</th></tr></thead><tbody>
        <?php foreach ($results as $i => $r): $pct = ($r['total_marks'] ?? 1) > 0 ? round(($r['marks_obtained'] / $r['total_marks']) * 100) : 0; ?>
        <tr><td><?= $i + 1 ?></td><td><strong><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></strong></td><td><code><?= htmlspecialchars($r['student_code']) ?></code></td><td class="fw-bold"><?= $r['marks_obtained'] ?> / <?= $r['total_marks'] ?></td><td><div class="d-flex align-items-center"><span class="me-2"><?= $pct ?>%</span><div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-<?= $pct >= 50 ? 'success' : 'danger' ?>" style="width:<?= $pct ?>%"></div></div></div></td><td><?= $r['grade'] ?? '-' ?></td><td><span class="badge bg-<?= $r['marks_obtained'] >= ($r['passing_marks'] ?? 50) ? 'success' : 'danger' ?>"><?= $r['marks_obtained'] >= ($r['passing_marks'] ?? 50) ? 'قبول' : 'مردود' ?></span></td></tr>
        <?php endforeach; ?>
    </tbody></table></div></div>
</div>
<?php endif; ?>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>