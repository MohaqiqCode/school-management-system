<?php
/**
 * Modals Partial
 * مودال‌های مشترک و پرکاربرد سیستم
 */
?>

<!-- ============================================ -->
<!-- 1. Confirm Delete Modal -->
<!-- ============================================ -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                </div>
                <h5 class="mb-3">آیا از حذف این آیتم اطمینان دارید؟</h5>
                <p class="text-muted mb-0" id="confirmDeleteMessage">این عملیات قابل بازگشت نیست!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> انصراف
                </button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i> حذف
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 2. Confirm Action Modal -->
<!-- ============================================ -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmActionLabel">
                    <i class="fas fa-question-circle me-2"></i>تأیید عملیات
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body text-center py-4" id="confirmActionBody">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> انصراف
                </button>
                <button type="button" class="btn btn-primary px-4" id="confirmActionBtn">
                    <i class="fas fa-check me-1"></i> تأیید
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 3. Quick View Modal (Large) -->
<!-- ============================================ -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="quickViewLabel">
                    <i class="fas fa-eye me-2"></i>جزئیات
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body" id="quickViewBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                    </div>
                    <p class="mt-2 text-muted">در حال بارگذاری اطلاعات...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> بستن
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 4. File Upload Modal -->
<!-- ============================================ -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-upload me-2"></i>آپلود فایل
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label for="documentType" class="form-label">نوع مدرک</label>
                        <select class="form-select" id="documentType" name="document_type">
                            <option value="">انتخاب کنید...</option>
                            <option value="birth_certificate">شناسنامه</option>
                            <option value="national_card">کارت ملی</option>
                            <option value="transfer_certificate">گواهی انتقال</option>
                            <option value="photo">عکس</option>
                            <option value="medical_record">پرونده پزشکی</option>
                            <option value="other">سایر</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="uploadFile" class="form-label">انتخاب فایل</label>
                        <div class="upload-area border rounded-3 p-4 text-center" id="dropZone">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                            <p class="mb-1">فایل را اینجا رها کنید یا کلیک کنید</p>
                            <small class="text-muted">فرمت‌های مجاز: JPG, PNG, PDF (حداکثر 5MB)</small>
                            <input type="file" class="d-none" id="uploadFile" name="file" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div id="filePreview" class="mt-2 d-none">
                            <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expiryDate" class="form-label">تاریخ انقضا (اختیاری)</label>
                        <input type="text" class="form-control persian-datepicker" id="expiryDate" name="expiry_date" placeholder="انتخاب تاریخ">
                    </div>
                    
                    <div class="mb-3">
                        <label for="uploadNotes" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="uploadNotes" name="notes" rows="2" placeholder="توضیحات اختیاری..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-success" id="uploadSubmitBtn">
                    <i class="fas fa-upload me-1"></i> آپلود
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 5. Send Notification Modal -->
<!-- ============================================ -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="sendNotificationLabel">
                    <i class="fas fa-bell me-2"></i>ارسال اعلان
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <form id="notificationForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نوع گیرنده</label>
                            <select class="form-select" name="recipient_type" id="recipientType">
                                <option value="all">همه</option>
                                <option value="branch">شعبه خاص</option>
                                <option value="class">کلاس خاص</option>
                                <option value="section">بخش خاص</option>
                                <option value="role">نقش خاص</option>
                                <option value="individual">فرد خاص</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">نوع اعلان</label>
                            <select class="form-select" name="notification_type">
                                <option value="info">اطلاع‌رسانی</option>
                                <option value="announcement">اطلاعیه</option>
                                <option value="reminder">یادآوری</option>
                                <option value="alert">هشدار</option>
                                <option value="emergency">فوری</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">عنوان اعلان <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required placeholder="عنوان اعلان را وارد کنید...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">متن اعلان <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="message" rows="4" required placeholder="متن اعلان را وارد کنید..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">کانال ارسال</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="channels[]" value="in_app" checked>
                            <label class="form-check-label">درون برنامه</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="channels[]" value="email">
                            <label class="form-check-label">ایمیل</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="channels[]" value="sms">
                            <label class="form-check-label">پیامک</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-warning" id="sendNotificationBtn">
                    <i class="fas fa-paper-plane me-1"></i> ارسال
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 6. Export Data Modal -->
<!-- ============================================ -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-download me-2"></i>خروجی داده‌ها
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">فرمت خروجی مورد نظر را انتخاب کنید:</p>
                <div class="row g-3">
                    <div class="col-4">
                        <div class="card border-primary text-center p-3 export-option" data-format="excel" style="cursor: pointer;">
                            <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                            <h6 class="mb-0">Excel</h6>
                            <small class="text-muted">.xlsx</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-primary text-center p-3 export-option" data-format="pdf" style="cursor: pointer;">
                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                            <h6 class="mb-0">PDF</h6>
                            <small class="text-muted">.pdf</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-primary text-center p-3 export-option" data-format="csv" style="cursor: pointer;">
                            <i class="fas fa-file-csv fa-3x text-info mb-2"></i>
                            <h6 class="mb-0">CSV</h6>
                            <small class="text-muted">.csv</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-none" id="exportProgress">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                    <small class="text-muted mt-1">در حال آماده‌سازی فایل...</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 7. Change Status Modal -->
