<?php
$parentName = $parentName ?? 'والد گرامی';
$studentName = $studentName ?? '';
$examName = $examName ?? '';
$className = $className ?? '';
$resultUrl = $resultUrl ?? config('app_url') . '/login';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;">
<div style="max-width:550px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 15px rgba(0,0,0,0.1);">

    <div style="background:#4e73df;padding:30px 25px;text-align:center;">
        <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
            <span style="font-size:28px;">📊</span>
        </div>
        <h2 style="color:#fff;margin:0;font-size:20px;">نتایج آزمون منتشر شد</h2>
    </div>

    <div style="padding:30px 25px;">
        <p style="color:#333;font-size:16px;margin:0 0 15px;"><?= htmlspecialchars($parentName) ?> عزیز،</p>
        <p style="color:#555;line-height:1.8;font-size:14px;margin:0 0 20px;">نتایج آزمون <strong><?= htmlspecialchars($examName) ?></strong> برای <strong><?= htmlspecialchars($studentName) ?></strong> منتشر شده است.</p>

        <!-- Info Box -->
        <div style="background:#f0f4ff;border:1px solid #4e73df;border-radius:10px;padding:20px;text-align:center;margin:0 0 20px;">
            <div style="margin-bottom:8px;"><span style="color:#666;">دانش‌آموز:</span> <strong><?= htmlspecialchars($studentName) ?></strong></div>
            <div style="margin-bottom:8px;"><span style="color:#666;">آزمون:</span> <strong><?= htmlspecialchars($examName) ?></strong></div>
            <div><span style="color:#666;">کلاس:</span> <strong><?= htmlspecialchars($className) ?></strong></div>
        </div>

        <p style="color:#555;line-height:1.8;font-size:14px;margin:0 0 20px;">برای مشاهده کارنامه کامل و جزئیات نمرات، وارد پنل کاربری خود شوید.</p>

        <div style="text-align:center;margin:25px 0;">
            <a href="<?= $resultUrl ?>" style="background:#4e73df;color:#fff;text-decoration:none;padding:14px 35px;border-radius:50px;font-size:15px;font-weight:bold;display:inline-block;">📄 مشاهده کارنامه</a>
        </div>
    </div>

    <div style="background:#f8f9fc;padding:15px 25px;text-align:center;border-top:1px solid #e3e6f0;">
        <p style="color:#aaa;font-size:11px;margin:0;">© <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?></p>
    </div>
</div>
</body>
</html>