<?php
$title = 'مدیریت آزمون‌ها';
$exams = $exams ?? [];
$academicYears = $academicYears ?? [];
$classes = $classes ?? [];
$selectedYear = $selectedYear ?? '';
$selectedClass = $selectedClass ?? '';

$content = function() use ($exams, $academicYears, $classes, $selectedYear, $selectedClass) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-alt me-2"></i>مدیریت آزمون‌ها</h1>
    <?php if (hasPermission('create_exams')): ?>
    <a href="<?= url('/exams/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>ایجاد آزمون جدید</a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/exams') ?>" method="GET" class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="academic_year_id" onchange="this.form.submit()">
                    <option value="">همه سال‌های تحصیلی</option>
                    <?php foreach ($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>" <?= $selectedYear == $ay['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ay['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="class_id" onchange="this.form.submit()">
                    <option value="">همه کلاس‌ها</option>
                    <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= $selectedClass == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <?php if ($selectedYear || $selectedClass): ?>
                <a href="<?= url('/exams') ?>" class="btn btn-outline-secondary w-100"><i class="fas fa-times me-1"></i>پاک کردن فیلتر</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Exams Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>لیست آزمون‌ها</h6>
        <span class="badge bg-primary rounded-pill"><?= count($exams ?? []) ?> آزمون</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>نوع</th><th>درس</th><th>کلاس</th><th>تاریخ</th><th>نمره کل</th><th>نمره قبولی</th><th>وضعیت</th><th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($exams)): ?>
                        <?php foreach ($exams as $i => $exam): 
                            $statusColors = ['scheduled'=>'info','ongoing'=>'warning','completed'=>'success','cancelled'=>'danger'];
                            $statusTexts = ['scheduled'=>'برنامه‌ریزی شده','ongoing'=>'در حال برگزاری','completed'=>'تکمیل شده','cancelled'=>'لغو شده'];
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($exam['exam_type_name'] ?? '') ?></span></td>
                            <td><strong><?= htmlspecialchars($exam['subject_name']) ?></strong></td>
                            <td><?= htmlspecialchars($exam['class_name']) ?></td>
                            <td><small><?= date('Y/m/d', strtotime($exam['exam_date'] ?? '')) ?></small><br><small class="text-muted"><?= $exam['start_time'] ?? '' ?> - <?= $exam['end_time'] ?? '' ?></small></td>
                            <td class="text-center"><?= $exam['total_marks'] ?></td>
                            <td class="text-center"><?= $exam['passing_marks'] ?></td>
                            <td><span class="badge bg-<?= $statusColors[$exam['status']] ?? 'secondary' ?>"><?= $statusTexts[$exam['status']] ?? $exam['status'] ?></span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($exam['status'] === 'scheduled' || $exam['status'] === 'ongoing'): ?>
                                    <a href="<?= url('/exams/' . $exam['id'] . '/marks') ?>" class="btn btn-success" title="ثبت نمرات"><i class="fas fa-pen"></i></a>
                                    <?php endif; ?>
                                    <?php if ($exam['status'] === 'completed'): ?>
                                    <a href="<?= url('/results?exam_id=' . $exam['id']) ?>" class="btn btn-info" title="مشاهده نتایج"><i class="fas fa-eye"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('edit_exams')): ?>
                                    <a href="<?= url('/exams/' . $exam['id'] . '/edit') ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('delete_exams')): ?>
                                    <button class="btn btn-danger" onclick="confirmDeleteExam(<?= $exam['id'] ?>, '<?= htmlspecialchars($exam['subject_name']) ?>')"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-file-alt fa-3x mb-2 d-block"></i>آزمونی یافت نشد</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmDeleteExam(id, name) {
        Swal.fire({ title: 'حذف آزمون', text: 'آیا از حذف آزمون "' + name + '" اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => { if (r.isConfirmed) { const f = document.createElement('form'); f.method = 'POST'; f.action = '<?= url('/exams') ?>/' + id; f.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE">'; document.body.appendChild(f); f.submit(); } });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>