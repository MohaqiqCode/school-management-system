<?php
$title = 'مدیریت کاربران';
$users = $users ?? [];
$roles = $roles ?? [];
$branches = $branches ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
$roleFilter = $roleFilter ?? '';
$branchFilter = $branchFilter ?? '';
$statusFilter = $statusFilter ?? '';

$content = function() use ($users, $roles, $branches, $currentPage, $totalPages, $total, $search, $roleFilter, $branchFilter, $statusFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users-cog me-2"></i>مدیریت کاربران</h1>
    <?php if (hasPermission('create_users')): ?>
    <a href="<?= url('/users/create') ?>" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i>ایجاد کاربر جدید</a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/users') ?>" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="جستجو بر اساس نام، ایمیل یا کد کاربری..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="role">
                    <option value="">همه نقش‌ها</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= $roleFilter == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['display_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="branch">
                    <option value="">همه شعب</option>
                    <?php foreach ($branches as $br): ?>
                    <option value="<?= $br['id'] ?>" <?= $branchFilter == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>فعال</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>غیرفعال</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>فیلتر</button>
                <?php if ($search || $roleFilter || $branchFilter || $statusFilter !== ''): ?>
                <a href="<?= url('/users') ?>" class="btn btn-outline-secondary w-100 mt-1">پاک کردن</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>لیست کاربران</h6>
        <span class="badge bg-primary rounded-pill"><?= number_format($total) ?> کاربر</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="8%">تصویر</th>
                        <th width="18%">نام</th>
                        <th width="18%">ایمیل</th>
                        <th width="10%">کد کاربری</th>
                        <th width="12%">نقش</th>
                        <th width="12%">شعبه</th>
                        <th width="8%">وضعیت</th>
                        <th width="9%">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $i => $user): ?>
                        <tr>
                            <td><?= ($currentPage - 1) * 25 + $i + 1 ?></td>
                            <td>
                                <img src="<?= $user['profile_photo'] ?? asset('images/default-avatar.png') ?>" 
                                     class="rounded-circle" width="38" height="38" alt="">
                            </td>
                            <td>
                                <a href="<?= url('/users/' . $user['id']) ?>" class="text-decoration-none fw-bold">
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                </a>
                                <?php if (!empty($user['phone'])): ?>
                                <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($user['phone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars($user['email']) ?></small></td>
                            <td><code><?= htmlspecialchars($user['user_code']) ?></code></td>
                            <td>
                                <span class="badge" style="background-color:<?= $user['color'] ?? '#858796' ?>; color:white;">
                                    <i class="<?= $user['icon'] ?? 'fas fa-user' ?> me-1"></i>
                                    <?= htmlspecialchars($user['role_display'] ?? $user['role_name'] ?? '') ?>
                                </span>
                            </td>
                            <td><small><?= htmlspecialchars($user['branch_name'] ?? '-') ?></small></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge bg-danger">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/users/' . $user['id']) ?>" class="btn btn-info" title="مشاهده" data-bs-toggle="tooltip"><i class="fas fa-eye"></i></a>
                                    <?php if (hasPermission('edit_users')): ?>
                                    <a href="<?= url('/users/' . $user['id'] . '/edit') ?>" class="btn btn-warning" title="ویرایش" data-bs-toggle="tooltip"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('change_user_status')): ?>
                                    <button type="button" class="btn btn-<?= $user['is_active'] ? 'secondary' : 'success' ?>" title="<?= $user['is_active'] ? 'غیرفعال کردن' : 'فعال کردن' ?>" onclick="toggleStatus(<?= $user['id'] ?>, this)"><i class="fas fa-<?= $user['is_active'] ? 'ban' : 'check' ?>"></i></button>
                                    <?php endif; ?>
                                    <?php if (hasPermission('delete_users')): ?>
                                    <button type="button" class="btn btn-danger" title="حذف" onclick="confirmDeleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>')"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-users-slash fa-3x mb-2 d-block"></i>هیچ کاربری یافت نشد</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination justify-content-center mb-0">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&role=<?= $roleFilter ?>&branch=<?= $branchFilter ?>&status=<?= $statusFilter ?>"><?= $p ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><p id="deleteUserMsg"></p></div><div class="modal-footer justify-content-center"><button class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><form id="deleteUserForm" method="POST"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="btn btn-danger">حذف</button></form></div></div></div>
</div>

<script>
    function toggleStatus(id, btn) {
        fetch('<?= url('/users') ?>/' + id + '/toggle-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>' }
        }).then(r => r.json()).then(d => { if (d.success) location.reload(); else toastr.error(d.message); });
    }
    function confirmDeleteUser(id, name) {
        document.getElementById('deleteUserMsg').textContent = 'آیا از حذف کاربر "' + name + '" اطمینان دارید؟';
        document.getElementById('deleteUserForm').action = '<?= url('/users') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>