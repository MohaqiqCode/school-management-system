<?php
$name = $name ?? 'کاربر';
$email = $email ?? '';
$password = $password ?? '';
$role = $role ?? '';
$loginUrl = $loginUrl ?? config('app_url') . '/login';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;">
<div style="max-width:600px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 15px rgba(0,0,0,0.1);">

    <!-- Header -->
    <div style="background:linear-gradient(135deg,#667eea,#764ba2);padding:35px 25px;text-align:center;">
        <div style="width:65px;height:65px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:15px;">
            <span style="font-size:32px;">🎓</span>
        </div>
        <h1 style="color:#fff;margin:0;font-size:22px;"><?= config('app_name', 'سیستم مدیریت مدرسه') ?></h1>
        <p style="color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px;">به خانواده ما خوش آمدید!</p>
    </div>

    <!-- Body -->
    <div style="padding:35px 30px;">
        <h2 style="color:#333;margin:0 0 15px;font-size:20px;">سلام <?= htmlspecialchars($name) ?> عزیز،</h2>
        <p style="color:#555;line-height:1.8;font-size:15px;margin:0 0 20px;">حساب کاربری شما با موفقیت در <strong><?= config('app_name', 'سیستم مدیریت مدرسه') ?></strong> ایجاد شده است. اکنون می‌توانید با اطلاعات زیر وارد سیستم شوید:</p>

        <!-- Credentials Box -->
        <div style="background:#f8f9fc;border:1px solid #e3e6f0;border-radius:10px;padding:20px 25px;margin:0 0 25px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr><td style="padding:6px 0;color:#666;text-align:right;" width="100">👤 نام کاربری:</td><td style="padding:6px 0;text-align:right;"><strong><?= htmlspecialchars($email) ?></strong></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">🔑 رمز عبور:</td><td style="padding:6px 0;text-align:right;"><strong><?= htmlspecialchars($password) ?></strong></td></tr>
                <tr><td style="padding:6px 0;color:#666;text-align:right;">🏫 نقش:</td><td style="padding:6px 0;text-align:right;"><?= htmlspecialchars($role) ?></td></tr>
            </table>
        </div>

        <p style="color:#e74a3b;font-size:13px;margin:0 0 25px;background:#fde8e8;padding:12px 15px;border-radius:8px;">⚠️ <strong>توصیه امنیتی:</strong> لطفاً پس از اولین ورود، رمز عبور خود را تغییر دهید.</p>

        <!-- CTA -->
        <div style="text-align:center;margin:0 0 20px;">
            <a href="<?= $loginUrl ?>" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;text-decoration:none;padding:14px 40px;border-radius:50px;font-size:16px;font-weight:bold;display:inline-block;transition:0.3s;">ورود به سیستم</a>
        </div>
    </div>

    <!-- Footer -->
    <div style="background:#f8f9fc;padding:18px 25px;text-align:center;border-top:1px solid #e3e6f0;">
        <p style="color:#888;font-size:11px;margin:0 0 3px;">این یک ایمیل خودکار است. لطفاً به آن پاسخ ندهید.</p>
        <p style="color:#888;font-size:11px;margin:0;">© <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?></p>
    </div>
</div>
</body>
</html>