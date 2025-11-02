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
    .dashboard-container {
        padding: 2rem 0;
        animation: fadeIn 0.6s ease-out;
        position: relative;
        z-index: 1;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    /* Welcome Header */
    .welcome-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        animation: slideInDown 0.6s ease-out;
        position: relative;
        z-index: 10;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .welcome-text h1 {
        font-size: clamp(1.5rem, 3vw, 2.5rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .welcome-text p {
        color: #6b7280;
        font-size: 1.05rem;
        margin: 0;
    }
    
    .user-badge {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
        animation-fill-mode: both;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
    
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
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        border-color: rgba(102, 126, 234, 0.2);
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
        font-size: 2rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    
    .stat-info p {
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        transition: all 0.3s ease;
    }
    
    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: #667eea;
    }
    
    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        color: #f59e0b;
    }
    
    .stat-card.danger .stat-icon {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
        color: #ef4444;
    }
    
    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
        color: #3b82f6;
    }
    
    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    /* Section Headers */
    .section-header {
        margin: 3rem 0 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .section-header h3 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
    }
    
    .section-header::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.3), transparent);
    }
    
    /* Quick Actions Grid */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .action-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid #f3f4f6;
        position: relative;
        overflow: hidden;
        animation: slideInUp 0.6s ease-out;
        animation-fill-mode: both;
    }
    
    .action-card:nth-child(1) { animation-delay: 0.5s; }
    .action-card:nth-child(2) { animation-delay: 0.6s; }
    .action-card:nth-child(3) { animation-delay: 0.7s; }
    .action-card:nth-child(4) { animation-delay: 0.8s; }
    .action-card:nth-child(5) { animation-delay: 0.9s; }
    .action-card:nth-child(6) { animation-delay: 1.0s; }
    
    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .action-card:hover::before {
        opacity: 1;
    }
    
    .action-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }
    
    .action-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }
    
    .action-card:hover .action-icon {
        transform: scale(1.1) rotate(-5deg);
    }
    
    /* Action Card 1 - Browse Books */
    .action-card:nth-child(1) .action-icon {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: #667eea;
    }
    
    .action-card:nth-child(1) .action-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    /* Action Card 2 - Reserved Books */
    .action-card:nth-child(2) .action-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        color: #f59e0b;
    }
    
    .action-card:nth-child(2) .action-btn {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
    }
    
    /* Action Card 3 - Borrow History */
    .action-card:nth-child(3) .action-icon {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.1));
        color: #8b5cf6;
    }
    
    .action-card:nth-child(3) .action-btn {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
    }
    
    /* Action Card 4 - Return Books */
    .action-card:nth-child(4) .action-icon {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        color: #10b981;
    }
    
    .action-card:nth-child(4) .action-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }
    
    /* Action Card 5 - Pay Fines */
    .action-card:nth-child(5) .action-icon {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
        color: #ef4444;
    }
    
    .action-card:nth-child(5) .action-btn {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }
    
    /* Action Card 6 - Edit Profile */
    .action-card:nth-child(6) .action-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
        color: #3b82f6;
    }
    
    .action-card:nth-child(6) .action-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
    }
    
    .action-card h5 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.75rem;
        position: relative;
        z-index: 1;
    }
    
    .action-card p {
        color: #6b7280;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        position: relative;
        z-index: 1;
    }
    
    .action-btn {
        padding: 0.75rem 1.75rem;
        border-radius: 12px;
        border: none;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }
    
    /* Recent Activity Table */
    .activity-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        animation: slideInUp 0.6s ease-out 1.1s both;
    }
    
    .activity-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        padding: 1.75rem 2rem;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .activity-header h5 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .activity-header i {
        color: #667eea;
    }
    
    .activity-body {
        padding: 2rem;
    }
    
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .modern-table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        color: #6b7280;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .modern-table tbody td {
        padding: 1.25rem 1rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .modern-table tbody tr {
        transition: all 0.3s ease;
    }
    
    .modern-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.03);
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #9ca3af;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .empty-state p {
        font-size: 1.05rem;
        margin: 0;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .welcome-header {
            display: none;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .actions-grid {
            grid-template-columns: 1fr;
        }
        
        .activity-header,
        .activity-body {
            padding: 1.25rem;
        }
        
        .modern-table {
            font-size: 0.875rem;
        }
        
        .modern-table thead th,
        .modern-table tbody td {
            padding: 0.75rem 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .section-header h3 {
            font-size: 1.5rem;
        }
        
        .stat-info h4 {
            font-size: 1.5rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }
    }
</style>

<div class="dashboard-container">
    <div class="container">
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
                    <h4>₹<?= $userStats['total_fines'] ?></h4>
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
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/books" class="action-btn">
                    <i class="fas fa-search"></i>
                    <span>Browse Now</span>
                </a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <h5>Reserved Books</h5>
                <p>View your pending and approved book reservations</p>
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/reserved-books" class="action-btn">
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
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/borrow-history" class="action-btn">
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
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/returns" class="action-btn">
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
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/fines" class="action-btn">
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
                <a href="<?= BASE_URL ?><?= $_SESSION['userType'] === 'Faculty' ? 'faculty' : 'user' ?>/profile" class="action-btn">
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
                                    <td><?= date('M d, Y', strtotime($activity['borrow_date'])) ?></td>
                                    <td><?= htmlspecialchars($activity['title']) ?></td>
                                    <td><?= htmlspecialchars($activity['author']) ?></td>
                                    <td><?= $activity['return_date'] ? 'Returned' : 'Borrowed' ?></td>
                                    <td>
                                        <?php if ($activity['return_date']): ?>
                                            <span style="color: #10b981; font-weight: 600;">✓ Returned</span>
                                        <?php elseif (strtotime($activity['due_date']) < time()): ?>
                                            <span style="color: #ef4444; font-weight: 600;">⚠ Overdue</span>
                                        <?php else: ?>
                                            <span style="color: #3b82f6; font-weight: 600;">📖 Active</span>
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

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>