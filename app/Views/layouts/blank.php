<?php
/**
 * Blank Layout
 * لایه خالی - برای صفحاتی که نیاز به سایدبار و نوبار ندارند
 * مانند: صفحات خطا، صفحات مستقل، popup ها
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <title><?= $title ?? 'صفحه' ?> | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    
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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'IRANSans', 'Tahoma', 'Segoe UI', sans-serif;
            background: #f8f9fc;
            color: #333;
            line-height: 1.6;
        }
        
        /* Minimal Header */
        .blank-header {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .blank-header .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 18px;
        }
        
        .blank-header .brand i {
            font-size: 24px;
        }
        
        .blank-header .actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .blank-header .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e3e6f0;
            background: white;
            color: #666;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }
        
        .blank-header .btn-icon:hover {
            background: #f8f9fc;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Content */
        .blank-content {
            padding: 30px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Page Title */
        .blank-page-title {
            margin-bottom: 25px;
        }
        
        .blank-page-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .blank-page-title .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "\f053";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 10px;
            color: #aaa;
            padding: 0 5px;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: #2e59d9;
            border-color: #2e59d9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        }
        
        /* Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            padding: 10px 15px;
            transition: all 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background: #f8f9fc;
            font-weight: 600;
            color: #555;
            white-space: nowrap;
            padding: 12px 15px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background: #f8f9fc;
        }
        
        /* Loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e3e6f0;
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Footer */
        .blank-footer {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 13px;
            border-top: 1px solid #e3e6f0;
            margin-top: 40px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .blank-content {
                padding: 20px 15px;
            }
            
            .blank-page-title h1 {
                font-size: 20px;
            }
        }
        
        /* RTL */
        .me-auto {
            margin-left: auto !important;
            margin-right: 0 !important;
        }
        
        .ms-auto {
            margin-right: auto !important;
            margin-left: 0 !important;
        }
        
        /* Print */
        @media print {
            .blank-header, .blank-footer, .no-print {
                display: none !important;
            }
            
            .blank-content {
                padding: 0;
            }
        }
    </style>
    
    <?php if (isset($styles)): ?>
        <?= $styles ?>
    <?php endif; ?>
</head>
<body>
    
    <!-- Optional Header -->
    <?php if (!isset($hideHeader) || !$hideHeader): ?>
    <header class="blank-header">
        <a href="<?= url('/dashboard') ?>" class="brand">
            <i class="fas fa-graduation-cap"></i>
            <span><?= config('app_name_short', 'SMS') ?></span>
        </a>
        
        <div class="actions">
            <?php if (isset($currentUser)): ?>
            <span class="text-muted small d-none d-md-inline">
                <i class="fas fa-user me-1"></i>
                <?= htmlspecialchars($currentUser['first_name'] ?? '') ?> <?= htmlspecialchars($currentUser['last_name'] ?? '') ?>
            </span>
            <?php endif; ?>
            
            <a href="<?= url('/dashboard') ?>" class="btn-icon" title="داشبورد" data-bs-toggle="tooltip">
                <i class="fas fa-th-large"></i>
            </a>
            
            <?php if (isset($currentUser)): ?>
            <a href="<?= url('/logout') ?>" class="btn-icon" title="خروج" data-bs-toggle="tooltip">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <?php endif; ?>
        </div>
    </header>
    <?php endif; ?>
    
    <!-- Alerts Container -->
    <?php if (!isset($hideAlerts) || !$hideAlerts): ?>
    <div class="container-fluid px-3 pt-3">
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); endif; ?>
        
        <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['warning']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['warning']); endif; ?>
        
        <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['info']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['info']); endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="blank-content">
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <?php if (!isset($hideFooter) || !$hideFooter): ?>
    <footer class="blank-footer">
        <div class="container">
            <span>© <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?> | نسخه <?= config('app_version', '1.0.0') ?></span>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        const BASE_URL = '<?= config('app_url') ?>';
        const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        
        // Toastr Config
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-left",
            timeOut: "5000",
            rtl: true
        };
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert-dismissible').forEach(alert => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
    
    <?php if (isset($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>