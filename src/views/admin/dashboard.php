<?php
$pageTitle = 'Admin Dashboard';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .dashboard-container {
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    .dashboard-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }

    .dashboard-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .dashboard-subtitle {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    /* Enhanced Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--card-color-1), var(--card-color-2));
    }

    .stat-card.blue {
        --card-color-1: #667eea;
        --card-color-2: #764ba2;
    }

    .stat-card.green {
        --card-color-1: #10b981;
        --card-color-2: #059669;
    }

    .stat-card.orange {
        --card-color-1: #f59e0b;
        --card-color-2: #d97706;
    }

    .stat-card.red {
        --card-color-1: #ef4444;
        --card-color-2: #dc2626;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .stat-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--card-color-1), var(--card-color-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .stat-icon {
        font-size: 3rem;
        opacity: 0.15;
        background: linear-gradient(135deg, var(--card-color-1), var(--card-color-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Quick Actions - Simplified */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .action-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
        display: block;
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--action-color);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .action-card:hover {
        transform: translateX(8px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .action-card:hover::before {
        transform: scaleY(1);
    }

    .action-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--action-color);
        color: white;
        font-size: 1.75rem;
        margin-bottom: 1.5rem;
    }

    .action-card.books { --action-color: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .action-card.users { --action-color: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .action-card.requests { --action-color: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .action-card.fines { --action-color: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .action-card.reports { --action-color: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
    .action-card.notifications { --action-color: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
    .action-card.maintenance { --action-color: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); }

    .action-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .action-description {
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 1rem;
    }

    .action-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: var(--action-color);
        color: white;
    }

    /* System Health */
    .health-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .health-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f3f4f6;
    }

    .health-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .health-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        background: #f9fafb;
        transition: all 0.3s ease;
    }

    .health-item:hover {
        background: #f3f4f6;
        transform: scale(1.02);
    }

    .health-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .health-icon.success { background: #d1fae5; color: #065f46; }
    .health-icon.warning { background: #fef3c7; color: #92400e; }
    .health-icon.danger { background: #fee2e2; color: #991b1b; }

    .health-info h6 {
        margin: 0 0 0.25rem 0;
        font-size: 0.95rem;
        color: #6b7280;
    }

    /* Recent Activity */
    .activity-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f3f4f6;
    }

    .activity-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .activity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.3s ease;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background: #f9fafb;
        border-radius: 8px;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .stats-grid,
        .actions-grid,
        .health-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-title {
            font-size: 1.75rem;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="fas fa-tachometer-alt"></i>
            Admin Dashboard
        </h1>
        <p class="dashboard-subtitle">Welcome back! Here's what's happening in your library today.</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-content">
                <div>
                    <div class="stat-number"><?= $stats['users']['total_users'] ?? 0 ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-content">
                <div>
                    <div class="stat-number"><?= $stats['books']['total_books'] ?? 0 ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-content">
                <div>
                    <div class="stat-number"><?= $stats['active_borrowings'] ?? 0 ?></div>
                    <div class="stat-label">Active Borrowings</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-content">
                <div>
                    <div class="stat-number"><?= $stats['overdue_books'] ?? 0 ?></div>
                    <div class="stat-label">Overdue Books</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions - Streamlined -->
    <div class="actions-grid">
        <a href="<?= BASE_URL ?>admin/books" class="action-card books">
            <div class="action-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-book"></i>
            </div>
            <h3 class="action-title">Books</h3>
            <p class="action-description">Manage your library collection</p>
            <span class="action-badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-arrow-right"></i> Manage
            </span>
        </a>

        <a href="<?= BASE_URL ?>admin/users" class="action-card users">
            <div class="action-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="action-title">Users</h3>
            <p class="action-description">View and manage user accounts</p>
            <span class="action-badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-arrow-right"></i> Manage
            </span>
        </a>

        <a href="<?= BASE_URL ?>admin/borrow-requests" class="action-card requests">
            <div class="action-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h3 class="action-title">Borrow Requests</h3>
            <p class="action-description">Approve or reject book requests</p>
            <span class="action-badge" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-arrow-right"></i> Manage
            </span>
        </a>

        <a href="<?= BASE_URL ?>admin/fines" class="action-card fines">
            <div class="action-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <h3 class="action-title">Fines</h3>
            <p class="action-description">Monitor and manage overdue fines</p>
            <span class="action-badge" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="fas fa-arrow-right"></i> Manage
            </span>
        </a>

        <a href="<?= BASE_URL ?>admin/reports" class="action-card reports">
            <div class="action-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3 class="action-title">Reports</h3>
            <p class="action-description">View analytics and statistics</p>
            <span class="action-badge" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="fas fa-arrow-right"></i> View
            </span>
        </a>

        <a href="<?= BASE_URL ?>admin/notifications" class="action-card notifications">
            <div class="action-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="fas fa-bell"></i>
            </div>
            <h3 class="action-title">Notifications</h3>
            <p class="action-description">View system notifications</p>
            <span class="action-badge" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <?php if (!empty($notifications) && count(array_filter($notifications, fn($n) => !$n['isRead'])) > 0): ?>
                    <i class="fas fa-bell"></i> <?= count(array_filter($notifications, fn($n) => !$n['isRead'])) ?> New
                <?php else: ?>
                    <i class="fas fa-arrow-right"></i> View
                <?php endif; ?>
            </span>
        </a>

        <a href="<?= BASE_URL ?>admin/maintenance" class="action-card maintenance">
            <div class="action-icon" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                <i class="fas fa-tools"></i>
            </div>
            <h3 class="action-title">Maintenance</h3>
            <p class="action-description">System health and backups</p>
            <span class="action-badge" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                <?php if (isset($systemHealth['overall']) && $systemHealth['overall'] !== 'healthy'): ?>
                    <i class="fas fa-exclamation-triangle"></i> Warning
                <?php else: ?>
                    <i class="fas fa-arrow-right"></i> View
                <?php endif; ?>
            </span>
        </a>
    </div>

    <!-- System Health -->
    <?php if (isset($systemHealth)): ?>
    <div class="health-card">
        <div class="health-header">
            <h2 class="health-title">
                <i class="fas fa-heartbeat"></i>
                System Health
            </h2>
            <?php
            $overallStatus = $systemHealth['overall'] ?? 'unknown';
            $statusClass = $overallStatus === 'healthy' ? 'success' : ($overallStatus === 'warning' ? 'warning' : 'danger');
            ?>
            <span class="badge bg-<?= $statusClass ?> p-2"><?= ucfirst($overallStatus) ?></span>
        </div>
        
        <div class="health-grid">
            <?php
            $dbStatus = $systemHealth['database'] ?? 'unknown';
            $dbClass = $dbStatus === 'healthy' ? 'success' : 'danger';
            ?>
            <div class="health-item">
                <div class="health-icon <?= $dbClass ?>">
                    <i class="fas fa-database"></i>
                </div>
                <div class="health-info">
                    <h6>Database</h6>
                    <span class="badge bg-<?= $dbClass ?>"><?= ucfirst($dbStatus) ?></span>
                </div>
            </div>

            <?php
            $diskStatus = $systemHealth['disk_space'] ?? 'unknown';
            $diskClass = $diskStatus === 'healthy' ? 'success' : ($diskStatus === 'warning' ? 'warning' : 'danger');
            ?>
            <div class="health-item">
                <div class="health-icon <?= $diskClass ?>">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="health-info">
                    <h6>Disk Space</h6>
                    <span class="badge bg-<?= $diskClass ?>"><?= ucfirst($diskStatus) ?></span>
                </div>
            </div>

            <?php
            $overdueCount = $systemHealth['overdue_books'] ?? 0;
            $overdueClass = $overdueCount > 50 ? 'danger' : ($overdueCount > 20 ? 'warning' : 'success');
            ?>
            <div class="health-item">
                <div class="health-icon <?= $overdueClass ?>">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="health-info">
                    <h6>Overdue Books</h6>
                    <span class="badge bg-<?= $overdueClass ?>"><?= $overdueCount ?></span>
                </div>
            </div>

            <?php
            $lowStockCount = $systemHealth['low_stock_books'] ?? 0;
            $stockClass = $lowStockCount > 20 ? 'danger' : ($lowStockCount > 10 ? 'warning' : 'success');
            ?>
            <div class="health-item">
                <div class="health-icon <?= $stockClass ?>">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="health-info">
                    <h6>Low Stock</h6>
                    <span class="badge bg-<?= $stockClass ?>"><?= $lowStockCount ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Activity & Popular Books -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="activity-card">
                <div class="activity-header">
                    <h2 class="activity-title">
                        <i class="fas fa-history"></i>
                        Recent Transactions
                    </h2>
                </div>
                <?php if (!empty($recentTransactions)): ?>
                    <?php foreach (array_slice($recentTransactions, 0, 5) as $transaction): ?>
                        <div class="activity-item">
                            <div>
                                <strong><?= htmlspecialchars($transaction['emailId']) ?></strong>
                                <span class="text-muted mx-2">borrowed</span>
                                <strong><?= htmlspecialchars($transaction['bookName']) ?></strong>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-clock"></i> <?= date('M j, Y', strtotime($transaction['borrowDate'])) ?>
                                </div>
                            </div>
                            <div>
                                <?php if (empty($transaction['returnDate'])): ?>
                                    <span class="badge bg-warning">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Returned</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>No recent transactions</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="activity-card">
                <div class="activity-header">
                    <h2 class="activity-title">
                        <i class="fas fa-star"></i>
                        Popular Books
                    </h2>
                </div>
                <?php if (!empty($popularBooks)): ?>
                    <?php foreach (array_slice($popularBooks, 0, 5) as $book): ?>
                        <div class="activity-item">
                            <div>
                                <strong><?= htmlspecialchars($book['bookName']) ?></strong>
                                <div class="text-muted small"><?= htmlspecialchars($book['authorName']) ?></div>
                            </div>
                            <span class="badge bg-primary"><?= $book['borrow_count'] ?? 0 ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>No data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
