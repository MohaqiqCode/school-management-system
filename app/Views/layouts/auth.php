<?php
/**
 * Auth Layout
 * لایه مخصوص صفحات احراز هویت (ورود، ثبت نام، فراموشی رمز عبور)
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $title ?? 'ورود' ?> | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Custom Auth CSS -->
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
    
    <style>
        :root {
            --primary-color: <?= config('app.colors.primary', '#4e73df') ?>;
            --secondary-color: <?= config('app.colors.secondary', '#858796') ?>;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'IRANSans', 'Tahoma', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Background Animation */
        .auth-background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .auth-background::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: rotate 30s linear infinite;
        }
        
        .auth-background .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite;
        }
        
        .auth-background .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -100px;
            animation-delay: 0s;
        }
        
        .auth-background .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -50px;
            animation-delay: -5s;
        }
        
        .auth-background .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 10%;
            animation-delay: -10s;
        }
        
        .auth-background .shape:nth-child(4) {
            width: 250px;
            height: 250px;
            bottom: 20%;
            right: 15%;
            animation-delay: -15s;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.1); }
        }
        
        /* Auth Container */
        .auth-wrapper {
            width: 100%;
            max-width: 480px;
            padding: 20px;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Auth Card */
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .auth-card:hover {
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.3);
        }
        
        /* Auth Header */
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 120%;
            height: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
        }
        
        .auth-logo {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }
        
        .auth-logo:hover {
            transform: scale(1.1) rotate(10deg);
        }
        
        .auth-logo i {
            font-size: 42px;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .auth-header h2 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 22px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .auth-header p {
            opacity: 0.9;
            margin-bottom: 0;
            font-size: 14px;
        }
        
        /* Auth Body */
        .auth-body {
            padding: 40px 35px;
        }
        
        /* Form Styles */
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating > .form-control,
        .form-floating > .form-select {
            border-radius: 12px;
            border: 2px solid #e3e6f0;
            padding: 1rem 0.75rem;
            height: calc(3.5rem + 2px);
            transition: all 0.3s ease;
            text-align: right;
        }
        
        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
            background: #fff;
        }
        
        .form-floating > label {
            padding: 1rem 0.75rem;
            right: 0;
            left: auto;
            text-align: right;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
            color: var(--primary-color);
        }
        
        /* Input Group */
        .input-group .form-control {
            border-radius: 0 12px 12px 0 !important;
        }
        
        .input-group .input-group-text {
            border-radius: 12px 0 0 12px !important;
            background: #f8f9fc;
            border: 2px solid #e3e6f0;
            border-left: none;
        }
        
        .input-group .input-group-text:first-child {
            border-radius: 0 12px 12px 0 !important;
        }
        
        .input-group .form-control:focus + .input-group-text {
            border-color: var(--primary-color);
        }
        
        /* Password Toggle */
        .password-toggle {
            cursor: pointer;
            background: transparent;
            border: none;
            padding: 0 15px;
            transition: color 0.2s;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        /* Buttons */
        .btn-auth {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-auth::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn-auth:hover::before {
            left: 100%;
        }
        
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-auth:active {
            transform: translateY(0);
        }
        
        .btn-auth:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Links */
        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .auth-link:hover {
            color: #2e59d9;
            text-decoration: underline;
        }
        
        /* Checkbox */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: #aaa;
        }
        
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .auth-divider span {
            padding: 0 15px;
            font-size: 13px;
        }
        
        /* Alert Styles */
        .alert {
            border-radius: 12px;
            border: none;
            font-size: 14px;
        }
        
        .alert-danger {
            background: #fde8e8;
            color: #c81e1e;
        }
        
        .alert-success {
            background: #def7ec;
            color: #046c4e;
        }
        
        .alert-warning {
            background: #fdf6b2;
            color: #8e4b10;
        }
        
        .alert-info {
            background: #e1effe;
            color: #1e429f;
        }
        
        /* Footer */
        .auth-footer {
            background: #f8f9fc;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e3e6f0;
        }
        
        .auth-footer small {
            color: #888;
            font-size: 12px;
        }
        
        /* Loading Spinner */
        .spinner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            z-index: 10;
        }
        
        .spinner-circle {
            width: 40px;
            height: 40px;
            border: 4px solid #e3e6f0;
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .auth-wrapper {
                padding: 10px;
            }
            
            .auth-header {
                padding: 30px 20px 25px;
            }
            
            .auth-body {
                padding: 30px 20px;
            }
            
            .auth-logo {
                width: 70px;
                height: 70px;
            }
            
            .auth-logo i {
                font-size: 32px;
            }
        }
        
        /* RTL Specific */
        .form-floating > .form-control {
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }
        
        .form-check {
            padding-right: 1.5em;
            padding-left: 0;
        }
        
        .form-check .form-check-input {
            float: right;
            margin-right: -1.5em;
            margin-left: 0;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white !important;
            }
            
            .auth-background {
                display: none !important;
            }
            
            .auth-card {
                box-shadow: none !important;
            }
        }
    </style>
    
    <?php if (isset($styles)): ?>
        <?= $styles ?>
    <?php endif; ?>
</head>
<body>
    
    <!-- Background -->
    <div class="auth-background">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <!-- Main Content -->
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2><?= config('app_name_short', 'SMS') ?></h2>
                <p><?= config('app_name', 'سیستم مدیریت مدرسه') ?></p>
            </div>
            
            <!-- Content -->
            <?= $content ?? '' ?>
            
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        // CSRF Token Setup
        const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        const BASE_URL = '<?= config('app_url') ?>';
        
        // Toastr Configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-left",
            "timeOut": "5000",
            "rtl": true,
            "preventDuplicates": true
        };
        
        // Show alerts if present
        <?php if (isset($_SESSION['error'])): ?>
        toastr.error('<?= htmlspecialchars($_SESSION['error']) ?>');
        <?php unset($_SESSION['error']); endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
        toastr.success('<?= htmlspecialchars($_SESSION['success']) ?>');
        <?php unset($_SESSION['success']); endif; ?>
        
        <?php if (isset($_SESSION['warning'])): ?>
        toastr.warning('<?= htmlspecialchars($_SESSION['warning']) ?>');
        <?php unset($_SESSION['warning']); endif; ?>
    </script>
    
    <?php if (isset($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>