<?php
$title = 'مدیریت ترم‌ها';
$semesters = $semesters ?? [];
$academicYears = $academicYears ?? [];
$academicYearFilter = $academicYearFilter ?? '';

$content = function() use ($semesters, $academicYears, $academicYearFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-calendar-week me-2"></i>مدیریت ترم‌های تحصیلی
    </h1>
    <?php if (hasPermission('create_semesters')): ?>
    <a href="<?= url('/semesters/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>ایجاد ترم جدید
    </a>
    <?php endif; ?>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/semesters') ?>" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-bold">فیلتر بر اساس سال تحصیلی</label>
                <select class="form-select" name="academic_year_id" onchange="this.form.submit()">
                    <option value="">همه سال‌ها</option>
                    <?php foreach ($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>" <?= $academicYearFilter == $ay['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ay['year_name']) ?> <?= $ay['is_current'] ? '(جاری)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <?php if ($academicYearFilter): ?>
                <a href="<?= url('/semesters') ?>" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>پاک کردن فیلتر
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Semesters Grid -->
<div class="row">
    <?php if (!empty($semesters)): ?>
        <?php foreach ($semesters as $semester): 
            $isCurrent = $semester['is_current'] ?? false;
            $startDate = $semester['start_date'] ?? '';
            $endDate = $semester['end_date'] ?? '';
            $totalDays = ceil((strtotime($endDate) - strtotime($startDate)) / 86400);
            $daysPassed = ceil((time() - strtotime($startDate)) / 86400);
            $progress = $totalDays > 0 ? min(100, max(0, round(($daysPassed / $totalDays) * 100))) : 0;
            $isActive = strtotime($startDate) <= time() && strtotime($endDate) >= time();
        ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-4 <?= $isCurrent ? 'border-success' : ($isActive ? 'border-primary' : 'border-secondary') ?>">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($semester['semester_name']) ?></h5>
                            <small class="text-muted">
                                ترم <?= $semester['semester_number'] ?? '' ?> | 
                                <?= htmlspecialchars($semester['year_name'] ?? '') ?>
                            </small>
                        </div>
                        <div>
                            <?php if ($isCurrent): ?>
                            <span class="badge bg-success"><i class="fas fa-star me-1"></i>ترم جاری</span>
                            <?php elseif ($isActive): ?>
                            <span class="badge bg-primary">در حال برگزاری</span>
                            <?php elseif (strtotime($endDate) < time()): ?>
                            <span class="badge bg-secondary">پایان یافته</span>
                            <?php else: ?>
                            <span class="badge bg-info">پیش رو</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="bg-light rounded p-2 mb-3">
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">شروع:</span>
                            <strong><?= date('Y/m/d', strtotime($startDate)) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">پایان:</span>
                            <strong><?= date('Y/m/d', strtotime($endDate)) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">مدت:</span>
                            <strong><?= $totalDays ?> روز</strong>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <?php if ($isActive || $isCurrent): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>پیشرفت ترم</span>
                            <span><?= $progress ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?= $progress > 80 ? 'warning' : 'success' ?> progress-bar-striped <?= $isCurrent ? 'progress-bar-animated' : '' ?>" 
                                 style="width: <?= $progress ?>%;" role="progressbar"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge bg-info">
                            <i class="fas fa-calendar-alt me-1"></i><?= $semester['timetable_count'] ?? 0 ?> برنامه
                        </span>
                        <span class="badge bg-warning">
                            <i class="fas fa-file-alt me-1"></i><?= $semester['exam_count'] ?? 0 ?> آزمون
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <?php if (hasPermission('edit_semesters')): ?>
                        <a href="<?= url('/semesters/' . $semester['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1">
                            <i class="fas fa-edit me-1"></i>ویرایش
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('/timetable?semester_id=' . $semester['id']) ?>" class="btn btn-outline-info btn-sm flex-grow-1">
                            <i class="fas fa-clock me-1"></i>برنامه‌ها
                        </a>
                        <?php if (hasPermission('delete_semesters') && ($semester['timetable_count'] ?? 0) == 0): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="confirmDeleteSemester(<?= $semester['id'] ?>, '<?= htmlspecialchars($semester['semester_name']) ?>')" 
                                title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-week fa-4x mb-3 d-block"></i>
                <h5>هیچ ترمی یافت نشد</h5>
                <?php if (hasPermission('create_semesters')): ?>
                <a href="<?= url('/semesters/create') ?>" class="btn btn-primary mt-2">ایجاد اولین ترم</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteSemesterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-danger mb-3"></i>
                <h5 id="deleteSemesterTitle"></h5>
                <p class="text-muted">برنامه‌های هفتگی و آزمون‌های مرتبط نیز تحت تأثیر قرار می‌گیرند.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteSemesterForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteSemester(id, name) {
        document.getElementById('deleteSemesterTitle').textContent = 'آیا از حذف ترم "' + name + '" اطمینان دارید؟';
        document.getElementById('deleteSemesterForm').action = '<?= url('/semesters') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteSemesterModal')).show();
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>