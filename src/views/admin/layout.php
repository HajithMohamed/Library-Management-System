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
    
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 1rem;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
        }
        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 0.5rem;
        }
        .main-content {
            padding: 2rem;
        }
        .dashboard-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 0.5rem;
        }
    </style>
    
    <!-- Page-specific CSS -->
    <?php if (isset($additionalCss)): ?>
        <?= $additionalCss ?>
    <?php endif; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <?php include APP_ROOT . '/views/admin/sidebar.php'; ?>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ml-sm-auto px-4 main-content">
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

    <!-- JavaScript Libraries -->
    <script src="<?= BASE_URL ?>assets/js/jquery.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($additionalJs)): ?>
        <?= $additionalJs ?>
    <?php endif; ?>
</body>
</html>
