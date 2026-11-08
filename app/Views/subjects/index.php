<?php
$title = 'مدیریت دروس';
$subjects = $subjects ?? [];
$departments = $departments ?? [];
$departmentFilter = $departmentFilter ?? '';

$content = function() use ($subjects, $departments, $departmentFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-book me-2"></i>مدیریت دروس
    </h1>
    <?php if (hasPermission('create_subjects')): ?>
    <a href="<?= url('/subjects/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>ایجاد درس جدید
    </a>
    <?php endif; ?>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/subjects') ?>" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-bold">فیلتر بر اساس دپارتمان</label>
                <select class="form-select" name="department_id" onchange="this.form.submit()">
                    <option value="">همه دپارتمان‌ها</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $departmentFilter == $dept['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <?php if ($departmentFilter): ?>
                <a href="<?= url('/subjects') ?>" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>پاک کردن فیلتر
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Subjects Grid -->
<div class="row">
    <?php if (!empty($subjects)): ?>
        <?php foreach ($subjects as $subject): ?>
        <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
            <div class="card shadow h-100 border-top border-4 <?= $subject['is_core'] ? 'border-primary' : 'border-info' ?>">
                <div class="card-body text-center">
                    <!-- Subject Icon -->
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 60px; height: 60px;">
                            <i class="fas fa-book fa-2x <?= $subject['is_core'] ? 'text-primary' : 'text-info' ?>"></i>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($subject['subject_name']) ?></h6>
                    <small class="text-muted d-block mb-2">
                        <code><?= htmlspecialchars($subject['subject_code']) ?></code>
                    </small>
                    
                    <div class="mb-2">
                        <small class="text-muted"><?= htmlspecialchars($subject['department_name'] ?? '') ?></small>
                    </div>
                    
                    <!-- Badges -->
                    <div class="d-flex justify-content-center gap-1 mb-3 flex-wrap">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-clock me-1"></i><?= $subject['credit_hours'] ?? '1' ?> واحد
                        </span>
                        <?php if ($subject['is_core']): ?>
                        <span class="badge bg-primary">اصلی</span>
                        <?php else: ?>
                        <span class="badge bg-info">اختیاری</span>
                        <?php endif; ?>
                        <?php if ($subject['is_active']): ?>
                        <span class="badge bg-success">فعال</span>
                        <?php else: ?>
                        <span class="badge bg-danger">غیرفعال</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Class Count -->
                    <small class="text-muted d-block mb-3">
                        در <?= $subject['class_count'] ?? 0 ?> کلاس تدریس می‌شود
                    </small>
                    
                    <?php if (!empty($subject['description'])): ?>
                    <p class="small text-muted mb-3"><?= htmlspecialchars(mb_substr($subject['description'], 0, 60)) ?><?= mb_strlen($subject['description']) > 60 ? '...' : '' ?></p>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div class="d-flex gap-1">
                        <?php if (hasPermission('edit_subjects')): ?>
                        <a href="<?= url('/subjects/' . $subject['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('delete_subjects') && ($subject['class_count'] ?? 0) == 0): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="confirmDeleteSubject(<?= $subject['id'] ?>, '<?= htmlspecialchars($subject['subject_name']) ?>')" 
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
                <i class="fas fa-book fa-4x mb-3 d-block"></i>
                <h5>هیچ درسی یافت نشد</h5>
                <?php if (hasPermission('create_subjects')): ?>
                <a href="<?= url('/subjects/create') ?>" class="btn btn-primary mt-2">ایجاد اولین درس</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-book fa-3x text-danger mb-3"></i>
                <h5 id="deleteSubjectTitle"></h5>
                <p class="text-muted">این عملیات قابل بازگشت نیست.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteSubjectForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteSubject(id, name) {
        document.getElementById('deleteSubjectTitle').textContent = 'آیا از حذف درس "' + name + '" اطمینان دارید؟';
        document.getElementById('deleteSubjectForm').action = '<?= url('/subjects') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteSubjectModal')).show();
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>