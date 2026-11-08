<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - نشست منقضی شده | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{background:linear-gradient(135deg,#f39c12,#e67e22);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Tahoma,sans-serif}
        .error-card{background:white;border-radius:20px;padding:50px 40px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-width:550px;width:90%;text-align:center;animation:fadeInUp 0.6s ease-out}
        .error-icon{font-size:80px;color:#f39c12;margin-bottom:15px;animation:pulse 2s infinite}
        .error-code{font-size:100px;font-weight:900;color:#f39c12;line-height:1;margin-bottom:5px}
        .error-title{font-size:22px;font-weight:700;color:#333;margin-bottom:10px}
        .error-message{color:#666;margin-bottom:25px;line-height:1.8}
        .btn-refresh{background:#f39c12;color:white;border:none;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s}
        .btn-refresh:hover{background:#e67e22;color:white;transform:translateY(-3px);box-shadow:0 10px 20px rgba(243,156,18,0.4)}
        .btn-login{background:transparent;color:#666;border:1px solid #ddd;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;margin-right:10px;transition:all 0.3s}
        .btn-login:hover{background:#f5f5f5;color:#333}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="fas fa-clock"></i></div>
        <div class="error-code">419</div>
        <h2 class="error-title">نشست منقضی شده!</h2>
        <p class="error-message">نشست شما به دلیل عدم فعالیت طولانی مدت یا مسائل امنیتی منقضی شده است. لطفاً صفحه را رفرش کرده یا مجدداً وارد شوید.</p>
        <div>
            <a href="javascript:location.reload()" class="btn-refresh"><i class="fas fa-sync-alt me-2"></i>رفرش صفحه</a>
            <a href="<?= url('/login') ?>" class="btn-login"><i class="fas fa-sign-in-alt me-2"></i>ورود مجدد</a>
        </div>
    </div>
</body>
</html>