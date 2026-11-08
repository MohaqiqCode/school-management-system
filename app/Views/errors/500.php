<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - خطای سرور | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{background:#1a1a2e;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Tahoma,sans-serif}
        .error-card{background:#16213e;border-radius:20px;padding:50px 40px;box-shadow:0 20px 60px rgba(0,0,0,0.5);max-width:550px;width:90%;text-align:center;animation:fadeInUp 0.6s ease-out;border:1px solid rgba(255,255,255,0.1)}
        .error-icon{font-size:80px;color:#e74a3b;margin-bottom:15px;animation:glow 2s ease-in-out infinite}
        .error-code{font-size:100px;font-weight:900;color:#e74a3b;line-height:1;margin-bottom:5px;text-shadow:0 0 30px rgba(231,76,60,0.5)}
        .error-title{font-size:22px;font-weight:700;color:#e0e0e0;margin-bottom:10px}
        .error-message{color:#aaa;margin-bottom:25px;line-height:1.8}
        .btn-home{background:#e74a3b;color:white;border:none;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s}
        .btn-home:hover{background:#c0392b;color:white;transform:translateY(-3px);box-shadow:0 10px 20px rgba(231,76,60,0.4)}
        .btn-back{background:transparent;color:#aaa;border:1px solid rgba(255,255,255,0.2);padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;margin-right:10px;transition:all 0.3s}
        .btn-back:hover{background:rgba(255,255,255,0.05);color:#fff}
        .error-details{margin-top:20px;padding:15px;background:rgba(0,0,0,0.3);border-radius:10px;text-align:right;direction:ltr;max-height:150px;overflow:auto}
        .error-details code{color:#e74a3b;font-size:12px}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        @keyframes glow{0%,100%{text-shadow:0 0 20px rgba(231,76,60,0.5)}50%{text-shadow:0 0 40px rgba(231,76,60,0.8),0 0 60px rgba(231,76,60,0.4)}}
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="error-code">500</div>
        <h2 class="error-title">خطای سرور!</h2>
        <p class="error-message">متأسفانه مشکلی در سرور رخ داده است. تیم فنی در جریان قرار گرفته و در حال بررسی است. لطفاً چند دقیقه دیگر مجدداً تلاش کنید.</p>
        <div>
            <a href="<?= url('/dashboard') ?>" class="btn-home"><i class="fas fa-home me-2"></i>داشبورد</a>
            <a href="javascript:location.reload()" class="btn-back"><i class="fas fa-redo me-2"></i>تلاش مجدد</a>
        </div>
        <?php if (config('app_debug', false) && isset($exception)): ?>
        <div class="error-details"><code><?= htmlspecialchars($exception->getMessage()) ?><br><br><?= htmlspecialchars($exception->getTraceAsString()) ?></code></div>
        <?php endif; ?>
    </div>
</body>
</html>