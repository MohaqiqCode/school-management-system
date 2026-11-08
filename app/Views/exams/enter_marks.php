<?php
$title = 'ثبت نمرات';
$exam = $exam ?? [];
$students = $students ?? [];

$content = function() use ($exam, $students) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0"><i class="fas fa-pen me-2"></i>ثبت نمرات</h1><p class="text-muted mb-0"><?= htmlspecialchars($exam['subject_name'] ?? '') ?> | <?= htmlspecialchars($exam['class_name'] ?? '') ?> | نمره کل: <?= $exam['total_marks'] ?? 0 ?> | قبولی: <?= $exam['passing_marks'] ?? 0 ?></p></div>
    <a href="<?= url('/exams') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<form action="<?= url('/exams/' . ($exam['id'] ?? '') . '/marks') ?>" method="POST" id="marksForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 fw-bold">لیست دانش‌آموزان (<?= count($students ?? []) ?>)</h6>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="autoFillMarks()"><i class="fas fa-magic me-1"></i>پر کردن خودکار</button>
        </div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>نام</th><th>کد</th><th>نمره</th><th>نمره عملی</th><th>ارزیابی داخلی</th><th>غایب</th><th>توضیحات</th></tr></thead><tbody>
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($s['student_code']) ?></code></td>
                    <td><input type="number" class="form-control form-control-sm marks-input" name="marks[<?= $s['id'] ?>][marks_obtained]" value="<?= $s['marks_obtained'] ?? '' ?>" step="0.01" min="0" max="<?= $exam['total_marks'] ?? 100 ?>" style="width:90px" <?= $s['is_absent'] ? 'disabled' : '' ?>></td>
                    <td><input type="number" class="form-control form-control-sm" name="marks[<?= $s['id'] ?>][practical_marks]" value="<?= $s['practical_marks'] ?? '0' ?>" step="0.01" style="width:80px"></td>
                    <td><input type="number" class="form-control form-control-sm" name="marks[<?= $s['id'] ?>][internal_assessment]" value="<?= $s['internal_assessment'] ?? '0' ?>" step="0.01" style="width:80px"></td>
                    <td><div class="form-check"><input class="form-check-input absent-check" type="checkbox" name="marks[<?= $s['id'] ?>][is_absent]" value="1" <?= $s['is_absent'] ? 'checked' : '' ?> onchange="toggleMarks(this)"></div></td>
                    <td><input type="text" class="form-control form-control-sm" name="marks[<?= $s['id'] ?>][remarks]" value="<?= htmlspecialchars($s['remarks'] ?? '') ?>" placeholder="توضیحات..."></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-4">دانش‌آموزی یافت نشد</td></tr>
            <?php endif; ?>
        </tbody></table></div></div>
        <div class="card-footer"><button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>ذخیره نمرات</button></div>
    </div>
</form>

<script>
    function toggleMarks(cb) { const row = cb.closest('tr'); const inputs = row.querySelectorAll('.marks-input'); inputs.forEach(inp => inp.disabled = cb.checked); if (cb.checked) inputs.forEach(inp => inp.value = ''); }
    function autoFillMarks() { const max = <?= $exam['total_marks'] ?? 100 ?>; document.querySelectorAll('.marks-input').forEach(inp => { if (!inp.disabled && !inp.value) inp.value = (Math.floor(Math.random() * (max * 0.4)) + max * 0.5).toFixed(1); }); }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>