<?php
$name = $name ?? 'کاربر';
$resetLink = $resetLink ?? '#';
$expiryHours = $expiryHours ?? 1;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;">
<div style="max-width:550px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 15px rgba(0,0,0,0.1);">

    <div style="background:#f39c12;padding:30px 25px;text-align:center;">
        <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
            <span style="font-size:28px;">🔑</span>
        </div>
        <h2 style="color:#fff;margin:0;font-size:20px;">بازنشانی رمز عبور</h2>
    </div>

    <div style="padding:30px 25px;">
        <p style="color:#333;font-size:16px;margin:0 0 15px;">سلام <?= htmlspecialchars($name) ?> عزیز،</p>
        <p style="color:#555;line-height:1.8;font-size:14px;margin:0 0 20px;">درخواست بازنشانی رمز عبور برای حساب شما ثبت شده است. برای تنظیم رمز عبور جدید، روی دکمه زیر کلیک کنید:</p>

        <div style="text-align:center;margin:25px 0;">
            <a href="<?= $resetLink ?>" style="background:#f39c12;color:#fff;text-decoration:none;padding:14px 35px;border-radius:50px;font-size:15px;font-weight:bold;display:inline-block;">بازنشانی رمز عبور</a>
        </div>

        <p style="color:#888;font-size:13px;margin:0 0 8px;text-align:center;">یا لینک زیر را در مرورگر خود باز کنید:</p>
        <p style="background:#f8f9fc;padding:10px;border-radius:8px;text-align:center;word-break:break-all;font-size:12px;color:#666;margin:0 0 20px;"><a href="<?= $resetLink ?>" style="color:#f39c12;"><?= $resetLink ?></a></p>

        <div style="background:#fff3cd;border:1px solid #f39c12;border-radius:8px;padding:12px 15px;text-align:center;">
            <p style="color:#856404;font-size:13px;margin:0;">⏰ این لینک تا <strong><?= $expiryHours ?> ساعت</strong> دیگر معتبر است.</p>
        </div>

        <p style="color:#888;font-size:12px;margin:20px 0 0;text-align:center;">اگر شما این درخواست را نداده‌اید، این ایمیل را نادیده بگیرید.</p>
    </div>

    <div style="background:#f8f9fc;padding:15px 25px;text-align:center;border-top:1px solid #e3e6f0;">
        <p style="color:#aaa;font-size:11px;margin:0;">© <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?></p>
    </div>
</div>
</body>
</html>