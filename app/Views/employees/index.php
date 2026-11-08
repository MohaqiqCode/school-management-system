<?php
$title = 'مدیریت کارمندان';
$employees = $employees ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
$typeFilter = $typeFilter ?? '';

$content = function() use ($employees, $currentPage, $totalPages, $total, $search, $typeFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-briefcase me-2"></i>مدیریت کارمندان</h1>
    <?php if (hasPermission('create_employees')): ?>
    <a href="<?= url('/employees/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>ثبت کارمند جدید</a>
    <?php endif; ?>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/employees') ?>" method="GET" class="row g-3">
            <div class="col-md-4"><input type="text" class="form-control" name="search" placeholder="جستجو..." value="<?= htmlspecialchars($search) ?>"></div>
            <div class="col-md-3">
                <select class="form-select" name="employee_type"><option value="">همه نوع‌ها</option>
                    <option value="administrative" <?= $typeFilter === 'administrative' ? 'selected' : '' ?>>اداری</option>
                    <option value="support" <?= $typeFilter === 'support' ? 'selected' : '' ?>>پشتیبانی</option>
                    <option value="maintenance" <?= $typeFilter === 'maintenance' ? 'selected' : '' ?>>تاسیسات</option>
                    <option value="security" <?= $typeFilter === 'security' ? 'selected' : '' ?>>حراست</option>
                </select>
            </div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>فیلتر</button></div>
            <div class="col-md-2"><a href="<?= url('/employees') ?>" class="btn btn-outline-secondary w-100">پاک کردن</a></div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>لیست کارمندان</h6><span class="badge bg-primary rounded-pill"><?= number_format($total) ?> کارمند</span></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>تصویر</th><th>نام</th><th>کد</th><th>سمت</th><th>نوع</th><th>دپارتمان</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
        <?php if (!empty($employees)): ?>
            <?php foreach ($employees as $i => $emp): ?>
            <tr><td><?= ($currentPage-1)*25+$i+1 ?></td><td><img src="<?= $emp['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle" width="38" height="38"></td><td><strong><?= htmlspecialchars($emp['first_name'].' '.$emp['last_name']) ?></strong><br><small><?= htmlspecialchars($emp['email'] ?? '') ?></small></td><td><code><?= htmlspecialchars($emp['employee_code']) ?></code></td><td><?= htmlspecialchars($emp['designation'] ?? '-') ?></td><td><span class="badge bg-secondary"><?= htmlspecialchars($emp['employee_type'] ?? '') ?></span></td><td><?= htmlspecialchars($emp['department_name'] ?? '-') ?></td><td><?= ($emp['user_active']??false) ? '<span class="badge bg-success">فعال</span>' : '<span class="badge bg-danger">غیرفعال</span>' ?></td>
            <td><div class="btn-group btn-group-sm"><a href="<?= url('/employees/'.$emp['id'].'/edit') ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a></div></td></tr>
            <?php endforeach; ?>
        <?php else: ?><tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-briefcase fa-3x mb-2 d-block"></i>کارمندی یافت نشد</td></tr><?php endif; ?>
    </tbody></table></div></div>
    <?php if ($totalPages > 1): ?><div class="card-footer"><nav><ul class="pagination justify-content-center mb-0"><?php for($p=1;$p<=$totalPages;$p++): ?><li class="page-item <?= $p==$currentPage?'active':'' ?>"><a class="page-link" href="?page=<?=$p?>&search=<?=urlencode($search)?>&employee_type=<?=$typeFilter?>"><?=$p?></a></li><?php endfor; ?></ul></nav></div><?php endif; ?>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>