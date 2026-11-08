<?php
$title = 'قالب‌های اعلان';
$templates = $templates ?? [];

$content = function() use ($templates) {
    ob_start();
    $templateIcons = ['welcome'=>'fa-handshake','fee_reminder'=>'fa-money-bill','payment_receipt'=>'fa-receipt','attendance_alert'=>'fa-calendar-times','result_published'=>'fa-star','event'=>'fa-calendar-star','custom'=>'fa-pen'];
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-envelope-open-text me-2"></i>قالب‌های اعلان</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal"><i class="fas fa-plus me-1"></i>ایجاد قالب جدید</button>
</div>

<div class="row">
    <?php if (!empty($templates)): ?>
        <?php foreach ($templates as $tpl): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;"><i class="fas <?= $templateIcons[$tpl['type'] ?? 'custom'] ?? 'fa-pen' ?> text-primary fa-lg"></i></div>
                        <div><h6 class="fw-bold mb-0"><?= htmlspecialchars($tpl['name']) ?></h6><small class="text-muted"><?= htmlspecialchars($tpl['type'] ?? '') ?></small></div>
                    </div>
                    <p class="small text-muted mb-3"><?= htmlspecialchars(mb_substr($tpl['message'] ?? '', 0, 100)) ?><?= mb_strlen($tpl['message'] ?? '') > 100 ? '...' : '' ?></p>
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-info btn-sm flex-grow-1" onclick="useTemplate(<?= $tpl['id'] ?>)"><i class="fas fa-paper-plane me-1"></i>استفاده</button>
                        <button class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-envelope-open-text fa-4x mb-3 d-block"></i><h5>قالبی تعریف نشده است</h5></div></div>
    <?php endif; ?>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form action="<?= url('/notifications/templates') ?>" method="POST"><div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>ایجاد قالب</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">نام قالب</label><input type="text" class="form-control" name="name" required></div><div class="col-md-6 mb-3"><label class="form-label">نوع</label><select class="form-select" name="type"><option value="custom">سفارشی</option><option value="welcome">خوش‌آمدگویی</option><option value="fee_reminder">یادآوری شهریه</option><option value="attendance_alert">هشدار حضور</option><option value="result_published">نتایج آزمون</option><option value="event">رویداد</option></select></div></div><div class="mb-3"><label class="form-label">عنوان</label><input type="text" class="form-control" name="title" placeholder="می‌توانید از {name} استفاده کنید"></div><div class="mb-3"><label class="form-label">متن پیام</label><textarea class="form-control" name="message" rows="5" placeholder="متن قالب... می‌توانید از {name}, {class}, {amount} استفاده کنید"></textarea></div><div class="small text-muted">متغیرهای قابل استفاده: <code>{name}</code> <code>{class}</code> <code>{amount}</code> <code>{date}</code> <code>{exam}</code></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>ذخیره</button></div></form></div></div></div>

<script>function useTemplate(id) { window.location.href = '<?= url('/notifications/create') ?>?template_id=' + id; }</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>