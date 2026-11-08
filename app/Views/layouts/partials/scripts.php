<?php
/**
 * Scripts Partial
 * اسکریپت‌های مشترک و عمومی سیستم
 */
?>

<!-- ============================================ -->
<!-- JavaScript Libraries -->
<!-- ============================================ -->

<!-- jQuery (must be first) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- jQuery Migrate -->
<script src="https://code.jquery.com/jquery-migrate-3.4.1.min.js"></script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<!-- DataTables Persian Language -->
<script>
    const persianLanguage = {
        "sEmptyTable": "هیچ داده‌ای در جدول وجود ندارد",
        "sInfo": "نمایش _START_ تا _END_ از _TOTAL_ رکورد",
        "sInfoEmpty": "نمایش 0 تا 0 از 0 رکورد",
        "sInfoFiltered": "(فیلتر شده از _MAX_ رکورد)",
        "sInfoPostFix": "",
        "sInfoThousands": ",",
        "sLengthMenu": "نمایش _MENU_ رکورد",
        "sLoadingRecords": "در حال بارگذاری...",
        "sProcessing": "در حال پردازش...",
        "sSearch": "جستجو:",
        "sZeroRecords": "رکوردی با این مشخصات یافت نشد",
        "oPaginate": {
            "sFirst": "ابتدا",
            "sLast": "انتها",
            "sNext": "بعدی",
            "sPrevious": "قبلی"
        },
        "oAria": {
            "sSortAscending": ": فعال سازی نمایش به صورت صعودی",
            "sSortDescending": ": فعال سازی نمایش به صورت نزولی"
        }
    };
</script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fa.js"></script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap5@6.1.8/index.global.min.js"></script>

<!-- Persian Date -->
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Dropzone JS -->
<script src="https://cdn.jsdelivr.net/npm/dropzone@6.0.0-beta.2/dist/dropzone-min.js"></script>

<!-- Quill Editor JS -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.js"></script>

<!-- Lightbox JS -->
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/js/lightbox.min.js"></script>

<!-- AOS Animation JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fa.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<!-- Print.js -->
<script src="https://cdn.jsdelivr.net/npm/print-js@1.6.0/dist/print.js"></script>

<!-- XLSX for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- JSZip for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>

<!-- PDFMake for PDF Export -->
<script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/pdfmake.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/vfs_fonts.js"></script>

<!-- ============================================ -->
<!-- Custom JavaScript -->
<!-- ============================================ -->
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/dashboard.js') ?>"></script>
<script src="<?= asset('js/form-validation.js') ?>"></script>
<script src="<?= asset('js/table-utils.js') ?>"></script>

