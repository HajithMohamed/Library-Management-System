<?php
$pageTitle = 'Admin Dashboard';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .modern-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        background: white;
    }
    
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card {
        border: none;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        padding: 1.5rem;
        color: white;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        pointer-events: none;
    }
    
    .stat-card.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
    }
    
    .stat-card.danger {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .stat-icon {
        font-size: 3rem;
        opacity: 0.3;
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .stat-content {
        position: relative;
        z-index: 1;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .action-card {
        border: none;
        border-radius: 16px;
        background: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .action-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }
    
    .action-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        transition: all 0.3s ease;
    }
    
    .action-card:hover .action-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .action-icon.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .action-icon.success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        color: white;
    }
    
    .action-icon.info {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #333;
    }
    
    .action-icon.warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #333;
    }
    
    .btn-modern {
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        text-transform: uppercase;
        font-size: 0.813rem;
        letter-spacing: 0.5px;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        position: relative;
        padding-left: 1rem;
    }
    
    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 70%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-modern thead th {
        background: #f7fafc;
        border: none;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.813rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem;
    }
    
    .table-modern tbody tr {
        transition: background 0.2s ease;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-modern tbody tr:hover {
        background: #f7fafc;
    }
    
    .table-modern tbody td {
        padding: 1rem;
        vertical-align: middle;
        border: none;
    }
    
    .badge-modern {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .card-header-modern {
        background: white;
        border-bottom: 2px solid #f7fafc;
        padding: 1.25rem 1.5rem;
        border-radius: 16px 16px 0 0;
    }
    
    .card-header-modern h5 {
        color: #2d3748;
        font-weight: 700;
        font-size: 1.125rem;
        margin: 0;
    }
    
    .modern-card .card-body {
        background: white;
    }
    
    .popular-book-item {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 0.75rem;
        background: #f7fafc;
        transition: all 0.2s ease;
    }
    
    .popular-book-item:hover {
        background: #edf2f7;
        transform: translateX(5px);
    }
    
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
    }
    
    .page-header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
    }
    
    .container {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
</style>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>
            <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
        </h1>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-5">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card primary">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['users']['total_users'] ?? 0 ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card success">
                <i class="fas fa-book stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['books']['total_books'] ?? 0 ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card warning">
                <i class="fas fa-book-reader stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['active_borrowings'] ?? 0 ?></div>
                    <div class="stat-label">Active Borrowings</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card danger">
                <i class="fas fa-exclamation-triangle stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['overdue_books'] ?? 0 ?></div>
                    <div class="stat-label">Overdue Books</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="section-title">Quick Actions</h3>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="action-card">
                <div class="card-body text-center p-4">
                    <div class="action-icon primary">
                        <i class="fas fa-book"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Manage Books</h5>
                    <p class="card-text text-muted mb-3">Add, edit, or remove books</p>
                    <a href="<?= BASE_URL ?>admin/books" class="btn btn-primary btn-modern">
                        <i class="fas fa-arrow-right me-1"></i> Manage
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="action-card">
                <div class="card-body text-center p-4">
                    <div class="action-icon success">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Manage Users</h5>
                    <p class="card-text text-muted mb-3">View and manage user accounts</p>
                    <a href="<?= BASE_URL ?>admin/users" class="btn btn-success btn-modern">
                        <i class="fas fa-arrow-right me-1"></i> Manage
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="action-card">
                <div class="card-body text-center p-4">
                    <div class="action-icon info">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Reports</h5>
                    <p class="card-text text-muted mb-3">Generate system reports</p>
                    <a href="<?= BASE_URL ?>admin/reports" class="btn btn-info btn-modern">
                        <i class="fas fa-arrow-right me-1"></i> Reports
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="action-card">
                <div class="card-body text-center p-4">
                    <div class="action-icon warning">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Settings</h5>
                    <p class="card-text text-muted mb-3">System configuration</p>
                    <a href="<?= BASE_URL ?>admin/settings" class="btn btn-warning btn-modern">
                        <i class="fas fa-arrow-right me-1"></i> Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions & Popular Books -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5>
                        <i class="fas fa-history me-2"></i> Recent Transactions
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
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
                                            <td class="fw-semibold"><?= date('M j, Y', strtotime($transaction['borrowDate'])) ?></td>
                                            <td><?= htmlspecialchars($transaction['emailId']) ?></td>
                                            <td><?= htmlspecialchars($transaction['bookName']) ?></td>
                                            <td>
                                                <span class="badge-modern bg-primary">Borrow</span>
                                            </td>
                                            <td>
                                                <?php if (empty($transaction['returnDate'])): ?>
                                                    <span class="badge-modern bg-warning text-dark">Active</span>
                                                <?php else: ?>
                                                    <span class="badge-modern bg-success">Returned</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-2"></i> No recent transactions
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5>
                        <i class="fas fa-star me-2"></i> Popular Books
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($popularBooks)): ?>
                        <?php foreach ($popularBooks as $book): ?>
                            <div class="popular-book-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($book['bookName']) ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($book['authorName']) ?>
                                        </small>
                                    </div>
                                    <span class="badge-modern bg-primary ms-3"><?= $book['borrow_count'] ?? 0 ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-info-circle me-2"></i> No data available
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>