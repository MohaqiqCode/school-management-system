<?php
$title = 'مدارک دانش‌آموز';
$student = $student ?? [];
$documents = $documents ?? [];

$content = function() use ($student, $documents) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div><h1 class="h3 mb-0 text-gray-800"><i class="fas fa-folder-open me-2"></i>مدارک دانش‌آموز</h1><p class="text-muted mb-0 mt-1"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></p></div>
    <div>
        <button class="btn btn-success" onclick="document.getElementById('uploadModal').classList.add('show')" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-1"></i>آپلود مدرک</button>
        <a href="<?= url('/students/' . ($student['id'] ?? '')) ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <?php if (!empty($documents)): ?>
        <?php foreach ($documents as $doc): 
            $statusColors = ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'];
            $statusTexts = ['pending' => 'در انتظار', 'verified' => 'تأیید شده', 'rejected' => 'رد شده'];
        ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-<?= $statusColors[$doc['status'] ?? 'pending'] ?>"><?= $statusTexts[$doc['status'] ?? 'pending'] ?></span>
                        <small class="text-muted"><?= date('Y/m/d', strtotime($doc['upload_date'] ?? '')) ?></small>
                    </div>
                    <h6 class="fw-bold"><?= htmlspecialchars($doc['document_name']) ?></h6>
                    <small class="text-muted d-block mb-2">نوع: <?= htmlspecialchars($doc['document_type']) ?></small>
                    <?php if ($doc['file_size']): ?><small class="text-muted d-block mb-2">حجم: <?= number_format($doc['file_size'] / 1024, 1) ?> KB</small><?php endif; ?>
                    <?php if ($doc['expiry_date']): ?><small class="text-warning d-block mb-2"><i class="fas fa-clock me-1"></i>انقضا: <?= date('Y/m/d', strtotime($doc['expiry_date'])) ?></small><?php endif; ?>
                    <div class="d-flex gap-1 mt-2">
                        <a href="<?= asset($doc['file_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm flex-grow-1"><i class="fas fa-download me-1"></i>دانلود</a>
                        <?php if ($doc['status'] === 'pending'): ?>
                        <button class="btn btn-outline-success btn-sm" onclick="verifyDoc(<?= $doc['id'] ?>, 'verified')" title="تأیید"><i class="fas fa-check"></i></button>
                        <button class="btn btn-outline-danger btn-sm" onclick="verifyDoc(<?= $doc['id'] ?>, 'rejected')" title="رد"><i class="fas fa-times"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="text-center py-5 text-muted"><i class="fas fa-folder-open fa-4x mb-3 d-block"></i><h5>مدرکی آپلود نشده است</h5></div></div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="fas fa-upload me-2"></i>آپلود مدرک</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form action="<?= url('/students/' . ($student['id'] ?? '') . '/documents') ?>" method="POST" enctype="multipart/form-data"><div class="modal-body"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><div class="mb-3"><label class="form-label">نوع مدرک</label><select class="form-select" name="document_type" required><option value="">انتخاب کنید...</option><option value="birth_certificate">شناسنامه</option><option value="national_card">کارت ملی</option><option value="photo">عکس</option><option value="medical_record">پرونده پزشکی</option><option value="other">سایر</option></select></div><div class="mb-3"><label class="form-label">فایل</label><input type="file" class="form-control" name="document" required></div><div class="mb-3"><label class="form-label">تاریخ انقضا</label><input type="text" class="form-control persian-datepicker" name="expiry_date"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i>آپلود</button></div></form></div></div></div>

<script>
    function verifyDoc(id, status) {
        fetch('<?= url('/students') ?>/document/' + id + '/verify', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>' }, body: JSON.stringify({ status: status }) })
            .then(r => r.json()).then(d => { if (d.success) location.reload(); else toastr.error(d.message); });
    }
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>