<!-- ============================================ -->
<!-- Global Configuration -->
<!-- ============================================ -->
<script>
    // Prevent console.log in production
    <?php if (config('app_environment') === 'production'): ?>
    if (typeof console !== 'undefined') {
        console.log = function() {};
    }
    <?php endif; ?>
    
    // Global Constants
    const APP = {
        NAME: '<?= config('app_name', 'سیستم مدیریت مدرسه') ?>',
        VERSION: '<?= config('app_version', '1.0.0') ?>',
        BASE_URL: '<?= rtrim(config('app_url'), '/') ?>',
        API_URL: '<?= rtrim(config('app_url'), '/') ?>/api',
        ASSETS_URL: '<?= rtrim(config('app_url'), '/') ?>/assets',
        CSRF_TOKEN: '<?= $_SESSION['csrf_token'] ?? '' ?>',
        CURRENT_USER: <?= json_encode($currentUser ?? null, JSON_UNESCAPED_UNICODE) ?>,
        LOCALE: '<?= config('app_locale', 'fa') ?>',
        DIRECTION: 'rtl',
        ENVIRONMENT: '<?= config('app_environment', 'production') ?>',
        DEBUG: <?= config('app_debug', false) ? 'true' : 'false' ?>,
    };
    
    // AJAX Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': APP.CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest'
        },
        beforeSend: function() {
            // Show loading indicator if needed
        },
        complete: function() {
            // Hide loading indicator
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطای احراز هویت',
                    text: 'لطفاً مجدداً وارد سیستم شوید',
                    confirmButtonText: 'ورود',
                    confirmButtonColor: '#4e73df'
                }).then(() => {
                    window.location.href = APP.BASE_URL + '/login';
                });
            } else if (xhr.status === 403) {
                toastr.error('شما دسترسی به این بخش را ندارید');
            } else if (xhr.status === 419) {
                toastr.error('توکن امنیتی منقضی شده است. لطفاً صفحه را رفرش کنید');
            } else if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.keys(errors).forEach(field => {
                        $(`[name="${field}"]`).addClass('is-invalid');
                        $(`[name="${field}"]`).siblings('.invalid-feedback').text(errors[field][0]);
                    });
                }
            } else if (xhr.status === 500) {
                toastr.error('خطای سرور رخ داده است. لطفاً بعداً تلاش کنید');
            }
        }
    });
    
    // Toastr Configuration
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-left",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut",
        "rtl": true,
        "preventDuplicates": true,
        "newestOnTop": false,
        "onclick": null
    };
    
    // SweetAlert2 Default Config
    const SwalDefault = Swal.mixin({
        confirmButtonColor: '#4e73df',
        cancelButtonColor: '#e74a3b',
        confirmButtonText: 'تأیید',
        cancelButtonText: 'انصراف',
        showCancelButton: true,
        focusConfirm: false,
        reverseButtons: true
    });
    
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });
    
    // Initialize Persian Datepickers
    $(document).ready(function() {
        $('.persian-datepicker').persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
            observer: true,
            calendar: {
                persian: {
                    locale: 'fa'
                }
            }
        });
        
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            language: 'fa',
            dir: 'rtl',
            width: '100%'
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                boundary: 'window'
            });
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
        
        // Hide page loader
        setTimeout(function() {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.add('hidden');
                setTimeout(() => loader.remove(), 300);
            }
        }, 500);
    });
    
    // ============================================
    // Utility Functions
    // ============================================
    
    /**
     * Format number with commas (Persian)
     */
    function formatNumber(number, decimals = 0) {
        return new Intl.NumberFormat('fa-IR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }
    
    /**
     * Format currency (Toman)
     */
    function formatCurrency(amount) {
        return formatNumber(amount) + ' تومان';
    }
    
    /**
     * Format date (Jalali)
     */
    function formatDate(date, format = 'YYYY/MM/DD') {
        if (!date) return '';
        const pd = new persianDate(date);
        return pd.format(format);
    }
    
    /**
     * Format date time (Jalali)
     */
    function formatDateTime(date) {
        if (!date) return '';
        const pd = new persianDate(date);
        return pd.format('YYYY/MM/DD HH:mm');
    }
    
    /**
     * Time ago in Persian
     */
    function timeAgo(date) {
        const now = new Date();
        const then = new Date(date);
        const diff = Math.floor((now - then) / 1000);
        
        if (diff < 60) return 'همین الان';
        if (diff < 3600) return Math.floor(diff / 60) + ' دقیقه پیش';
        if (diff < 86400) return Math.floor(diff / 3600) + ' ساعت پیش';
        if (diff < 2592000) return Math.floor(diff / 86400) + ' روز پیش';
        if (diff < 31104000) return Math.floor(diff / 2592000) + ' ماه پیش';
        return Math.floor(diff / 31104000) + ' سال پیش';
    }
    
    /**
     * Get URL parameter
     */
    function getUrlParam(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    
    /**
     * Update URL parameter without reload
     */
    function updateUrlParam(key, value) {
        const url = new URL(window.location);
        url.searchParams.set(key, value);
        window.history.pushState({}, '', url);
    }
    
    /**
     * Copy text to clipboard
     */
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                toastr.success('کپی شد!');
            });
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            toastr.success('کپی شد!');
        }
    }
    
    /**
     * Print element
     */
    function printElement(selector) {
        const element = document.querySelector(selector);
        if (element) {
            printJS({
                printable: selector.replace('#', ''),
                type: 'html',
                style: `
                    @import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css');
                    body { font-family: Tahoma; direction: rtl; }
                    @page { margin: 10mm; }
                `
            });
        }
    }
    
    /**
     * Export table to Excel
     */
    function exportTableToExcel(tableId, filename = 'export') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
        XLSX.writeFile(wb, `${filename}_${formatDate(new Date(), 'YYYY-MM-DD')}.xlsx`);
    }
    
    /**
     * Generate random color
     */
    function randomColor() {
        const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    /**
     * Toggle dark mode
     */
    function toggleDarkMode() {
        const darkCSS = document.getElementById('darkModeCSS');
        const isDark = darkCSS.disabled;
        darkCSS.disabled = !isDark;
        localStorage.setItem('darkMode', !isDark);
        updateDarkModeUI(!isDark);
    }
    
    function updateDarkModeUI(isDark) {
        const btn = document.getElementById('darkModeToggle');
        if (btn) {
            btn.innerHTML = isDark 
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
        }
    }
    
    // Check saved dark mode
    (function() {
        const savedDarkMode = localStorage.getItem('darkMode') === 'true';
        if (savedDarkMode) {
            document.getElementById('darkModeCSS').disabled = false;
        }
    })();
    
    // ============================================
    // Keyboard Shortcuts
    // ============================================
    document.addEventListener('keydown', function(e) {
        // Ctrl+S - Save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const form = document.querySelector('form');
            if (form) form.submit();
        }
        
        // Escape - Close modals, go back
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            if (openModals.length > 0) {
                // Bootstrap handles this
            } else {
                // Could go back
            }
        }
        
        // Ctrl+D - Go to Dashboard
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            window.location.href = APP.BASE_URL + '/dashboard';
        }
    });
    
    // ============================================
    // Service Worker Registration (PWA)
    // ============================================
    if ('serviceWorker' in navigator && APP.ENVIRONMENT === 'production') {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register(APP.BASE_URL + '/sw.js').then(function(registration) {
                console.log('ServiceWorker registered');
            }).catch(function(err) {
                console.log('ServiceWorker registration failed: ', err);
            });
        });
    }
    
    // ============================================
    // Error Tracking
    // ============================================
    window.addEventListener('error', function(e) {
        if (APP.DEBUG) {
            console.error('Global error:', e.message, e.filename, e.lineno);
        }
        
        // Optionally send to server
        if (APP.ENVIRONMENT === 'production') {
            fetch(APP.API_URL + '/log-error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': APP.CSRF_TOKEN
                },
                body: JSON.stringify({
                    message: e.message,
                    file: e.filename,
                    line: e.lineno,
                    url: window.location.href,
                    userAgent: navigator.userAgent
                })
            }).catch(() => {});
        }
    });
    
    // Log unhandled promise rejections
    window.addEventListener('unhandledrejection', function(e) {
        if (APP.DEBUG) {
            console.error('Unhandled rejection:', e.reason);
        }
    });
</script>

<!-- ============================================ -->
<!-- Page Specific Scripts -->
<!-- ============================================ -->
<?php if (isset($scripts)): ?>
    <?= $scripts ?>
<?php endif; ?>

<!-- Close body and html tags (opened in header.php) -->
</body>
</html>