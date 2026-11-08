<?php
$title = 'تغییر رمز عبور';
$metaDescription = 'تغییر رمز عبور حساب کاربری';
$currentUser = $currentUser ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

ob_start();
?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-key me-2"></i>تغییر رمز عبور
    </h1>
    <a href="<?= url('/profile') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>بازگشت به پروفایل
    </a>
</div>

<div class="row">
    <div class="col-xl-6 col-lg-8 mx-auto">
        
        <!-- Security Notice -->
        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-shield-alt fa-2x me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">نکات امنیتی</h6>
                <ul class="mb-0 small">
                    <li>از رمز عبور قوی و منحصر به فرد استفاده کنید.</li>
                    <li>رمز عبور خود را با هیچ‌کس به اشتراک نگذارید.</li>
                    <li>پس از تغییر رمز عبور، در سایر دستگاه‌ها نیز از سیستم خارج خواهید شد.</li>
                </ul>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-lock me-2"></i>فرم تغییر رمز عبور
                </h6>
            </div>
            <div class="card-body">
                
                <!-- Alerts -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
                    <i class="fas fa-times-circle me-2"></i>
                    <div><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); endif; ?>

                <form action="<?= url('/change-password') ?>" method="POST" id="changePasswordForm" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <!-- Current Password -->
                    <div class="mb-4">
                        <label for="current_password" class="form-label fw-bold">
                            رمز عبور فعلی <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-unlock-alt"></i>
                            </span>
                            <input type="password" 
                                   class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                   id="current_password" 
                                   name="current_password" 
                                   placeholder="رمز عبور فعلی را وارد کنید"
                                   required
                                   autofocus>
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="togglePassword('current_password', 'icon1')" tabindex="-1">
                                <i class="fas fa-eye" id="icon1"></i>
                            </button>
                            <?php if (isset($errors['current_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['current_password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">
                            رمز عبور جدید <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                   id="new_password" 
                                   name="new_password" 
                                   placeholder="حداقل ۸ کاراکتر"
                                   required>
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="togglePassword('new_password', 'icon2')" tabindex="-1">
                                <i class="fas fa-eye" id="icon2"></i>
                            </button>
                            <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-bold">
                            تکرار رمز عبور جدید <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="تکرار رمز عبور جدید"
                                   required>
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="togglePassword('confirm_password', 'icon3')" tabindex="-1">
                                <i class="fas fa-eye" id="icon3"></i>
                            </button>
                            <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div id="passwordMatchFeedback" class="form-text d-none text-danger">
                            <i class="fas fa-times me-1"></i> رمز عبور و تکرار آن مطابقت ندارند
                        </div>
                    </div>

                    <!-- Password Strength -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">قدرت رمز عبور:</small>
                            <small id="strengthLabel" class="fw-bold text-danger">وارد نشده</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" id="strengthBar" 
                                 role="progressbar" style="width: 0%;" 
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="card bg-light mb-4">
                        <div class="card-body py-2">
                            <h6 class="fw-bold mb-2 small">الزامات رمز عبور جدید:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small id="req1" class="d-block mb-1 text-muted">
                                        <i class="fas fa-circle me-1"></i> حداقل ۸ کاراکتر
                                    </small>
                                    <small id="req2" class="d-block mb-1 text-muted">
                                        <i class="fas fa-circle me-1"></i> حداقل یک حرف بزرگ
                                    </small>
                                    <small id="req3" class="d-block mb-1 text-muted">
                                        <i class="fas fa-circle me-1"></i> حداقل یک حرف کوچک
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small id="req4" class="d-block mb-1 text-muted">
                                        <i class="fas fa-circle me-1"></i> حداقل یک عدد
                                    </small>
                                    <small id="req5" class="d-block mb-1 text-muted">
                                        <i class="fas fa-circle me-1"></i> حداقل یک کاراکتر خاص
                                    </small>
                                    <small id="req6" class="d-block mb-1 text-muted">
                                        <i class="fas fa-circle me-1"></i> عدم شباهت به نام کاربری
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/profile') ?>" class="btn btn-secondary px-4">
                            <i class="fas fa-times me-1"></i> انصراف
                        </a>
                        <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                            <i class="fas fa-save me-1"></i>
                            <span>تغییر رمز عبور</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password History (optional) -->
        <?php if (!empty($passwordHistory ?? [])): ?>
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-info">
                    <i class="fas fa-history me-2"></i>تاریخچه تغییرات رمز عبور
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>تاریخ</th>
                                <th>IP</th>
                                <th>مرورگر</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($passwordHistory as $history): ?>
                            <tr>
                                <td>
                                    <small><?= date('Y/m/d H:i:s', strtotime($history['created_at'])) ?></small>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($history['ip_address'] ?? '-') ?></code>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars(mb_substr($history['user_agent'] ?? '', 0, 50)) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLabel = document.getElementById('strengthLabel');
    const matchFeedback = document.getElementById('passwordMatchFeedback');

    function checkStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score += 20;
        if (password.length >= 12) score += 10;
        if (/[A-Z]/.test(password)) score += 20;
        if (/[a-z]/.test(password)) score += 15;
        if (/[0-9]/.test(password)) score += 20;
        if (/[^A-Za-z0-9]/.test(password)) score += 15;
        
        return Math.min(100, score);
    }

    function updateUI(password) {
        const score = checkStrength(password);
        const confirmValue = confirmPassword.value;
        
        // Update strength bar
        strengthBar.style.width = score + '%';
        strengthBar.className = 'progress-bar';
        
        if (score < 30) {
            strengthBar.classList.add('bg-danger');
            strengthLabel.textContent = 'ضعیف';
            strengthLabel.className = 'fw-bold text-danger';
        } else if (score < 60) {
            strengthBar.classList.add('bg-warning');
            strengthLabel.textContent = 'متوسط';
            strengthLabel.className = 'fw-bold text-warning';
        } else if (score < 80) {
            strengthBar.classList.add('bg-info');
            strengthLabel.textContent = 'خوب';
            strengthLabel.className = 'fw-bold text-info';
        } else {
            strengthBar.classList.add('bg-success');
            strengthLabel.textContent = 'عالی';
            strengthLabel.className = 'fw-bold text-success';
        }
        
        // Update requirements
        updateRequirement('req1', password.length >= 8);
        updateRequirement('req2', /[A-Z]/.test(password));
        updateRequirement('req3', /[a-z]/.test(password));
        updateRequirement('req4', /[0-9]/.test(password));
        updateRequirement('req5', /[^A-Za-z0-9]/.test(password));
        
        // Check match
        if (password && confirmValue) {
            if (password === confirmValue) {
                matchFeedback.classList.add('d-none');
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');
            } else {
                matchFeedback.classList.remove('d-none');
                confirmPassword.classList.add('is-invalid');
                confirmPassword.classList.remove('is-valid');
            }
        } else {
            matchFeedback.classList.add('d-none');
            confirmPassword.classList.remove('is-invalid', 'is-valid');
        }
    }

    function updateRequirement(id, valid) {
        const el = document.getElementById(id);
        if (valid) {
            el.innerHTML = el.innerHTML.replace('fa-circle', 'fa-check-circle')
                                       .replace('text-muted', 'text-success');
        } else {
            el.innerHTML = el.innerHTML.replace('fa-check-circle', 'fa-circle')
                                       .replace('text-success', 'text-muted');
        }
    }

    newPassword.addEventListener('input', () => updateUI(newPassword.value));
    confirmPassword.addEventListener('input', () => updateUI(newPassword.value));

    // Form validation
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const current = document.getElementById('current_password').value;
        const newPwd = newPassword.value;
        const confirm = confirmPassword.value;
        let hasError = false;
        
        if (!current) {
            toastr.error('لطفاً رمز عبور فعلی را وارد کنید');
            hasError = true;
        }
        
        if (!newPwd) {
            toastr.error('لطفاً رمز عبور جدید را وارد کنید');
            hasError = true;
        } else if (newPwd.length < 8) {
            toastr.error('رمز عبور جدید باید حداقل ۸ کاراکتر باشد');
            hasError = true;
        }
        
        if (newPwd !== confirm) {
            toastr.error('رمز عبور جدید و تکرار آن مطابقت ندارند');
            hasError = true;
        }
        
        if (current === newPwd) {
            toastr.error('رمز عبور جدید نباید با رمز عبور فعلی یکسان باشد');
            hasError = true;
        }
        
        if (hasError) {
            e.preventDefault();
            return;
        }
        
        // Show loading
        const btn = document.getElementById('submitBtn');
        const spinner = document.getElementById('submitSpinner');
        btn.disabled = true;
        spinner.classList.remove('d-none');
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>