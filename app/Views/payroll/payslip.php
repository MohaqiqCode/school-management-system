<?php
$title = 'فیش حقوقی';
$payroll = $payroll ?? [];

$content = function() use ($payroll) {
    ob_start();
    $allowances = $payroll['allowances'] ?? 0;
    $deductions = $payroll['deductions'] ?? 0;
    $gross = ($payroll['basic_salary'] ?? 0) + $allowances;
    $net = $gross - $deductions;
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
    <h1 class="h3 mb-0"><i class="fas fa-file-alt me-2"></i>فیش حقوقی</h1>
    <div>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>چاپ</button>
        <a href="<?= url('/payroll') ?>" class="btn btn-secondary btn-sm ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row"><div class="col-lg-8 mx-auto"><div class="card shadow" id="payslipPrint">
    <!-- Header -->
    <div class="card-body p-4">
        <div class="text-center border-bottom pb-3 mb-4">
            <h4 class="fw-bold mb-0"><?= config('app_name', 'سیستم مدیریت مدرسه') ?></h4>
            <h5 class="text-muted">فیش حقوقی</h5>
        </div>

        <!-- Employee & Period Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="100"><strong>نام:</strong></td><td><?= htmlspecialchars(($payroll['first_name'] ?? '') . ' ' . ($payroll['last_name'] ?? '')) ?></td></tr>
                    <tr><td><strong>ایمیل:</strong></td><td><?= htmlspecialchars($payroll['email'] ?? '') ?></td></tr>
                    <tr><td><strong>شعبه:</strong></td><td><?= htmlspecialchars($payroll['branch_name'] ?? '') ?></td></tr>
                    <tr><td><strong>گرید:</strong></td><td><code><?= htmlspecialchars($payroll['salary_grade'] ?? '-') ?></code></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="100"><strong>دوره:</strong></td><td><?= date('Y/m/d', strtotime($payroll['pay_period_start'] ?? '')) ?> تا <?= date('Y/m/d', strtotime($payroll['pay_period_end'] ?? '')) ?></td></tr>
                    <tr><td><strong>تاریخ پرداخت:</strong></td><td><?= $payroll['payment_date'] ? date('Y/m/d', strtotime($payroll['payment_date'])) : '-' ?></td></tr>
                    <tr><td><strong>وضعیت:</strong></td><td><span class="badge bg-<?= ($payroll['status'] ?? '') === 'paid' ? 'success' : (($payroll['status'] ?? '') === 'processed' ? 'info' : 'warning') ?>"><?= ($payroll['status'] ?? '') === 'paid' ? 'پرداخت شده' : (($payroll['status'] ?? '') === 'processed' ? 'پردازش شده' : 'در انتظار') ?></span></td></tr>
                </table>
            </div>
        </div>

        <!-- Salary Details -->
        <div class="row mb-3">
            <div class="col-md-6"><div class="card bg-light"><div class="card-body py-2"><h6 class="fw-bold text-success mb-2">مزایا و دریافتی‌ها</h6><table class="table table-sm mb-0"><tr><td>حقوق پایه</td><td class="text-end"><?= number_format($payroll['basic_salary'] ?? 0) ?> ت</td></tr><tr><td>مزایا</td><td class="text-end"><?= number_format($allowances) ?> ت</td></tr><tr class="fw-bold"><td>جمع ناخالص</td><td class="text-end"><?= number_format($gross) ?> ت</td></tr></table></div></div></div>
            <div class="col-md-6"><div class="card bg-light"><div class="card-body py-2"><h6 class="fw-bold text-danger mb-2">کسورات</h6><table class="table table-sm mb-0"><tr><td>کسورات</td><td class="text-end"><?= number_format($deductions) ?> ت</td></tr></table></div></div></div>
        </div>

        <!-- Net Salary -->
        <div class="card bg-primary bg-opacity-10 border-primary mb-4"><div class="card-body text-center py-3"><h4 class="mb-0">خالص پرداختی: <strong class="text-primary"><?= number_format($net) ?> تومان</strong></h4></div></div>

        <!-- Signatures -->
        <div class="row mt-5 pt-4"><div class="col-6 text-center"><div class="border-top pt-2">مهر و امضای کارفرما</div></div><div class="col-6 text-center"><div class="border-top pt-2">امضای کارمند</div></div></div>
    </div>
</div></div></div>

<style>@media print{body{background:white}.no-print{display:none!important}.card{box-shadow:none!important;border:1px solid #ddd!important}}</style>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php'; $content = $content();
?>