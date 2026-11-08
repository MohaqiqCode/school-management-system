<?php
/**
 * Header Partial
 * هدر سایت - شامل متا تگ‌ها، CSS ها و تنظیمات اولیه
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $metaDescription ?? 'سیستم مدیریت مدرسه - پنل مدیریت هوشمند' ?>">
    <meta name="keywords" content="<?= $metaKeywords ?? 'مدیریت مدرسه, دانش آموز, معلم, حضور و غیاب, آزمون, شهریه' ?>">
    <meta name="author" content="<?= config('app_name', 'سیستم مدیریت مدرسه') ?>">
    <meta name="robots" content="<?= $metaRobots ?? 'noindex, nofollow' ?>">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?= $title ?? 'پنل مدیریت' ?> | <?= config('app_name', 'سیستم مدیریت مدرسه') ?>">
    <meta property="og:description" content="<?= $metaDescription ?? 'سیستم مدیریت مدرسه - پنل مدیریت هوشمند' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= config('app_url') ?>">
    <meta property="og:image" content="<?= asset('images/og-image.png') ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'پنل مدیریت' ?>">
    <meta name="twitter:description" content="<?= $metaDescription ?? '' ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= $canonicalUrl ?? config('app_url') . $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('images/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= asset('site.webmanifest') ?>">
    <link rel="mask-icon" href="<?= asset('images/safari-pinned-tab.svg') ?>" color="#4e73df">
    <meta name="msapplication-TileColor" content="#4e73df">
    <meta name="theme-color" content="#4e73df">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= asset('manifest.json') ?>">
    
    <!-- Page Title -->
    <title><?= $title ?? 'پنل مدیریت' ?> | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    <link rel="preload" href="https://code.jquery.com/jquery-3.7.0.min.js" as="script">
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Vazir Font (Persian) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
    
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
    
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.min.css">
    
    <!-- Persian DatePicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    
    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@6.0.0-beta.2/dist/dropzone.css">
    
    <!-- Quill Editor CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.snow.css">
    
    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/css/lightbox.min.css">
    
    <!-- AOS Animation CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/rtl.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>" media="print">
    
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="<?= asset('css/dark-mode.css') ?>" id="darkModeCSS" disabled>
    
    <!-- Inline Critical CSS -->
    <style>
        /* Critical CSS for faster initial render */
        .wrapper { display: flex; width: 100%; min-height: 100vh; }
        .sidebar { width: 260px; position: fixed; top: 0; right: 0; bottom: 0; z-index: 1000; transition: all 0.3s; }
        .main-content { flex: 1; margin-right: 260px; transition: all 0.3s; background: #f8f9fc; min-height: 100vh; }
        
        @media (max-width: 768px) {
            .sidebar { margin-right: -260px; }
            .sidebar.show { margin-right: 0; }
            .main-content { margin-right: 0; }
        }
        
        /* Loading Screen */
        #pageLoader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        #pageLoader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #e3e6f0;
            border-top-color: #4e73df;
            border-radius: 50%;
            animation: loaderSpin 0.8s linear infinite;
        }
        
        @keyframes loaderSpin {
            to { transform: rotate(360deg); }
        }
    </style>
    
    <?php if (isset($styles)): ?>
        <?= $styles ?>
    <?php endif; ?>
</head>
<body>
    
    <!-- Page Loader -->
    <div id="pageLoader">
        <div class="text-center">
            <div class="loader-spinner mb-3"></div>
            <div class="text-muted">در حال بارگذاری...</div>
        </div>
    </div>