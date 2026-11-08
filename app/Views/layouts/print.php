<?php
/**
 * Print Layout
 * لایه مخصوص صفحات قابل چاپ
 * مانند: کارنامه، فیش حقوقی، رسید پرداخت، گزارش‌ها
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'چاپ' ?> | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --border-color: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'IRANSans', 'Tahoma', 'Segoe UI', sans-serif;
            background: white;
            color: #000;
            line-height: 1.8;
            font-size: 13px;
        }
        
        /* Print Container */
        .print-container {
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            margin: 0 auto;
            padding: 15mm;
            background: white;
        }
        
        /* Print Header */
        .print-header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .print-header .logo {
            max-width: 80px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .print-header h1 {
            font-size: 22px;
            font-weight: 900;
            margin-bottom: 5px;
            color: #000;
        }
        
        .print-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .print-header .subtitle {
            font-size: 13px;
            color: #666;
        }
        
        /* Print Info Box */
        .print-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .print-info .info-item {
            margin-bottom: 5px;
        }
        
        .print-info .info-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            min-width: 100px;
        }
        
        .print-info .info-value {
            color: #000;
        }
        
        /* Print Title */
        .print-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin: 25px 0 20px;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .table th {
            background: #e9ecef;
            font-weight: 700;
            padding: 8px 10px;
            border: 1px solid #000;
            text-align: center;
            white-space: nowrap;
        }
        
        .table td {
            padding: 6px 10px;
            border: 1px solid #000;
            text-align: center;
        }
        
        .table-striped tbody tr:nth-child(odd) {
            background: #f8f9fa;
        }
        
        .table-bordered {
            border: 2px solid #000;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000;
        }
        
        /* Total Row */
        .table .total-row {
            background: #e9ecef !important;
            font-weight: 700;
        }
        
        .table .total-row td {
            border-top: 2px solid #000;
        }
        
        /* Print Footer */
        .print-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        
        .print-footer .signatures {
            display: flex;
            justify-content: space-around;
            width: 100%;
            margin-top: 40px;
        }
        
        .print-footer .signature-box {
            text-align: center;
            min-width: 150px;
        }
        
        .print-footer .signature-line {
            border-bottom: 1px solid #000;
            width: 150px;
            margin: 40px auto 5px;
        }
        
        .print-footer .signature-label {
            font-size: 11px;
            color: #666;
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(0, 0, 0, 0.03);
            pointer-events: none;
            z-index: -1;
            white-space: nowrap;
            font-weight: 900;
        }
        
        /* Barcode/QR Code */
        .barcode-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .barcode-container img {
            max-width: 200px;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border: 1px solid #000;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-paid { background: #d4edda; }
        .status-pending { background: #fff3cd; }
        .status-overdue { background: #f8d7da; }
        
        /* Grades */
        .grade-pass { color: #155724; font-weight: 700; }
        .grade-fail { color: #721c24; font-weight: 700; }
        
        /* Print Button (hidden in print) */
        .no-print {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .btn-print {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .btn-print:hover {
            background: #2e59d9;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }
        
        .btn-print i {
            margin-left: 8px;
        }
        
        /* Back Button */
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        /* Print Media Queries */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            
            body {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .print-container {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            
            .no-print {
                display: none !important;
            }
            
            .table th {
                background: #e9ecef !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .table-striped tbody tr:nth-child(odd) {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .watermark {
                display: block !important;
            }
            
            a {
                text-decoration: none !important;
                color: #000 !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .no-page-break {
                page-break-inside: avoid;
            }
        }
        
        /* Screen only */
        @media screen {
            body {
                background: #e9ecef;
                padding: 20px;
            }
            
            .print-container {
                background: white;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                margin: 20px auto;
                border-radius: 5px;
            }
            
            .watermark {
                display: none;
            }
        }
        
        /* RTL specific */
        .text-end {
            text-align: left !important;
        }
        
        .text-start {
            text-align: right !important;
        }
    </style>
    
    <?php if (isset($styles)): ?>
        <?= $styles ?>
    <?php endif; ?>
</head>
<body>
    
    <!-- Print Controls (hidden when printing) -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> چاپ
        </button>
        <a href="javascript:history.back()" class="btn-back">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>
    
    <!-- Watermark -->
    <?php if (isset($watermark) && $watermark): ?>
    <div class="watermark"><?= $watermark ?></div>
    <?php endif; ?>
    
    <!-- Print Content -->
    <div class="print-container">
        <?= $content ?? '' ?>
    </div>
    
    <!-- Auto Print Script (optional) -->
    <?php if (isset($autoPrint) && $autoPrint): ?>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
    <?php endif; ?>
    
    <script>
        // Keyboard shortcut for print
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                // Default browser print dialog will open
            }
        });
    </script>
    
    <?php if (isset($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>