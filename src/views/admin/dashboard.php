<?php
$pageTitle = 'Admin Dashboard';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </h1>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $stats['users']['total_users'] ?? 0 ?></h4>
                            <p class="card-text">Total Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $stats['books']['total_books'] ?? 0 ?></h4>
                            <p class="card-text">Total Books</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $stats['active_borrowings'] ?? 0 ?></h4>
                            <p class="card-text">Active Borrowings</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book-reader fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $stats['overdue_books'] ?? 0 ?></h4>
                            <p class="card-text">Overdue Books</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <h3>Quick Actions</h3>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Manage Books</h5>
                    <p class="card-text">Add, edit, or remove books</p>
                    <a href="<?= BASE_URL ?>admin/books" class="btn btn-primary">
                        <i class="fas fa-book"></i> Manage
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Manage Users</h5>
                    <p class="card-text">View and manage user accounts</p>
                    <a href="<?= BASE_URL ?>admin/users" class="btn btn-success">
                        <i class="fas fa-users"></i> Manage
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Statistics</h5>
                    <p class="card-text">View library statistics and analytics</p>
                    <a href="<?= BASE_URL ?>admin/reports" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-envelope-open-text fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Borrow Requests</h5>
                    <p class="card-text">Manage borrow requests</p>
                    <a href="<?= BASE_URL ?>admin/borrow-requests" class="btn btn-warning">
                        <i class="fas fa-envelope-open-text"></i> Manage
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Admin Features -->
    <div class="row mb-4">
        <div class="col-12">
            <h3>Enhanced Admin Features</h3>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Fine Management</h5>
                    <p class="card-text">View, update, and manage fines</p>
                    <a href="<?= BASE_URL ?>admin/fines" class="btn btn-danger">
                        <i class="fas fa-money-bill-wave"></i> Manage Fines
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-envelope-open-text fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Borrow Requests</h5>
                    <p class="card-text">Manage borrow requests</p>
                    <a href="<?= BASE_URL ?>admin/borrow-requests" class="btn btn-warning">
                        <i class="fas fa-envelope-open-text"></i> Manage Requests
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-bell fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Notifications</h5>
                    <p class="card-text">View system notifications</p>
                    <a href="<?= BASE_URL ?>admin/notifications" class="btn btn-info">
                        <i class="fas fa-bell"></i> Notifications
                        <?php if (!empty($notifications) && count(array_filter($notifications, fn($n) => !$n['isRead'])) > 0): ?>
                            <span class="badge bg-danger ms-1"><?= count(array_filter($notifications, fn($n) => !$n['isRead'])) ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">System Maintenance</h5>
                    <p class="card-text">System health and maintenance</p>
                    <a href="<?= BASE_URL ?>admin/maintenance" class="btn btn-secondary">
                        <i class="fas fa-tools"></i> Maintenance
                        <?php if (isset($systemHealth['overall']) && $systemHealth['overall'] !== 'healthy'): ?>
                            <span class="badge bg-warning ms-1">!</span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Status -->
    <?php if (isset($systemHealth)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat"></i> System Health Status
                        <?php
                        $overallStatus = $systemHealth['overall'] ?? 'unknown';
                        $statusClass = $overallStatus === 'healthy' ? 'success' : ($overallStatus === 'warning' ? 'warning' : 'danger');
                        ?>
                        <span class="badge bg-<?= $statusClass ?> ms-2"><?= ucfirst($overallStatus) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $dbStatus = $systemHealth['database'] ?? 'unknown';
                                    $dbClass = $dbStatus === 'healthy' ? 'success' : 'danger';
                                    ?>
                                    <i class="fas fa-database fa-2x text-<?= $dbClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Database</h6>
                                    <span class="badge bg-<?= $dbClass ?>"><?= ucfirst($dbStatus) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $diskStatus = $systemHealth['disk_space'] ?? 'unknown';
                                    $diskClass = $diskStatus === 'healthy' ? 'success' : ($diskStatus === 'warning' ? 'warning' : 'danger');
                                    ?>
                                    <i class="fas fa-hdd fa-2x text-<?= $diskClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Disk Space</h6>
                                    <span class="badge bg-<?= $diskClass ?>"><?= ucfirst($diskStatus) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $overdueCount = $systemHealth['overdue_books'] ?? 0;
                                    $overdueClass = $overdueCount > 50 ? 'danger' : ($overdueCount > 20 ? 'warning' : 'success');
                                    ?>
                                    <i class="fas fa-exclamation-triangle fa-2x text-<?= $overdueClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Overdue Books</h6>
                                    <span class="badge bg-<?= $overdueClass ?>"><?= $overdueCount ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $lowStockCount = $systemHealth['low_stock_books'] ?? 0;
                                    $stockClass = $lowStockCount > 20 ? 'danger' : ($lowStockCount > 10 ? 'warning' : 'success');
                                    ?>
                                    <i class="fas fa-box-open fa-2x text-<?= $stockClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Low Stock</h6>
                                    <span class="badge bg-<?= $stockClass ?>"><?= $lowStockCount ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Transactions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Book</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentTransactions)): ?>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($transaction['borrowDate'])) ?></td>
                                            <td><?= htmlspecialchars($transaction['emailId']) ?></td>
                                            <td><?= htmlspecialchars($transaction['bookName']) ?></td>
                                            <td>
                                                <span class="badge bg-primary">Borrow</span>
                                            </td>
                                            <td>
                                                <?php if (empty($transaction['returnDate'])): ?>
                                                    <span class="badge bg-warning">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Returned</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No recent transactions
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star"></i> Popular Books
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($popularBooks)): ?>
                        <?php foreach ($popularBooks as $book): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($book['bookName']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($book['authorName']) ?></small>
                                </div>
                                <span class="badge bg-primary"><?= $book['borrow_count'] ?? 0 ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">
                            <i class="fas fa-info-circle"></i> No data available
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
