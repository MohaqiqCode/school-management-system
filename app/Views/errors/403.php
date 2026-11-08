<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - دسترسی غیرمجاز | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{background:linear-gradient(135deg,#e74a3b,#c0392b);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Tahoma,sans-serif}
        .error-card{background:white;border-radius:20px;padding:50px 40px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-width:550px;width:90%;text-align:center;animation:fadeInUp 0.6s ease-out}
        .error-icon{font-size:80px;color:#e74a3b;margin-bottom:15px;animation:bounce 2s infinite}
        .error-code{font-size:100px;font-weight:900;color:#e74a3b;line-height:1;margin-bottom:5px}
        .error-title{font-size:22px;font-weight:700;color:#333;margin-bottom:10px}
        .error-message{color:#666;margin-bottom:25px;line-height:1.8}
        .btn-home{background:#e74a3b;color:white;border:none;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s}
        .btn-home:hover{background:#c0392b;color:white;transform:translateY(-3px);box-shadow:0 10px 20px rgba(231,76,60,0.4)}
        .btn-back{background:transparent;color:#666;border:1px solid #ddd;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;margin-right:10px;transition:all 0.3s}
        .btn-back:hover{background:#f5f5f5;color:#333}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        @keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-15px)}}
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="fas fa-lock"></i></div>
        <div class="error-code">403</div>
        <h2 class="error-title">دسترسی غیرمجاز!</h2>
        <p class="error-message">شما مجوز دسترسی به این صفحه را ندارید. اگر فکر می‌کنید این یک اشتباه است، لطفاً با مدیر سیستم تماس بگیرید.</p>
        <div>
            <a href="<?= url('/dashboard') ?>" class="btn-home"><i class="fas fa-home me-2"></i>بازگشت به داشبورد</a>
            <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-right me-2"></i>صفحه قبل</a>
        </div>
    </div>
</body>
</html>