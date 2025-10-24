<?php
$pageTitle = 'Access Denied';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .error-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .error-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
        width: 100%;
        max-width: 600px;
        text-align: center;
        padding: 3rem;
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

    .error-icon {
        font-size: 6rem;
        margin-bottom: 1.5rem;
        color: #ef4444;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 0.6;
            transform: scale(0.9);
        }
        50% {
            opacity: 1;
            transform: scale(1);
        }
        100% {
            opacity: 0.6;
            transform: scale(0.9);
        }
    }

    .error-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .error-code {
        display: inline-block;
        background: #fee2e2;
        color: #b91c1c;
        padding: 0.5rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .error-message {
        color: #6b7280;
        font-size: 1.1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-2px);
    }
</style>

<div class="error-container">
    <div class="error-card">
        <i class="fas fa-ban error-icon"></i>
        <h1 class="error-title">Access Denied</h1>
        <div class="error-code">403 Forbidden</div>
        <p class="error-message">
            Sorry, you don't have permission to access this page. 
            Please make sure you're logged in with the appropriate account type.
        </p>
        <div class="action-buttons">
            <?php if (!isset($_SESSION['userId'])): ?>
                <a href="<?= BASE_URL ?>login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Log In
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>" class="btn btn-secondary">
                <i class="fas fa-home"></i> Go to Homepage
            </a>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>