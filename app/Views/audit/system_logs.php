<?php
$title = 'لاگ‌های سیستم';
$logs = $logs ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$levelFilter = $levelFilter ?? '';
$dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-7 days'));
$dateTo = $dateTo ?? date('Y-m-d');

$content = function() use ($logs, $currentPage, $totalPages, $total, $levelFilter, $dateFrom, $dateTo) {
    ob_start();
    $levelColors = ['debug'=>'secondary','info'=>'primary','warning'=>'warning','error'=>'danger','critical'=>'dark'];
    $levelIcons = ['debug'=>'fa-bug','info'=>'fa-info-circle','warning'=>'fa-exclamation-triangle','error'=>'fa-times-circle','critical'=>'fa-skull'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-server me-2"></i>لاگ‌های سیستم</h1>
    <div>
        <a href="<?= url('/audit-logs') ?>" class="btn btn-info btn-sm"><i class="fas fa-history me-1"></i>لاگ ممیزی</a>
        <a href="<?= url('/login-history') ?>" class="btn btn-warning btn-sm ms-1"><i class="fas fa-sign-in-alt me-1"></i>تاریخچه ورود</a>
    </div>
</div>

<!-- Error Stats -->
<?php $errorCount = count(array_filter($logs ?? [], fn($l) => in_array($l['level'], ['error','critical']))); ?>
<?php if ($errorCount > 0): ?>
<div class="alert alert-danger d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-circle fa-2x me-3"></i>
    <div><strong><?= $errorCount ?> خطا</strong> در بازه زمانی انتخاب شده رخ داده است.</div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body"><form class="row g-3">
        <div class="col-md-3">
            <label class="form-label small">سطح</label>
            <select class="form-select form-select-sm" name="level">
                <option value="">همه سطوح</option>
                <option value="debug" <?=$levelFilter==='debug'?'selected':''?>>Debug</option>
                <option value="info" <?=$levelFilter==='info'?'selected':''?>>Info</option>
                <option value="warning" <?=$levelFilter==='warning'?'selected':''?>>Warning</option>
                <option value="error" <?=$levelFilter==='error'?'selected':''?>>Error</option>
                <option value="critical" <?=$levelFilter==='critical'?'selected':''?>>Critical</option>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label small">از تاریخ</label><input type="date" class="form-control form-control-sm" name="date_from" value="<?=$dateFrom?>"></div>
        <div class="col-md-3"><label class="form-label small">تا تاریخ</label><input type="date" class="form-control form-control-sm" name="date_to" value="<?=$dateTo?>"></div>
        <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>اعمال</button></div>
    </form></div>
</div>

<!-- Logs Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold">لیست لاگ‌ها</h6><span class="badge bg-primary"><?=number_format($total)?> لاگ</span></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>سطح</th><th>ماژول</th><th>پیام</th><th>زمان</th><th>جزئیات</th></tr></thead><tbody>
    <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $i => $log): ?>
        <tr class="<?= in_array($log['level'],['error','critical'])?'table-danger':(in_array($log['level'],['warning'])?'table-warning':'') ?>">
            <td><?=($currentPage-1)*50+$i+1?></td>
            <td><span class="badge bg-<?=$levelColors[$log['level']]??'secondary'?>"><i class="fas <?=$levelIcons[$log['level']]??'fa-circle'?> me-1"></i><?=strtoupper($log['level'])?></span></td>
            <td><?=htmlspecialchars($log['module']??'-')?></td>
            <td><small><?=htmlspecialchars(mb_substr($log['message'],0,120))?><?=mb_strlen($log['message']??'')>120?'...':''?></small></td>
            <td><small><?=date('Y/m/d H:i:s',strtotime($log['created_at']))?></small></td>
            <td><?php if(!empty($log['context'])): ?><button class="btn btn-sm btn-outline-info" onclick="showContext('<?=htmlspecialchars($log['context'])?>')"><i class="fas fa-eye"></i></button><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?><tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-server fa-3x mb-2 d-block"></i>هیچ لاگی یافت نشد</td></tr><?php endif; ?>
    </tbody></table></div></div>
    <?php if($totalPages>1): ?><div class="card-footer"><nav><ul class="pagination justify-content-center mb-0"><?php for($p=1;$p<=$totalPages;$p++): ?><li class="page-item <?=$p==$currentPage?'active':''?>"><a class="page-link" href="?page=<?=$p?>&level=<?=$levelFilter?>&date_from=<?=$dateFrom?>&date_to=<?=$dateTo?>"><?=$p?></a></li><?php endfor; ?></ul></nav></div><?php endif; ?>
</div>

<script>function showContext(ctx){Swal.fire({title:'جزئیات',html:`<pre class="text-start bg-light p-3 rounded small" style="max-height:300px;overflow:auto;direction:ltr;">${JSON.stringify(JSON.parse(ctx),null,2)}</pre>`,width:'700px',confirmButtonText:'بستن'})}</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>