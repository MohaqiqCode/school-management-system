<?php
$title = 'مدیریت بخش‌ها';
$sections = $sections ?? [];
$classes = $classes ?? [];
$selectedClass = $selectedClass ?? '';

$content = function() use ($sections, $classes, $selectedClass) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-layer-group me-2"></i>مدیریت بخش‌ها
    </h1>
    <?php if (hasPermission('create_sections')): ?>
    <a href="<?= url('/sections/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>ایجاد بخش جدید
    </a>
    <?php endif; ?>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/sections') ?>" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-bold">فیلتر بر اساس کلاس</label>
                <select class="form-select" name="class_id" onchange="this.form.submit()">
                    <option value="">همه کلاس‌ها</option>
                    <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= $selectedClass == $cl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cl['class_name']) ?> (پایه <?= $cl['class_level'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <?php if ($selectedClass): ?>
                <a href="<?= url('/sections') ?>" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>پاک کردن فیلتر
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Sections Grid -->
<div class="row">
    <?php if (!empty($sections)): ?>
        <?php foreach ($sections as $section): 
            $occupancy = ($section['capacity'] ?? 1) > 0 ? round((($section['student_count'] ?? 0) / $section['capacity']) * 100) : 0;
        ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-4 <?= $occupancy > 80 ? 'border-danger' : ($occupancy > 50 ? 'border-warning' : 'border-success') ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">بخش <?= htmlspecialchars($section['section_name']) ?></h5>
                            <small class="text-muted">
                                <code><?= htmlspecialchars($section['section_code']) ?></code> | 
                                <?= htmlspecialchars($section['class_name'] ?? '') ?> (پایه <?= $section['class_level'] ?? '' ?>)
                            </small>
                        </div>
                        <div>
                            <?php if ($section['is_active'] ?? false): ?>
                            <span class="badge bg-success">فعال</span>
                            <?php else: ?>
                            <span class="badge bg-danger">غیرفعال</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Teacher & Room -->
                    <div class="bg-light rounded p-2 mb-3">
                        <div class="small">
                            <i class="fas fa-user-tie me-1 text-primary"></i>
                            <strong>معلم:</strong> <?= htmlspecialchars($section['class_teacher_name'] ?? 'تعیین نشده') ?>
                        </div>
                        <?php if (!empty($section['room_number'])): ?>
                        <div class="small mt-1">
                            <i class="fas fa-door-open me-1 text-info"></i>
                            <strong>کلاس درس:</strong> <?= htmlspecialchars($section['room_number']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Student Count & Capacity -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>دانش‌آموزان</span>
                            <strong><?= $section['student_count'] ?? 0 ?> / <?= $section['capacity'] ?? 0 ?></strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?= $occupancy > 80 ? 'danger' : ($occupancy > 50 ? 'warning' : 'success') ?>" 
                                 style="width: <?= $occupancy ?>%;" role="progressbar"></div>
                        </div>
                        <small class="text-muted"><?= $occupancy ?>% تکمیل</small>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <?php if (hasPermission('edit_sections')): ?>
                        <a href="<?= url('/sections/' . $section['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1">
                            <i class="fas fa-edit me-1"></i>ویرایش
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('/students?section_id=' . $section['id']) ?>" class="btn btn-outline-info btn-sm flex-grow-1">
                            <i class="fas fa-users me-1"></i>دانش‌آموزان
                        </a>
                        <?php if (hasPermission('delete_sections') && ($section['student_count'] ?? 0) == 0): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="confirmDeleteSection(<?= $section['id'] ?>, 'بخش <?= htmlspecialchars($section['section_name']) ?>')" 
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
                <i class="fas fa-layer-group fa-4x mb-3 d-block"></i>
                <h5>هیچ بخشی یافت نشد</h5>
                <?php if (hasPermission('create_sections')): ?>
                <a href="<?= url('/sections/create') ?>" class="btn btn-primary mt-2">ایجاد اولین بخش</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteSectionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                <h5 id="deleteSectionTitle"></h5>
                <p class="text-muted">این عملیات قابل بازگشت نیست.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteSectionForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteSection(id, name) {
        document.getElementById('deleteSectionTitle').textContent = 'آیا از حذف ' + name + ' اطمینان دارید؟';
        document.getElementById('deleteSectionForm').action = '<?= url('/sections') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteSectionModal')).show();
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>