<!-- ============================================ -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="changeStatusLabel">
                    <i class="fas fa-exchange-alt me-2"></i>تغییر وضعیت
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body text-center">
                <p id="changeStatusMessage" class="mb-3">وضعیت جدید را انتخاب کنید:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success status-option" data-status="active">
                        <i class="fas fa-check-circle me-1"></i> فعال
                    </button>
                    <button type="button" class="btn btn-warning status-option" data-status="inactive">
                        <i class="fas fa-pause-circle me-1"></i> غیرفعال
                    </button>
                    <button type="button" class="btn btn-danger status-option" data-status="banned">
                        <i class="fas fa-ban me-1"></i> مسدود
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 8. Filter Modal -->
<!-- ============================================ -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="fas fa-filter me-2"></i>فیلترهای پیشرفته
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body" id="filterModalBody">
                <!-- Dynamic filter content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn">
                    <i class="fas fa-undo me-1"></i> پاک کردن
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                    <i class="fas fa-check me-1"></i> اعمال فیلتر
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 9. Image Preview Modal -->
<!-- ============================================ -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0">
                <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 rounded-circle" 
                        data-bs-dismiss="modal" style="z-index: 1; width: 40px; height: 40px;">
                    <i class="fas fa-times"></i>
                </button>
                <img src="" id="previewImage" class="img-fluid rounded" style="max-height: 90vh;" alt="پیش‌نمایش">
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- 10. Loading Modal -->
<!-- ============================================ -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body text-center">
                <div class="spinner-border text-light" style="width: 4rem; height: 4rem;" role="status">
                    <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
                <p class="text-white mt-3 mb-0" id="loadingMessage">لطفاً صبر کنید...</p>
            </div>
        </div>
    </div>
</div>

<script>
    // ============================================
    // Modal Helper Functions
    // ============================================
    
    /**
     * Show confirm delete modal
     */
    function showConfirmDelete(message, callback) {
        const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        document.getElementById('confirmDeleteMessage').textContent = message || 'این عملیات قابل بازگشت نیست!';
        
        const btn = document.getElementById('confirmDeleteBtn');
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', function() {
            if (typeof callback === 'function') {
                callback();
            }
            modal.hide();
        });
        
        modal.show();
    }
    
    /**
     * Show confirm action modal
     */
    function showConfirmAction(title, message, confirmText, callback, color = 'primary') {
        document.getElementById('confirmActionLabel').innerHTML = `<i class="fas fa-question-circle me-2"></i>${title}`;
        document.getElementById('confirmActionBody').innerHTML = message;
        
        const btn = document.getElementById('confirmActionBtn');
        btn.className = `btn btn-${color} px-4`;
        btn.innerHTML = `<i class="fas fa-check me-1"></i> ${confirmText || 'تأیید'}`;
        
        const modal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
        
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', function() {
            if (typeof callback === 'function') {
                callback();
            }
            modal.hide();
        });
        
        modal.show();
    }
    
    /**
     * Show quick view modal
     */
    function showQuickView(title, url) {
        document.getElementById('quickViewLabel').innerHTML = `<i class="fas fa-eye me-2"></i>${title}`;
        
        const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();
        
        document.getElementById('quickViewBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
                <p class="mt-2 text-muted">در حال بارگذاری اطلاعات...</p>
            </div>
        `;
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('quickViewBody').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('quickViewBody').innerHTML = `
                    <div class="alert alert-danger">خطا در بارگذاری اطلاعات</div>
                `;
            });
    }
    
    /**
     * Show image preview
     */
    function showImagePreview(src) {
        document.getElementById('previewImage').src = src;
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    }
    
    /**
     * Show loading modal
     */
    function showLoading(message = 'لطفاً صبر کنید...') {
        document.getElementById('loadingMessage').textContent = message;
        const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
        modal.show();
        return modal;
    }
    
    /**
     * Hide loading modal
     */
    function hideLoading(modal) {
        if (modal) {
            modal.hide();
        } else {
            const m = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
            if (m) m.hide();
        }
    }
    
    /**
     * Show status change modal
     */
    function showChangeStatus(currentStatus, callback) {
        const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
        
        document.querySelectorAll('.status-option').forEach(btn => {
            btn.classList.remove('active', 'disabled');
            if (btn.dataset.status === currentStatus) {
                btn.classList.add('disabled');
                btn.innerHTML += ' <small>(فعلی)</small>';
            }
            
            btn.onclick = function() {
                if (typeof callback === 'function') {
                    callback(this.dataset.status);
                }
                modal.hide();
            };
        });
        
        modal.show();
    }
    
    /**
     * Export data
     */
    function showExportModal(exportUrl) {
        const modal = new bootstrap.Modal(document.getElementById('exportModal'));
        modal.show();
        
        document.querySelectorAll('.export-option').forEach(card => {
            card.onclick = function() {
                const format = this.dataset.format;
                document.getElementById('exportProgress').classList.remove('d-none');
                
                window.location.href = `${exportUrl}?format=${format}`;
                
                setTimeout(() => {
                    modal.hide();
                    document.getElementById('exportProgress').classList.add('d-none');
                }, 2000);
            };
        });
    }
    
    // Initialize modals
    document.addEventListener('DOMContentLoaded', function() {
        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                if (openModals.length > 0) {
                    // Bootstrap handles this by default
                }
            }
        });
    });
</script>

<style>
    .upload-area {
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .upload-area:hover,
    .upload-area.dragover {
        border-color: #4e73df;
        background: #f0f4ff;
    }
    
    .export-option {
        transition: all 0.3s ease;
    }
    
    .export-option:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #4e73df !important;
    }
    
    .status-option {
        transition: all 0.2s ease;
    }
    
    .status-option:hover {
        transform: scale(1.02);
    }
    
    .modal-backdrop {
        backdrop-filter: blur(2px);
    }
</style>