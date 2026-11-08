<?php
$title = 'مدیریت نقش‌ها و مجوزها';
$roles = $roles ?? [];

$content = function() use ($roles) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-user-shield me-2"></i>مدیریت نقش‌ها و مجوزها
    </h1>
    <?php if (hasPermission('create_roles')): ?>
    <a href="<?= url('/roles/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>ایجاد نقش جدید
    </a>
    <?php endif; ?>
</div>

<!-- Roles Grid -->
<div class="row">
    <?php if (!empty($roles)): ?>
        <?php foreach ($roles as $role): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-start border-4" style="border-color: <?= htmlspecialchars($role['color'] ?? '#858796') ?> !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 55px; height: 55px; background-color: <?= htmlspecialchars($role['color'] ?? '#858796') ?>20;">
                            <i class="<?= htmlspecialchars($role['icon'] ?? 'fas fa-user') ?> fa-lg" 
                               style="color: <?= htmlspecialchars($role['color'] ?? '#858796') ?>;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($role['display_name']) ?></h5>
                            <small class="text-muted"><?= htmlspecialchars($role['name']) ?></small>
                        </div>
                        <?php if ($role['is_system_role'] ?? false): ?>
                        <span class="badge bg-warning ms-auto" title="نقش سیستمی" data-bs-toggle="tooltip">
                            <i class="fas fa-lock"></i>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-muted small mb-3"><?= htmlspecialchars(mb_substr($role['description'] ?? '', 0, 100)) ?><?= mb_strlen($role['description'] ?? '') > 100 ? '...' : '' ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-primary rounded-pill me-1"><?= $role['user_count'] ?? 0 ?> کاربر</span>
                            <span class="badge bg-info rounded-pill"><?= $role['permission_count'] ?? 0 ?> مجوز</span>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <?php if (hasPermission('edit_roles') && ($role['can_be_modified'] ?? true)): ?>
                        <a href="<?= url('/roles/' . $role['id'] . '/edit') ?>" class="btn btn-outline-warning btn-sm flex-grow-1">
                            <i class="fas fa-edit me-1"></i>ویرایش
                        </a>
                        <?php endif; ?>
                        
                        <a href="<?= url('/roles/' . $role['id'] . '/edit') ?>" class="btn btn-outline-info btn-sm flex-grow-1">
                            <i class="fas fa-key me-1"></i>مجوزها
                        </a>
                        
                        <?php if (hasPermission('delete_roles') && ($role['can_be_deleted'] ?? true) && ($role['user_count'] ?? 0) == 0): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="confirmDeleteRole(<?= $role['id'] ?>, '<?= htmlspecialchars($role['display_name']) ?>')"
                                title="حذف" data-bs-toggle="tooltip">
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
                <i class="fas fa-shield-alt fa-4x mb-3 d-block"></i>
                <h5>هیچ نقشی تعریف نشده است</h5>
                <a href="<?= url('/roles/create') ?>" class="btn btn-primary mt-2">ایجاد اولین نقش</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Summary Cards -->
<?php if (!empty($roles)): ?>
<div class="row mt-2">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>خلاصه</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>نقش</th>
                                <th>نوع</th>
                                <th>تعداد کاربران</th>
                                <th>تعداد مجوزها</th>
                                <th>قابل ویرایش</th>
                                <th>قابل حذف</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($role['display_name']) ?></span>
                                </td>
                                <td>
                                    <?php if ($role['is_system_role'] ?? false): ?>
                                    <span class="badge bg-warning text-dark">سیستمی</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">سفارشی</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-primary"><?= $role['user_count'] ?? 0 ?></span></td>
                                <td><span class="badge bg-info"><?= $role['permission_count'] ?? 0 ?></span></td>
                                <td>
                                    <?php if ($role['can_be_modified'] ?? true): ?>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <?php else: ?>
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($role['can_be_deleted'] ?? true) && ($role['user_count'] ?? 0) == 0): ?>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <?php else: ?>
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف نقش</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-shield-alt fa-3x text-danger mb-3"></i>
                <h5 id="deleteRoleTitle">آیا از حذف این نقش اطمینان دارید؟</h5>
                <p class="text-muted">این عملیات قابل بازگشت نیست. کاربران این نقش بدون نقش خواهند ماند.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteRoleForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف نقش</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteRole(id, name) {
        document.getElementById('deleteRoleTitle').textContent = 'آیا از حذف نقش "' + name + '" اطمینان دارید؟';
        document.getElementById('deleteRoleForm').action = '<?= url('/roles') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteRoleModal')).show();
    }
    
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
    });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>