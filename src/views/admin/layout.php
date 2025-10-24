<?php
// Ensure user is logged in and is admin
if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
    header('Location: ' . BASE_URL . 'login');
    exit();
}

// Set current page for navigation highlighting
$currentPage = $currentPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?> - Library System</title>
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    
    <!-- Page-specific CSS -->
    <?php if (isset($additionalCss)): ?>
        <?= $additionalCss ?>
    <?php endif; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <?php include APP_ROOT . '/views/admin/sidebar.php'; ?>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <nav class="navbar navbar-light mt-3 mb-4">
                    <span class="navbar-brand mb-0 h1"><?= $pageTitle ?? 'Admin Dashboard' ?></span>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?= substr($_SESSION['name'] ?? 'A', 0, 1); ?>
                        </div>
                        <span><?= $_SESSION['name'] ?? 'Admin'; ?></span>
                    </div>
                </nav>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="content">
                    <?php if (isset($contentView) && file_exists($contentView)): ?>
                        <?php include $contentView; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Content view not found: <?= $contentView ?? 'No view specified' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="<?= BASE_URL ?>assets/js/jquery.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($additionalJs)): ?>
        <?= $additionalJs ?>
    <?php endif; ?>
</body>
</html>
