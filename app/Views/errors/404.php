<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - صفحه یافت نشد | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Tahoma,sans-serif}
        .error-card{background:white;border-radius:20px;padding:50px 40px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-width:550px;width:90%;text-align:center;animation:fadeInUp 0.6s ease-out}
        .error-icon{font-size:80px;color:#f6c23e;margin-bottom:15px;animation:swing 2s ease-in-out infinite}
        .error-code{font-size:100px;font-weight:900;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;margin-bottom:5px}
        .error-title{font-size:22px;font-weight:700;color:#333;margin-bottom:10px}
        .error-message{color:#666;margin-bottom:25px;line-height:1.8}
        .btn-home{background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s}
        .btn-home:hover{color:white;transform:translateY(-3px);box-shadow:0 10px 20px rgba(102,126,234,0.4)}
        .btn-back{background:transparent;color:#666;border:1px solid #ddd;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;margin-right:10px;transition:all 0.3s}
        .btn-back:hover{background:#f5f5f5;color:#333}
        .search-box{margin-top:20px;display:flex;gap:5px;justify-content:center}
        .search-box input{border:1px solid #ddd;border-radius:50px;padding:8px 20px;width:200px;text-align:right}
        .search-box button{background:#667eea;color:white;border:none;border-radius:50px;padding:8px 15px;cursor:pointer}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        @keyframes swing{0%,100%{transform:rotate(0)}25%{transform:rotate(-10deg)}75%{transform:rotate(10deg)}}
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">🔍</div>
        <div class="error-code">404</div>
        <h2 class="error-title">صفحه مورد نظر یافت نشد!</h2>
        <p class="error-message">متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد، حذف شده یا آدرس آن تغییر کرده است. لطفاً آدرس را بررسی کنید.</p>
        <div>
            <a href="<?= url('/dashboard') ?>" class="btn-home"><i class="fas fa-home me-2"></i>داشبورد</a>
            <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-right me-2"></i>بازگشت</a>
        </div>
        <div class="search-box">
            <input type="text" placeholder="جستجو..." onkeypress="if(event.key==='Enter')window.location='<?=url('/search')?>?q='+this.value">
            <button onclick="window.location='<?=url('/search')?>?q='+this.previousElementSibling.value"><i class="fas fa-search"></i></button>
        </div>
    </div>
</body>
</html>