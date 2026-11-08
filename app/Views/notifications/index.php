<?php
$title = 'مدیریت اعلان‌ها';
$notifications = $notifications ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$typeFilter = $typeFilter ?? '';

$content = function() use ($notifications, $currentPage, $totalPages, $total, $typeFilter) {
    ob_start();
    $typeIcons = ['announcement'=>'fa-bullhorn','reminder'=>'fa-clock','alert'=>'fa-exclamation-triangle','info'=>'fa-info-circle','emergency'=>'fa-exclamation-circle'];
    $typeColors = ['announcement'=>'primary','reminder'=>'info','alert'=>'warning','info'=>'secondary','emergency'=>'danger'];
    $typeLabels = ['announcement'=>'اطلاعیه','reminder'=>'یادآوری','alert'=>'هشدار','info'=>'اطلاع‌رسانی','emergency'=>'فوری'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-bell me-2"></i>مدیریت اعلان‌ها</h1>
    <?php if (hasPermission('send_notifications')): ?>
    <a href="<?= url('/notifications/create') ?>" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>ارسال اعلان جدید</a>
    <?php endif; ?>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="<?= url('/notifications') ?>" method="GET" class="row g-3">
            <div class="col-md-6">
                <select class="form-select" name="type" onchange="this.form.submit()">
                    <option value="">همه انواع</option>
                    <?php foreach ($typeLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $typeFilter === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <?php if ($typeFilter): ?>
                <a href="<?= url('/notifications') ?>" class="btn btn-outline-secondary w-100">پاک کردن فیلتر</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Notifications List -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>لیست اعلان‌ها</h6>
        <span class="badge bg-primary rounded-pill"><?= number_format($total) ?> اعلان</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="list-group-item border-0 border-bottom py-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-<?= $typeColors[$notif['notification_type']] ?> bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas <?= $typeIcons[$notif['notification_type']] ?? 'fa-bell' ?> text-<?= $typeColors[$notif['notification_type']] ?> fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($notif['title']) ?></h6>
                                <div>
                                    <span class="badge bg-<?= $typeColors[$notif['notification_type']] ?> me-1"><?= $typeLabels[$notif['notification_type']] ?? $notif['notification_type'] ?></span>
                                    <?php if ($notif['is_sent']): ?>
                                    <span class="badge bg-success">ارسال شده</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">پیش‌نویس</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="text-muted small mb-2"><?= htmlspecialchars(mb_substr($notif['message'], 0, 150)) ?><?= mb_strlen($notif['message'] ?? '') > 150 ? '...' : '' ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i> فرستنده: <?= htmlspecialchars($notif['sender_name'] ?? 'سیستم') ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-users me-1"></i> گیرنده: 
                                    <?php
                                    $recipientTexts = ['all'=>'همه', 'branch'=>'شعبه', 'class'=>'کلاس', 'section'=>'بخش', 'role'=>'نقش', 'individual'=>'فرد'];
                                    echo $recipientTexts[$notif['recipient_type']] ?? $notif['recipient_type'];
                                    ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-clock me-1"></i> <?= date('Y/m/d H:i', strtotime($notif['created_at'])) ?>
                                </small>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(<?= $notif['id'] ?>)"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-bell-slash fa-3x mb-3 d-block"></i>
                    <h5>اعلانی وجود ندارد</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav><ul class="pagination justify-content-center mb-0">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&type=<?= $typeFilter ?>"><?= $p ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteNotification(id) {
    Swal.fire({ title: 'حذف اعلان', text: 'آیا از حذف این اعلان اطمینان دارید؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'حذف', cancelButtonText: 'انصراف', confirmButtonColor: '#e74a3b' }).then(r => {
        if (r.isConfirmed) {
            fetch('<?= url('/notifications') ?>/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>' } })
                .then(r => r.json()).then(d => { if (d.success) location.reload(); else toastr.error(d.message); });
        }
    });
}
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>