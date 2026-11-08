<?php
$parentName = $parentName ?? 'والد گرامی';
$studentName = $studentName ?? '';
$amount = $amount ?? 0;
$paymentDate = $paymentDate ?? date('Y-m-d H:i:s');
$paymentMethod = $paymentMethod ?? 'آنلاین';
$receiptNumber = $receiptNumber ?? '';
$trackingCode = $trackingCode ?? '';
$categoryName = $categoryName ?? '';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;">
<div style="max-width:550px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 15px rgba(0,0,0,0.1);">

    <div style="background:#1cc88a;padding:30px 25px;text-align:center;">
        <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
            <span style="font-size:28px;">✅</span>
        </div>
        <h2 style="color:#fff;margin:0;font-size:20px;">رسید پرداخت</h2>
        <p style="color:rgba(255,255,255,0.85);margin:5px 0 0;font-size:13px;">پرداخت با موفقیت انجام شد</p>
    </div>

    <div style="padding:30px 25px;">
        <p style="color:#333;font-size:16px;margin:0 0 20px;"><?= htmlspecialchars($parentName) ?> عزیز،</p>
        <p style="color:#555;line-height:1.8;font-size:14px;margin:0 0 20px;">پرداخت شهریه <strong><?= htmlspecialchars($studentName) ?></strong> با موفقیت ثبت گردید. جزئیات پرداخت به شرح زیر است:</p>

        <!-- Receipt Box -->
        <div style="background:#f8f9fc;border:2px dashed #1cc88a;border-radius:12px;padding:25px;margin:0 0 20px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr><td style="padding:6px 0;color:#666;text-align:right;" width="110">📋 شماره رسید:</td><td style="padding:6px 0;text-align:right;"><strong><?= htmlspecialchars($receiptNumber) ?></strong></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">🔢 کد پیگیری:</td><td style="padding:6px 0;text-align:right;"><strong><?= htmlspecialchars($trackingCode) ?></strong></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">💰 مبلغ:</td><td style="padding:6px 0;text-align:right;"><strong style="color:#1cc88a;font-size:18px;"><?= number_format($amount) ?> تومان</strong></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">📂 دسته‌بندی:</td><td style="padding:6px 0;text-align:right;"><?= htmlspecialchars($categoryName) ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">💳 روش پرداخت:</td><td style="padding:6px 0;text-align:right;"><?= htmlspecialchars($paymentMethod) ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">📅 تاریخ:</td><td style="padding:6px 0;text-align:right;"><?= date('Y/m/d H:i', strtotime($paymentDate)) ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">👤 دانش‌آموز:</td><td style="padding:6px 0;text-align:right;"><?= htmlspecialchars($studentName) ?></td></tr>
            </table>
        </div>

        <div style="background:#def7ec;border-radius:8px;padding:12px 15px;text-align:center;">
            <p style="color:#046c4e;font-size:13px;margin:0;">از پرداخت به موقع شما سپاسگزاریم. 🙏</p>
        </div>
    </div>

    <div style="background:#f8f9fc;padding:15px 25px;text-align:center;border-top:1px solid #e3e6f0;">
        <p style="color:#aaa;font-size:11px;margin:0 0 3px;">این یک رسید رسمی است. لطفاً آن را نزد خود نگه دارید.</p>
        <p style="color:#aaa;font-size:11px;margin:0;">© <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?></p>
    </div>
</div>
</body>
</html>