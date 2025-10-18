<?php
$pageTitle = 'Access Denied';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-template">
                <h1 class="display-1 text-danger">
                    <i class="fas fa-ban"></i> 403
                </h1>
                <h2 class="display-4">Access Denied</h2>
                <div class="error-details mb-4">
                    <p class="lead">You don't have permission to access this page.</p>
                    <p class="text-muted">Please contact the administrator if you believe this is an error.</p>
                </div>
                <div class="error-actions">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-home"></i> Go Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
