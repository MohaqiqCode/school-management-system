<?php ob_start(); ?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-building me-2"></i>ایجاد شعبه جدید
    </h1>
    <a href="<?= url('/branches') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>بازگشت به لیست
    </a>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <!-- Main Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>اطلاعات شعبه
                </h6>
            </div>
            <div class="card-body">
                <form action="<?= url('/branches') ?>" method="POST" enctype="multipart/form-data" id="branchForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="row">
                        <!-- Branch Code -->
                        <div class="col-md-6 mb-3">
                            <label for="branch_code" class="form-label">
                                کد شعبه <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" 
                                       class="form-control <?= has_error('branch_code') ? 'is-invalid' : '' ?>" 
                                       id="branch_code" 
                                       name="branch_code" 
                                       value="<?= old('branch_code') ?>"
                                       placeholder="مثال: BRANCH-01"
                                       required>
                                <?php if (has_error('branch_code')): ?>
                                <div class="invalid-feedback"><?= error('branch_code') ?></div>
                                <?php endif; ?>
                            </div>
                            <small class="form-text text-muted">کد یکتا برای شعبه (فقط حروف و اعداد)</small>
                        </div>
                        
                        <!-- Branch Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                نام شعبه <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name') ?>"
                                   placeholder="نام کامل شعبه"
                                   required>
                            <?php if (has_error('name')): ?>
                            <div class="invalid-feedback"><?= error('name') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">ایمیل</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" 
                                       class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" 
                                       id="email" 
                                       name="email" 
                                       value="<?= old('email') ?>"
                                       placeholder="branch@school.com">
                                <?php if (has_error('email')): ?>
                                <div class="invalid-feedback"><?= error('email') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">شماره تماس</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?= old('phone') ?>"
                                       placeholder="021-12345678">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- City -->
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">شهر</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?= old('city') ?>" placeholder="تهران">
                        </div>
                        
                        <!-- State -->
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">استان</label>
                            <input type="text" class="form-control" id="state" name="state" 
                                   value="<?= old('state') ?>" placeholder="تهران">
                        </div>
                        
                        <!-- Postal Code -->
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">کد پستی</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                   value="<?= old('postal_code') ?>" placeholder="1234567890">
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label">آدرس</label>
                        <textarea class="form-control" id="address" name="address" rows="2" 
                                  placeholder="آدرس کامل شعبه"><?= old('address') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <!-- Principal Name -->
                        <div class="col-md-6 mb-3">
                            <label for="principal_name" class="form-label">نام مدیر</label>
                            <input type="text" class="form-control" id="principal_name" name="principal_name" 
                                   value="<?= old('principal_name') ?>" placeholder="نام و نام خانوادگی مدیر">
                        </div>
                        
                        <!-- Established Date -->
                        <div class="col-md-3 mb-3">
                            <label for="established_date" class="form-label">تاریخ تأسیس</label>
                            <input type="text" class="form-control persian-datepicker" 
                                   id="established_date" name="established_date" 
                                   value="<?= old('established_date') ?>" placeholder="انتخاب تاریخ">
                        </div>
                        
                        <!-- Total Capacity -->
                        <div class="col-md-3 mb-3">
                            <label for="total_capacity" class="form-label">ظرفیت کل</label>
                            <input type="number" class="form-control" id="total_capacity" name="total_capacity" 
                                   value="<?= old('total_capacity', 500) ?>" min="1" max="10000">
                        </div>
                    </div>
                    
                    <!-- Logo Upload -->
                    <div class="mb-3">
                        <label for="logo" class="form-label">لوگوی شعبه</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*"
                                   onchange="previewImage(this)">
                        </div>
                        <div class="mt-2">
                            <img id="logoPreview" src="#" alt="پیش‌نمایش" 
                                 style="max-height: 100px; display: none;" class="rounded border">
                        </div>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" <?= old('is_active', '1') == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">شعبه فعال باشد</label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/branches') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> انصراف
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> ذخیره شعبه
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Help -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-info">
                    <i class="fas fa-lightbulb me-2"></i>راهنما
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>کد شعبه:</strong> یک کد یکتا برای شناسایی شعبه وارد کنید.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>نام شعبه:</strong> نام کامل و رسمی شعبه را وارد نمایید.
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <strong>لوگو:</strong> تصویر لوگوی شعبه با فرمت JPG یا PNG.
                    </li>
                    <li>
                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                        پس از ایجاد شعبه، دپارتمان‌های پیش‌فرض به صورت خودکار ایجاد می‌شوند.
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Quick Info -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-success">
                    <i class="fas fa-chart-bar me-2"></i>خلاصه سیستم
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>کل شعب:</span>
                    <strong><?= $totalBranches ?? 0 ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>شعب فعال:</span>
                    <strong class="text-success"><?= $activeBranches ?? 0 ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>شعب غیرفعال:</span>
                    <strong class="text-danger"><?= $inactiveBranches ?? 0 ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('logoPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Initialize Persian Datepicker
    $(document).ready(function() {
        $('.persian-datepicker').persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
        });
    });
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>