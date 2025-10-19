<?php
$pageTitle = 'Page Not Found';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-template">
                <h1 class="display-1 text-primary">
                    <i class="fas fa-exclamation-triangle"></i> 404
                </h1>
                <h2 class="display-4">Page Not Found</h2>
                <div class="error-details mb-4">
                    <p class="lead">Sorry, the page you are looking for could not be found.</p>
                    <p class="text-muted">The page might have been moved, deleted, or you might have entered the wrong URL.</p>
                </div>
                <div class="error-actions">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-home"></i> Take Me Home
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
