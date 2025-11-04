<?php
$pageTitle = 'Analytics';

include APP_ROOT . '/views/admin/admin-navbar.php';

// Safe extraction of variables with defaults
$stats = $stats ?? [];
$borrowTrends = $borrowTrends ?? [];
$topBooks = $topBooks ?? [];
$categoryDistribution = $categoryDistribution ?? [];
$userActivity = $userActivity ?? [];
$fineStats = $fineStats ?? [];
$monthlyStats = $monthlyStats ?? [];
$recentActivities = $recentActivities ?? [];
?>
<style>
  /* Layout Structure */
  body {
    margin: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
  }

  .admin-layout {
    display: flex;
    min-height: 100vh;
  }

  /* Main Content Area */
  .main-content {
    flex: 1;
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    background: transparent;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 80px;
  }

  .analytics-container {
    padding: 2.5rem;
    flex: 1;
    width: 100%;
  }

  /* Page Header - Simple Dashboard Style */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #e2e8f0;
  }

  .page-header-left h1 {
    font-size: 2rem;
    margin: 0 0 0.5rem 0;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.02em;
  }

  .breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
  }

  .breadcrumb a {
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s ease;
  }

  .breadcrumb a:hover {
    color: #667eea;
  }

  .breadcrumb-separator {
    color: #cbd5e1;
  }

  .breadcrumb .active {
    color: #0f172a;
    font-weight: 500;
  }

  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.75rem;
    margin-bottom: 2.5rem;
  }

  @media (max-width: 1400px) {
    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .stats-grid {
      grid-template-columns: 1fr;
    }
  }

  .stat-card {
    background: white;
    padding: 2rem;
    border-radius: 18px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(226, 232, 240, 0.6);
    position: relative;
    overflow: hidden;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1), 0 20px 30px rgba(0, 0, 0, 0.06);
  }

  .stat-card:hover::before {
    transform: scaleX(1);
  }

  .stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
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
    flex-shrink: 0;
  }

  .stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
  }

  .stat-icon.primary {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
    color: #6366f1;
  }

  .stat-icon.success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
    color: #10b981;
  }

  .stat-icon.danger {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
    color: #ef4444;
  }

  .stat-icon.warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
    color: #f59e0b;
  }

  .stat-icon.info {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.15) 0%, rgba(8, 145, 178, 0.15) 100%);
    color: #06b6d4;
  }

  .stat-title {
    color: #64748b;
    font-size: 0.95rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
  }

  .stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.5rem;
    line-height: 1;
    letter-spacing: -0.02em;
  }

  .stat-subtitle {
    color: #94a3b8;
    font-size: 0.9rem;
    font-weight: 500;
  }

  /* Charts Grid */
  .charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    margin-bottom: 2.5rem;
  }

  @media (max-width: 1200px) {
    .charts-grid {
      grid-template-columns: 1fr;
    }
  }

  .chart-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(226, 232, 240, 0.6);
    transition: all 0.3s ease;
  }

  .chart-card:hover {
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.08), 0 16px 24px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
  }

  .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.75rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
  }

  .chart-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    letter-spacing: -0.01em;
  }

  .chart-title i {
    color: #667eea;
    font-size: 1.35rem;
  }

  .chart-actions {
    display: flex;
    gap: 0.625rem;
  }

  .chart-btn {
    padding: 0.625rem 1.25rem;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #475569;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .chart-btn:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  canvas {
    max-height: 320px !important;
  }

  /* Table Styles */
  .table-responsive {
    overflow-x: auto;
  }

  .data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .data-table thead th {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #334155;
    font-weight: 700;
    padding: 1.125rem 1rem;
    text-align: left;
    font-size: 0.875rem;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .data-table thead th:first-child {
    border-radius: 12px 0 0 0;
  }

  .data-table thead th:last-child {
    border-radius: 0 12px 0 0;
  }

  .data-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
  }

  .data-table tbody tr:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    transform: scale(1.01);
  }

  .data-table tbody td {
    padding: 1.125rem 1rem;
    color: #475569;
    font-weight: 500;
  }

  .badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    display: inline-block;
    letter-spacing: 0.3px;
  }

  .badge-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
  }

  .badge-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
  }

  .badge-danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
  }

  .badge-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
  }

  /* Activity Timeline */
  .activity-timeline {
    max-height: 560px;
    overflow-y: auto;
    padding-right: 0.5rem;
  }

  .activity-timeline::-webkit-scrollbar {
    width: 6px;
  }

  .activity-timeline::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
  }

  .activity-timeline::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }

  .activity-timeline::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  .activity-item {
    display: flex;
    gap: 1.25rem;
    padding: 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
    border-radius: 12px;
    margin-bottom: 0.5rem;
  }

  .activity-item:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    transform: translateX(4px);
  }

  .activity-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.25rem;
  }

  .activity-icon.success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
    color: #10b981;
  }

  .activity-icon.warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
    color: #f59e0b;
  }

  .activity-content {
    flex: 1;
  }

  .activity-description {
    color: #0f172a;
    font-weight: 600;
    margin-bottom: 0.375rem;
    font-size: 0.95rem;
  }

  .activity-time {
    color: #94a3b8;
    font-size: 0.875rem;
    font-weight: 500;
  }

  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .analytics-container {
      padding: 1.5rem;
    }

    .page-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }

    .page-header-left h1 {
      font-size: 1.5rem;
    }

    .breadcrumb {
      font-size: 0.8rem;
    }

    .stat-value {
      font-size: 2rem;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      font-size: 1.5rem;
    }
  }
