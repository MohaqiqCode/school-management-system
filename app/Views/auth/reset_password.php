<?php
$title = 'بازنشانی رمز عبور';
$metaDescription = 'تنظیم رمز عبور جدید - سیستم مدیریت مدرسه';
$token = $token ?? '';
$email = $email ?? '';
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

// Validate token presence
if (empty($token)) {
    $_SESSION['error'] = 'لینک بازیابی نامعتبر است';
    header('Location: ' . url('/login'));
    exit;
}

ob_start();
?>

<div class="auth-body">
    <!-- Page Title -->
    <div class="text-center mb-4">
        <div class="mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" 
                 style="width: 80px; height: 80px;">
                <i class="fas fa-lock fa-3x text-success"></i>
            </div>
        </div>
        <h4 class="fw-bold">بازنشانی رمز عبور</h4>
        <p class="text-muted small">
            رمز عبور جدید خود را وارد کنید.
            <?php if ($email): ?>
            <br><strong><?= htmlspecialchars($email) ?></strong>
            <?php endif; ?>
        </p>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <div><?= htmlspecialchars($_SESSION['error']) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- Reset Password Form -->
    <form action="<?= url('/reset-password') ?>" method="POST" id="resetPasswordForm" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <!-- New Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-bold">
                <i class="fas fa-lock me-1 text-success"></i> رمز عبور جدید
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-key"></i>
                </span>
                <input type="password" 
                       class="form-control form-control-lg <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                       id="password" 
                       name="password" 
                       placeholder="حداقل ۸ کاراکتر"
                       required
                       autofocus>
                <button type="button" class="btn btn-outline-secondary password-toggle" 
                        onclick="togglePassword('password', 'toggleIcon1')" tabindex="-1">
                    <i class="fas fa-eye" id="toggleIcon1"></i>
                </button>
                <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-bold">
                <i class="fas fa-lock me-1 text-success"></i> تکرار رمز عبور جدید
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-key"></i>
                </span>
                <input type="password" 
                       class="form-control form-control-lg <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="تکرار رمز عبور"
                       required>
                <button type="button" class="btn btn-outline-secondary password-toggle" 
                        onclick="togglePassword('password_confirmation', 'toggleIcon2')" tabindex="-1">
                    <i class="fas fa-eye" id="toggleIcon2"></i>
                </button>
                <?php if (isset($errors['password_confirmation'])): ?>
                <div class="invalid-feedback"><?= $errors['password_confirmation'] ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Password Strength Indicator -->
        <div class="mb-4">
            <div class="password-strength">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar" id="passwordStrengthBar" 
                         role="progressbar" style="width: 0%;" 
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-1" id="passwordStrengthText">قدرت رمز عبور</small>
            </div>
        </div>

        <!-- Password Requirements -->
        <div class="mb-4 p-3 bg-light rounded-3">
            <h6 class="fw-bold mb-2 small">الزامات رمز عبور:</h6>
            <ul class="list-unstyled mb-0 small" id="passwordRequirements">
                <li class="mb-1" id="req-length">
                    <i class="fas fa-times-circle text-danger me-1"></i>
                    حداقل ۸ کاراکتر
                </li>
                <li class="mb-1" id="req-uppercase">
                    <i class="fas fa-times-circle text-danger me-1"></i>
                    حداقل یک حرف بزرگ انگلیسی
                </li>
                <li class="mb-1" id="req-lowercase">
                    <i class="fas fa-times-circle text-danger me-1"></i>
                    حداقل یک حرف کوچک انگلیسی
                </li>
                <li class="mb-1" id="req-number">
                    <i class="fas fa-times-circle text-danger me-1"></i>
                    حداقل یک عدد
                </li>
                <li id="req-match">
                    <i class="fas fa-times-circle text-danger me-1"></i>
                    مطابقت رمز عبور با تکرار آن
                </li>
            </ul>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-auth" id="submitBtn">
                <i class="fas fa-save me-2"></i>
                <span>بازنشانی رمز عبور</span>
                <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner" role="status"></span>
            </button>
        </div>

        <div class="text-center">
            <a href="<?= url('/login') ?>" class="auth-link">
                <i class="fas fa-arrow-right me-1"></i> بازگشت به صفحه ورود
            </a>
        </div>
    </form>
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

    // Password strength checker
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');

    function checkPasswordStrength(password) {
        let score = 0;
        
        // Length check
        if (password.length >= 8) score += 25;
        if (password.length >= 12) score += 10;
        
        // Uppercase check
        if (/[A-Z]/.test(password)) score += 20;
        
        // Lowercase check
        if (/[a-z]/.test(password)) score += 15;
        
        // Number check
        if (/[0-9]/.test(password)) score += 15;
        
        // Special character check
        if (/[^A-Za-z0-9]/.test(password)) score += 15;
        
        return Math.min(100, score);
    }

    function updateStrengthUI(score) {
        strengthBar.style.width = score + '%';
        strengthBar.className = 'progress-bar';
        
        if (score < 40) {
            strengthBar.classList.add('bg-danger');
            strengthText.textContent = 'ضعیف';
        } else if (score < 70) {
            strengthBar.classList.add('bg-warning');
            strengthText.textContent = 'متوسط';
        } else {
            strengthBar.classList.add('bg-success');
            strengthText.textContent = 'قوی';
        }
    }

    function updateRequirements(password) {
        // Length
        const reqLength = document.getElementById('req-length');
        if (password.length >= 8) {
            reqLength.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> حداقل ۸ کاراکتر';
        } else {
            reqLength.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> حداقل ۸ کاراکتر';
        }
        
        // Uppercase
        const reqUpper = document.getElementById('req-uppercase');
        if (/[A-Z]/.test(password)) {
            reqUpper.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> حداقل یک حرف بزرگ انگلیسی';
        } else {
            reqUpper.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> حداقل یک حرف بزرگ انگلیسی';
        }
        
        // Lowercase
        const reqLower = document.getElementById('req-lowercase');
        if (/[a-z]/.test(password)) {
            reqLower.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> حداقل یک حرف کوچک انگلیسی';
        } else {
            reqLower.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> حداقل یک حرف کوچک انگلیسی';
        }
        
        // Number
        const reqNumber = document.getElementById('req-number');
        if (/[0-9]/.test(password)) {
            reqNumber.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> حداقل یک عدد';
        } else {
            reqNumber.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> حداقل یک عدد';
        }
        
        // Match
        const reqMatch = document.getElementById('req-match');
        const confirmValue = document.getElementById('password_confirmation').value;
        if (password && password === confirmValue) {
            reqMatch.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> مطابقت رمز عبور با تکرار آن';
        } else if (confirmValue) {
            reqMatch.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> مطابقت رمز عبور با تکرار آن';
        } else {
            reqMatch.innerHTML = '<i class="fas fa-circle text-muted me-1"></i> مطابقت رمز عبور با تکرار آن';
        }
    }

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const score = checkPasswordStrength(password);
        updateStrengthUI(score);
        updateRequirements(password);
    });

    confirmInput.addEventListener('input', function() {
        updateRequirements(passwordInput.value);
    });

    // Form submission
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (password !== confirm) {
            e.preventDefault();
            toastr.error('رمز عبور و تکرار آن مطابقت ندارند');
            return;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            toastr.error('رمز عبور باید حداقل ۸ کاراکتر باشد');
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
require __DIR__ . '/../layouts/auth.php';
?>