<?php
$pageTitle = 'Verify OTP';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .otp-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .otp-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        animation: slideInUp 0.5s ease-out;
        max-width: 600px;
        width: 100%;
        margin: 0 auto;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .otp-header {
        text-align: center;
        padding: 2rem 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
        border-bottom: 1px solid rgba(102, 126, 234, 0.12);
    }

    .otp-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        font-size: 1.75rem;
        color: white;
        box-shadow: 0 8px 16px rgba(102, 126, 234, 0.25);
    }

    .otp-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .otp-subtitle {
        color: #6b7280;
        font-size: 1rem;
    }

    .otp-body {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
        font-size: 0.9375rem;
        display: block;
    }

    .input-group-modern {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
        transition: color 0.2s ease;
    }

    .form-control-modern {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: #f9fafb;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
    }

    .form-control-modern:focus+.input-icon {
        color: #667eea;
    }

    .btn-otp {
        width: 100%;
        padding: 0.875rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
        margin-top: 1rem;
    }

    .btn-otp:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.35);
    }

    .btn-otp:active {
        transform: translateY(0);
    }

    .btn-otp i {
        margin-right: 0.5rem;
    }

    .otp-footer {
        text-align: center;
        padding: 1.25rem 1.5rem 1.75rem;
        border-top: 1px solid rgba(102, 126, 234, 0.12);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
    }

    @media (max-width: 640px) {
        .otp-container {
            padding: 20px 16px;
        }

        .otp-card {
            border-radius: 12px;
        }

        .otp-header {
            padding: 1.5rem 1rem;
        }

        .otp-body {
            padding: 1.5rem;
        }

        .otp-title {
            font-size: 1.5rem;
        }

        .otp-subtitle {
            font-size: 0.9375rem;
        }

        .btn-otp {
            padding: 0.75rem;
        }
    }
</style>
<link rel="stylesheet" href="../assets/css/form-icons-fix.css">

<div class="otp-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="otp-card">
                    <div class="otp-header">
                        <div class="otp-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h2 class="otp-title">Verify Your Email</h2>
                        <p class="otp-subtitle">Enter the 6-digit code we sent to your email</p>
                    </div>

                    <div class="otp-body">
                        <form method="POST" action="<?= BASE_URL ?>verify-otp">
                            <div class="form-group">
                                <label for="otp" class="form-label">Verification Code</label>
                                <div class="input-group-modern">
                                    <input type="tel"
                                        class="form-control-modern"
                                        id="otp"
                                        name="otp"
                                        placeholder="Enter 6-digit code"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        pattern="[0-9]{6}"
                                        minlength="6"
                                        maxlength="6"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"
                                        required
                                        value="<?= htmlspecialchars($_POST['otp'] ?? '') ?>">
                                    <i class="fas fa-key input-icon"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn-otp">
                                <i class="fas fa-check-circle"></i>
                                Verify Account
                            </button>
                        </form>
                    </div>

                    <div class="otp-footer">
                        <p class="mb-0">Didn't receive the code? Check your spam folder or request a new one from support.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