</style>

<main class="main-content">
  <div class="analytics-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-header-left">
        <h1>Analytics & Reports</h1>
        <div class="breadcrumb">
          <a href="<?= BASE_URL ?>admin/dashboard">Home</a>
          <span class="breadcrumb-separator">/</span>
          <span class="active">Analytics & Reports</span>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <div class="stat-title">Total Books</div>
            <div class="stat-value"><?= number_format($stats['total_books'] ?? 0) ?></div>
            <div class="stat-subtitle"><?= number_format(($stats['total_copies'] ?? 0)) ?> total copies</div>
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
            <div class="stat-value"><?= number_format($stats['total_users'] ?? 0) ?></div>
            <div class="stat-subtitle"><?= number_format(($userActivity[0]['activeUsers'] ?? 0) + ($userActivity[1]['activeUsers'] ?? 0)) ?> active this month</div>
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
            <div class="stat-value"><?= number_format($stats['active_borrowings'] ?? 0) ?></div>
            <div class="stat-subtitle">Currently on loan</div>
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
            <div class="stat-value">0</div>
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
            <div class="stat-value">0</div>
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
            <div class="stat-value">LKR<?= number_format($stats['total_fines'] ?? 0, 2) ?></div>
            <div class="stat-subtitle">
              <?php
              $pendingFines = 0;
              foreach ($fineStats as $stat) {
                $pendingFines += $stat['pendingFines'] ?? 0;
              }
              ?>
              LKR<?= number_format($pendingFines, 2) ?> pending
            </div>
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
          </div>
        </div>
        <canvas id="borrowTrendsChart"></canvas>
      </div>

      <!-- Category Distribution Chart -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="chart-title"><i class="fas fa-chart-pie"></i> Books by Category</h3>
          <div class="chart-actions">
          </div>
        </div>
        <canvas id="categoryChart"></canvas>
      </div>

      <!-- Monthly Statistics Chart -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="chart-title"><i class="fas fa-chart-bar"></i> Monthly Activity</h3>
          <div class="chart-actions">
          </div>
        </div>
        <canvas id="monthlyStatsChart"></canvas>
      </div>

      <!-- Fine Statistics Chart -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="chart-title"><i class="fas fa-money-bill-wave"></i> Fine Collection (6 Months)</h3>
          <div class="chart-actions">
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
                    <td><strong><?= htmlspecialchars($book['bookName'] ?? '') ?></strong></td>
                    <td><?= htmlspecialchars($book['authorName'] ?? '') ?></td>
                    <td><span class="badge badge-info"><?= (int)($book['borrowCount'] ?? 0) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">
                    <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                    No data available
                  </td>
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
                <div class="activity-icon <?= ($activity['type'] ?? '') === 'Transaction' ? 'success' : 'warning' ?>">
                  <i class="fas fa-<?= ($activity['type'] ?? '') === 'Transaction' ? 'book' : 'hand-paper' ?>"></i>
                </div>
                <div class="activity-content">
                  <div class="activity-description"><?= htmlspecialchars($activity['description'] ?? '') ?></div>
                  <div class="activity-time"><?= date('M j, Y H:i', strtotime($activity['timestamp'] ?? 'now')) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #94a3b8;">
              <i class="fas fa-inbox" style="font-size: 3.5rem; opacity: 0.3; margin-bottom: 1rem; display: block;"></i>
              <p style="font-weight: 600; font-size: 1.1rem; margin: 0;">No recent activities</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
