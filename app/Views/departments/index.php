<?php
$title = 'مدیریت دپارتمان‌ها';
$departments = $departments ?? [];
$branches = $branches ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
$branchFilter = $branchFilter ?? '';

$content = function() use ($departments, $branches, $currentPage, $totalPages, $total, $search, $branchFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-sitemap me-2"></i>مدیریت دپارتمان‌ها</h1>
    <?php if (hasPermission('create_departments')): ?>
    <a href="<?= url('/departments/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>ایجاد دپارتمان جدید</a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/departments') ?>" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="جستجو بر اساس نام، کد یا توضیحات..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="branch_id">
                    <option value="">همه شعب</option>
                    <?php foreach ($branches as $branch): ?>
                    <option value="<?= $branch['id'] ?>" <?= $branchFilter == $branch['id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال فیلتر</button>
                <?php if ($search || $branchFilter): ?>
                <a href="<?= url('/departments') ?>" class="btn btn-outline-secondary w-100 mt-1">پاک کردن</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Departments Cards -->
<div class="row">
    <?php if (!empty($departments)): ?>
        <?php foreach ($departments as $dept): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-sitemap fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($dept['name']) ?></h5>
                            <small class="text-muted">
                                <code><?= htmlspecialchars($dept['department_code']) ?></code> | 
                                <?= htmlspecialchars($dept['branch_name'] ?? '') ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if (!empty($dept['description'])): ?>
                    <p class="text-muted small mb-3"><?= htmlspecialchars(mb_substr($dept['description'], 0, 80)) ?><?= mb_strlen($dept['description']) > 80 ? '...' : '' ?></p>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-info me-1"><i class="fas fa-chalkboard-teacher me-1"></i><?= $dept['teacher_count'] ?? 0 ?> معلم</span>
                            <span class="badge bg-success"><i class="fas fa-book me-1"></i><?= $dept['subject_count'] ?? 0 ?> درس</span>
                        </div>
                    </div>

                    <?php if (!empty($dept['head_teacher_name'])): ?>
                    <div class="mb-2 small text-muted">
                        <i class="fas fa-user-tie me-1"></i>مدیر دپارتمان: <strong><?= htmlspecialchars($dept['head_teacher_name']) ?></strong>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2 mt-3">
                        <?php if (hasPermission('edit_departments')): ?>
                        <a href="<?= url('/departments/' . $dept['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1">
                            <i class="fas fa-edit me-1"></i>ویرایش
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('delete_departments')): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteDept(<?= $dept['id'] ?>, '<?= htmlspecialchars($dept['name']) ?>')" title="حذف">
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
                <i class="fas fa-sitemap fa-4x mb-3 d-block"></i>
                <h5>هیچ دپارتمانی یافت نشد</h5>
                <?php if (hasPermission('create_departments')): ?>
                <a href="<?= url('/departments/create') ?>" class="btn btn-primary mt-2">ایجاد اولین دپارتمان</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav><ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p == $currentPage ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&branch_id=<?= $branchFilter ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>

<!-- Delete Modal -->
<div class="modal fade" id="deleteDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-sitemap fa-3x text-danger mb-3"></i>
                <h5 id="deleteDeptTitle"></h5>
                <p class="text-muted">این عملیات قابل بازگشت نیست.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteDeptForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteDept(id, name) {
        document.getElementById('deleteDeptTitle').textContent = 'آیا از حذف دپارتمان "' + name + '" اطمینان دارید؟';
        document.getElementById('deleteDeptForm').action = '<?= url('/departments') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteDeptModal')).show();
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>