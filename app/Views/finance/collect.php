<?php
$title = 'دریافت شهریه';
$pendingFees = $pendingFees ?? [];
$student = $student ?? null;
$searchStudent = $searchStudent ?? '';

$content = function() use ($pendingFees, $student, $searchStudent) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-money-bill me-2"></i>دریافت شهریه</h1>
    <a href="<?= url('/fees') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
</div>

<!-- Search Student -->
<div class="card shadow mb-4"><div class="card-body"><form action="<?= url('/fees/collect') ?>" method="GET" class="row g-3"><div class="col-md-10"><input type="text" class="form-control" name="student_id" placeholder="جستجوی دانش‌آموز با کد یا نام..." value="<?= htmlspecialchars($searchStudent) ?>"></div><div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>جستجو</button></div></form></div></div>

<?php if ($student): ?>
<div class="alert alert-info"><strong><?= htmlspecialchars(($student['first_name']??'').' '.($student['last_name']??'')) ?></strong> | کد: <?= htmlspecialchars($student['student_code']??'') ?> | کلاس: <?= htmlspecialchars($student['class_name']??'') ?></div>

<?php if (!empty($pendingFees)): ?>
    <?php foreach ($pendingFees as $fee): ?>
    <div class="card shadow mb-3"><div class="card-body"><div class="row align-items-center"><div class="col-md-4"><h6 class="fw-bold"><?= htmlspecialchars($fee['category_name']) ?></h6><small class="text-muted">سررسید: <?= $fee['due_date'] ?> | وضعیت: <span class="badge bg-<?= $fee['status']==='overdue'?'danger':'warning' ?>"><?= $fee['status']==='overdue'?'سررسید گذشته':'در انتظار' ?></span></small></div><div class="col-md-3"><small>مبلغ کل:</small><br><strong><?= number_format($fee['net_payable']) ?> ت</strong></div><div class="col-md-2"><small>پرداختی:</small><br><strong class="text-success"><?= number_format($fee['paid_amount']) ?> ت</strong></div><div class="col-md-2"><small>باقیمانده:</small><br><strong class="text-danger"><?= number_format($fee['balance_amount']) ?> ت</strong></div><div class="col-md-1"><button class="btn btn-success btn-sm" onclick="showPayModal(<?= $fee['id'] ?>, <?= $fee['balance_amount'] ?>)"><i class="fas fa-money-bill"></i></button></div></div></div></div>
    <?php endforeach; ?>
<?php else: ?><div class="alert alert-success">شهریه معوقی ندارد</div><?php endif; ?>
<?php endif; ?>

<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><form id="payForm"><div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="fas fa-money-bill me-2"></i>پرداخت</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']??''?>"><input type="hidden" name="student_fee_id" id="payFeeId"><div class="mb-3"><label class="form-label">مبلغ (حداکثر: <span id="maxAmount"></span> ت)</label><input type="number" class="form-control" name="amount" id="payAmount" required></div><div class="mb-3"><label class="form-label">روش پرداخت</label><select class="form-select" name="payment_method"><option value="cash">نقد</option><option value="bank_transfer">کارت به کارت</option><option value="online">آنلاین</option><option value="check">چک</option></select></div><div class="mb-3"><label class="form-label">توضیحات</label><input type="text" class="form-control" name="remarks"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button><button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>ثبت پرداخت</button></div></form></div></div></div>

<script>
function showPayModal(id, max) { document.getElementById('payFeeId').value = id; document.getElementById('maxAmount').textContent = max.toLocaleString('fa-IR'); document.getElementById('payAmount').max = max; document.getElementById('payAmount').value = max; new bootstrap.Modal(document.getElementById('payModal')).show(); }
document.getElementById('payForm').addEventListener('submit', function(e) { e.preventDefault(); const fd = new FormData(this); fetch('<?=url('/fees/collect')?>', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': '<?=$_SESSION['csrf_token']??''?>' } }).then(r => r.json()).then(d => { if (d.success) { toastr.success(d.message); setTimeout(() => location.reload(), 1000); } else { toastr.error(d.message); } }); });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>