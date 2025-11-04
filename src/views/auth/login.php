<?php
$pageTitle = 'Login';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .login-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
        width: 100%;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-header {
        text-align: center;
        padding: 2.5rem 2rem 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-bottom: 1px solid rgba(102, 126, 234, 0.1);
    }

    .login-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        font-size: 2.5rem;
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        animation: scaleIn 0.5s ease-out 0.2s both;
    }

    @keyframes scaleIn {
        from {
            transform: scale(0);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .login-title {
        font-size: 2rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .login-subtitle {
        color: #6b7280;
        font-size: 1.05rem;
    }

    .login-body {
        padding: 2.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        display: block;
    }

    .input-wrapper {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1.1rem;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f9fafb;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-input:focus+.input-icon {
        color: #667eea;
    }

    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        font-size: 1.1rem;
        padding: 0.5rem;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .password-toggle:hover {
        color: #667eea;
    }

    .btn-login {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        margin-top: 1rem;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login i {
        margin-right: 0.5rem;
    }

    .login-footer {
        text-align: center;
        padding: 1.5rem 2.5rem 2.5rem;
        border-top: 1px solid rgba(102, 126, 234, 0.1);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.02), rgba(118, 75, 162, 0.02));
    }

    .signup-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .signup-link:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .form-input.is-invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .error-message i {
        margin-top: 2px;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    /* Responsive */
    @media (max-width: 576px) {

        .login-header,
        .login-body,
        .login-footer {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .login-title {
            font-size: 1.75rem;
        }
    }
</style>
<link rel="stylesheet" href="../assets/css/form-icons-fix.css">
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <h2 class="login-title">Welcome Back!</h2>
                        <p class="login-subtitle">Sign in to your account</p>
                    </div>

                    <div class="login-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444;">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" action="<?= BASE_URL ?>login">
                            <div class="form-group">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-wrapper">
                                    <input type="text"
                                        class="form-input <?= isset($_SESSION['validation_errors']['username']) ? 'is-invalid' : '' ?>"
                                        id="username"
                                        name="username"
                                        placeholder="Enter your username"
                                        required
                                        value="<?= htmlspecialchars($_SESSION['form_data']['username'] ?? $_POST['username'] ?? '') ?>">
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                                <?php if (isset($_SESSION['validation_errors']['username'])): ?>
                                    <div class="error-message" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                                        <i class="fas fa-exclamation-circle" style="margin-top: 2px;"></i>
                                        <span><?= htmlspecialchars($_SESSION['validation_errors']['username']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-wrapper password-wrapper">
                                    <input type="password"
                                        class="form-input <?= isset($_SESSION['validation_errors']['password']) ? 'is-invalid' : '' ?>"
                                        id="password"
                                        name="password"
                                        placeholder="Enter your password"
                                        required>
                                    <i class="fas fa-lock input-icon"></i>
                                    <button type="button" class="password-toggle" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <?php if (isset($_SESSION['validation_errors']['password'])): ?>
                                    <div class="error-message" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                                        <i class="fas fa-exclamation-circle" style="margin-top: 2px;"></i>
                                        <span><?= htmlspecialchars($_SESSION['validation_errors']['password']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn-login">
                                <i class="fas fa-sign-in-alt"></i>
                                Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>forgot-password" class="signup-link">
                                <i class="fas fa-key"></i> Forgot Password?
                            </a>
                        </div>
                    </div>

                    <div class="login-footer">
                        <p class="mb-0">
                            Don't have an account?
                            <a href="<?= BASE_URL ?>signup" class="signup-link">
                                Sign up here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/form-validation.js"></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Simple validation for login
    const loginForm = document.querySelector('form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            let isValid = true;
            
            // Clear errors
            this.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
                el.style.borderColor = '';
            });
            this.querySelectorAll('.error-message').forEach(el => el.remove());
            
            if (!username.value.trim()) {
                showError(username, 'Username is required');
                isValid = false;
            }
            
            if (!password.value.trim()) {
                showError(password, 'Password is required');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                const firstError = this.querySelector('.is-invalid');
                if (firstError) {
                    firstError.focus();
                }
            }
        });
    }
</script>

<?php 
// Clear validation errors and form data after displaying
unset($_SESSION['validation_errors']);
unset($_SESSION['form_data']);
?>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
