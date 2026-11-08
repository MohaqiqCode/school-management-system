<?php
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>ورود | <?= config('app_name', 'سیستم مدیریت مدرسه') ?></title>
    
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'IRANSans', 'Tahoma', sans-serif;
        }
        
        .auth-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .auth-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .auth-header .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .auth-header .logo i {
            font-size: 40px;
            color: white;
        }
        
        .auth-header h3 {
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .auth-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .auth-body {
            padding: 40px 30px;
        }
        
        .form-floating > label {
            right: 0;
            left: auto;
        }
        
        .form-floating > .form-control {
            text-align: right;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .input-group .btn {
            border-color: #dee2e6;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .auth-footer {
            text-align: center;
            padding: 20px 30px;
            background: #f8f9fa;
        }
        
        .password-toggle {
            cursor: pointer;
            background: white;
            border: 1px solid #dee2e6;
            border-right: none;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-card {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>سیستم مدیریت مدرسه</h3>
                <p>پنل مدیریت هوشمند</p>
            </div>
            
            <!-- Body -->
            <div class="auth-body">
                <!-- Alerts -->
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); endif; ?>
                
                <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['warning']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['warning']); endif; ?>
                
                <!-- Login Form -->
                <form action="<?= url('/login') ?>" method="POST" id="loginForm" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="form-floating mb-4">
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" 
                               name="email" 
                               placeholder="example@school.com"
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               required>
                        <label for="email">
                            <i class="fas fa-envelope me-1"></i> آدرس ایمیل
                        </label>
                        <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" 
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" 
                               name="password" 
                               placeholder="رمز عبور"
                               required>
                        <label for="password">
                            <i class="fas fa-lock me-1"></i> رمز عبور
                        </label>
                        <button type="button" class="btn password-toggle position-absolute" 
                                style="left: 0; top: 10px; z-index: 5;" 
                                onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">
                                مرا به خاطر بسپار
                            </label>
                        </div>
                        <a href="<?= url('/forgot-password') ?>" class="text-decoration-none">
                            <i class="fas fa-key me-1"></i> فراموشی رمز عبور؟
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="loginButton">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        <span>ورود به سیستم</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" id="loginSpinner" role="status"></span>
                    </button>
                </form>
                
                <!-- Social Login (Optional) -->
                <div class="text-center mt-4">
                    <p class="text-muted">یا ورود با</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-outline-danger btn-sm" disabled title="به زودی">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button class="btn btn-outline-primary btn-sm" disabled title="به زودی">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="auth-footer">
                <small class="text-muted">
                    © <?= date('Y') ?> <?= config('app_name', 'سیستم مدیریت مدرسه') ?>
                </small>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            const spinner = document.getElementById('loginSpinner');
            const buttonText = button.querySelector('span');
            
            button.disabled = true;
            spinner.classList.remove('d-none');
            buttonText.textContent = 'در حال ورود...';
        });
        
        // Show error with SweetAlert2 if present
        <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: '<?= htmlspecialchars($_SESSION['error']) ?>',
            confirmButtonText: 'باشه',
            confirmButtonColor: '#4e73df'
        });
        <?php endif; ?>
    </script>
</body>
</html>