<?php
$parentName = $parentName ?? 'والد گرامی';
$studentName = $studentName ?? '';
$amount = $amount ?? 0;
$dueDate = $dueDate ?? '';
$categoryName = $categoryName ?? '';
$daysLeft = $daysLeft ?? 0;
$paymentUrl = $paymentUrl ?? config('app_url') . '/login';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;">
<div style="max-width:550px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 15px rgba(0,0,0,0.1);">

    <div style="background:<?= $daysLeft <= 3 ? '#e74a3b' : '#f6c23e' ?>;padding:30px 25px;text-align:center;">
        <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
            <span style="font-size:28px;">💰</span>
        </div>
        <h2 style="color:#fff;margin:0;font-size:20px;">یادآوری پرداخت شهریه</h2>
    </div>

    <div style="padding:30px 25px;">
        <p style="color:#333;font-size:16px;margin:0 0 15px;"><?= htmlspecialchars($parentName) ?> عزیز،</p>
        <p style="color:#555;line-height:1.8;font-size:14px;margin:0 0 20px;">به اطلاع می‌رساند شهریه <strong><?= htmlspecialchars($studentName) ?></strong> برای <strong><?= htmlspecialchars($categoryName) ?></strong> به شرح زیر می‌باشد:</p>

        <!-- Fee Details -->
        <div style="background:#f8f9fc;border:1px solid #e3e6f0;border-radius:10px;padding:20px;text-align:center;margin:0 0 20px;">
            <div style="font-size:28px;font-weight:900;color:<?= $daysLeft <= 3 ? '#e74a3b' : '#f6c23e' ?>;margin-bottom:5px;"><?= number_format($amount) ?> تومان</div>
            <div style="color:#666;font-size:14px;">مهلت پرداخت: <strong><?= date('Y/m/d', strtotime($dueDate)) ?></strong></div>
            <?php if ($daysLeft > 0): ?>
            <div style="margin-top:8px;font-size:13px;color:<?= $daysLeft <= 3 ? '#e74a3b' : '#888' ?>;"><?= $daysLeft ?> روز باقی مانده</div>
            <?php else: ?>
            <div style="margin-top:8px;font-size:13px;color:#e74a3b;font-weight:bold;">⛔ سررسید گذشته است</div>
            <?php endif; ?>
        </div>

        <p style="color:#555;line-height:1.8;font-size:13px;margin:0 0 20px;">لطفاً در اسرع وقت نسبت به پرداخت شهریه اقدام فرمایید. در صورت تأخیر، مشمول جریمه دیرکرد خواهید شد.</p>

        <div style="text-align:center;margin:25px 0;">
            <a href="<?= $paymentUrl ?>" style="background:<?= $daysLeft <= 3 ? '#e74a3b' : '#f6c23e' ?>;color:#fff;text-decoration:none;padding:14px 35px;border-radius:50px;font-size:15px;font-weight:bold;display:inline-block;">ورود به پنل پرداخت</a>
        </div>
    </div>

    <div style="background:#f8f9fc;padding:15px 25px;text-align:center;border-top:1px solid #e3e6f0;">
        <p style="color:#aaa;font-size:11px;margin:0 0 3px;">این یک ایمیل خودکار است.</p>
        <p style="color:#aaa;font-size:11px;margin:0;">© <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?></p>
    </div>
</div>
</body>
</html>