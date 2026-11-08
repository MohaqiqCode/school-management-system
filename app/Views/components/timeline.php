<?php
/**
 * Timeline Component
 * 
 * Usage:
 *   echo timeline($events, ['showDate'=>true, 'animate'=>true]);
 */
function timeline($events, $options = []) {
    $showDate = $options['showDate'] ?? true;
    $animate = $options['animate'] ?? true;
    $emptyMessage = $options['emptyMessage'] ?? 'رویدادی ثبت نشده است';
    ob_start();
    if (empty($events)) { echo "<p class=\"text-center text-muted py-4\">{$emptyMessage}</p>"; return ob_get_clean(); }
?>
<div class="timeline" <?= $animate ? 'data-aos="fade-up"' : '' ?>>
    <?php foreach ($events as $event): 
        $color = $event['color'] ?? 'primary';
        $icon = $event['icon'] ?? 'fa-circle';
    ?>
    <div class="timeline-item">
        <div class="timeline-marker bg-<?= $color ?>"></div>
        <div class="timeline-content">
            <div class="d-flex justify-content-between">
                <strong class="small"><?= htmlspecialchars($event['title'] ?? '') ?></strong>
                <?php if ($showDate && !empty($event['date'])): ?>
                <small class="text-muted"><?= date('Y/m/d H:i', strtotime($event['date'])) ?></small>
                <?php endif; ?>
            </div>
            <?php if (!empty($event['description'])): ?>
            <p class="mb-1 small text-muted"><?= htmlspecialchars($event['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($event['badge'])): ?>
            <span class="badge bg-<?= $event['badgeColor'] ?? 'secondary' ?>"><?= htmlspecialchars($event['badge']) ?></span>
            <?php endif; ?>
            <?php if (!empty($event['user'])): ?>
            <small class="text-muted d-block mt-1"><i class="fas fa-user me-1"></i><?= htmlspecialchars($event['user']) ?></small>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<style>
.timeline{position:relative;padding-right:25px}.timeline::before{content:'';position:absolute;right:8px;top:0;bottom:0;width:2px;background:#e3e6f0}.timeline-item{position:relative;padding-bottom:20px}.timeline-item:last-child{padding-bottom:0}.timeline-marker{position:absolute;right:-6px;top:4px;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 2px #e3e6f0}.timeline-content{padding-right:15px;padding-bottom:5px;border-bottom:1px solid #f0f0f0}.timeline-item:last-child .timeline-content{border-bottom:none}
</style>
<?php return ob_get_clean(); }
?>