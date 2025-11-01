<?php
// Session checks, authentication, etc.
$pageTitle = 'System Maintenance';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
    .admin-layout { display: flex; min-height: 100vh; background: #f0f2f5; }
</style>

<div class="admin-layout">
    <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>
    
    <main class="main-content">
        <header class="top-header">
            <div class="header-left">
                <h1>System Maintenance</h1>
            </div>
        </header>
        
        <div class="page-content">
            <div class="alert alert-info">
                <i class="fas fa-tools"></i>
                Maintenance tools are under development.
            </div>
        </div>
        
        <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
    </main>
</div>
