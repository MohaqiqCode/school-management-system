<?php
$title = 'مدیریت سال‌های تحصیلی';
$academicYears = $academicYears ?? [];

$content = function() use ($academicYears) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-calendar-alt me-2"></i>مدیریت سال‌های تحصیلی
    </h1>
    <?php if (hasPermission('create_academic_years')): ?>
    <a href="<?= url('/academic-years/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>ایجاد سال تحصیلی جدید
    </a>
    <?php endif; ?>
</div>

<!-- Academic Years Timeline Style -->
<div class="row">
    <?php if (!empty($academicYears)): ?>
        <?php foreach ($academicYears as $year): 
            $isCurrent = $year['is_current'] ?? false;
            $isActive = ($year['status'] ?? '') === 'active';
            $startDate = $year['start_date'] ?? '';
            $endDate = $year['end_date'] ?? '';
            $totalDays = ceil((strtotime($endDate) - strtotime($startDate)) / 86400);
            $daysPassed = ceil((time() - strtotime($startDate)) / 86400);
            $progress = $totalDays > 0 ? min(100, max(0, round(($daysPassed / $totalDays) * 100))) : 0;
        ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-4 <?= $isCurrent ? 'border-success' : ($isActive ? 'border-primary' : 'border-secondary') ?>">
                <div class="card-body">
                    <!-- Status Badge -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <?php if ($isCurrent): ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>سال جاری</span>
                            <?php elseif ($isActive): ?>
                            <span class="badge bg-primary">فعال</span>
                            <?php elseif ($year['status'] === 'archived'): ?>
                            <span class="badge bg-secondary">آرشیو شده</span>
                            <?php else: ?>
                            <span class="badge bg-warning">غیرفعال</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted"><?= $year['semester_count'] ?? 0 ?> ترم</small>
                    </div>

                    <!-- Year Name -->
                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($year['year_name']) ?></h5>
                    <small class="text-muted d-block mb-3"><?= htmlspecialchars($year['branch_name'] ?? '') ?></small>

                    <!-- Date Range -->
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">شروع</small>
                        <strong><?= date('Y/m/d', strtotime($startDate)) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <small class="text-muted">پایان</small>
                        <strong><?= date('Y/m/d', strtotime($endDate)) ?></strong>
                    </div>

                    <!-- Progress Bar -->
                    <?php if ($isActive): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>پیشرفت</span>
                            <span><?= $progress ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-<?= $progress > 80 ? 'warning' : 'success' ?>" 
                                 style="width: <?= $progress ?>%;" 
                                 role="progressbar"></div>
                        </div>
                        <small class="text-muted"><?= $daysPassed ?> روز از <?= $totalDays ?> روز گذشته</small>
                    </div>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge bg-info"><?= $year['semester_count'] ?? 0 ?> ترم</span>
                        <span class="badge bg-primary"><?= $year['promotion_count'] ?? 0 ?> ارتقاء</span>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 mt-auto">
                        <?php if (hasPermission('edit_academic_years')): ?>
                        <a href="<?= url('/academic-years/' . $year['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1">
                            <i class="fas fa-edit me-1"></i>ویرایش
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('/semesters?academic_year_id=' . $year['id']) ?>" class="btn btn-outline-info btn-sm flex-grow-1">
                            <i class="fas fa-list me-1"></i>ترم‌ها
                        </a>
                        <?php if (hasPermission('delete_academic_years') && ($year['semester_count'] ?? 0) == 0): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteYear(<?= $year['id'] ?>, '<?= htmlspecialchars($year['year_name']) ?>')" title="حذف">
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
                <i class="fas fa-calendar-alt fa-4x mb-3 d-block"></i>
                <h5>هیچ سال تحصیلی تعریف نشده است</h5>
                <?php if (hasPermission('create_academic_years')): ?>
                <a href="<?= url('/academic-years/create') ?>" class="btn btn-primary mt-2">ایجاد اولین سال تحصیلی</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Legend -->
<?php if (!empty($academicYears)): ?>
<div class="card shadow mt-2">
    <div class="card-body">
        <div class="d-flex gap-4">
            <div><span class="badge bg-success me-1">&nbsp;</span> سال جاری</div>
            <div><span class="badge bg-primary me-1">&nbsp;</span> فعال</div>
            <div><span class="badge bg-secondary me-1">&nbsp;</span> آرشیو شده</div>
            <div><span class="badge bg-warning me-1">&nbsp;</span> غیرفعال</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Modal -->
<div class="modal fade" id="deleteYearModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-danger mb-3"></i>
                <h5 id="deleteYearTitle"></h5>
                <p class="text-muted">تمامی ترم‌ها و داده‌های مرتبط نیز حذف خواهند شد.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteYearForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteYear(id, name) {
        document.getElementById('deleteYearTitle').textContent = 'آیا از حذف سال تحصیلی "' + name + '" اطمینان دارید؟';
        document.getElementById('deleteYearForm').action = '<?= url('/academic-years') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteYearModal')).show();
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>