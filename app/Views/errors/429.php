<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - تعداد درخواست زیاد | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{background:linear-gradient(135deg,#3498db,#2980b9);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Tahoma,sans-serif}
        .error-card{background:white;border-radius:20px;padding:50px 40px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-width:550px;width:90%;text-align:center;animation:fadeInUp 0.6s ease-out}
        .error-icon{font-size:80px;color:#3498db;margin-bottom:15px;animation:shake 0.5s ease-in-out 3}
        .error-code{font-size:100px;font-weight:900;color:#3498db;line-height:1;margin-bottom:5px}
        .error-title{font-size:22px;font-weight:700;color:#333;margin-bottom:10px}
        .error-message{color:#666;margin-bottom:25px;line-height:1.8}
        .btn-wait{background:#3498db;color:white;border:none;padding:12px 30px;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s}
        .btn-wait:hover{background:#2980b9;color:white;transform:translateY(-3px);box-shadow:0 10px 20px rgba(52,152,219,0.4)}
        .countdown{font-size:40px;font-weight:900;color:#3498db;margin:10px 0}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        @keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-10px)}75%{transform:translateX(10px)}}
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="fas fa-tachometer-alt"></i></div>
        <div class="error-code">429</div>
        <h2 class="error-title">تعداد درخواست‌ها بیش از حد مجاز!</h2>
        <p class="error-message">شما در مدت کوتاهی درخواست‌های زیادی ارسال کرده‌اید. لطفاً کمی صبر کنید و سپس مجدداً تلاش نمایید.</p>
        <div class="countdown" id="countdown">60</div>
        <small class="text-muted d-block mb-3">ثانیه تا تلاش مجدد</small>
        <a href="javascript:location.reload()" class="btn-wait" id="retryBtn" style="display:none"><i class="fas fa-redo me-2"></i>تلاش مجدد</a>
    </div>
    <script>
        let seconds=60;const cd=document.getElementById('countdown'),btn=document.getElementById('retryBtn');
        const timer=setInterval(()=>{seconds--;cd.textContent=seconds;if(seconds<=0){clearInterval(timer);cd.style.display='none';btn.style.display='inline-block'}},1000);
    </script>
</body>
</html>