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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 50px 20px;
        position: relative;
        overflow: hidden;
    }

    .dashboard-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }

    /* Animated background particles */
    .dashboard-container::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background-image:
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        animation: float 20s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .dashboard-header {
        color: white;
        margin-bottom: 50px;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .dashboard-header h1 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 15px;
        text-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        letter-spacing: -0.5px;
        animation: fadeInDown 0.6s ease-out;
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

    .dashboard-header p {
        font-size: 1.2rem;
        opacity: 0.95;
        font-weight: 300;
        animation: fadeIn 0.8s ease-out 0.2s both;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 0.95;
        }
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
        position: relative;
        z-index: 1;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 35px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
        animation: slideUp 0.6s ease-out;
        animation-fill-mode: both;
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

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.15), transparent);
        transform: rotate(45deg);
        transition: all 0.6s ease;
    }

    .stat-card:hover::before {
        animation: shimmer 1.5s ease;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }

        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    .stat-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
    }

    .stat-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 25px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
        position: relative;
        z-index: 1;
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 10px 30px rgba(245, 87, 108, 0.5);
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        box-shadow: 0 10px 30px rgba(79, 172, 254, 0.5);
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        box-shadow: 0 10px 30px rgba(67, 233, 123, 0.5);
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

    .quick-actions {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        position: relative;
        z-index: 1;
        border: 1px solid rgba(255, 255, 255, 0.3);
        animation: slideUp 0.6s ease-out 0.5s both;
    }

    .quick-actions h2 {
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 30px;
    }

    .search-box {
        position: relative;
        margin-bottom: 30px;
    }

    .search-box input {
        width: 100%;
        padding: 18px 60px 18px 25px;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        font-size: 1.05rem;
        transition: all 0.3s ease;
        background: #f9fafb;
        font-weight: 500;
    }

    .search-box input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 5px rgba(102, 126, 234, 0.12);
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

    .search-box input:focus+i {
        color: #667eea;
    }

    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .action-btn {
        padding: 16px 28px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: white;
        border: none;
        cursor: pointer;
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
        background: rgba(255, 255, 255, 0.2);
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
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .action-btn.secondary {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 8px 25px rgba(240, 147, 251, 0.4);
    }

    .action-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 35px;
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }

    .content-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        animation: slideUp 0.6s ease-out;
        animation-fill-mode: both;
    }

    .content-grid .content-card:nth-child(1) {
        animation-delay: 0.6s;
    }

    .content-grid .content-card:nth-child(2) {
        animation-delay: 0.7s;
    }

    .content-card h3 {
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 30px;
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
        padding: 20px;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.3s ease;
        border-radius: 12px;
        margin-bottom: 10px;
    }

    .book-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        transform: translateX(8px);
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
        padding: 20px;
        border-left: 5px solid #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        border-radius: 12px;
        margin-bottom: 18px;
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
        transform: translateX(8px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
    }

    .notification-message {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
        font-size: 1.05rem;
    }

    .notification-time {
        font-size: 0.85rem;
        color: #9ca3af;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.4;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .empty-state p {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .full-width-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        overflow-x: auto;
        position: relative;
        z-index: 1;
        border: 1px solid rgba(255, 255, 255, 0.3);
        animation: slideUp 0.6s ease-out 0.8s both;
    }

    .full-width-card h2 {
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 30px;
    }

    .full-width-card h2 i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .modern-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 18px 20px;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }

    .modern-table thead th:first-child {
        border-radius: 14px 0 0 14px;
    }

    .modern-table thead th:last-child {
        border-radius: 0 14px 14px 0;
    }

    .modern-table tbody tr {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        transition: all 0.3s ease;
    }

    .modern-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.12) 0%, rgba(118, 75, 162, 0.12) 100%);
        transform: scale(1.01);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
    }

    .modern-table tbody td {
        padding: 20px;
        border-top: none;
        font-weight: 600;
        color: #374151;
    }

    .modern-table tbody tr td:first-child {
        border-radius: 14px 0 0 14px;
    }

    .modern-table tbody tr td:last-child {
        border-radius: 0 14px 14px 0;
    }

    .badge {
        display: inline-block;
        padding: 8px 18px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .badge.success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(67, 233, 123, 0.4);
    }

    .badge.pending {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(250, 112, 154, 0.4);
    }

    .view-all-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #667eea;
        font-weight: 700;
        font-size: 1.05rem;
        text-decoration: none;
        padding: 12px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .view-all-link:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        transform: translateY(-2px);
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-header h1 {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            grid-template-columns: 1fr;
        }

        .modern-table {
            font-size: 0.85rem;
        }

        .modern-table thead th,
        .modern-table tbody td {
            padding: 12px 15px;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-wrapper">
        <div class="dashboard-header">
            <h1><i class="fas fa-hand-wave" style="margin-right: 10px;"></i> Welcome Back,
                <?= htmlspecialchars($user['username'] ?? 'Faculty Member') ?>!</h1>
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
                    <i class="fas fa-exclamation-triangle"></i>
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
            <h2><i class="fas fa-rocket" style="margin-right: 8px;"></i> Quick Actions</h2>
            <div class="search-box">
                <input type="text" id="quickSearch" placeholder="Search for books by title, author, ISBN..."
                    onkeypress="handleQuickSearch(event)">
                <i class="fas fa-search"></i>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>faculty/books" class="action-btn primary">
                    <i class="fas fa-book"></i>
                    <span>Browse Books</span>
                </a>
                <a href="<?= BASE_URL ?>e-resources" class="action-btn primary">
                    <i class="fas fa-file-pdf"></i>
                    <span>E-Resources</span>
                </a>
                <a href="<?= BASE_URL ?>faculty/borrow-history" class="action-btn primary">
                    <i class="fas fa-history"></i>
                    <span>Borrow History</span>
                </a>
                <a href="<?= BASE_URL ?>faculty/fines" class="action-btn secondary">
                    <i class="fas fa-receipt"></i>
                    <span>View Fines</span>
                </a>
                <a href="<?= BASE_URL ?>faculty/return" class="action-btn secondary">
                    <i class="fas fa-undo"></i>
                    <span>Return Books</span>
                </a>
                <a href="<?= BASE_URL ?>faculty/reserved-books" class="action-btn secondary">
                    <i class="fas fa-bookmark"></i>
                    <span>View Reserved Books</span>
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
                        <a href="<?= BASE_URL ?>faculty/return" class="view-all-link">
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
                        <a href="<?= BASE_URL ?>faculty/notifications" class="view-all-link">
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
            <h2>
                <i class="fas fa-history"></i>
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
                                <td><?= htmlspecialchars($transaction['borrowDate'] ?? $transaction['issueDate'] ?? 'N/A') ?>
                                </td>
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
                    <a href="<?= BASE_URL ?>faculty/borrow-history" class="view-all-link">
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
                window.location.href = '<?= BASE_URL ?>faculty/books?search=' + encodeURIComponent(searchTerm);
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