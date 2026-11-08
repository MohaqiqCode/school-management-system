<?php
$title = 'دسته‌بندی شهریه';
$categories = $categories ?? [];

$content = function() use ($categories) {
    ob_start();
    $freqs = ['monthly'=>'ماهانه','quarterly'=>'سه ماهه','half_yearly'=>'شش ماهه','yearly'=>'سالانه','one_time'=>'یکبار'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-tags me-2"></i>دسته‌بندی شهریه</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="fas fa-plus me-1"></i>دسته‌بندی جدید</button>
</div>

<div class="row">
    <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
        <div class="col-xl-4 col-md-6 mb-4"><div class="card shadow h-100"><div class="card-body"><h6 class="fw-bold"><?= htmlspecialchars($cat['category_name']) ?></h6><small class="text-muted d-block mb-2"><?= $freqs[$cat['frequency']] ?? $cat['frequency'] ?> <?= $cat['is_recurring'] ? '(دوره‌ای)' : '(یکبار)' ?></small><?php if ($cat['description']): ?><p class="small text-muted"><?= htmlspecialchars($cat['description']) ?></p><?php endif; ?></div></div></div>
        <?php endforeach; ?>
    <?php else: ?><div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-tags fa-3x mb-2 d-block"></i>دسته‌بندی تعریف نشده</div></div><?php endif; ?>
</div>

<!-- Create Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="<?=url('/fees/categories')?>" method="POST"><div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>دسته‌بندی جدید</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><div class="mb-3"><label class="form-label">نام</label><input type="text" class="form-control" name="category_name" required></div><div class="mb-3"><label class="form-label">تناوب</label><select class="form-select" name="frequency"><?php foreach($freqs as $k=>$v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">توضیحات</label><textarea class="form-control" name="description" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div></form></div></div></div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>