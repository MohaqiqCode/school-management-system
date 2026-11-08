<?php ob_start(); ?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-building me-2"></i>مدیریت شعب
    </h1>
    <div>
        <?php if (hasPermission('create_branches')): ?>
        <a href="<?= url('/branches/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>ایجاد شعبه جدید
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Search and Filter Card -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/branches') ?>" method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search" 
                           placeholder="جستجو بر اساس نام، کد، شهر یا ایمیل..." 
                           value="<?= htmlspecialchars($search ?? '') ?>">
                    <button type="submit" class="btn btn-primary">جستجو</button>
                    <?php if (!empty($search)): ?>
                    <a href="<?= url('/branches') ?>" class="btn btn-outline-secondary">پاک کردن</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-success" onclick="exportData()">
                    <i class="fas fa-download me-1"></i> خروجی Excel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Branches Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary">
            <i class="fas fa-list me-2"></i>لیست شعب
        </h6>
        <span class="badge bg-primary"><?= $total ?? 0 ?> شعبه</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="branchesTable" width="100%">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">کد</th>
                        <th width="20%">نام شعبه</th>
                        <th width="15%">شهر</th>
                        <th width="10%">دانش‌آموزان</th>
                        <th width="10%">معلمان</th>
                        <th width="10%">درآمد ماه</th>
                        <th width="10%">وضعیت</th>
                        <th width="10%">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($branches)): ?>
                        <?php foreach ($branches as $index => $branch): ?>
                        <tr>
                            <td><?= ($currentPage - 1) * 25 + $index + 1 ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($branch['branch_code']) ?></span>
                            </td>
                            <td>
                                <a href="<?= url('/branches/' . $branch['id']) ?>" class="text-decoration-none fw-bold">
                                    <?= htmlspecialchars($branch['name']) ?>
                                </a>
                                <?php if ($branch['principal_name']): ?>
                                <br><small class="text-muted">مدیر: <?= htmlspecialchars($branch['principal_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                <?= htmlspecialchars($branch['city'] ?? '-') ?>
                            </td>
                            <td>
                                <span class="badge bg-success"><?= number_format($branch['student_count'] ?? 0) ?> نفر</span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= number_format($branch['teacher_count'] ?? 0) ?> نفر</span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">
                                    <?= number_format($branch['monthly_revenue'] ?? 0) ?> ت
                                </span>
                            </td>
                            <td>
                                <?php if ($branch['is_active']): ?>
                                <span class="badge bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge bg-danger">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/branches/' . $branch['id']) ?>" 
                                       class="btn btn-info" title="مشاهده" data-bs-toggle="tooltip">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (hasPermission('edit_branches')): ?>
                                    <a href="<?= url('/branches/' . $branch['id'] . '/edit') ?>" 
                                       class="btn btn-warning" title="ویرایش" data-bs-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('manage_branch_settings')): ?>
                                    <a href="<?= url('/branches/' . $branch['id'] . '/settings') ?>" 
                                       class="btn btn-secondary" title="تنظیمات" data-bs-toggle="tooltip">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('delete_branches')): ?>
                                    <button type="button" class="btn btn-danger" 
                                            title="حذف" data-bs-toggle="tooltip"
                                            onclick="confirmDelete(<?= $branch['id'] ?>, '<?= htmlspecialchars($branch['name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-2 d-block"></i>
                                هیچ شعبه‌ای یافت نشد
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search ?? '') ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search ?? '') ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>آیا از حذف شعبه <strong id="deleteBranchName"></strong> اطمینان دارید؟</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    این عملیات قابل بازگشت نیست!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> حذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteBranchName').textContent = name;
        document.getElementById('deleteForm').action = '<?= url('/branches') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    
    function exportData() {
        window.location.href = '<?= url('/api/export/branches') ?>?' + new URLSearchParams({
            search: '<?= $search ?? '' ?>',
            format: 'excel'
        }).toString();
    }
    
    $(document).ready(function() {
        $('#branchesTable').DataTable({
            responsive: true,
            paging: false,
            searching: false,
            info: false,
            order: [[0, 'asc']],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json'
            }
        });
    });
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>