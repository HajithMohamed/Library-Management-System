<?php
// Get message from session if set
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Determine which step to show based on session
$step = 1;
if (isset($_SESSION['reset_email']) && isset($_SESSION['otp_verified'])) {
    $step = 3;
} elseif (isset($_SESSION['reset_email'])) {
    $step = 2;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Library System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
            max-width: 450px;
            width: 100%;
            padding: 40px;
            margin: 20px;
        }
        h2 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: box-shadow 0.3s, transform 0.2s;
            margin-top: 10px;
        }
        .btn:hover {
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e0e0e0;
            z-index: -1;
        }
        .step:last-child::after {
            display: none;
        }
        .step-num {
            width: 40px;
            height: 40px;
            background: #e0e0e0;
            color: #666;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .step.active .step-num {
            background: #667eea;
            color: #fff;
        }
        .step.completed .step-num {
            background: #4caf50;
            color: #fff;
        }
        .step-label {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fa-solid fa-lock"></i> Reset Password</h2>
        <p class="subtitle">Follow the steps to reset your password</p>

        <div class="step-indicator">
            <div class="step <?php echo ($step >= 1) ? 'active' : ''; ?><?php echo ($step > 1) ? ' completed' : ''; ?>">
                <div class="step-num">1</div>
                <div class="step-label">Email</div>
            </div>
            <div class="step <?php echo ($step >= 2) ? 'active' : ''; ?><?php echo ($step > 2) ? ' completed' : ''; ?>">
                <div class="step-num">2</div>
                <div class="step-label">Verify OTP</div>
            </div>
            <div class="step <?php echo ($step >= 3) ? 'active' : ''; ?>">
                <div class="step-num">3</div>
                <div class="step-label">New Password</div>
            </div>
        </div>

        <?php echo $message; ?>

        <?php if ($step == 1): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>forgot-password">
                <div class="form-group">
                    <label for="email"><i class="fa-solid fa-envelope"></i> Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>
                <button type="submit" class="btn" name="action" value="send_otp">
                    <i class="fa-solid fa-paper-plane"></i> Send OTP
                </button>
            </form>
        <?php elseif ($step == 2): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>forgot-password">
                <div class="form-group">
                    <label for="otp"><i class="fa-solid fa-key"></i> Enter OTP</label>
                    <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric" required>
                    <p class="step-label" style="margin-top: 5px;">Check your email for the OTP</p>
                </div>
                <button type="submit" class="btn" name="action" value="verify_otp">
                    <i class="fa-solid fa-check"></i> Verify OTP
                </button>
            </form>
        <?php elseif ($step == 3): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>forgot-password">
                <div class="form-group">
                    <label for="password"><i class="fa-solid fa-lock"></i> New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password" required>
                    <p class="step-label" style="margin-top: 5px;">Minimum 6 characters</p>
                </div>
                <div class="form-group">
                    <label for="confirmPassword"><i class="fa-solid fa-lock"></i> Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn" name="action" value="reset_password">
                    <i class="fa-solid fa-key"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="<?php echo BASE_URL;?>login"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

    <script src="<?php echo BASE_URL;?>assets/js/form-validation.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const emailInput = this.querySelector('[name="email"]');
            const otpInput = this.querySelector('[name="otp"]');
            const passwordInput = this.querySelector('[name="password"]');
            const confirmPasswordInput = this.querySelector('[name="confirmPassword"]');
            
            let isValid = true;
            
            // Clear previous errors
            this.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
                el.style.borderColor = '';
            });
            this.querySelectorAll('.error-message').forEach(el => el.remove());
            
            // Email validation
            if (emailInput && emailInput.value) {
                if (!validateEmail(emailInput.value)) {
                    showError(emailInput, 'Please enter a valid email address');
                    isValid = false;
                }
            }
            
            // OTP validation
            if (otpInput && otpInput.value) {
                if (!validateOTP(otpInput.value)) {
                    showError(otpInput, 'OTP must be exactly 6 digits');
                    isValid = false;
                }
            }
            
            // Password validation
            if (passwordInput && passwordInput.value) {
                if (!validatePassword(passwordInput.value)) {
                    showError(passwordInput, 'Password must be at least 6 characters');
                    isValid = false;
                }
                
                // Confirm password match
                if (confirmPasswordInput && confirmPasswordInput.value !== passwordInput.value) {
                    showError(confirmPasswordInput, 'Passwords do not match');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                
                const firstError = this.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    });
    
    // OTP input - only numbers
    const otpInput = document.querySelector('[name="otp"]');
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    }
});
</script>
</body>
</html>
