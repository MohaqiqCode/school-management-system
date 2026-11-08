<?php
$title = 'پشتیبان‌گیری';
$backups = $backups ?? [];
$dbSize = $dbSize ?? 0;
$lastBackup = $lastBackup ?? null;

$content = function() use ($backups, $dbSize, $lastBackup) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-database me-2"></i>پشتیبان‌گیری</h1>
    <a href="<?= url('/settings') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<!-- Info Cards -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-primary mb-1">حجم دیتابیس</div><div class="h5 mb-0"><?= number_format($dbSize, 2) ?> MB</div></div></div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-left-info shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-info mb-1">آخرین پشتیبان</div><div class="h5 mb-0"><?= $lastBackup ? date('Y/m/d H:i', strtotime($lastBackup)) : 'تاکنون پشتیبان گرفته نشده' ?></div></div></div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card border-left-success shadow h-100"><div class="card-body"><div class="text-xs fw-bold text-success mb-1">تعداد پشتیبان‌ها</div><div class="h5 mb-0"><?= count($backups ?? []) ?> فایل</div></div></div>
    </div>
</div>

<!-- Create Backup -->
<div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>ایجاد پشتیبان جدید</h6></div>
    <div class="card-body">
        <form action="<?= url('/settings/backup/create') ?>" method="POST" class="row g-3 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="col-md-3">
                <label class="form-label fw-bold">نوع پشتیبان</label>
                <select class="form-select" name="type">
                    <option value="database">فقط دیتابیس</option>
                    <option value="files">فقط فایل‌ها</option>
                    <option value="full">کامل (دیتابیس + فایل‌ها)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">فشرده‌سازی</label>
                <select class="form-select" name="compress">
                    <option value="1">بله (ZIP)</option>
                    <option value="0">خیر</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="download" value="1" checked><label class="form-check-label">دانلود پس از ایجاد</label></div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100" id="backupBtn">
                    <i class="fas fa-download me-1"></i> <span>ایجاد پشتیبان</span>
                    <span class="spinner-border spinner-border-sm ms-1 d-none" id="backupSpinner"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Backup List -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between"><h6 class="m-0 fw-bold"><i class="fas fa-list me-2"></i>لیست پشتیبان‌ها</h6></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>نام فایل</th><th>نوع</th><th>حجم</th><th>تاریخ ایجاد</th><th>عملیات</th></tr></thead><tbody>
    <?php if (!empty($backups)): ?>
        <?php foreach ($backups as $i => $backup): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><i class="fas fa-file-archive text-warning me-1"></i> <?= htmlspecialchars($backup['filename']) ?></td>
            <td><span class="badge bg-info"><?= htmlspecialchars($backup['type'] ?? 'database') ?></span></td>
            <td><?= number_format(($backup['size'] ?? 0) / 1024 / 1024, 2) ?> MB</td>
            <td><small><?= date('Y/m/d H:i', strtotime($backup['created_at'])) ?></small></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="<?= url('/settings/backup/download/' . $backup['id']) ?>" class="btn btn-outline-primary" title="دانلود"><i class="fas fa-download"></i></a>
                    <button class="btn btn-outline-info" onclick="restoreBackup(<?= $backup['id'] ?>)" title="بازیابی"><i class="fas fa-undo"></i></button>
                    <button class="btn btn-outline-danger" onclick="deleteBackup(<?= $backup['id'] ?>)" title="حذف"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-database fa-3x mb-2 d-block"></i>هیچ پشتیبانی وجود ندارد</td></tr>
    <?php endif; ?>
    </tbody></table></div></div>
</div>

<!-- Schedule Info -->
<div class="card shadow">
    <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-clock me-2"></i>زمان‌بندی خودکار</h6></div>
    <div class="card-body">
        <form action="<?= url('/settings/backup/schedule') ?>" method="POST" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="col-md-3">
                <label class="form-label">تناوب</label>
                <select class="form-select" name="frequency">
                    <option value="daily">روزانه</option>
                    <option value="weekly">هفتگی</option>
                    <option value="monthly">ماهانه</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">ساعت</label>
                <input type="time" class="form-control" name="time" value="02:00">
            </div>
            <div class="col-md-3">
                <label class="form-label">نگهداری (تعداد)</label>
                <input type="number" class="form-control" name="keep" value="7" min="1">
            </div>
            <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-info w-100"><i class="fas fa-save me-1"></i>ذخیره زمان‌بندی</button></div>
        </form>
    </div>
</div>

<script>
document.querySelector('form[action*="backup/create"]').addEventListener('submit', function(){document.getElementById('backupBtn').disabled=true;document.getElementById('backupSpinner').classList.remove('d-none');});
function deleteBackup(id){Swal.fire({title:'حذف پشتیبان',text:'این عملیات قابل بازگشت نیست.',icon:'warning',showCancelButton:true,confirmButtonText:'حذف',confirmButtonColor:'#e74a3b'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/settings/backup/delete')?>/'+id,{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else toastr.error(d.message)})}})}
function restoreBackup(id){Swal.fire({title:'بازیابی پشتیبان',text:'داده‌های فعلی با این پشتیبان جایگزین می‌شوند. ادامه می‌دهید؟',icon:'warning',showCancelButton:true,confirmButtonText:'بازیابی',confirmButtonColor:'#f6c23e'}).then(r=>{if(r.isConfirmed){fetch('<?=url('/settings/backup/restore')?>/'+id,{method:'POST',headers:{'X-CSRF-TOKEN':'<?=$_SESSION['csrf_token']??''?>'}}).then(r=>r.json()).then(d=>toastr[d.success?'success':'error'](d.message))}})}
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>