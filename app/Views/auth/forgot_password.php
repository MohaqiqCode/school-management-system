<?php
$title = 'فراموشی رمز عبور';
$metaDescription = 'بازیابی رمز عبور - سیستم مدیریت مدرسه';
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

ob_start();
?>

<div class="auth-body">
    <!-- Page Title -->
    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="fas fa-key fa-3x text-primary"></i>
        </div>
        <h4 class="fw-bold">فراموشی رمز عبور</h4>
        <p class="text-muted small">
            آدرس ایمیل خود را وارد کنید تا لینک بازیابی رمز عبور برای شما ارسال شود.
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

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <div><?= htmlspecialchars($_SESSION['success']) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <!-- Forgot Password Form -->
    <form action="<?= url('/forgot-password') ?>" method="POST" id="forgotPasswordForm" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="mb-4">
            <label for="email" class="form-label fw-bold">
                <i class="fas fa-envelope me-1 text-primary"></i> آدرس ایمیل
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-at"></i>
                </span>
                <input type="email" 
                       class="form-control form-control-lg <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                       id="email" 
                       name="email" 
                       placeholder="example@school.com"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       required
                       autofocus>
                <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>
            <small class="form-text text-muted mt-1">
                <i class="fas fa-info-circle me-1"></i>
                لینک بازیابی به این ایمیل ارسال خواهد شد.
            </small>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-auth" id="submitBtn">
                <i class="fas fa-paper-plane me-2"></i>
                <span>ارسال لینک بازیابی</span>
                <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner" role="status"></span>
            </button>
        </div>

        <div class="text-center">
            <a href="<?= url('/login') ?>" class="auth-link">
                <i class="fas fa-arrow-right me-1"></i> بازگشت به صفحه ورود
            </a>
        </div>
    </form>

    <!-- Help Box -->
    <div class="mt-4 p-3 bg-light rounded-3">
        <h6 class="fw-bold mb-2">
            <i class="fas fa-question-circle text-info me-2"></i>راهنما
        </h6>
        <ul class="list-unstyled mb-0 small text-muted">
            <li class="mb-1">
                <i class="fas fa-check text-success me-1"></i>
                ایمیل وارد شده باید همان ایمیلی باشد که با آن ثبت نام کرده‌اید.
            </li>
            <li class="mb-1">
                <i class="fas fa-check text-success me-1"></i>
                لینک بازیابی به ایمیل شما ارسال می‌شود.
            </li>
            <li class="mb-1">
                <i class="fas fa-check text-success me-1"></i>
                لینک ارسال شده تا ۱ ساعت معتبر است.
            </li>
            <li>
                <i class="fas fa-check text-success me-1"></i>
                اگر ایمیل را دریافت نکردید، پوشه spam را بررسی کنید.
            </li>
        </ul>
    </div>
</div>

<script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        
        if (!email) {
            e.preventDefault();
            toastr.error('لطفاً ایمیل خود را وارد کنید');
            return;
        }
        
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            e.preventDefault();
            toastr.error('لطفاً یک ایمیل معتبر وارد کنید');
            return;
        }
        
        // Show loading state
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