</main>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
  // Chart.js Global Configuration
  Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
  Chart.defaults.font.size = 13;
  Chart.defaults.color = '#64748b';

  // Borrowing Trends Chart
  const borrowTrendsCtx = document.getElementById('borrowTrendsChart').getContext('2d');
  new Chart(borrowTrendsCtx, {
    type: 'line',
    data: {
      labels: <?= json_encode(array_map(fn($t) => date('M j', strtotime($t['date'] ?? 'now')), $borrowTrends ?? [])) ?>,
      datasets: [{
        label: 'Books Borrowed',
        data: <?= json_encode(array_map(fn($t) => (int)($t['count'] ?? 0), $borrowTrends ?? [])) ?>,
        borderColor: '#667eea',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
        tension: 0.4,
        fill: true,
        borderWidth: 3,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: '#667eea',
        pointBorderColor: '#fff',
        pointBorderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#0f172a',
          padding: 12,
          titleColor: '#fff',
          bodyColor: '#fff',
          cornerRadius: 8,
          displayColors: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9',
            drawBorder: false
          },
          ticks: {
            padding: 10
          }
        },
        x: {
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            padding: 10
          }
        }
      }
    }
  });

  // Category Distribution Chart
  const categoryCtx = document.getElementById('categoryChart').getContext('2d');
  new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_map(fn($c) => $c['category'] ?? '', $categoryDistribution ?? [])) ?>,
      datasets: [{
        data: <?= json_encode(array_map(fn($c) => (int)($c['count'] ?? 0), $categoryDistribution ?? [])) ?>,
        backgroundColor: ['#667eea', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#14b8a6'],
        borderWidth: 0,
        hoverOffset: 15
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 20,
            font: {
              size: 12,
              weight: '600'
            },
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: '#0f172a',
          padding: 12,
          cornerRadius: 8
        }
      }
    }
  });

  // Monthly Statistics Chart
  const monthlyStatsCtx = document.getElementById('monthlyStatsChart').getContext('2d');
  new Chart(monthlyStatsCtx, {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(fn($m) => date('M Y', strtotime(($m['month'] ?? '') . '-01')), $monthlyStats ?? [])) ?>,
      datasets: [{
        label: 'Issues',
        data: <?= json_encode(array_map(fn($m) => (int)($m['issues'] ?? 0), $monthlyStats ?? [])) ?>,
        backgroundColor: '#667eea',
        borderRadius: 8,
        borderSkipped: false
      }, {
        label: 'Returns',
        data: <?= json_encode(array_map(fn($m) => (int)($m['returns'] ?? 0), $monthlyStats ?? [])) ?>,
        backgroundColor: '#10b981',
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          align: 'end',
          labels: {
            padding: 15,
            font: {
              size: 12,
              weight: '600'
            },
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: '#0f172a',
          padding: 12,
          cornerRadius: 8
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9',
            drawBorder: false
          },
          ticks: {
            padding: 10
          }
        },
        x: {
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            padding: 10
          }
        }
      }
    }
  });

  // Fine Statistics Chart
  const fineStatsCtx = document.getElementById('fineStatsChart').getContext('2d');
  new Chart(fineStatsCtx, {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(fn($f) => date('M Y', strtotime(($f['month'] ?? '') . '-01')), $fineStats ?? [])) ?>,
      datasets: [{
        label: 'Paid Fines',
        data: <?= json_encode(array_map(fn($f) => (float)($f['paidFines'] ?? 0), $fineStats ?? [])) ?>,
        backgroundColor: '#10b981',
        borderRadius: 8,
        borderSkipped: false
      }, {
        label: 'Pending Fines',
        data: <?= json_encode(array_map(fn($f) => (float)($f['pendingFines'] ?? 0), $fineStats ?? [])) ?>,
        backgroundColor: '#f59e0b',
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          align: 'end',
          labels: {
            padding: 15,
            font: {
              size: 12,
              weight: '600'
            },
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: '#0f172a',
          padding: 12,
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              return context.dataset.label + ': LKR' + context.parsed.y.toFixed(2);
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9',
            drawBorder: false
          },
          ticks: {
            padding: 10,
            callback: function(value) {
              return 'LKR' + value;
            }
          }
        },
        x: {
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            padding: 10
          }
        }
      }
    }
  });
</script>
