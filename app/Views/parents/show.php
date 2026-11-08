<?php
$title = 'جزئیات والد';
$parent = $parent ?? [];
$children = $children ?? [];
$feeSummary = $feeSummary ?? [];

$content = function() use ($parent, $children, $feeSummary) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-friends me-2"></i>پروفایل والد</h1>
    <div>
        <a href="<?= url('/parents/' . ($parent['id'] ?? '') . '/edit') ?>" class="btn btn-warning"><i class="fas fa-edit me-1"></i>ویرایش</a>
        <a href="<?= url('/parents') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <!-- Parent Card -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow text-center"><div class="card-body pt-5 pb-4">
            <img src="<?= $parent['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle shadow mb-3" width="100" height="100">
            <h4 class="fw-bold"><?= htmlspecialchars(($parent['first_name'] ?? '') . ' ' . ($parent['last_name'] ?? '')) ?></h4>
            <p class="text-muted"><?= htmlspecialchars($parent['email'] ?? '') ?></p>
        </div>
        <div class="list-group list-group-flush text-start">
            <div class="list-group-item"><small class="text-muted">کد والد</small><br><code><?= htmlspecialchars($parent['parent_code'] ?? '') ?></code></div>
            <div class="list-group-item"><small class="text-muted">شماره تماس</small><br><?= htmlspecialchars($parent['phone'] ?? '-') ?></div>
            <div class="list-group-item"><small class="text-muted">شغل</small><br><?= htmlspecialchars($parent['occupation'] ?? '-') ?></div>
            <div class="list-group-item"><small class="text-muted">محل کار</small><br><?= htmlspecialchars($parent['workplace'] ?? '-') ?></div>
            <div class="list-group-item"><small class="text-muted">آدرس</small><br><?= htmlspecialchars($parent['address'] ?? '-') ?></div>
        </div></div>
    </div>

    <!-- Children -->
    <div class="col-xl-8 col-lg-7">
        <h5 class="fw-bold mb-3"><i class="fas fa-child me-2"></i>فرزندان (<?= count($children ?? []) ?>)</h5>
        <?php if (!empty($children)): ?>
            <?php foreach ($children as $child): $fees = $feeSummary[$child['id']] ?? []; ?>
            <div class="card shadow mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="d-flex align-items-center">
                                <img src="<?= $child['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle me-2" width="45" height="45">
                                <div>
                                    <strong><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($child['class_name'] ?? '') ?> - <?= htmlspecialchars($child['section_name'] ?? '') ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($fees)): ?>
                                <?php foreach ($fees as $fee): ?>
                                <div class="d-flex justify-content-between small"><span><?= htmlspecialchars($fee['category_name']) ?></span><span class="text-<?= ($fee['balance_amount'] ?? 0) > 0 ? 'danger' : 'success' ?> fw-bold"><?= number_format($fee['balance_amount'] ?? 0) ?> ت</span></div>
                                <?php endforeach; ?>
                            <?php else: ?><small class="text-success">بدون بدهی</small><?php endif; ?>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="<?= url('/students/' . $child['id']) ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i>مشاهده</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-4 text-muted">فرزندی ثبت نشده است</div>
        <?php endif; ?>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>