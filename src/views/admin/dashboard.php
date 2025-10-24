<?php
$pageTitle = 'Admin Dashboard';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: hidden;
    }

    /* Main Layout Container */
    .admin-layout {
        display: flex;
        min-height: 100vh;
        background: #f0f2f5;
    }

    /* Left Sidebar */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar.collapsed {
        width: 80px;
    }

    /* Sidebar Header */
    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .sidebar-logo i {
        font-size: 1.8rem;
        color: #667eea;
    }

    .sidebar-logo h2 {
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        white-space: nowrap;
    }

    .sidebar.collapsed .sidebar-logo h2 {
        display: none;
    }

    .sidebar-toggle {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Sidebar Navigation */
    .sidebar-nav {
        padding: 1rem 0;
    }

    .nav-section {
        margin-bottom: 1.5rem;
    }

    .nav-section-title {
        padding: 0.5rem 1.5rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.5);
        font-weight: 600;
    }

    .sidebar.collapsed .nav-section-title {
        display: none;
    }

    .nav-item {
        position: relative;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1.5rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .nav-link:hover,
    .nav-link.active {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .nav-link:hover::before,
    .nav-link.active::before {
        transform: scaleY(1);
    }

    .nav-link i {
        font-size: 1.25rem;
        width: 24px;
        text-align: center;
    }

    .nav-link span {
        white-space: nowrap;
    }

    .sidebar.collapsed .nav-link span {
        display: none;
    }

    .nav-badge {
        margin-left: auto;
        background: #ef4444;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .sidebar.collapsed .nav-badge {
        display: none;
    }

    /* Sidebar Footer */
    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.2);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .user-role {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
    }

    .sidebar.collapsed .user-info {
        display: none;
    }

    /* Main Content Area */
    .main-content {
        flex: 1;
        margin-left: 280px;
        transition: margin-left 0.3s ease;
        min-height: 100vh;
    }

    .sidebar.collapsed ~ .main-content {
        margin-left: 80px;
    }

    /* Top Header */
    .top-header {
        background: white;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-left h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .breadcrumb {
        display: flex;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.9rem;
    }

    .header-right {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .header-btn {
        background: white;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        color: #64748b;
    }

    .header-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .notification-btn {
        position: relative;
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    /* Dashboard Content */
    .dashboard-content {
        padding: 2rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
        background: var(--card-gradient);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card.blue { --card-gradient: linear-gradient(90deg, #667eea, #764ba2); }
    .stat-card.green { --card-gradient: linear-gradient(90deg, #10b981, #059669); }
    .stat-card.orange { --card-gradient: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.red { --card-gradient: linear-gradient(90deg, #ef4444, #dc2626); }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--card-gradient);
        color: white;
        font-size: 1.5rem;
    }

    .stat-info h3 {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }

    .stat-footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #10b981;
        font-size: 0.85rem;
        margin-top: 0.75rem;
    }

    .stat-footer.decrease {
        color: #ef4444;
    }

    /* Content Cards */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .content-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-title i {
        color: #667eea;
    }

    .view-all {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .view-all:hover {
        text-decoration: underline;
    }

    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #f1f5f9;
    }

    .activity-details strong {
        color: #1e293b;
    }

    .activity-time {
        color: #64748b;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-badge.active {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.returned {
        background: #d1fae5;
        color: #065f46;
    }

    /* Popular Books List */
    .book-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .book-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 10px;
    }

    .book-info strong {
        color: #1e293b;
        display: block;
        margin-bottom: 0.25rem;
    }

    .book-author {
        color: #64748b;
        font-size: 0.85rem;
    }

    .book-count {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        font-weight: 600;
    }

    /* System Health */
    .health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }

    .health-item {
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 10px;
        text-align: center;
    }

    .health-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.5rem;
    }

    .health-icon.success {
        background: #d1fae5;
        color: #065f46;
    }

    .health-icon.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .health-icon.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .health-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .health-value {
        font-weight: 600;
        font-size: 1.1rem;
        color: #1e293b;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.mobile-open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 0;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .mobile-overlay.show {
            display: block;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .top-header {
            padding: 1rem;
        }

        .header-left h1 {
            font-size: 1.25rem;
        }

        .dashboard-content {
            padding: 1rem;
        }
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #1e293b;
        cursor: pointer;
        padding: 0.5rem;
    }

    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: block;
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<!-- Mobile Overlay -->
<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Admin Layout -->
<div class="admin-layout">
    <!-- Left Sidebar -->
<?include APP_ROOT . '/views/admin/admin-navbar.php' ?>;

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
                <div class="breadcrumb">
                    <span>Home</span>
                    <span>/</span>
                    <span>Dashboard</span>
                </div>
            </div>
            <div class="header-right">
                <button class="header-btn notification-btn">
                    <i class="fas fa-bell"></i>
                    <?php if (!empty($notifications) && count(array_filter($notifications, fn($n) => !$n['isRead'])) > 0): ?>
                        <span class="notification-badge"><?= count(array_filter($notifications, fn($n) => !$n['isRead'])) ?></span>
                    <?php endif; ?>
                </button>
                <button class="header-btn">
                    <i class="fas fa-search"></i>
                </button>
                <a href="<?= BASE_URL ?>logout" class="header-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Total Books</h3>
                            <div class="stat-number"><?= $stats['totalBooks'] ?? 0 ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <i class="fas fa-arrow-up"></i>
                        <span>12% increase</span>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Active Users</h3>
                            <div class="stat-number"><?= $stats['activeUsers'] ?? 0 ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <i class="fas fa-arrow-up"></i>
                        <span>8% increase</span>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Borrowed Books</h3>
                            <div class="stat-number"><?= $stats['borrowedBooks'] ?? 0 ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-book-reader"></i>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <i class="fas fa-arrow-up"></i>
                        <span>5% increase</span>
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Overdue Books</h3>
                            <div class="stat-number"><?= $stats['overdueBooks'] ?? 0 ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-footer decrease">
                        <i class="fas fa-arrow-down"></i>
                        <span>3% decrease</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Popular Books -->
            <div class="content-grid">
                <!-- Recent Transactions -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history"></i>
                            Recent Transactions
                        </h2>
                        <a href="<?= BASE_URL ?>admin/transactions" class="view-all">View All →</a>
                    </div>
                    
                    <?php if (!empty($recentTransactions)): ?>
                        <div class="activity-list">
                            <?php foreach (array_slice($recentTransactions, 0, 5) as $transaction): ?>
                                <div class="activity-item">
                                    <div class="activity-details">
                                        <div>
                                            <strong><?= htmlspecialchars($transaction['emailId']) ?></strong>
                                            <span style="color: #64748b;"> borrowed </span>
                                            <strong><?= htmlspecialchars($transaction['bookName']) ?></strong>
                                        </div>
                                        <div class="activity-time">
                                            <i class="fas fa-clock"></i>
                                            <?= date('M j, Y', strtotime($transaction['borrowDate'])) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if (empty($transaction['returnDate'])): ?>
                                            <span class="status-badge active">Active</span>
                                        <?php else: ?>
                                            <span class="status-badge returned">Returned</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No recent transactions</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Popular Books -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-star"></i>
                            Popular Books
                        </h2>
                        <a href="<?= BASE_URL ?>admin/books" class="view-all">View All →</a>
                    </div>
                    
                    <?php if (!empty($popularBooks)): ?>
                        <div class="book-list">
                            <?php foreach (array_slice($popularBooks, 0, 5) as $book): ?>
                                <div class="book-item">
                                    <div class="book-info">
                                        <strong><?= htmlspecialchars($book['bookName']) ?></strong>
                                        <div class="book-author"><?= htmlspecialchars($book['authorName']) ?></div>
                                    </div>
                                    <div class="book-count"><?= $book['borrow_count'] ?? 0 ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Health -->
            <?php if (isset($systemHealth)): ?>
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-heartbeat"></i>
                        System Health
                    </h2>
                    <?php
                    $overallStatus = $systemHealth['overall'] ?? 'unknown';
                    $statusClass = $overallStatus === 'healthy' ? 'success' : ($overallStatus === 'warning' ? 'warning' : 'danger');
                    ?>
                    <span class="status-badge <?= $statusClass ?>"><?= ucfirst($overallStatus) ?></span>
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
                        <div class="health-label">Database</div>
                        <div class="health-value"><?= ucfirst($dbStatus) ?></div>
                    </div>

                    <?php
                    $diskStatus = $systemHealth['disk_space'] ?? 'unknown';
                    $diskClass = $diskStatus === 'healthy' ? 'success' : ($diskStatus === 'warning' ? 'warning' : 'danger');
                    ?>
                    <div class="health-item">
                        <div class="health-icon <?= $diskClass ?>">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div class="health-label">Disk Space</div>
                        <div class="health-value"><?= ucfirst($diskStatus) ?></div>
                    </div>

                    <?php
                    $overdueCount = $systemHealth['overdue_books'] ?? 0;
                    $overdueClass = $overdueCount > 50 ? 'danger' : ($overdueCount > 20 ? 'warning' : 'success');
                    ?>
                    <div class="health-item">
                        <div class="health-icon <?= $overdueClass ?>">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="health-label">Overdue Books</div>
                        <div class="health-value"><?= $overdueCount ?></div>
                    </div>

                    <?php
                    $lowStockCount = $systemHealth['low_stock_books'] ?? 0;
                    $stockClass = $lowStockCount > 20 ? 'danger' : ($lowStockCount > 10 ? 'warning' : 'success');
                    ?>
                    <div class="health-item">
                        <div class="health-icon <?= $stockClass ?>">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="health-label">Low Stock</div>
                        <div class="health-value"><?= $lowStockCount ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
    </main>
</div>

<script>
    // Toggle Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    // Toggle Mobile Sidebar
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('show');
    }

    // Load sidebar state from localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        }

        // Set active nav link based on current URL
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });
    });

    // Close mobile sidebar when clicking outside
    document.querySelector('.mobile-overlay')?.addEventListener('click', function() {
        toggleMobileSidebar();
    });
</script>