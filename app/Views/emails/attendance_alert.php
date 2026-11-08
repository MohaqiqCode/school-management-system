<?php
$parentName = $parentName ?? 'والد گرامی';
$studentName = $studentName ?? '';
$date = $date ?? date('Y-m-d');
$status = $status ?? 'absent';
$totalAbsences = $totalAbsences ?? 0;
$className = $className ?? '';
$attendanceUrl = $attendanceUrl ?? config('app_url') . '/login';

$statusLabels = ['absent'=>'غایب','late'=>'تأخیر','half_day'=>'نیمه وقت'];
$statusColors = ['absent'=>'#e74a3b','late'=>'#f6c23e','half_day'=>'#3498db'];
$statusColor = $statusColors[$status] ?? '#e74a3b';
$statusLabel = $statusLabels[$status] ?? 'غایب';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;">
<div style="max-width:550px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 15px rgba(0,0,0,0.1);">

    <div style="background:<?= $statusColor ?>;padding:30px 25px;text-align:center;">
        <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
            <span style="font-size:28px;">⚠️</span>
        </div>
        <h2 style="color:#fff;margin:0;font-size:20px;">هشدار حضور و غیاب</h2>
    </div>

    <div style="padding:30px 25px;">
        <p style="color:#333;font-size:16px;margin:0 0 15px;"><?= htmlspecialchars($parentName) ?> عزیز،</p>
        <p style="color:#555;line-height:1.8;font-size:14px;margin:0 0 20px;">به اطلاع می‌رساند <strong><?= htmlspecialchars($studentName) ?></strong> در تاریخ <strong><?= date('Y/m/d', strtotime($date)) ?></strong> <span style="color:<?= $statusColor ?>;font-weight:bold;"><?= $statusLabel ?></span> بوده است.</p>

        <!-- Alert Box -->
        <div style="background:<?= $statusColor ?>15;border:2px solid <?= $statusColor ?>;border-radius:12px;padding:25px;text-align:center;margin:0 0 20px;">
            <div style="font-size:40px;margin-bottom:10px;"><?= $status === 'absent' ? '😔' : ($status === 'late' ? '⏰' : '📋') ?></div>
            <div style="font-size:18px;font-weight:900;color:<?= $statusColor ?>;margin-bottom:5px;"><?= $statusLabel ?></div>
            <div style="color:#666;font-size:14px;">دانش‌آموز: <strong><?= htmlspecialchars($studentName) ?></strong></div>
            <div style="color:#666;font-size:14px;">کلاس: <strong><?= htmlspecialchars($className) ?></strong></div>
            <div style="color:#666;font-size:14px;">تاریخ: <strong><?= date('Y/m/d', strtotime($date)) ?></strong></div>
            <?php if ($totalAbsences > 3): ?>
            <div style="margin-top:10px;padding:8px;background:rgba(231,76,60,0.1);border-radius:6px;color:#e74a3b;font-size:13px;">⚠️ مجموع غیبت‌ها: <strong><?= $totalAbsences ?> جلسه</strong></div>
            <?php endif; ?>
        </div>

        <p style="color:#555;line-height:1.8;font-size:13px;margin:0 0 20px;">لطفاً علت این غیبت را با مدرسه در میان بگذارید. در صورت تکرار، اقدامات انضباطی صورت خواهد گرفت.</p>

        <div style="text-align:center;margin:25px 0;">
            <a href="<?= $attendanceUrl ?>" style="background:<?= $statusColor ?>;color:#fff;text-decoration:none;padding:14px 35px;border-radius:50px;font-size:15px;font-weight:bold;display:inline-block;">مشاهده جزئیات حضور و غیاب</a>
        </div>
    </div>

    <div style="background:#f8f9fc;padding:15px 25px;text-align:center;border-top:1px solid #e3e6f0;">
        <p style="color:#aaa;font-size:11px;margin:0 0 3px;">این یک پیام خودکار است.</p>
        <p style="color:#aaa;font-size:11px;margin:0;">© <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?></p>
    </div>
</div>
</body>
</html>