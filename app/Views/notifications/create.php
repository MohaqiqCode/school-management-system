<?php
$title = 'ارسال اعلان جدید';
$branches = $branches ?? [];
$classes = $classes ?? [];
$roles = $roles ?? [];
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($branches, $classes, $roles, $old, $errors) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-paper-plane me-2"></i>ارسال اعلان جدید</h1>
    <a href="<?= url('/notifications') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>تنظیم اعلان</h6></div>
            <div class="card-body">
                <form action="<?= url('/notifications') ?>" method="POST" id="notificationForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <!-- Recipient Type -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">گیرنده <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <?php
                            $recipientOptions = [
                                'all' => ['icon' => 'fa-globe', 'label' => 'همه', 'color' => 'primary'],
                                'branch' => ['icon' => 'fa-building', 'label' => 'شعبه', 'color' => 'info'],
                                'class' => ['icon' => 'fa-chalkboard', 'label' => 'کلاس', 'color' => 'success'],
                                'section' => ['icon' => 'fa-layer-group', 'label' => 'بخش', 'color' => 'warning'],
                                'role' => ['icon' => 'fa-user-shield', 'label' => 'نقش', 'color' => 'secondary'],
                                'individual' => ['icon' => 'fa-user', 'label' => 'فرد', 'color' => 'dark'],
                            ];
                            foreach ($recipientOptions as $val => $opt):
                            ?>
                            <div class="col-md-4 col-sm-6">
                                <input type="radio" class="btn-check" name="recipient_type" id="rec_<?= $val ?>" value="<?= $val ?>" <?= ($old['recipient_type'] ?? 'all') === $val ? 'checked' : '' ?> onchange="toggleRecipientFields('<?= $val ?>')">
                                <label class="btn btn-outline-<?= $opt['color'] ?> w-100" for="rec_<?= $val ?>">
                                    <i class="fas <?= $opt['icon'] ?> me-1"></i> <?= $opt['label'] ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Recipient Detail (shown based on selection) -->
                    <div class="mb-3 d-none" id="recipientDetail">
                        <label class="form-label fw-bold">انتخاب <span id="recipientLabel"></span></label>
                        <select class="form-select" name="recipient_id" id="recipientSelect">
                            <option value="">انتخاب کنید...</option>
                        </select>
                    </div>

                    <!-- Notification Type -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع اعلان <span class="text-danger">*</span></label>
                        <select class="form-select" name="notification_type" required>
                            <option value="info" <?= ($old['notification_type'] ?? 'info') === 'info' ? 'selected' : '' ?>>📢 اطلاع‌رسانی</option>
                            <option value="announcement" <?= ($old['notification_type'] ?? '') === 'announcement' ? 'selected' : '' ?>>📣 اطلاعیه</option>
                            <option value="reminder" <?= ($old['notification_type'] ?? '') === 'reminder' ? 'selected' : '' ?>>⏰ یادآوری</option>
                            <option value="alert" <?= ($old['notification_type'] ?? '') === 'alert' ? 'selected' : '' ?>>⚠️ هشدار</option>
                            <option value="emergency" <?= ($old['notification_type'] ?? '') === 'emergency' ? 'selected' : '' ?>>🚨 فوری</option>
                        </select>
                    </div>

                    <!-- Title -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">عنوان <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" placeholder="عنوان اعلان..." required>
                        <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= $errors['title'] ?></div><?php endif; ?>
                    </div>

                    <!-- Message -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">متن پیام <span class="text-danger">*</span></label>
                        <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" name="message" rows="5" placeholder="متن اعلان را وارد کنید..." required><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                        <?php if (isset($errors['message'])): ?><div class="invalid-feedback"><?= $errors['message'] ?></div><?php endif; ?>
                        <small class="text-muted">می‌توانید از متغیرهای <code>{name}</code> و <code>{class}</code> استفاده کنید.</small>
                    </div>

                    <!-- Channels -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">کانال‌های ارسال</label>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="channel" value="in_app" checked> <label class="form-check-label">درون برنامه</label></div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="channel" value="email"> <label class="form-check-label">ایمیل</label></div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="channel" value="sms"> <label class="form-check-label">پیامک</label></div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/notifications') ?>" class="btn btn-secondary"><i class="fas fa-times me-1"></i>انصراف</a>
                        <div>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary"><i class="fas fa-save me-1"></i>ذخیره پیش‌نویس</button>
                            <button type="submit" name="action" value="send" class="btn btn-primary ms-2"><i class="fas fa-paper-plane me-1"></i>ارسال اعلان</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-eye me-2"></i>پیش‌نمایش</h6></div>
            <div class="card-body">
                <div id="previewCard" class="border rounded p-3">
                    <div class="d-flex align-items-start mb-2">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2"><i class="fas fa-bell text-primary"></i></div>
                        <div>
                            <h6 class="mb-0" id="previewTitle">عنوان اعلان</h6>
                            <small class="text-muted" id="previewType">اطلاع‌رسانی</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-0" id="previewMessage">متن اعلان در اینجا نمایش داده می‌شود...</p>
                </div>
            </div>
        </div>

        <div class="card shadow"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-lightbulb me-2"></i>نکات</h6></div><div class="card-body"><ul class="small text-muted mb-0"><li class="mb-2">اعلان‌های فوری با اولویت بالا نمایش داده می‌شوند.</li><li class="mb-2">برای ارسال به همه، گزینه "همه" را انتخاب کنید.</li><li>می‌توانید اعلان را به صورت پیش‌نویس ذخیره و بعداً ارسال کنید.</li></ul></div></div>
    </div>
