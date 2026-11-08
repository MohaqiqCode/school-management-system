<?php
$title = 'ویرایش شعبه';
$branch = $branch ?? [];
$old = $_SESSION['old'] ?? $branch;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$content = function() use ($branch, $old, $errors) {
    ob_start();
?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit me-2"></i>ویرایش شعبه
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 mt-2">
                <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/branches') ?>">شعب</a></li>
                <li class="breadcrumb-item active">ویرایش <?= htmlspecialchars($branch['name'] ?? '') ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= url('/branches') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>بازگشت به لیست
        </a>
        <a href="<?= url('/branches/' . ($branch['id'] ?? '')) ?>" class="btn btn-info ms-1">
            <i class="fas fa-eye me-1"></i> مشاهده
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Form -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>اطلاعات شعبه
                </h6>
                <span class="badge bg-secondary"><?= htmlspecialchars($branch['branch_code'] ?? '') ?></span>
            </div>
            <div class="card-body">
                <form action="<?= url('/branches/' . ($branch['id'] ?? '')) ?>" method="POST" enctype="multipart/form-data" id="branchForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" id="branchTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">
                                <i class="fas fa-info-circle me-1"></i> اطلاعات پایه
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">
                                <i class="fas fa-address-book me-1"></i> تماس و آدرس
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button">
                                <i class="fas fa-image me-1"></i> لوگو و رسانه
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="branchTabContent">
                        
                        <!-- Basic Info Tab -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-bold">
                                        نام شعبه <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                               id="name" name="name" 
                                               value="<?= htmlspecialchars($old['name'] ?? $branch['name'] ?? '') ?>" required>
                                        <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="principal_name" class="form-label fw-bold">نام مدیر</label>
                                    <input type="text" class="form-control" id="principal_name" name="principal_name" 
                                           value="<?= htmlspecialchars($old['principal_name'] ?? $branch['principal_name'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="established_date" class="form-label fw-bold">تاریخ تأسیس</label>
                                    <input type="text" class="form-control persian-datepicker" id="established_date" name="established_date" 
                                           value="<?= htmlspecialchars($old['established_date'] ?? $branch['established_date'] ?? '') ?>" placeholder="انتخاب تاریخ">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="total_capacity" class="form-label fw-bold">ظرفیت کل</label>
                                    <input type="number" class="form-control" id="total_capacity" name="total_capacity" 
                                           value="<?= htmlspecialchars($old['total_capacity'] ?? $branch['total_capacity'] ?? 500) ?>" min="1">
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               <?= ($old['is_active'] ?? $branch['is_active'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="is_active">شعبه فعال باشد</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact & Address Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-bold">ایمیل</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                               id="email" name="email" 
                                               value="<?= htmlspecialchars($old['email'] ?? $branch['email'] ?? '') ?>">
                                        <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-bold">شماره تماس</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?= htmlspecialchars($old['phone'] ?? $branch['phone'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label fw-bold">شهر</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= htmlspecialchars($old['city'] ?? $branch['city'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label fw-bold">استان</label>
                                    <input type="text" class="form-control" id="state" name="state" 
                                           value="<?= htmlspecialchars($old['state'] ?? $branch['state'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="postal_code" class="form-label fw-bold">کد پستی</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?= htmlspecialchars($old['postal_code'] ?? $branch['postal_code'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label fw-bold">آدرس کامل</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($old['address'] ?? $branch['address'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Media Tab -->
                        <div class="tab-pane fade" id="media" role="tabpanel">
                            <div class="mb-4">
                                <label class="form-label fw-bold">لوگوی فعلی</label>
                                <div class="text-center p-3 bg-light rounded">
                                    <?php if (!empty($branch['logo_path'])): ?>
                                    <img src="<?= asset($branch['logo_path']) ?>" alt="Logo" class="img-thumbnail" style="max-height: 150px;">
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="delete_logo" id="delete_logo" value="1">
                                            <label class="form-check-label text-danger" for="delete_logo">حذف لوگو</label>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-muted py-4">
                                        <i class="fas fa-image fa-3x mb-2 d-block"></i>
                                        لوگویی آپلود نشده است
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="logo" class="form-label fw-bold">آپلود لوگوی جدید</label>
                                <div class="upload-area border rounded-3 p-4 text-center bg-light" id="dropZone">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="mb-1">فایل را اینجا رها کنید یا کلیک کنید</p>
                                    <small class="text-muted">JPG، PNG یا GIF - حداکثر 2MB</small>
                                    <input type="file" class="d-none" id="logo" name="logo" accept="image/*" onchange="previewNewLogo(this)">
                                </div>
                                <div id="newLogoPreview" class="mt-2 text-center d-none">
                                    <img src="#" alt="پیش‌نمایش" class="img-thumbnail" style="max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="removeNewLogo()">حذف</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $branch['id'] ?? 0 ?>, '<?= htmlspecialchars($branch['name'] ?? '') ?>')">
                                <i class="fas fa-trash me-1"></i> حذف شعبه
                            </button>
                        </div>
                        <div>
                            <a href="<?= url('/branches') ?>" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> انصراف
                            </a>
                            <button type="submit" class="btn btn-primary px-4 ms-2">
                                <i class="fas fa-save me-1"></i> ذخیره تغییرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-xl-4 col-lg-5">
        <!-- Stats Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i>آمار شعبه</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>دانش‌آموزان</span>
                        <span class="badge bg-primary rounded-pill"><?= number_format($branch['student_count'] ?? 0) ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>معلمان</span>
                        <span class="badge bg-success rounded-pill"><?= number_format($branch['teacher_count'] ?? 0) ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>کارمندان</span>
                        <span class="badge bg-info rounded-pill"><?= number_format($branch['employee_count'] ?? 0) ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>کلاس‌ها</span>
                        <span class="badge bg-warning rounded-pill"><?= number_format($branch['class_count'] ?? 0) ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>نرخ تکمیل ظرفیت</span>
                        <span class="badge bg-<?= ($branch['occupancy_rate'] ?? 0) > 80 ? 'danger' : 'success' ?> rounded-pill"><?= $branch['occupancy_rate'] ?? 0 ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Update Info -->
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="fas fa-info-circle me-2"></i>اطلاعات ثبت</h6></div>
            <div class="card-body">
                <div class="mb-2"><small class="text-muted">تاریخ ایجاد:</small><br><strong><?= date('Y/m/d H:i', strtotime($branch['created_at'] ?? 'now')) ?></strong></div>
                <div><small class="text-muted">آخرین بروزرسانی:</small><br><strong><?= date('Y/m/d H:i', strtotime($branch['updated_at'] ?? 'now')) ?></strong></div>
            </div>
        </div>

        <!-- Help -->
        <div class="card shadow bg-light">
            <div class="card-body">
                <h6><i class="fas fa-lightbulb text-warning me-2"></i>راهنما</h6>
                <ul class="small text-muted mb-0">
                    <li>برای تغییر لوگو، تب "لوگو و رسانه" را انتخاب کنید.</li>
                    <li>غیرفعال کردن شعبه باعث مخفی شدن آن از لیست‌ها می‌شود.</li>
                    <li>تغییرات پس از ذخیره بلافاصله اعمال می‌شوند.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تأیید حذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                <h5>آیا از حذف این شعبه اطمینان دارید؟</h5>
                <p class="text-muted" id="deleteMessage"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteMessage').textContent = 'شعبه "' + name + '" حذف خواهد شد. این عملیات قابل بازگشت نیست!';
        document.getElementById('deleteForm').action = '<?= url('/branches') ?>/' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function previewNewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('newLogoPreview').classList.remove('d-none');
                document.getElementById('newLogoPreview').querySelector('img').src = e.target.result;
                document.getElementById('dropZone').classList.add('border-success');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeNewLogo() {
        document.getElementById('logo').value = '';
        document.getElementById('newLogoPreview').classList.add('d-none');
        document.getElementById('dropZone').classList.remove('border-success');
    }

    // Drag and drop
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('click', () => document.getElementById('logo').click());
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('bg-primary', 'bg-opacity-10'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('bg-primary', 'bg-opacity-10'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('bg-primary', 'bg-opacity-10');
        document.getElementById('logo').files = e.dataTransfer.files;
        previewNewLogo({ files: e.dataTransfer.files });
    });

    // Initialize datepicker
    $(document).ready(function() {
        $('.persian-datepicker').persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false
        });
    });
</script>

<?php return ob_get_clean(); };
require __DIR__ . '/../layouts/main.php';
$content = $content();
?>