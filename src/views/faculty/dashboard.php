<?php
// Ensure user is authenticated
if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
    header('Location: /login');
    exit();
}

include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    .dashboard-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        min-height: 100vh;
        padding: 50px 20px;
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }
    
    .dashboard-content {
        max-width: 1600px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
        width: 95%;
    }
    
    .dashboard-header {
        color: white;
        margin-bottom: 50px;
        text-align: center;
        animation: fadeInDown 0.8s ease;
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .dashboard-header h1 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 15px;
        text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
        letter-spacing: -0.5px;
    }
    
    .dashboard-header h1 i {
        display: inline-block;
        animation: wave 2s ease-in-out infinite;
    }
    
    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(20deg); }
        75% { transform: rotate(-20deg); }
    }
    
    .dashboard-header p {
        font-size: 1.2rem;
        opacity: 0.95;
        font-weight: 300;
        letter-spacing: 0.5px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
        animation: fadeInUp 0.8s ease 0.2s both;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        padding: 35px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    .stat-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: 0 25px 70px rgba(0,0,0,0.25);
    }
    
    .stat-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 25px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    
    .stat-icon i {
        font-size: 2rem;
    }
    
    .stat-card:hover .stat-icon {
        transform: rotate(10deg) scale(1.1);
    }
    
    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 10px 25px rgba(245, 87, 108, 0.4);
    }
    
    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
    }
    
    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        box-shadow: 0 10px 25px rgba(67, 233, 123, 0.4);
    }
    
    .stat-label {
        font-size: 0.95rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        font-weight: 700;
    }
    
    .stat-value {
        font-size: 3rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
    }
    
    .stat-card.warning .stat-value {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .stat-card.success .stat-value {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .stat-card.info .stat-value {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .quick-actions {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        padding: 40px;
        margin-bottom: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        border: 1px solid rgba(255, 255, 255, 0.5);
        animation: fadeInUp 0.8s ease 0.3s both;
    }
    
    .quick-actions h2 {
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 25px;
    }
    
    .quick-actions h2 i {
        margin-right: 8px;
    }
    
    .search-box {
        position: relative;
        margin-bottom: 25px;
    }
    
    .search-box input {
        width: 100%;
        padding: 18px 60px 18px 25px;
        border: 2px solid #e5e7eb;
        border-radius: 20px;
        font-size: 1.05rem;
        transition: all 0.3s ease;
        background: white;
        font-weight: 500;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 5px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    
    .search-box i {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.3rem;
        color: #9ca3af;
        transition: color 0.3s ease;
    }
    
    .search-box input:focus + i {
        color: #667eea;
    }
    
    .action-buttons {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
    }
    
    .action-btn {
        padding: 16px 32px;
        border-radius: 16px;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        position: relative;
        overflow: hidden;
    }
    
    .action-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .action-btn:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .action-btn i {
        position: relative;
        z-index: 1;
    }
    
    .action-btn span {
        position: relative;
        z-index: 1;
    }
    
    .action-btn.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .action-btn.secondary {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 8px 20px rgba(240, 147, 251, 0.4);
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.35);
    }
    
    .action-btn:active {
        transform: translateY(-1px);
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 35px;
        margin-bottom: 40px;
        animation: fadeInUp 0.8s ease 0.4s both;
    }
    
    .content-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        padding: 35px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        border: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
    }
    
    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 70px rgba(0,0,0,0.2);
    }
    
    .content-card h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .content-card h3 i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 1.6rem;
    }
    
    .book-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .book-item {
        padding: 18px;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.3s ease;
        border-radius: 12px;
        margin-bottom: 5px;
    }
    
    .book-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        transform: translateX(5px);
        border-color: transparent;
    }
    
    .book-item:last-child {
        border-bottom: none;
    }
    
    .book-title {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
        font-size: 1.05rem;
    }
    
    .book-meta {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    .notification-item {
        padding: 18px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-radius: 12px;
        margin-bottom: 12px;
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }
    
    .notification-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
    }
    
    .notification-message {
        color: #1f2937;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .notification-time {
        font-size: 0.85rem;
        color: #9ca3af;
        font-weight: 500;
    }
    
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #9ca3af;
    }
    
    .empty-state i {
        font-size: 3.5rem;
        margin-bottom: 20px;
        opacity: 0.3;
        display: block;
        color: #d1d5db;
    }
    
    .empty-state p {
        font-size: 1.1rem;
        font-weight: 600;
        color: #9ca3af;
    }
    
    .full-width-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        border: 1px solid rgba(255, 255, 255, 0.5);
        animation: fadeInUp 0.8s ease 0.5s both;
    }
    
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }
    
    .modern-table thead tr {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .modern-table thead th {
        padding: 18px 20px;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }
    
    .modern-table thead th:first-child {
        border-radius: 12px 0 0 12px;
    }
    
    .modern-table thead th:last-child {
        border-radius: 0 12px 12px 0;
    }
    
    .modern-table tbody tr {
        background: white;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .modern-table tbody tr:hover {
        transform: scale(1.01);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
    }
    
    .modern-table tbody td {
        padding: 18px 20px;
        color: #1f2937;
        font-weight: 500;
    }
    
    .modern-table tbody tr td:first-child {
        border-radius: 12px 0 0 12px;
    }
    
    .modern-table tbody tr td:last-child {
        border-radius: 0 12px 12px 0;
    }
    
    .badge {
        display: inline-block;
        padding: 8px 18px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge.success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(67, 233, 123, 0.3);
    }
    
    .badge.pending {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(250, 112, 154, 0.3);
    }
    
    .view-all-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #667eea;
        font-weight: 700;
        font-size: 1.05rem;
        text-decoration: none;
        transition: all 0.3s ease;
        padding: 12px;
        border-radius: 12px;
    }
    
    .view-all-link:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .dashboard-header h1 {
            font-size: 2rem;
        }
        
        .dashboard-header p {
            font-size: 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .content-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .action-btn {
            width: 100%;
            justify-content: center;
        }
        
        .modern-table {
            font-size: 0.9rem;
        }
        
        .modern-table thead th,
        .modern-table tbody td {
            padding: 12px 10px;
        }
    }
    
    @media (max-width: 480px) {
        .dashboard-container {
            padding: 30px 15px;
        }
        
        .stat-card,
        .quick-actions,
        .content-card,
        .full-width-card {
            padding: 25px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-hand-wave"></i> Welcome Back, <?= htmlspecialchars($user['username'] ?? 'Faculty Member') ?>!</h1>
            <p>Here's your library overview for today</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-label">Books Borrowed</div>
                <div class="stat-value"><?= count($borrowedBooks ?? []) ?></div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-label">Books Overdue</div>
                <div class="stat-value"><?= count($overdueBooks ?? []) ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div class="stat-label">Reserved Books</div>
                <div class="stat-value"><?= count($reservedBooks ?? []) ?></div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-label">Notifications</div>
                <div class="stat-value"><?= count($notifications ?? []) ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2><i class="fas fa-rocket"></i> Quick Actions</h2>
            <div class="search-box">
                <input type="text" 
                       id="quickSearch" 
                       placeholder="Search for books by title, author, ISBN..." 
                       onkeypress="handleQuickSearch(event)">
                <i class="fas fa-search"></i>
            </div>
            <div class="action-buttons">
                <a href="/faculty/books" class="action-btn primary">
                    <i class="fas fa-book"></i>
                    <span>Browse Books</span>
                </a>
                <a href="/faculty/borrow-history" class="action-btn primary">
                    <i class="fas fa-history"></i>
                    <span>Borrow History</span>
                </a>
                <a href="/faculty/book-request" class="action-btn secondary">
                    <i class="fas fa-plus-circle"></i>
                    <span>Request New Book</span>
                </a>
                <a href="/faculty/return" class="action-btn secondary">
                    <i class="fas fa-undo"></i>
                    <span>Return Books</span>
                </a>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Borrowed Books -->
            <div class="content-card">
                <h3>
                    <i class="fas fa-book-open"></i>
                    Currently Borrowed
                </h3>
                <?php if (!empty($borrowedBooks)): ?>
                    <ul class="book-list">
                        <?php foreach (array_slice($borrowedBooks, 0, 5) as $book): ?>
                            <li class="book-item">
                                <div class="book-title">
                                    <?= htmlspecialchars($book['title'] ?? $book['bookName'] ?? 'Unknown') ?>
                                </div>
                                <div class="book-meta">
                                    Due: <?= htmlspecialchars($book['dueDate'] ?? $book['returnDate'] ?? 'N/A') ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($borrowedBooks) > 5): ?>
                        <a href="/faculty/return" class="view-all-link">
                            View All (<?= count($borrowedBooks) ?>) →
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <p>No borrowed books</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="content-card">
                <h3>
                    <i class="fas fa-bell"></i>
                    Recent Notifications
                </h3>
                <?php if (!empty($notifications)): ?>
                    <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                        <div class="notification-item">
                            <div class="notification-message">
                                <?= htmlspecialchars($notification['message'] ?? $notification['content'] ?? 'No message') ?>
                            </div>
                            <div class="notification-time">
                                <?= htmlspecialchars($notification['createdAt'] ?? $notification['created_at'] ?? date('Y-m-d H:i:s')) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($notifications) > 5): ?>
                        <a href="/faculty/notifications" class="view-all-link">
                            View All (<?= count($notifications) ?>) →
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>No new notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="full-width-card">
            <h2 style="font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 25px;">
                <i class="fas fa-history" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                Recent Transaction History
            </h2>
            <?php if (!empty($transactionHistory)): ?>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Book Title</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($transactionHistory, 0, 10) as $transaction): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($transaction['transactionId'] ?? $transaction['tid'] ?? 'N/A') ?></strong>
                                </td>
                                <td><?= htmlspecialchars($transaction['title'] ?? $transaction['bookName'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($transaction['borrowDate'] ?? $transaction['issueDate'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($transaction['returnDate'] ?? 'Not Returned') ?></td>
                                <td>
                                    <?php if (empty($transaction['returnDate'])): ?>
                                        <span class="badge pending">Borrowed</span>
                                    <?php else: ?>
                                        <span class="badge success">Returned</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($transactionHistory) > 10): ?>
                    <a href="/faculty/borrow-history" class="view-all-link">
                        View All Transactions →
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>No transaction history</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function handleQuickSearch(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const searchTerm = document.getElementById('quickSearch').value.trim();
        if (searchTerm) {
            window.location.href = '/faculty/books?q=' + encodeURIComponent(searchTerm);
        }
    }
}

// Add Font Awesome if not already included
if (!document.querySelector('link[href*="font-awesome"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
    document.head.appendChild(link);
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>