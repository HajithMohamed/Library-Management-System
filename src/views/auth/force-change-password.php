<?php
$pageTitle = 'Change Your Password';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .force-pw-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: linear-gradient(135deg, #f0f4ff 0%, #faf5ff 100%);
    }

    .force-pw-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        max-width: 520px;
        width: 100%;
        animation: slideInUp 0.5s ease-out;
    }

    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .force-pw-header {
        text-align: center;
        padding: 2rem 1.5rem;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(249, 115, 22, 0.08));
        border-bottom: 1px solid rgba(239, 68, 68, 0.12);
    }

    .force-pw-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
        border-radius: 50%;
        font-size: 2rem;
        color: white;
        box-shadow: 0 8px 16px rgba(239, 68, 68, 0.25);
    }

    .force-pw-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .force-pw-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .force-pw-body {
        padding: 2rem;
    }

    .security-notice {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .security-notice i {
        color: #f59e0b;
        font-size: 1.2rem;
        margin-top: 2px;
    }

    .security-notice p {
        margin: 0;
        color: #92400e;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
        font-size: 0.9375rem;
        display: block;
    }

    .form-label .required {
        color: #dc2626;
    }

    .input-wrapper {
        position: relative;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
        background: #f9fafb;
    }

    .form-input:focus {
        outline: none;
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .input-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
    }

    .toggle-pw {
        position: absolute;
        right: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #9ca3af;
        font-size: 1rem;
        background: none;
        border: none;
        padding: 0;
    }

    .toggle-pw:hover { color: #6366f1; }

    .password-requirements {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-top: 0.5rem;
    }

    .password-requirements p {
        margin: 0 0 0.375rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
    }

    .requirement {
        font-size: 0.8rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        margin-bottom: 0.25rem;
    }

    .requirement.met { color: #22c55e; }
    .requirement.met i { color: #22c55e; }

    .btn-change-pw {
        width: 100%;
        padding: 0.875rem;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        margin-top: 0.5rem;
    }

    .btn-change-pw:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
    }

    .btn-change-pw:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
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

    @media (max-width: 640px) {
        .force-pw-container { padding: 20px 16px; }
        .force-pw-card { border-radius: 12px; }
        .force-pw-header { padding: 1.5rem 1rem; }
        .force-pw-body { padding: 1.5rem; }
    }
</style>

<div class="force-pw-container">
    <div class="force-pw-card">
        <div class="force-pw-header">
            <div class="force-pw-icon">
                <i class="fas fa-key"></i>
            </div>
            <h2 class="force-pw-title">Change Your Password</h2>
            <p class="force-pw-subtitle">
                Your account was created by an administrator. For security reasons,
                you must set a new password before continuing.
            </p>
        </div>

        <div class="force-pw-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="security-notice">
                <i class="fas fa-shield-alt"></i>
                <p>
                    Choose a strong password that you haven't used before.
                    Your temporary password will be replaced permanently.
                </p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>force-change-password" id="forceChangePwForm">
                <div class="form-group">
                    <label class="form-label">
                        New Password <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-input" name="new_password" id="newPassword"
                            placeholder="Enter your new password" required minlength="6">
                        <button type="button" class="toggle-pw" onclick="togglePassword('newPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-requirements" id="pwRequirements">
                        <p>Password must have:</p>
                        <div class="requirement" id="req-length">
                            <i class="fas fa-circle" style="font-size: 0.4rem;"></i> At least 6 characters
                        </div>
                        <div class="requirement" id="req-upper">
                            <i class="fas fa-circle" style="font-size: 0.4rem;"></i> One uppercase letter
                        </div>
                        <div class="requirement" id="req-lower">
                            <i class="fas fa-circle" style="font-size: 0.4rem;"></i> One lowercase letter
                        </div>
                        <div class="requirement" id="req-number">
                            <i class="fas fa-circle" style="font-size: 0.4rem;"></i> One number
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Confirm New Password <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-input" name="confirm_password" id="confirmPassword"
                            placeholder="Re-enter your new password" required minlength="6">
                        <button type="button" class="toggle-pw" onclick="togglePassword('confirmPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="matchMessage" style="font-size: 0.8rem; margin-top: 0.375rem; display: none;"></div>
                </div>

                <button type="submit" class="btn-change-pw" id="submitBtn">
                    <i class="fas fa-check-circle"></i> Set New Password &amp; Continue
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const newPw = document.getElementById('newPassword');
    const confirmPw = document.getElementById('confirmPassword');
    const matchMsg = document.getElementById('matchMessage');

    function checkRequirements() {
        const val = newPw.value;
        setReq('req-length', val.length >= 6);
        setReq('req-upper', /[A-Z]/.test(val));
        setReq('req-lower', /[a-z]/.test(val));
        setReq('req-number', /[0-9]/.test(val));
    }

    function setReq(id, met) {
        const el = document.getElementById(id);
        const icon = el.querySelector('i');
        if (met) {
            el.classList.add('met');
            icon.className = 'fas fa-check-circle';
            icon.style.fontSize = '';
        } else {
            el.classList.remove('met');
            icon.className = 'fas fa-circle';
            icon.style.fontSize = '0.4rem';
        }
    }

    function checkMatch() {
        if (confirmPw.value.length === 0) {
            matchMsg.style.display = 'none';
            return;
        }
        matchMsg.style.display = 'block';
        if (newPw.value === confirmPw.value) {
            matchMsg.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
            matchMsg.style.color = '#22c55e';
        } else {
            matchMsg.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
            matchMsg.style.color = '#ef4444';
        }
    }

    newPw.addEventListener('input', function() { checkRequirements(); checkMatch(); });
    confirmPw.addEventListener('input', checkMatch);

    document.getElementById('forceChangePwForm').addEventListener('submit', function(e) {
        if (newPw.value.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long.');
            return;
        }
        if (newPw.value !== confirmPw.value) {
            e.preventDefault();
            alert('Passwords do not match.');
            return;
        }
    });
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
