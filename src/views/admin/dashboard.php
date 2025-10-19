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
                    <h5 class="card-title">Reports</h5>
                    <p class="card-text">Generate system reports</p>
                    <a href="<?= BASE_URL ?>admin/reports" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-cog fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Settings</h5>
                    <p class="card-text">System configuration</p>
                    <a href="<?= BASE_URL ?>admin/settings" class="btn btn-warning">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
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
