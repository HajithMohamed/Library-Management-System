<?php
$pageTitle = 'Verify Password Change';
include APP_ROOT . '/views/layouts/header.php';

// Get masked email and expiry from session
$maskedEmail = $_SESSION['pw_change_masked_email'] ?? 'your email';
$otpExpiry = $_SESSION['pw_change_otp_expiry'] ?? 0;
$remainingSeconds = max(0, $otpExpiry - time());

// Determine user type for form actions
$userType = strtolower($_SESSION['userType'] ?? 'Student');
if ($userType === 'student') $userType = 'user';
$verifyAction = BASE_URL . $userType . '/verify-password-otp';
$resendAction = BASE_URL . $userType . '/resend-password-otp';
$cancelAction = BASE_URL . $userType . '/cancel-password-change';
?>

<style>
    .otp-verify-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .otp-verify-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        animation: slideInUp 0.5s ease-out;
        max-width: 560px;
        width: 100%;
        margin: 0 auto;
    }

    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .otp-verify-header {
        text-align: center;
        padding: 2.5rem 2rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
        border-bottom: 1px solid rgba(102, 126, 234, 0.12);
    }

    .otp-verify-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 18px;
        font-size: 2rem;
        color: white;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .otp-verify-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.75rem;
    }

    .otp-verify-subtitle {
        color: #6b7280;
        font-size: 1rem;
        line-height: 1.6;
    }

    .email-badge {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
        margin-top: 0.5rem;
    }

    .otp-verify-body {
        padding: 2rem 2.5rem;
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

    .alert i { flex-shrink: 0; margin-top: 2px; }

    /* Timer */
    .otp-timer {
        text-align: center;
        margin-bottom: 1.5rem;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 10px;
        font-size: 0.9rem;
        color: #6b7280;
    }

    .otp-timer .timer-value {
        font-weight: 700;
        color: #667eea;
        font-size: 1.1rem;
    }

    .otp-timer.expired .timer-value {
        color: #ef4444;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        display: block;
    }

    .otp-input-wrapper {
        position: relative;
    }

    .otp-input {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1.75rem;
        font-weight: 700;
        text-align: center;
        letter-spacing: 0.4em;
        transition: all 0.2s ease;
        background: #f9fafb;
    }

    .otp-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
    }

    .otp-input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1.25rem;
    }

    .btn-verify {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        margin-top: 0.5rem;
    }

    .btn-verify:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(102, 126, 234, 0.4);
    }

    .btn-verify:active { transform: translateY(0); }

    .btn-verify:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-verify i { margin-right: 0.5rem; }

    .otp-verify-footer {
        text-align: center;
        padding: 1.5rem 2rem 2rem;
        border-top: 1px solid rgba(102, 126, 234, 0.12);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
    }

    .footer-actions {
        display: flex;
        justify-content: center;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .footer-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.2s;
    }

    .footer-link:hover {
        color: #764ba2;
        text-decoration: none;
    }

    .footer-link.cancel-link {
        color: #6b7280;
    }

    .footer-link.cancel-link:hover {
        color: #ef4444;
    }

    .help-text {
        color: #9ca3af;
        font-size: 0.85rem;
        margin-top: 1rem;
    }

    /* Security notice */
    .security-tip {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-top: 1.5rem;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .security-tip i { color: #f59e0b; flex-shrink: 0; margin-top: 2px; }

    .security-tip p {
        color: #92400e;
        font-size: 0.85rem;
        margin: 0;
        line-height: 1.5;
    }

    @media (max-width: 640px) {
        .otp-verify-container {
            padding: 20px 16px;
        }
        .otp-verify-body {
            padding: 1.5rem;
        }
        .otp-input {
            font-size: 1.5rem;
            letter-spacing: 0.3em;
        }
        .footer-actions {
            flex-direction: column;
            gap: 1rem;
        }
    }
</style>

<div class="otp-verify-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="otp-verify-card">
                    <!-- Header -->
                    <div class="otp-verify-header">
                        <div class="otp-verify-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h2 class="otp-verify-title">Verify Your Identity</h2>
                        <p class="otp-verify-subtitle">
                            To confirm your password change, enter the 6-digit code sent to
                            <br>
                            <span class="email-badge">
                                <i class="fas fa-envelope"></i>
                                <?= htmlspecialchars($maskedEmail) ?>
                            </span>
                        </p>
                    </div>

                    <!-- Body -->
                    <div class="otp-verify-body">
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

                        <!-- Countdown Timer -->
                        <div class="otp-timer" id="otpTimer">
                            Code expires in: <span class="timer-value" id="timerValue">--:--</span>
                        </div>

                        <!-- OTP Form -->
                        <form method="POST" action="<?= htmlspecialchars($verifyAction) ?>" id="otpForm">
                            <div class="form-group">
                                <label class="form-label">Verification Code</label>
                                <div class="otp-input-wrapper">
                                    <i class="fas fa-key otp-input-icon"></i>
                                    <input type="text"
                                        class="otp-input"
                                        id="otp"
                                        name="otp"
                                        placeholder="000000"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        pattern="[0-9]{6}"
                                        minlength="6"
                                        maxlength="6"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"
                                        required
                                        autofocus>
                                </div>
                            </div>

                            <button type="submit" class="btn-verify" id="verifyBtn">
                                <i class="fas fa-check-circle"></i>
                                Verify & Change Password
                            </button>
                        </form>

                        <!-- Security Tip -->
                        <div class="security-tip">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Never share your verification code with anyone. Library staff will never ask for your code.</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="otp-verify-footer">
                        <div class="footer-actions">
                            <a href="<?= htmlspecialchars($resendAction) ?>" class="footer-link" id="resendLink">
                                <i class="fas fa-redo"></i>
                                Resend Code
                            </a>
                            <a href="<?= htmlspecialchars($cancelAction) ?>" class="footer-link cancel-link">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                        <p class="help-text">
                            Didn't receive the code? Check your spam folder or click "Resend Code".
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    const verifyBtn = document.getElementById('verifyBtn');
    const form = document.getElementById('otpForm');
    const timerValue = document.getElementById('timerValue');
    const timerContainer = document.getElementById('otpTimer');
    
    // Countdown timer
    let remainingSeconds = <?= (int)$remainingSeconds ?>;
    
    function updateTimer() {
        if (remainingSeconds <= 0) {
            timerValue.textContent = 'EXPIRED';
            timerContainer.classList.add('expired');
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-clock"></i> Code Expired - Please Resend';
            return;
        }
        
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        timerValue.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        remainingSeconds--;
        setTimeout(updateTimer, 1000);
    }
    
    updateTimer();
    
    // Focus OTP input
    otpInput.focus();
    
    // Form submit handling
    form.addEventListener('submit', function(e) {
        const otp = otpInput.value.trim();
        
        if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
            e.preventDefault();
            otpInput.style.borderColor = '#ef4444';
            otpInput.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.12)';
            return;
        }
        
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    });
    
    // Reset styling on input
    otpInput.addEventListener('input', function() {
        this.style.borderColor = '';
        this.style.boxShadow = '';
    });
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
