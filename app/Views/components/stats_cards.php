<?php
/**
 * Stats Cards Component
 * 
 * Usage:
 *   echo stats_card('دانش‌آموزان', 150, 'primary', 'fa-user-graduate', '/students');
 *   echo stats_cards_row([['title'=>'...','value'=>...,'color'=>'...','icon'=>'...','url'=>'...']]);
 */

function stats_card($title, $value, $color = 'primary', $icon = 'fa-chart-bar', $url = null, $options = []) {
    $suffix = $options['suffix'] ?? '';
    $prefix = $options['prefix'] ?? '';
    $subtitle = $options['subtitle'] ?? '';
    $progress = $options['progress'] ?? null;
    ob_start();
?>
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-<?= $color ?> shadow h-100 py-2">
        <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col">
                    <div class="text-xs fw-bold text-<?= $color ?> text-uppercase mb-1"><?= $title ?></div>
                    <div class="h5 mb-0 fw-bold text-gray-800"><?= $prefix ?><?= is_numeric($value) ? number_format($value) : $value ?><?= $suffix ?></div>
                    <?php if ($subtitle): ?><div class="small text-muted mt-1"><?= $subtitle ?></div><?php endif; ?>
                    <?php if ($progress !== null): ?>
                    <div class="progress mt-2" style="height:4px"><div class="progress-bar bg-<?=$color?>" style="width:<?=$progress?>%"></div></div>
                    <?php endif; ?>
                </div>
                <div class="col-auto"><i class="fas <?=$icon?> fa-2x text-gray-300"></i></div>
            </div>
            <?php if ($url): ?><div class="mt-2"><a href="<?=$url?>" class="small text-decoration-none">مشاهده <i class="fas fa-arrow-left me-1"></i></a></div><?php endif; ?>
        </div>
    </div>
</div>
<?php return ob_get_clean(); }

function stats_cards_row($cards) {
    ob_start();
    echo '<div class="row">';
    foreach ($cards as $card) {
        echo stats_card($card['title'], $card['value'], $card['color'] ?? 'primary', $card['icon'] ?? 'fa-chart-bar', $card['url'] ?? null, $card);
    }
    echo '</div>';
    return ob_get_clean();
}

function mini_stat_card($title, $value, $color = 'primary', $icon = 'fa-user') {
    ob_start();
?>
<div class="col-md-3 col-6 mb-3">
    <div class="card bg-light"><div class="card-body text-center py-3">
        <i class="fas <?=$icon?> fa-lg text-<?=$color?> mb-1"></i>
        <div class="h5 mb-0"><?=is_numeric($value)?number_format($value):$value?></div>
        <small class="text-muted"><?=$title?></small>
    </div></div>
</div>
<?php return ob_get_clean(); }
?>