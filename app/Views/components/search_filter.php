<?php
/**
 * Search & Filter Component
 * 
 * Usage:
 *   echo search_filter([
 *       'search' => ['placeholder'=>'جستجو...'],
 *       'filters' => [['name'=>'status','label'=>'وضعیت','type'=>'select','options'=>[...]]],
 *       'action' => '/students'
 *   ]);
 */
function search_filter($config) {
    $action = $config['action'] ?? $_SERVER['REQUEST_URI'];
    $search = $config['search'] ?? null;
    $filters = $config['filters'] ?? [];
    $method = $config['method'] ?? 'GET';
    $showReset = $config['showReset'] ?? true;
    $hasActiveFilter = false;
    foreach ($filters as $f) { if (!empty($_GET[$f['name']] ?? '')) { $hasActiveFilter = true; break; } }
    if (!empty($_GET['search'] ?? '')) $hasActiveFilter = true;
    ob_start();
?>
<div class="card shadow mb-4"><div class="card-body"><form action="<?=$action?>" method="<?=$method?>" class="row g-3 align-items-end">
    <?php if ($search): ?>
    <div class="col-md-<?= 12 - (count($filters)*3) - 2 ?>">
        <label class="form-label small fw-bold"><?=$search['label']??'جستجو'?></label>
        <div class="input-group"><span class="input-group-text"><i class="fas fa-search"></i></span><input type="text" class="form-control" name="search" placeholder="<?=$search['placeholder']??'جستجو...'?>" value="<?=htmlspecialchars($_GET['search']??'')?>"></div>
    </div>
    <?php endif; ?>
    <?php foreach ($filters as $filter): $name=$filter['name']; ?>
    <div class="col-md-3"><label class="form-label small fw-bold"><?=$filter['label']?></label>
        <?php if (($filter['type']??'select') === 'select'): ?>
        <select class="form-select form-select-sm" name="<?=$name?>" <?=($filter['onchange']??false)?'onchange="this.form.submit()"':''?>>
            <option value=""><?=$filter['placeholder']??'همه'?></option>
            <?php foreach (($filter['options']??[]) as $val=>$text): ?><option value="<?=$val?>" <?=($_GET[$name]??'')==$val?'selected':''?>><?=htmlspecialchars($text)?></option><?php endforeach; ?>
        </select>
        <?php elseif (($filter['type']??'') === 'date'): ?>
        <input type="date" class="form-control form-control-sm" name="<?=$name?>" value="<?=htmlspecialchars($_GET[$name]??'')?>">
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-filter me-1"></i>اعمال</button>
        <?php if ($showReset && $hasActiveFilter): ?><a href="<?=$action?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a><?php endif; ?>
    </div>
</form></div></div>
<?php return ob_get_clean(); }
?>