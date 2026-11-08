<?php
$title = 'تاریخچه ورود و خروج';
$logs = $logs ?? [];
$users = $users ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$userIdFilter = $userIdFilter ?? '';

$content = function() use ($logs, $users, $currentPage, $totalPages, $total, $userIdFilter) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-sign-in-alt me-2"></i>تاریخچه ورود و خروج</h1>
    <a href="<?= url('/audit-logs') ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<!-- Filter -->
<div class="card shadow mb-4"><div class="card-body"><form class="row g-3"><div class="col-md-8"><select class="form-select" name="user_id"><option value="">همه کاربران</option><?php foreach($users as $u): ?><option value="<?=$u['id']?>" <?=$userIdFilter==$u['id']?'selected':''?>><?=htmlspecialchars($u['first_name'].' '.$u['last_name'])?></option><?php endforeach; ?></select></div><div class="col-md-4"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>اعمال</button></div></form></div></div>

<!-- Stats -->
<?php
$totalLogins = count(array_filter($logs??[], fn($l)=>$l['action']==='login'));
$totalFailures = count(array_filter($logs??[], fn($l)=>$l['action']==='login_failed'));
$uniqueIPs = count(array_unique(array_column($logs??[], 'ip_address')));
?>
<div class="row">
    <div class="col-md-4 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h4 text-success"><?=number_format($totalLogins)?></div><small>ورود موفق</small></div></div></div>
    <div class="col-md-4 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h4 text-danger"><?=number_format($totalFailures)?></div><small>ورود ناموفق</small></div></div></div>
    <div class="col-md-4 mb-3"><div class="card bg-light"><div class="card-body text-center"><div class="h4 text-info"><?=number_format($uniqueIPs)?></div><small>IP منحصر به فرد</small></div></div></div>
</div>

<!-- Table -->
<div class="card shadow mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>کاربر</th><th>ایمیل</th><th>عملیات</th><th>IP</th><th>مرورگر</th><th>تاریخ</th></tr></thead><tbody>
<?php if(!empty($logs)): ?>
    <?php foreach($logs as $i=>$l): ?>
    <tr class="<?=$l['action']==='login_failed'?'table-danger':($l['action']==='login'?'table-success':'')?>">
        <td><?=($currentPage-1)*50+$i+1?></td>
        <td><strong><?=htmlspecialchars($l['user_name']??'نامشخص')?></strong></td>
        <td><?=htmlspecialchars($l['user_email']??'-')?></td>
        <td><span class="badge bg-<?=$l['action']==='login'?'success':($l['action']==='logout'?'secondary':'danger')?>"><i class="fas fa-<?=$l['action']==='login'?'sign-in-alt':($l['action']==='logout'?'sign-out-alt':'times-circle')?> me-1"></i><?=$l['action']==='login'?'ورود':($l['action']==='logout'?'خروج':'ناموفق')?></span></td>
        <td><code><?=htmlspecialchars($l['ip_address']??'-')?></code></td>
        <td><small class="text-muted"><?=htmlspecialchars(mb_substr($l['user_agent']??'',0,50))?></small></td>
        <td><small><?=date('Y/m/d H:i:s',strtotime($l['created_at']))?></small></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?><tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-sign-in-alt fa-3x mb-2 d-block"></i>تاریخچه‌ای یافت نشد</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php if($totalPages>1): ?><div class="card-footer"><nav><ul class="pagination justify-content-center mb-0"><?php for($p=1;$p<=$totalPages;$p++): ?><li class="page-item <?=$p==$currentPage?'active':''?>"><a class="page-link" href="?page=<?=$p?>&user_id=<?=$userIdFilter?>"><?=$p?></a></li><?php endfor; ?></ul></nav></div><?php endif; ?>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>