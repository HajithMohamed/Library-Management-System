<?php
$pageTitle = 'Analytics & Reports';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
    :root {
        --primary-color: #6366f1;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #06b6d4;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f0f2f5;
        margin: 0;
        padding: 0;
    }

    .analytics-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
    }

    .page-header h1 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .page-header p {
        opacity: 0.9;
        font-size: 1.1rem;
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
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-icon.primary { background: rgba(99, 102, 241, 0.1); color: var(--primary-color); }
    .stat-icon.success { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
    .stat-icon.danger { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
    .stat-icon.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
    .stat-icon.info { background: rgba(6, 182, 212, 0.1); color: var(--info-color); }

    .stat-title {
        color: var(--gray-500);
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .stat-subtitle {
        color: var(--gray-500);
        font-size: 0.85rem;
    }

    /* Charts Grid */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .chart-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .chart-actions {
        display: flex;
        gap: 0.5rem;
    }

    .chart-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--gray-200);
        background: white;
        color: var(--gray-700);
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .chart-btn:hover {
        background: var(--gray-50);
    }

    canvas {
        max-height: 300px !important;
    }

    /* Table Styles */
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table thead th {
        background: var(--gray-50);
        color: var(--gray-700);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        font-size: 0.85rem;
        text-transform: uppercase;
        border-bottom: 2px solid var(--gray-200);
    }

    .data-table tbody tr {
        border-bottom: 1px solid var(--gray-100);
        transition: background 0.2s ease;
    }

    .data-table tbody tr:hover {
        background: var(--gray-50);
    }

    .data-table tbody td {
        padding: 1rem;
        color: var(--gray-700);
    }

    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1e40af; }

    /* Activity Timeline */
    .activity-timeline {
        max-height: 500px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-icon.success { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
    .activity-icon.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }

    .activity-content {
        flex: 1;
    }

    .activity-description {
        color: var(--gray-900);
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .activity-time {
        color: var(--gray-500);
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .analytics-container {
            padding: 1rem;
        }
    }
</style>

<div class="analytics-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Analytics & Reports</h1>
        <p>Comprehensive overview of library performance and statistics</p>
    </div>

    <!-- Overall Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Books</div>
                    <div class="stat-value"><?= number_format($stats['books']['total']) ?></div>
                    <div class="stat-subtitle"><?= number_format($stats['books']['copies']) ?> total copies</div>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Active Users</div>
                    <div class="stat-value"><?= number_format($stats['users']['total']) ?></div>
                    <div class="stat-subtitle"><?= $stats['users']['students'] ?> Students | <?= $stats['users']['faculty'] ?> Faculty</div>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Books Borrowed</div>
                    <div class="stat-value"><?= number_format($stats['books']['borrowed']) ?></div>
                    <div class="stat-subtitle"><?= number_format($stats['books']['available']) ?> available</div>
                </div>
                <div class="stat-icon info">
                    <i class="fas fa-book-reader"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Pending Requests</div>
                    <div class="stat-value"><?= number_format($stats['pendingRequests']) ?></div>
                    <div class="stat-subtitle">Awaiting approval</div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Overdue Books</div>
                    <div class="stat-value"><?= number_format($stats['overdue']) ?></div>
                    <div class="stat-subtitle">Require attention</div>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Fines</div>
                    <div class="stat-value">₹<?= number_format($stats['fines']['total'] ?? 0, 2) ?></div>
                    <div class="stat-subtitle">₹<?= number_format($stats['fines']['pending'] ?? 0, 2) ?> pending</div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Borrowing Trends Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-line"></i> Borrowing Trends (30 Days)</h3>
                <div class="chart-actions">
                    <button class="chart-btn"><i class="fas fa-download"></i> Export</button>
                </div>
            </div>
            <canvas id="borrowTrendsChart"></canvas>
        </div>

        <!-- Category Distribution Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-pie"></i> Books by Category</h3>
                <div class="chart-actions">
                    <button class="chart-btn"><i class="fas fa-download"></i> Export</button>
                </div>
            </div>
            <canvas id="categoryChart"></canvas>
        </div>

        <!-- Monthly Statistics Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-bar"></i> Monthly Activity</h3>
                <div class="chart-actions">
                    <button class="chart-btn"><i class="fas fa-download"></i> Export</button>
                </div>
            </div>
            <canvas id="monthlyStatsChart"></canvas>
        </div>

        <!-- Fine Statistics Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-money-bill-wave"></i> Fine Collection (6 Months)</h3>
                <div class="chart-actions">
                    <button class="chart-btn"><i class="fas fa-download"></i> Export</button>
                </div>
            </div>
            <canvas id="fineStatsChart"></canvas>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="charts-grid">
        <!-- Top Books Table -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-trophy"></i> Top 10 Most Borrowed Books</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrows</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topBooks)): ?>
                            <?php foreach ($topBooks as $index => $book): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($book['bookName']) ?></td>
                                    <td><?= htmlspecialchars($book['authorName']) ?></td>
                                    <td><span class="badge badge-info"><?= $book['borrowCount'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--gray-500);">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-history"></i> Recent Activities</h3>
            </div>
            <div class="activity-timeline">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $activity['type'] === 'Transaction' ? 'success' : 'warning' ?>">
                                <i class="fas fa-<?= $activity['type'] === 'Transaction' ? 'book' : 'hand-paper' ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-description"><?= htmlspecialchars($activity['description']) ?></div>
                                <div class="activity-time"><?= date('M j, Y H:i', strtotime($activity['timestamp'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: var(--gray-500);">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                        <p>No recent activities</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Borrowing Trends Chart
const borrowTrendsCtx = document.getElementById('borrowTrendsChart').getContext('2d');
new Chart(borrowTrendsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($t) => date('M j', strtotime($t['date'])), $borrowTrends)) ?>,
        datasets: [{
            label: 'Books Borrowed',
            data: <?= json_encode(array_map(fn($t) => $t['count'], $borrowTrends)) ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Category Distribution Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map(fn($c) => $c['category'], $categoryDistribution)) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($c) => $c['count'], $categoryDistribution)) ?>,
            backgroundColor: ['#6366f1', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#14b8a6']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Monthly Statistics Chart
const monthlyStatsCtx = document.getElementById('monthlyStatsChart').getContext('2d');
new Chart(monthlyStatsCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($m) => date('M Y', strtotime($m['month'] . '-01')), $monthlyStats)) ?>,
        datasets: [{
            label: 'Issues',
            data: <?= json_encode(array_map(fn($m) => $m['issues'], $monthlyStats)) ?>,
            backgroundColor: '#6366f1'
        }, {
            label: 'Returns',
            data: <?= json_encode(array_map(fn($m) => $m['returns'], $monthlyStats)) ?>,
            backgroundColor: '#10b981'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Fine Statistics Chart
const fineStatsCtx = document.getElementById('fineStatsChart').getContext('2d');
new Chart(fineStatsCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($f) => date('M Y', strtotime($f['month'] . '-01')), $fineStats)) ?>,
        datasets: [{
            label: 'Paid Fines',
            data: <?= json_encode(array_map(fn($f) => $f['paidFines'], $fineStats)) ?>,
            backgroundColor: '#10b981'
        }, {
            label: 'Pending Fines',
            data: <?= json_encode(array_map(fn($f) => $f['pendingFines'], $fineStats)) ?>,
            backgroundColor: '#f59e0b'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
