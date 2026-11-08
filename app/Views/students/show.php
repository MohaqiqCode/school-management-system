<?php
$title = 'پروفایل دانش‌آموز';
$student = $student ?? [];
$parents = $parents ?? [];
$documents = $documents ?? [];
$attendanceSummary = $attendanceSummary ?? [];
$recentResults = $recentResults ?? [];
$promotionHistory = $promotionHistory ?? [];
$feeStatus = $feeStatus ?? [];

$content = function() use ($student, $parents, $documents, $attendanceSummary, $recentResults, $promotionHistory, $feeStatus) {
    ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-graduate me-2"></i>پروفایل دانش‌آموز</h1>
    <div>
        <?php if (hasPermission('edit_students')): ?><a href="<?= url('/students/' . ($student['id'] ?? '') . '/edit') ?>" class="btn btn-warning"><i class="fas fa-edit me-1"></i>ویرایش</a><?php endif; ?>
        <a href="<?= url('/students') ?>" class="btn btn-secondary ms-1"><i class="fas fa-arrow-right me-1"></i>بازگشت</a>
    </div>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow text-center"><div class="card-body pt-5 pb-4">
            <img src="<?= $student['profile_photo'] ?? asset('images/default-avatar.png') ?>" class="rounded-circle shadow mb-3" width="100" height="100">
            <h4 class="fw-bold"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h4>
            <span class="badge bg-primary"><?= htmlspecialchars($student['class_name'] ?? '') ?> - <?= htmlspecialchars($student['section_name'] ?? '') ?></span>
            <div class="mt-2"><code><?= htmlspecialchars($student['student_code'] ?? '') ?></code></div>
        </div></div>

        <!-- Attendance -->
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="fas fa-calendar-check me-2"></i>حضور و غیاب</h6></div><div class="card-body text-center">
            <div class="display-4 fw-bold text-<?= ($attendanceSummary['overall_percentage'] ?? 0) >= 80 ? 'success' : (($attendanceSummary['overall_percentage'] ?? 0) >= 60 ? 'warning' : 'danger') ?>"><?= $attendanceSummary['overall_percentage'] ?? 0 ?>%</div>
            <small class="text-muted">نرخ حضور کلی</small>
            <div class="row mt-3"><div class="col-4"><span class="text-success"><?= $attendanceSummary['overall']['present'] ?? 0 ?></span><br><small>حاضر</small></div><div class="col-4"><span class="text-danger"><?= $attendanceSummary['current_month']['absent'] ?? 0 ?></span><br><small>غایب (ماه)</small></div><div class="col-4"><span class="text-info"><?= $attendanceSummary['current_month']['total'] ?? 0 ?></span><br><small>کل</small></div></div>
        </div></div>
    </div>

    <!-- Details -->
    <div class="col-xl-8 col-lg-7">
        <!-- Info -->
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>اطلاعات شخصی</h6></div><div class="card-body"><div class="row">
            <div class="col-md-4 mb-2"><small class="text-muted">ایمیل</small><br><?= htmlspecialchars($student['email'] ?? '-') ?></div>
            <div class="col-md-4 mb-2"><small class="text-muted">تلفن</small><br><?= htmlspecialchars($student['phone'] ?? '-') ?></div>
            <div class="col-md-4 mb-2"><small class="text-muted">تاریخ تولد</small><br><?= $student['date_of_birth'] ? date('Y/m/d', strtotime($student['date_of_birth'])) : '-' ?></div>
            <div class="col-md-4 mb-2"><small class="text-muted">گروه خونی</small><br><?= htmlspecialchars($student['blood_group'] ?? '-') ?></div>
            <div class="col-md-4 mb-2"><small class="text-muted">جنسیت</small><br><?= $student['gender'] === 'male' ? 'مرد' : ($student['gender'] === 'female' ? 'زن' : 'سایر') ?></div>
            <div class="col-md-4 mb-2"><small class="text-muted">تاریخ ثبت نام</small><br><?= date('Y/m/d', strtotime($student['admission_date'] ?? '')) ?></div>
        </div></div></div>

        <!-- Parents -->
        <?php if (!empty($parents)): ?><div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-user-friends me-2"></i>والدین</h6></div><div class="card-body"><?php foreach ($parents as $parent): ?><div class="d-flex align-items-center mb-2 p-2 bg-light rounded"><i class="fas fa-user me-2"></i><strong><?= htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']) ?></strong><span class="badge bg-secondary ms-2"><?= htmlspecialchars($parent['relationship'] ?? '') ?></span><span class="ms-auto small"><?= htmlspecialchars($parent['phone'] ?? '') ?></span></div><?php endforeach; ?></div></div><?php endif; ?>

        <!-- Recent Results -->
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="fas fa-star me-2"></i>نتایج اخیر</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>درس</th><th>نمره</th><th>نمره کل</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody><?php if (!empty($recentResults)): ?><?php foreach ($recentResults as $res): ?><tr><td><?= htmlspecialchars($res['subject_name']) ?></td><td class="fw-bold"><?= $res['marks_obtained'] ?></td><td><?= $res['total_marks'] ?></td><td><span class="badge bg-<?= $res['marks_obtained'] >= ($res['passing_marks'] ?? 50) ? 'success' : 'danger' ?>"><?= $res['marks_obtained'] >= ($res['passing_marks'] ?? 50) ? 'قبول' : 'مردود' ?></span></td><td><small><?= date('Y/m/d', strtotime($res['exam_date'] ?? '')) ?></small></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="5" class="text-center text-muted py-3">نتیجه‌ای ثبت نشده</td></tr><?php endif; ?></tbody></table></div></div></div>

        <!-- Fees -->
        <div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-danger"><i class="fas fa-money-bill me-2"></i>وضعیت شهریه</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>دسته‌بندی</th><th>مبلغ کل</th><th>پرداختی</th><th>باقیمانده</th><th>سررسید</th><th>وضعیت</th></tr></thead><tbody><?php if (!empty($feeStatus)): ?><?php foreach ($feeStatus as $fee): ?><tr><td><?= htmlspecialchars($fee['category_name']) ?></td><td><?= number_format($fee['net_payable'] ?? 0) ?></td><td class="text-success"><?= number_format($fee['paid_amount'] ?? 0) ?></td><td class="text-danger fw-bold"><?= number_format($fee['balance_amount'] ?? 0) ?></td><td><?= $fee['due_date'] ?? '' ?></td><td><span class="badge bg-<?= $fee['status'] === 'paid' ? 'success' : ($fee['status'] === 'overdue' ? 'danger' : 'warning') ?>"><?= $fee['status'] === 'paid' ? 'پرداخت شده' : ($fee['status'] === 'overdue' ? 'سررسید گذشته' : 'در انتظار') ?></span></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="6" class="text-center text-muted py-3">شهریه‌ای ثبت نشده</td></tr><?php endif; ?></tbody></table></div></div></div>
    </div>
</div>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>