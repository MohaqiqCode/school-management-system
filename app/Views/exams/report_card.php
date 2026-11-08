<?php
$title = 'کارنامه تحصیلی';
$student = $student ?? [];
$results = $results ?? [];

$content = function() use ($student, $results) {
    ob_start();
    $gpa = 0; $totalPoints = 0; $subjectCount = count($results ?? []);
    foreach ($results as $r) { $totalPoints += $r['grade_point'] ?? 0; }
    $gpa = $subjectCount > 0 ? round($totalPoints / $subjectCount, 2) : 0;
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-file-alt me-2"></i>کارنامه تحصیلی</h1>
    <div class="no-print">
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button>
        <a href="<?= url('/students/' . ($student['id'] ?? '')) ?>" class="btn btn-secondary btn-sm ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="card shadow mb-4" id="reportCard">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="text-center border-bottom pb-3 mb-4">
            <h4 class="fw-bold"><?= config('app_name', 'سیستم مدیریت مدرسه') ?></h4>
            <h5>کارنامه تحصیلی</h5>
        </div>

        <!-- Student Info -->
        <div class="row mb-4">
            <div class="col-md-6"><table class="table table-sm table-borderless"><tr><td width="120"><strong>نام دانش‌آموز:</strong></td><td><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></td></tr><tr><td><strong>کد دانش‌آموزی:</strong></td><td><?= htmlspecialchars($student['student_code'] ?? '') ?></td></tr><tr><td><strong>کلاس:</strong></td><td><?= htmlspecialchars($student['class_name'] ?? '') ?> - <?= htmlspecialchars($student['section_name'] ?? '') ?></td></tr></table></div>
            <div class="col-md-6 text-end"><div class="d-inline-block text-start"><table class="table table-sm table-borderless"><tr><td><strong>تاریخ چاپ:</strong></td><td><?= date('Y/m/d') ?></td></tr><tr><td><strong>معدل:</strong></td><td><span class="badge bg-<?= $gpa >= 3 ? 'success' : ($gpa >= 2 ? 'warning' : 'danger') ?> fs-6"><?= $gpa ?></span></td></tr></table></div></div>
        </div>

        <!-- Results Table -->
        <div class="table-responsive"><table class="table table-bordered text-center"><thead class="table-light"><tr><th>#</th><th>درس</th><th>نوع آزمون</th><th>نمره</th><th>نمره کل</th><th>درصد</th><th>نمره</th><th>امتیاز</th><th>وضعیت</th></tr></thead><tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $i => $r): $pct = ($r['total_marks'] ?? 1) > 0 ? round(($r['marks_obtained'] / $r['total_marks']) * 100) : 0; ?>
                <tr><td><?= $i + 1 ?></td><td><?= htmlspecialchars($r['subject_name']) ?></td><td><?= htmlspecialchars($r['exam_type']) ?></td><td class="fw-bold"><?= $r['marks_obtained'] ?></td><td><?= $r['total_marks'] ?></td><td><?= $pct ?>%</td><td><?= $r['grade'] ?></td><td><?= $r['grade_point'] ?></td><td><span class="badge bg-<?= $r['marks_obtained'] >= ($r['passing_marks'] ?? 50) ? 'success' : 'danger' ?>"><?= $r['marks_obtained'] >= ($r['passing_marks'] ?? 50) ? 'قبول' : 'مردود' ?></span></td></tr>
                <?php endforeach; ?>
            <?php else: ?><tr><td colspan="9" class="text-center py-3">نتیجه‌ای ثبت نشده</td></tr><?php endif; ?>
        </tbody></table></div>

        <!-- Signatures -->
        <div class="row mt-5 pt-4"><div class="col-4 text-center"><div class="border-top pt-2">امضای معلم</div></div><div class="col-4 text-center"><div class="border-top pt-2">امضای مدیر</div></div><div class="col-4 text-center"><div class="border-top pt-2">امضای والدین</div></div></div>
    </div>
</div>

<style>@media print{body{background:white}.no-print{display:none!important}.card{box-shadow:none!important;border:1px solid #ddd!important}}</style>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>