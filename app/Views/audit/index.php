<?php
$title = 'لاگ‌های ممیزی';
$logs = $logs ?? [];
$actions = $actions ?? [];
$modules = $modules ?? [];
$users = $users ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$userIdFilter = $userIdFilter ?? '';
$actionFilter = $actionFilter ?? '';
$moduleFilter = $moduleFilter ?? '';
$dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $dateTo ?? date('Y-m-d');

$content = function() use ($logs, $actions, $modules, $users, $currentPage, $totalPages, $total, $userIdFilter, $actionFilter, $moduleFilter, $dateFrom, $dateTo) {
    ob_start();
    $actionColors = ['login'=>'success','logout'=>'secondary','login_failed'=>'danger','create'=>'primary','update'=>'info','delete'=>'danger','password_reset'=>'warning','export'=>'dark'];
    $actionIcons = ['login'=>'fa-sign-in-alt','logout'=>'fa-sign-out-alt','login_failed'=>'fa-times-circle','create'=>'fa-plus-circle','update'=>'fa-edit','delete'=>'fa-trash','password_reset'=>'fa-key','export'=>'fa-download'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-history me-2"></i>لاگ‌های ممیزی</h1>
    <div>
        <a href="<?= url('/audit-logs/export') ?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>خروجی CSV</a>
        <a href="<?= url('/system-logs') ?>" class="btn btn-info btn-sm ms-1"><i class="fas fa-server me-1"></i>لاگ سیستم</a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-filter me-2"></i>فیلترها</h6>
        <span class="badge bg-primary rounded-pill"><?= number_format($total) ?> رویداد</span>
    </div>
    <div class="card-body">
        <form action="<?= url('/audit-logs') ?>" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small">کاربر</label>
                <select class="form-select form-select-sm" name="user_id">
                    <option value="">همه کاربران</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $userIdFilter == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">عملیات</label>
                <select class="form-select form-select-sm" name="action">
                    <option value="">همه</option>
                    <?php foreach ($actions as $act): ?>
                    <option value="<?= htmlspecialchars($act['action']) ?>" <?= $actionFilter === $act['action'] ? 'selected' : '' ?>><?= htmlspecialchars($act['action']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">ماژول</label>
                <select class="form-select form-select-sm" name="module">
                    <option value="">همه</option>
                    <?php foreach ($modules as $mod): ?>
                    <option value="<?= htmlspecialchars($mod['module']) ?>" <?= $moduleFilter === $mod['module'] ? 'selected' : '' ?>><?= htmlspecialchars($mod['module']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">از تاریخ</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?= $dateFrom ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">تا تاریخ</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?= $dateTo ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>اعمال</button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card shadow mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">کاربر</th>
                        <th width="10%">عملیات</th>
                        <th width="10%">ماژول</th>
                        <th width="30%">توضیحات</th>
                        <th width="12%">IP</th>
                        <th width="15%">تاریخ</th>
                        <th width="6%">جزئیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $i => $log): ?>
                        <tr>
                            <td><?= ($currentPage - 1) * 50 + $i + 1 ?></td>
                            <td>
                                <?php if ($log['user_name']): ?>
                                <strong><?= htmlspecialchars($log['user_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($log['user_email'] ?? '') ?></small>
                                <?php else: ?>
                                <span class="text-muted">سیستم</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $actionColors[$log['action']] ?? 'secondary' ?>">
                                    <i class="fas <?= $actionIcons[$log['action']] ?? 'fa-circle' ?> me-1"></i>
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td><span class="badge bg-light text-dark"><?= htmlspecialchars($log['module']) ?></span></td>
                            <td>
                                <small><?= htmlspecialchars($log['description'] ?? '') ?></small>
                                <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
                                <button class="btn btn-sm btn-link p-0 ms-1" onclick="showChanges('<?= htmlspecialchars(json_encode(['old'=>$log['old_values'],'new'=>$log['new_values']], JSON_UNESCAPED_UNICODE)) ?>')" title="مشاهده تغییرات">
                                    <i class="fas fa-code"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                            <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                            <td><small><?= date('Y/m/d H:i:s', strtotime($log['created_at'])) ?></small></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="showDetails(<?= htmlspecialchars(json_encode($log, JSON_UNESCAPED_UNICODE)) ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-history fa-3x mb-2 d-block"></i>هیچ لاگی یافت نشد</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination justify-content-center mb-0">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&user_id=<?= $userIdFilter ?>&action=<?= $actionFilter ?>&module=<?= $moduleFilter ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>"><?= $p ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header bg-info text-white"><h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>جزئیات رویداد</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><pre class="bg-light p-3 rounded" id="detailsContent" style="max-height:400px;overflow:auto;font-size:13px;direction:ltr;"></pre></div>
    </div></div>
</div>

<script>
function showDetails(logData) {
    document.getElementById('detailsContent').textContent = JSON.stringify(logData, null, 2);
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}
function showChanges(jsonStr) {
    const data = JSON.parse(jsonStr);
    let html = '<div class="row"><div class="col-md-6"><h6 class="text-danger">قبل</h6><pre class="bg-light p-2 small" style="max-height:300px;overflow:auto;direction:ltr;">' + JSON.stringify(JSON.parse(data.old||'{}'), null, 2) + '</pre></div>';
    html += '<div class="col-md-6"><h6 class="text-success">بعد</h6><pre class="bg-light p-2 small" style="max-height:300px;overflow:auto;direction:ltr;">' + JSON.stringify(JSON.parse(data.new||'{}'), null, 2) + '</pre></div></div>';
    Swal.fire({ title: 'تغییرات', html: html, width: '800px', confirmButtonText: 'بستن' });
}
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>