<?php
$pageTitle = 'Dashboard';
include APP_ROOT . '/views/layouts/header.php';

// Get user stats from controller
$userStats = $userStats ?? [
    'borrowed_books' => 0,
    'overdue_books' => 0,
    'total_fines' => 0,
    'max_books' => $_SESSION['userType'] === 'Faculty' ? 5 : 3
];

$recentActivity = $recentActivity ?? [];
?>

<style>
    /* Hide scrollbars globally */
    body {
        overflow-x: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    body::-webkit-scrollbar {
        display: none;
    }

    .dashboard-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        position: relative;
        overflow: hidden;
    }

    /* Animated background particles */
    .dashboard-wrapper::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 20s infinite ease-in-out;
    }

    .dashboard-wrapper::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 25s infinite ease-in-out reverse;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0) translateX(0) rotate(0deg);
        }

        33% {
            transform: translateY(-30px) translateX(30px) rotate(120deg);
        }

        66% {
            transform: translateY(30px) translateX(-30px) rotate(240deg);
        }
    }

    .dashboard-container {
        max-width: 1400px;
        width: 100%;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* Welcome Header */
    .welcome-header {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
        animation: slideInDown 0.6s ease-out;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .welcome-text h1 {
        font-size: 2rem;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .welcome-text p {
        color: #6b7280;
        font-size: 1rem;
        margin: 0;
        font-weight: 500;
    }

    .user-badge {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50px;
        font-weight: 800;
        font-size: 1rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.5);
        position: relative;
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
        animation-fill-mode: both;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .stat-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .stat-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .stat-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .stat-card:nth-child(4) {
        animation-delay: 0.4s;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--card-color-1), var(--card-color-2));
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }

    .stat-card.primary {
        --card-color-1: #667eea;
        --card-color-2: #764ba2;
    }

    .stat-card.warning {
        --card-color-1: #f59e0b;
        --card-color-2: #f97316;
    }

    .stat-card.danger {
        --card-color-1: #ef4444;
        --card-color-2: #dc2626;
    }

    .stat-card.info {
        --card-color-1: #3b82f6;
        --card-color-2: #06b6d4;
    }

    .stat-info h4 {
        font-size: 2.25rem;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .stat-info p {
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.65rem;
        transition: all 0.3s ease;
    }

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
        color: #667eea;
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(249, 115, 22, 0.12));
        color: #f59e0b;
    }

    .stat-card.danger .stat-icon {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(220, 38, 38, 0.12));
        color: #ef4444;
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(6, 182, 212, 0.12));
        color: #3b82f6;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    /* Section Headers */
    .section-header {
        margin: 2.5rem 0 1.5rem;
        padding-left: 0.5rem;
    }

    .section-header h3 {
        font-size: 1.5rem;
        font-weight: 900;
        color: white;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        display: inline-block;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .section-header h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: white;
        border-radius: 2px;
    }

    /* Quick Actions Grid */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .action-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem 1.75rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.5);
        position: relative;
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
        animation-fill-mode: both;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        min-height: 280px;
    }

    .action-card:nth-child(1) {
        animation-delay: 0.5s;
    }

    .action-card:nth-child(2) {
        animation-delay: 0.6s;
    }

    .action-card:nth-child(3) {
        animation-delay: 0.7s;
    }

    .action-card:nth-child(4) {
        animation-delay: 0.8s;
    }

    .action-card:nth-child(5) {
        animation-delay: 0.9s;
    }

    .action-card:nth-child(6) {
        animation-delay: 1.0s;
    }

    .action-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .action-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.25rem;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .action-card:hover .action-icon {
        transform: scale(1.1) rotate(-5deg);
    }

    /* Action Card Colors */
    .action-card:nth-child(1) .action-icon {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
        color: #667eea;
    }

    .action-card:nth-child(1) .action-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
    }

    .action-card:nth-child(2) .action-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(249, 115, 22, 0.12));
        color: #f59e0b;
    }

    .action-card:nth-child(2) .action-btn {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);
    }

    .action-card:nth-child(3) .action-icon {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.12), rgba(124, 58, 237, 0.12));
        color: #8b5cf6;
    }

    .action-card:nth-child(3) .action-btn {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.35);
    }

    .action-card:nth-child(4) .action-icon {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.12));
        color: #10b981;
    }

    .action-card:nth-child(4) .action-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
    }

    .action-card:nth-child(5) .action-icon {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(220, 38, 38, 0.12));
        color: #ef4444;
    }

    .action-card:nth-child(5) .action-btn {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35);
    }

    .action-card:nth-child(6) .action-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(6, 182, 212, 0.12));
        color: #3b82f6;
    }

    .action-card:nth-child(6) .action-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35);
    }

    .action-card h5 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.65rem;
        position: relative;
        z-index: 1;
    }

    .action-card p {
        color: #6b7280;
        margin-bottom: auto;
        padding-bottom: 1.5rem;
        font-size: 0.9rem;
        position: relative;
        z-index: 1;
        line-height: 1.5;
        font-weight: 500;
        flex-grow: 1;
    }

    .action-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        border: none;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        font-size: 0.9rem;
        margin-top: auto;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    /* Recent Activity Card */
    .activity-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        animation: slideInUp 0.6s ease-out 1.1s both;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .activity-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.06), rgba(118, 75, 162, 0.06));
        padding: 1.75rem 2rem;
        border-bottom: 1px solid rgba(102, 126, 234, 0.1);
    }

    .activity-header h5 {
        font-size: 1.4rem;
        font-weight: 900;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .activity-header i {
        color: #667eea;
        font-size: 1.5rem;
    }

    .activity-body {
        padding: 2rem;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 800;
        color: #6b7280;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 2px solid #e5e7eb;
    }

    .modern-table tbody td {
        padding: 1.25rem 1rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .modern-table tbody tr {
        transition: all 0.3s ease;
    }

    .modern-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
    }

    .empty-state {
        text-align: center;
        padding: 3.5rem 1rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3.5rem;
        margin-bottom: 1.25rem;
        opacity: 0.3;
        color: #667eea;
    }

    .empty-state p {
        font-size: 1.05rem;
        margin: 0;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .dashboard-container {
            width: 96%;
        }
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 1.5rem 1rem;
        }

        .welcome-header {
            display: none;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .action-card {
            min-height: auto;
        }

        .activity-header,
        .activity-body {
            padding: 1.5rem;
        }

        .table-responsive {
            margin: -1.5rem;
            padding: 1.5rem;
        }

        .modern-table {
            display: block;
            font-size: 0.85rem;
        }

        .modern-table thead {
            display: none;
        }

        .modern-table tbody {
            display: block;
        }

        .modern-table tbody tr {
            display: block;
            margin-bottom: 1.5rem;
            background: rgba(102, 126, 234, 0.03);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .modern-table tbody td {
            display: block;
            padding: 0.75rem 0;
            border: none;
            text-align: left;
        }

        .modern-table tbody td::before {
            content: attr(data-label);
            font-weight: 800;
            color: #667eea;
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    }

    @media (max-width: 480px) {
        .section-header h3 {
            font-size: 1.3rem;
        }

        .stat-info h4 {
            font-size: 1.9rem;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            font-size: 1.5rem;
        }

        .action-icon {
            width: 62px;
            height: 62px;
            font-size: 1.85rem;
        }
    }
</style>

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <div class="welcome-text">
                <h1>Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['userId']) ?>! 👋</h1>
                <p>Here's what's happening with your library account today</p>
            </div>
            <span class="user-badge"><?= $_SESSION['userType'] ?></span>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-info">
                    <h4><?= $userStats['borrowed_books'] ?></h4>
                    <p>Books Borrowed</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-info">
                    <h4><?= $userStats['overdue_books'] ?></h4>
                    <p>Overdue Books</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-info">
                    <h4>LKR<?= $userStats['total_fines'] ?></h4>
                    <p>Total Fines</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-info">
                    <h4><?= $userStats['max_books'] ?></h4>
                    <p>Max Books Allowed</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section-header">
            <h3>Quick Actions</h3>
        </div>

        <div class="actions-grid">
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h5>Browse Books</h5>
                <p>Search and browse available books in the library</p>
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/books"
                    class="action-btn">
                    <i class="fas fa-search"></i>
                    <span>Browse Now</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <h5>E-Resources Library</h5>
                <p>Access digital library resources and E-Books</p>
                <a href="<?= BASE_URL ?>e-resources" class="action-btn">
                    <i class="fas fa-file-pdf"></i>
                    <span>Browse All E-Resources</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <h5>Reserved Books</h5>
                <p>View your pending and approved book reservations</p>
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/reserved-books"
                    class="action-btn">
                    <i class="fas fa-bookmark"></i>
                    <span>View Reserved</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h5>Borrow History</h5>
                <p>Track all your past and current book borrowings</p>
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/borrow-history"
                    class="action-btn">
                    <i class="fas fa-history"></i>
                    <span>View History</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <h5>Returns</h5>
                <p>View all books you have returned</p>
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/returns"
                    class="action-btn">
                    <i class="fas fa-undo"></i>
                    <span>View Returns</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h5>Pay Fines</h5>
                <p>View and pay your outstanding library fines</p>
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/fines"
                    class="action-btn">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Pay Fines</span>
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h5>Edit Profile</h5>
                <p>Update your profile and account information</p>
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/profile"
                    class="action-btn">
                    <i class="fas fa-user-edit"></i>
                    <span>View Profile</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="section-header">
            <h3>Recent Activity</h3>
        </div>

        <div class="activity-card">
            <div class="activity-header">
                <h5>
                    <i class="fas fa-history"></i>
                    Your Library History
                </h5>
            </div>
            <div class="activity-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Book</th>
                                <th>Author</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentActivity)): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p>No recent activity found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td data-label="Date"><?= date('M d, Y', strtotime($activity['borrow_date'])) ?></td>
                                        <td data-label="Book"><?= htmlspecialchars($activity['title']) ?></td>
                                        <td data-label="Author"><?= htmlspecialchars($activity['author']) ?></td>
                                        <td data-label="Action"><?= $activity['return_date'] ? 'Returned' : 'Borrowed' ?></td>
                                        <td data-label="Status">
                                            <?php if ($activity['return_date']): ?>
                                                <span style="color: #10b981; font-weight: 700;">✓ Returned</span>
                                            <?php elseif (strtotime($activity['due_date']) < time()): ?>
                                                <span style="color: #ef4444; font-weight: 700;">⚠ Overdue</span>
                                            <?php else: ?>
                                                <span style="color: #3b82f6; font-weight: 700;">📖 Active</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Add Font Awesome if not already included
    if (!document.querySelector('link[href*="font-awesome"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
        document.head.appendChild(link);
    }
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>