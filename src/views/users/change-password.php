<?php
$pageTitle = 'Change Password';
include APP_ROOT . '/views/layouts/header.php';

// Determine the correct form action and back link based on user type
$userType = $_SESSION['userType'] ?? 'Student';
switch (strtolower($userType)) {
    case 'admin':
        $formAction = BASE_URL . 'admin/change-password';
        $backLink = BASE_URL . 'admin/profile';
        $dashboardLink = BASE_URL . 'admin/dashboard';
        break;
    case 'faculty':
        $formAction = BASE_URL . 'faculty/change-password';
        $backLink = BASE_URL . 'faculty/profile';
        $dashboardLink = BASE_URL . 'faculty/dashboard';
        break;
    default:
        $formAction = BASE_URL . 'user/change-password';
        $backLink = BASE_URL . 'user/profile';
        $dashboardLink = BASE_URL . 'user/dashboard';
}
?>

<style>
    .change-pw-container {
        min-height: calc(100vh - 200px);
        padding: 2rem 0;
        animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .change-pw-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .change-pw-header h1 {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .change-pw-header p {
        color: #6b7280;
        font-size: 1.05rem;
        margin: 0;
    }
    
    .change-pw-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        max-width: 640px;
        margin: 0 auto;
        animation: slideInUp 0.5s ease-out 0.2s both;
    }
    
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card-header-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        padding: 2rem;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .card-header-section .icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 14px;
        font-size: 1.5rem;
    }
    
    .card-header-section h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
    }
    
    .card-body-section {
        padding: 2.5rem;
    }
    
    /* Security Notice */
    .security-notice {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(6, 182, 212, 0.05));
        border-left: 4px solid #3b82f6;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .security-notice .notice-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border-radius: 10px;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .security-notice .notice-text h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    
    .security-notice .notice-text p {
        color: #6b7280;
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-label i { color: #667eea; font-size: 0.9rem; }
    .required-star { color: #ef4444; }
    
    .input-wrapper {
        position: relative;
    }
    
    .form-input {
        width: 100%;
        padding: 0.875rem 3rem 0.875rem 2.75rem;
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
    
    .form-input.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
    
    .form-input.is-valid {
        border-color: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
    }
    
    .input-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
    }
    
    .toggle-password {
        position: absolute;
        right: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #9ca3af;
        font-size: 1rem;
        background: none;
        border: none;
        padding: 4px;
        transition: color 0.2s;
    }
    
    .toggle-password:hover { color: #667eea; }
    
    /* Password Strength Meter */
    .password-strength {
        margin-top: 0.75rem;
    }
    
    .strength-bar-container {
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    
    .strength-bar {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease, background-color 0.3s ease;
        width: 0%;
    }
    
    .strength-bar.weak { width: 25%; background: #ef4444; }
    .strength-bar.fair { width: 50%; background: #f59e0b; }
    .strength-bar.good { width: 75%; background: #3b82f6; }
    .strength-bar.strong { width: 100%; background: #22c55e; }
    
    .strength-label {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .strength-label.weak { color: #ef4444; }
    .strength-label.fair { color: #f59e0b; }
    .strength-label.good { color: #3b82f6; }
    .strength-label.strong { color: #22c55e; }
    
    /* Password Requirements */
    .password-requirements {
        background: #f8fafc;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-top: 0.75rem;
    }
    
    .password-requirements .req-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #6b7280;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .requirement {
        font-size: 0.85rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
        transition: color 0.2s ease;
    }
    
    .requirement i {
        font-size: 0.75rem;
        width: 16px;
        text-align: center;
    }
    
    .requirement.met { color: #22c55e; }
    .requirement.met i { color: #22c55e; }
    .requirement.unmet { color: #9ca3af; }
    .requirement.unmet i { color: #d1d5db; }
    
    /* Match indicator */
    .match-indicator {
        font-size: 0.85rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .match-indicator.match { color: #22c55e; }
    .match-indicator.no-match { color: #ef4444; }
    
    /* Action Buttons */
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f3f4f6;
    }
    
    .btn-submit {
        flex: 1;
        padding: 1rem 2rem;
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    }
    
    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .btn-back {
        padding: 1rem 2rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        background: white;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-back:hover {
        border-color: #667eea;
        color: #667eea;
        background: rgba(102, 126, 234, 0.05);
        text-decoration: none;
    }
    
    /* Alert styles */
    .alert {
        padding: 1rem 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    
    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    
    .alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-body-section {
            padding: 1.5rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-submit, .btn-back {
            width: 100%;
        }
    }
</style>

<div class="change-pw-container">
    <div class="container">
        <!-- Header -->
        <div class="change-pw-header">
            <h1><i class="fas fa-shield-alt" style="color: #667eea;"></i> Change Password</h1>
            <p>Update your account password to keep your account secure</p>
        </div>
        
        <div class="change-pw-card">
            <div class="card-header-section">
                <div class="icon-box">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>Update Password</h3>
            </div>
            
            <div class="card-body-section">
                <!-- Flash Messages -->
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    </div>
                <?php endif; ?>
                
                <!-- Security Notice -->
                <div class="security-notice">
                    <div class="notice-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="notice-text">
                        <h5>Security Information</h5>
                        <p>A verification code (OTP) will be sent to your registered email for identity confirmation. After verifying, your password will be changed and you will be logged out. A confirmation email will also be sent.</p>
                    </div>
                </div>
                
                <form method="POST" action="<?= htmlspecialchars($formAction) ?>" id="changePasswordForm" novalidate>
                    <input type="hidden" name="redirect_to" value="<?= strtolower($userType) === 'admin' ? 'admin/change-password' : (strtolower($userType) === 'faculty' ? 'faculty/change-password' : 'user/change-password') ?>">
                    
                    <!-- Current Password -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i>
                            Current Password <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   name="current_password" 
                                   id="current_password"
                                   class="form-input" 
                                   placeholder="Enter your current password"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="toggle-password" data-target="current_password" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- New Password -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i>
                            New Password <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   name="new_password" 
                                   id="new_password"
                                   class="form-input" 
                                   placeholder="Enter your new password"
                                   required
                                   autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="new_password" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Meter -->
                        <div class="password-strength" id="strengthMeter" style="display: none;">
                            <div class="strength-bar-container">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <span class="strength-label" id="strengthLabel"></span>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="password-requirements">
                            <div class="req-title">Password Requirements</div>
                            <div class="requirement unmet" id="req-length">
                                <i class="fas fa-circle"></i>
                                <span>At least 8 characters</span>
                            </div>
                            <div class="requirement unmet" id="req-upper">
                                <i class="fas fa-circle"></i>
                                <span>At least one uppercase letter (A-Z)</span>
                            </div>
                            <div class="requirement unmet" id="req-lower">
                                <i class="fas fa-circle"></i>
                                <span>At least one lowercase letter (a-z)</span>
                            </div>
                            <div class="requirement unmet" id="req-number">
                                <i class="fas fa-circle"></i>
                                <span>At least one number (0-9)</span>
                            </div>
                            <div class="requirement unmet" id="req-special">
                                <i class="fas fa-circle"></i>
                                <span>At least one special character (@, $, !, %, *, ?, &)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirm New Password -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-check-double"></i>
                            Confirm New Password <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirm_password"
                                   class="form-input" 
                                   placeholder="Re-enter your new password"
                                   required
                                   autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="match-indicator" id="matchIndicator" style="display: none;"></div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <a href="<?= htmlspecialchars($backLink) ?>" class="btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Back to Profile
                        </a>
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-paper-plane"></i>
                            Send Verification Code
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[@$!%*?&#^()_+\-=\[\]{};:'",.<>\/\\|`~]/.test(password)
        };
        
        // Update requirement indicators
        Object.keys(checks).forEach(function(key) {
            const el = document.getElementById('req-' + key);
            if (el) {
                const icon = el.querySelector('i');
                if (checks[key]) {
                    el.classList.remove('unmet');
                    el.classList.add('met');
                    icon.classList.remove('fa-circle');
                    icon.classList.add('fa-check-circle');
                    score++;
                } else {
                    el.classList.remove('met');
                    el.classList.add('unmet');
                    icon.classList.remove('fa-check-circle');
                    icon.classList.add('fa-circle');
                }
            }
        });
        
        // Extra points for length
        if (password.length >= 12) score++;
        if (password.length >= 16) score++;
        
        return { score: score, checks: checks };
    }
    
    function updateStrengthMeter(password) {
        const meter = document.getElementById('strengthMeter');
        const bar = document.getElementById('strengthBar');
        const label = document.getElementById('strengthLabel');
        
        if (password.length === 0) {
            meter.style.display = 'none';
            return;
        }
        
        meter.style.display = 'block';
        const result = checkPasswordStrength(password);
        
        // Remove all classes
        bar.className = 'strength-bar';
        label.className = 'strength-label';
        
        if (result.score <= 2) {
            bar.classList.add('weak');
            label.classList.add('weak');
            label.textContent = 'Weak';
        } else if (result.score <= 4) {
            bar.classList.add('fair');
            label.classList.add('fair');
            label.textContent = 'Fair';
        } else if (result.score <= 5) {
            bar.classList.add('good');
            label.classList.add('good');
            label.textContent = 'Good';
        } else {
            bar.classList.add('strong');
            label.classList.add('strong');
            label.textContent = 'Strong';
        }
    }
    
    function updateMatchIndicator() {
        const indicator = document.getElementById('matchIndicator');
        const newPw = newPasswordInput.value;
        const confirmPw = confirmPasswordInput.value;
        
        if (confirmPw.length === 0) {
            indicator.style.display = 'none';
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        indicator.style.display = 'flex';
        
        if (newPw === confirmPw) {
            indicator.className = 'match-indicator match';
            indicator.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
        } else {
            indicator.className = 'match-indicator no-match';
            indicator.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
            confirmPasswordInput.classList.remove('is-valid');
            confirmPasswordInput.classList.add('is-invalid');
        }
    }
    
    // Real-time validation
    newPasswordInput.addEventListener('input', function() {
        updateStrengthMeter(this.value);
        checkPasswordStrength(this.value);
        if (confirmPasswordInput.value.length > 0) {
            updateMatchIndicator();
        }
    });
    
    confirmPasswordInput.addEventListener('input', function() {
        updateMatchIndicator();
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const currentPw = document.getElementById('current_password').value;
        const newPw = newPasswordInput.value;
        const confirmPw = confirmPasswordInput.value;
        
        let errors = [];
        
        if (!currentPw) {
            errors.push('Current password is required.');
        }
        
        if (!newPw) {
            errors.push('New password is required.');
        }
        
        if (!confirmPw) {
            errors.push('Please confirm your new password.');
        }
        
        if (newPw && confirmPw && newPw !== confirmPw) {
            errors.push('New passwords do not match.');
        }
        
        // Check strength requirements
        if (newPw) {
            const result = checkPasswordStrength(newPw);
            if (!result.checks.length) errors.push('Password must be at least 8 characters.');
            if (!result.checks.upper) errors.push('Password must contain an uppercase letter.');
            if (!result.checks.lower) errors.push('Password must contain a lowercase letter.');
            if (!result.checks.number) errors.push('Password must contain a number.');
            if (!result.checks.special) errors.push('Password must contain a special character.');
        }
        
        if (currentPw && newPw && currentPw === newPw) {
            errors.push('New password cannot be the same as your current password.');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            
            // Show errors
            let existingAlert = form.querySelector('.alert-danger.client-error');
            if (existingAlert) existingAlert.remove();
            
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger client-error';
            alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i><div>' + errors.join('<br>') + '</div>';
            form.insertBefore(alertDiv, form.firstChild);
            
            alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        
        // Disable button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Verification Code...';
    });
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
