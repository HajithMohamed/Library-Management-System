<?php
// Ensure user is authenticated
if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
    header('Location: /login');
    exit();
}

$pageTitle = 'Faculty Dashboard';
include APP_ROOT . '/views/layouts/header.php';

// Get data passed from controller
$userStats = $userStats ?? [
    'borrowed_books' => 0,
    'overdue_books' => 0,
    'total_fines' => 0,
    'max_books' => 5
];

$stats = $stats ?? [
    'total_books' => 0,
    'reviews' => ['total_reviews' => 0, 'avg_rating' => 0],
    'categories' => [],
    'monthly' => []
];

$user = $user ?? ['username' => $_SESSION['username'] ?? 'Faculty Member'];
$borrowedBooks = $borrowedBooks ?? [];
$overdueBooks = $overdueBooks ?? [];
$reservedBooks = $reservedBooks ?? [];
$notifications = $notifications ?? [];
$transactionHistory = $transactionHistory ?? [];
?>

<style>
    .dashboard-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 40px 20px;
    }
    
    .dashboard-header {
        color: white;
        margin-bottom: 40px;
        text-align: center;
    }
    
    .dashboard-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .dashboard-header p {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        animation: shine 3s infinite;
    }
    
    @keyframes shine {
        0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    .stat-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }
    
    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }
    
    .quick-actions {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .quick-actions h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
    }
    
    .search-box {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-box input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .search-box i {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2rem;
        color: #9ca3af;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .action-btn {
        padding: 12px 25px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: white;
        border: none;
        cursor: pointer;
    }
    
    .action-btn.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .action-btn.secondary {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .content-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .content-card h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .content-card h3 i {
        color: #667eea;
    }
    
    .book-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .book-item {
        padding: 15px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s ease;
        border-radius: 10px;
    }
    
    .book-item:hover {
        background: #f9fafb;
    }
    
    .book-item:last-child {
        border-bottom: none;
    }
    
    .book-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 5px;
    }
    
    .book-meta {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .notification-item {
        padding: 15px;
        border-left: 4px solid #667eea;
        background: #f9fafb;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.2s ease;
    }
    
    .notification-item:hover {
        background: #f3f4f6;
        transform: translateX(5px);
    }
    
    .notification-message {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 5px;
    }
    
    .notification-time {
        font-size: 0.8rem;
        color: #9ca3af;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .full-width-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow-x: auto;
    }
    
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    
    .modern-table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .modern-table thead th:first-child {
        border-radius: 10px 0 0 10px;
    }
    
    .modern-table thead th:last-child {
        border-radius: 0 10px 10px 0;
    }
    
    .modern-table tbody tr {
        background: #f9fafb;
        transition: all 0.3s ease;
    }
    
    .modern-table tbody tr:hover {
        background: #f3f4f6;
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .modern-table tbody td {
        padding: 15px;
        border-top: none;
    }
    
    .modern-table tbody tr td:first-child {
        border-radius: 10px 0 0 10px;
    }
    
    .modern-table tbody tr td:last-child {
        border-radius: 0 10px 10px 0;
    }
    
    .badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge.success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    
    .badge.pending {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }
    
    .faculty-analytics {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .faculty-analytics h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }
    
    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .chart-card h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .charts-container {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .action-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>👋 Welcome Back, <?= htmlspecialchars($user['username'] ?? 'Faculty Member') ?>!</h1>
        <p>Here's your library overview for today</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                📚
            </div>
            <div class="stat-label">Books Borrowed</div>
            <div class="stat-value"><?= count($borrowedBooks ?? []) ?></div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                ⏰
            </div>
            <div class="stat-label">Books Overdue</div>
            <div class="stat-value"><?= count($overdueBooks ?? []) ?></div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                🔖
            </div>
            <div class="stat-label">Reserved Books</div>
            <div class="stat-value"><?= count($reservedBooks ?? []) ?></div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                🔔
            </div>
            <div class="stat-label">Notifications</div>
            <div class="stat-value"><?= count($notifications ?? []) ?></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>🚀 Quick Actions</h2>
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
                Browse Books
            </a>
            <a href="/faculty/borrow-history" class="action-btn primary">
                <i class="fas fa-history"></i>
                Borrow History
            </a>
            <a href="/faculty/return" class="action-btn secondary">
                <i class="fas fa-undo"></i>
                Return Books
            </a>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="faculty-analytics">
        <h2>📊 My Reading Analytics</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-value"><?php echo $stats['total_books'] ?? 0; ?></div>
                <div class="stat-label">Books Read</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-value"><?php echo number_format($stats['reviews']['avg_rating'] ?? 0, 1); ?></div>
                <div class="stat-label">Avg Rating Given</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✍️</div>
                <div class="stat-value"><?php echo $stats['reviews']['total_reviews'] ?? 0; ?></div>
                <div class="stat-label">Reviews Written</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-value"><?php echo count($stats['categories'] ?? []); ?></div>
                <div class="stat-label">Categories Explored</div>
            </div>
        </div>
        
        <div class="charts-container">
            <div class="chart-card">
                <h3>Reading by Category (Last 30 Days)</h3>
                <canvas id="categoryChart" height="300"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Reading Trend (Last 6 Months)</h3>
                <canvas id="trendChart" height="300"></canvas>
            </div>
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
                    <a href="/faculty/return" style="display: block; text-align: center; margin-top: 15px; color: #667eea; font-weight: 600;">
                        View All (<?= count($borrowedBooks) ?>)
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
                    <a href="/faculty/notifications" style="display: block; text-align: center; margin-top: 15px; color: #667eea; font-weight: 600;">
                        View All (<?= count($notifications) ?>)
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
        <h2 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 20px;">
            <i class="fas fa-history" style="color: #667eea;"></i>
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
                <a href="/faculty/borrow-history" style="display: block; text-align: center; margin-top: 20px; color: #667eea; font-weight: 600; font-size: 1.1rem;">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Category Chart
const categoryData = <?php echo json_encode($stats['categories'] ?? []); ?>;
if (categoryData.length > 0) {
    const categoryLabels = categoryData.map(d => d.category);
    const categoryCounts = categoryData.map(d => parseInt(d.borrow_count));
    
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryCounts,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });
}

// Trend Chart
const trendData = <?php echo json_encode($stats['monthly'] ?? []); ?>;
if (trendData.length > 0) {
    const trendLabels = trendData.map(d => {
        const [year, month] = d.month.split('-');
        return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    const trendCounts = trendData.map(d => parseInt(d.count));
    
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Books Borrowed',
                data: trendCounts,
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

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