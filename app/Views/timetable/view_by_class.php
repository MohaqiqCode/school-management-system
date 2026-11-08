<?php
$title = 'برنامه هفتگی کلاس';
$timetables = $timetables ?? [];
$days = $days ?? ['Saturday' => 'شنبه', 'Sunday' => 'یکشنبه', 'Monday' => 'دوشنبه', 'Tuesday' => 'سه‌شنبه', 'Wednesday' => 'چهارشنبه', 'Thursday' => 'پنج‌شنبه', 'Friday' => 'جمعه'];
$dayColors = ['Saturday' => '#e74a3b', 'Sunday' => '#3498db', 'Monday' => '#2ecc71', 'Tuesday' => '#f39c12', 'Wednesday' => '#9b59b6', 'Thursday' => '#1abc9c', 'Friday' => '#95a5a6'];

$content = function() use ($timetables, $days, $dayColors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-alt me-2"></i>برنامه هفتگی</h1>
    <div>
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button>
        <a href="<?= url('/timetable') ?>" class="btn btn-secondary btn-sm ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<?php foreach ($days as $dayEn => $dayFa): ?>
<div class="card shadow mb-4">
    <div class="card-header py-3" style="background-color: <?= $dayColors[$dayEn] ?>15; border-right: 4px solid <?= $dayColors[$dayEn] ?>;">
        <h6 class="m-0 fw-bold" style="color: <?= $dayColors[$dayEn] ?>;"><i class="fas fa-calendar-day me-2"></i><?= $dayFa ?></h6>
    </div>
    <div class="card-body p-0">
        <?php $dayTimetables = $timetables[$dayEn] ?? []; ?>
        <?php if (!empty($dayTimetables)): ?>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>ساعت</th><th>درس</th><th>معلم</th><th>بخش</th><th>کلاس درس</th></tr></thead><tbody>
            <?php foreach ($dayTimetables as $tt): ?>
            <tr><td><span class="badge bg-light text-dark"><?= $tt['start_time'] ?> - <?= $tt['end_time'] ?></span></td><td><strong><?= htmlspecialchars($tt['subject_name']) ?></strong></td><td><?= htmlspecialchars($tt['teacher_name'] ?? '') ?></td><td><?= htmlspecialchars($tt['section_name'] ?? '') ?></td><td><?= htmlspecialchars($tt['room_number'] ?? '-') ?></td></tr>
            <?php endforeach; ?>
        </tbody></table></div>
        <?php else: ?>
        <div class="text-center py-4 text-muted">در این روز کلاسی برگزار نمی‌شود</div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<style>@media print{.btn,.no-print{display:none!important}.card{break-inside:avoid;border:1px solid #ddd!important;box-shadow:none!important}}</style>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>