</div>

<script>
function toggleRecipientFields(type) {
    const detail = document.getElementById('recipientDetail');
    const select = document.getElementById('recipientSelect');
    const label = document.getElementById('recipientLabel');

    const config = {
        'branch': { label: 'شعبه', url: '<?= url('/api/branches') ?>' },
        'class': { label: 'کلاس', url: '<?= url('/api/classes') ?>' },
        'section': { label: 'بخش', url: '<?= url('/api/sections') ?>' },
        'role': { label: 'نقش', url: '<?= url('/api/roles') ?>' },
        'individual': { label: 'کاربر', url: '<?= url('/api/users') ?>' },
    };

    if (type === 'all') {
        detail.classList.add('d-none');
        return;
    }

    const cfg = config[type];
    if (cfg) {
        detail.classList.remove('d-none');
        label.textContent = cfg.label;
        select.innerHTML = '<option value="">در حال بارگذاری...</option>';
        fetch(cfg.url).then(r => r.json()).then(d => {
            select.innerHTML = '<option value="">انتخاب ' + cfg.label + '...</option>';
            if (d.success && d.data) d.data.forEach(item => {
                select.innerHTML += `<option value="${item.id}">${item.name || item.class_name || item.section_name || item.first_name + ' ' + item.last_name || item.display_name || ''}</option>`;
            });
        });
    }
}

// Live preview
document.querySelector('[name="title"]').addEventListener('input', function() { document.getElementById('previewTitle').textContent = this.value || 'عنوان اعلان'; });
document.querySelector('[name="message"]').addEventListener('input', function() { document.getElementById('previewMessage').textContent = this.value || 'متن اعلان در اینجا نمایش داده می‌شود...'; });
document.querySelector('[name="notification_type"]').addEventListener('change', function() {
    const labels = { info: 'اطلاع‌رسانی', announcement: 'اطلاعیه', reminder: 'یادآوری', alert: 'هشدار', emergency: 'فوری' };
    document.getElementById('previewType').textContent = labels[this.value] || this.value;
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const selectedType = document.querySelector('[name="recipient_type"]:checked')?.value || 'all';
    if (selectedType !== 'all') toggleRecipientFields(selectedType);
});
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>