<?php
$pageTitle = 'Analytics Dashboard';
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

  /* Modern Color Palette */
  :root {
    --primary-color: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #818cf8;
    --secondary-color: #8b5cf6;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --info-color: #06b6d4;
    --dark-color: #1f2937;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
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

  /* Main Content Area */
  .main-content {
    flex: 1;
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    min-height: 100vh;
  }

  .sidebar.collapsed~.main-content {
    margin-left: 80px;
  }

  /* Content Wrapper */
  .content-wrapper {
    padding: 2rem;
    max-width: 1600px;
    margin: 0 auto;
  }

  /* Page Header */
  .page-header {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-left: 4px solid;
    border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%) 1;
  }

  .page-header-content h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .page-header-content h1 i {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .page-header-content p {
    color: #64748b;
    font-size: 0.95rem;
  }

  .header-actions {
    display: flex;
    gap: 1rem;
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 0.95rem;
  }

  .btn-secondary {
    background: #64748b;
    color: white;
  }

  .btn-secondary:hover {
    background: #475569;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(100, 116, 139, 0.3);
  }

  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
  }

  /* Stats Cards */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
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
  }

  .stat-card.blue::before {
    background: linear-gradient(90deg, #667eea, #764ba2);
  }

  .stat-card.green::before {
    background: linear-gradient(90deg, #10b981, #059669);
  }

  .stat-card.orange::before {
    background: linear-gradient(90deg, #f59e0b, #d97706);
  }

  .stat-card.red::before {
    background: linear-gradient(90deg, #ef4444, #dc2626);
  }

  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  }

  .stat-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .stat-info h4 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
  }

  .stat-info p {
    color: #64748b;
    font-weight: 600;
    font-size: 1rem;
  }

  .stat-info .stat-change {
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }

  .stat-change.positive {
    color: #10b981;
  }

  .stat-change.negative {
    color: #ef4444;
  }

  .stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
  }

  .stat-card.blue .stat-icon {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
  }

  .stat-card.green .stat-icon {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
  }

  .stat-card.orange .stat-icon {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
  }

  .stat-card.red .stat-icon {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
  }

  /* Card Styles */
  .card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    transition: all 0.3s ease;
  }

  .card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
  }

  .card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .card-title i {
    color: #667eea;
  }

  /* Charts Container */
  .charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
  }

  .chart-container {
    height: 350px;
    position: relative;
  }

  /* Table Styles */
  .table-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
  }

  .table-wrapper {
    overflow-x: auto;
  }

  .table {
    width: 100%;
    border-collapse: collapse;
  }

  .table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .table thead th {
    padding: 1rem;
    text-align: left;
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    white-space: nowrap;
  }

  .table tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s ease;
  }

  .table tbody tr:hover {
    background: #f9fafb;
    transform: scale(1.01);
  }

  .table tbody td {
    padding: 1rem;
    color: #1e293b;
    font-size: 0.95rem;
  }

  .table tbody td strong {
    color: #0f172a;
  }

  /* Badge Styles */
  .badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
  }

  .badge-success {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-warning {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-danger {
    background: #fee2e2;
    color: #991b1b;
  }

  /* Progress Bar */
  .progress-bar {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 0.5rem;
  }

  .progress-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
  }

  .progress-fill.blue {
    background: linear-gradient(90deg, #667eea, #764ba2);
  }

  .progress-fill.green {
    background: linear-gradient(90deg, #10b981, #059669);
  }

  .progress-fill.orange {
    background: linear-gradient(90deg, #f59e0b, #d97706);
  }

  /* Responsive Design */
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

    .content-wrapper {
      padding: 1rem;
    }

    .page-header {
      flex-direction: column;
      gap: 1rem;
    }

    .stats-grid {
      grid-template-columns: 1fr;
    }

    .charts-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<!-- Admin Layout Container -->
<div class="admin-layout">
  <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>
  <!-- Main Content Area -->
  <main class="main-content">
    <div class="content-wrapper">
      <!-- Page Header -->
      <div class="page-header">
        <div class="page-header-content">
          <h1>
            <i class="fas fa-chart-line"></i>
            Analytics Dashboard
          </h1>
          <p>Comprehensive insights and performance metrics</p>
        </div>
        <div class="header-actions">
          <button class="btn btn-secondary" onclick="window.print()">
            <i class="fas fa-print"></i>
            Print
          </button>
          <button class="btn btn-primary" onclick="exportReport()">
            <i class="fas fa-download"></i>
            Export Report
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-content">
            <div class="stat-info">
              <h4>2,847</h4>
              <p>Total Books</p>
              <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                +12.5% from last month
              </div>
            </div>
            <div class="stat-icon">
              <i class="fas fa-book"></i>
            </div>
          </div>
        </div>

        <div class="stat-card green">
          <div class="stat-content">
            <div class="stat-info">
              <h4>1,234</h4>
              <p>Active Users</p>
              <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                +8.3% from last month
              </div>
            </div>
            <div class="stat-icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>

        <div class="stat-card orange">
          <div class="stat-content">
            <div class="stat-info">
              <h4>492</h4>
              <p>Current Borrowings</p>
              <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                +5.7% from last month
              </div>
            </div>
            <div class="stat-icon">
              <i class="fas fa-exchange-alt"></i>
            </div>
          </div>
        </div>

        <div class="stat-card red">
          <div class="stat-content">
            <div class="stat-info">
              <h4>₹18,450</h4>
              <p>Fines Collected</p>
              <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i>
                -3.2% from last month
              </div>
            </div>
            <div class="stat-icon">
              <i class="fas fa-dollar-sign"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-grid">
        <!-- Borrowing Trends Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-area"></i>
              Borrowing Trends
            </h3>
          </div>
          <div class="chart-container">
            <canvas id="borrowingTrendsChart"></canvas>
          </div>
        </div>

        <!-- Category Distribution Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-pie"></i>
              Category Distribution
            </h3>
          </div>
          <div class="chart-container">
            <canvas id="categoryChart"></canvas>
          </div>
        </div>

        <!-- Monthly Comparison Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-bar"></i>
              Monthly Comparison
            </h3>
          </div>
          <div class="chart-container">
            <canvas id="monthlyComparisonChart"></canvas>
          </div>
        </div>

        <!-- User Growth Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-users"></i>
              User Growth
            </h3>
          </div>
          <div class="chart-container">
            <canvas id="userGrowthChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Top Books Table -->
      <div class="table-container">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-trophy"></i>
            Top 10 Most Borrowed Books
          </h3>
        </div>
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Rank</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Borrowings</th>
                <th>Availability</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>#1</strong></td>
                <td>To Kill a Mockingbird</td>
                <td>Harper Lee</td>
                <td>Fiction</td>
                <td>
                  <strong>156</strong>
                  <div class="progress-bar">
                    <div class="progress-fill blue" style="width: 100%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-success">Available</span></td>
              </tr>
              <tr>
                <td><strong>#2</strong></td>
                <td>1984</td>
                <td>George Orwell</td>
                <td>Fiction</td>
                <td>
                  <strong>142</strong>
                  <div class="progress-bar">
                    <div class="progress-fill blue" style="width: 91%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-warning">Limited</span></td>
              </tr>
              <tr>
                <td><strong>#3</strong></td>
                <td>The Great Gatsby</td>
                <td>F. Scott Fitzgerald</td>
                <td>Fiction</td>
                <td>
                  <strong>138</strong>
                  <div class="progress-bar">
                    <div class="progress-fill green" style="width: 88%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-success">Available</span></td>
              </tr>
              <tr>
                <td><strong>#4</strong></td>
                <td>Pride and Prejudice</td>
                <td>Jane Austen</td>
                <td>Romance</td>
                <td>
                  <strong>128</strong>
                  <div class="progress-bar">
                    <div class="progress-fill green" style="width: 82%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-success">Available</span></td>
              </tr>
              <tr>
                <td><strong>#5</strong></td>
                <td>The Catcher in the Rye</td>
                <td>J.D. Salinger</td>
                <td>Fiction</td>
                <td>
                  <strong>124</strong>
                  <div class="progress-bar">
                    <div class="progress-fill orange" style="width: 79%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-danger">Unavailable</span></td>
              </tr>
              <tr>
                <td><strong>#6</strong></td>
                <td>Harry Potter Series</td>
                <td>J.K. Rowling</td>
                <td>Fantasy</td>
                <td>
                  <strong>115</strong>
                  <div class="progress-bar">
                    <div class="progress-fill orange" style="width: 74%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-warning">Limited</span></td>
              </tr>
              <tr>
                <td><strong>#7</strong></td>
                <td>The Hobbit</td>
                <td>J.R.R. Tolkien</td>
                <td>Fantasy</td>
                <td>
                  <strong>108</strong>
                  <div class="progress-bar">
                    <div class="progress-fill green" style="width: 69%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-success">Available</span></td>
              </tr>
              <tr>
                <td><strong>#8</strong></td>
                <td>The Lord of the Rings</td>
                <td>J.R.R. Tolkien</td>
                <td>Fantasy</td>
                <td>
                  <strong>102</strong>
                  <div class="progress-bar">
                    <div class="progress-fill orange" style="width: 65%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-warning">Limited</span></td>
              </tr>
              <tr>
                <td><strong>#9</strong></td>
                <td>The Alchemist</td>
                <td>Paulo Coelho</td>
                <td>Fiction</td>
                <td>
                  <strong>98</strong>
                  <div class="progress-bar">
                    <div class="progress-fill orange" style="width: 63%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-success">Available</span></td>
              </tr>
              <tr>
                <td><strong>#10</strong></td>
                <td>Animal Farm</td>
                <td>George Orwell</td>
                <td>Fiction</td>
                <td>
                  <strong>94</strong>
                  <div class="progress-bar">
                    <div class="progress-fill orange" style="width: 60%;"></div>
                  </div>
                </td>
                <td><span class="badge badge-success">Available</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
  </main>
</div>

<script>
  // Sidebar functions
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
  }

  function exportReport() {
    alert('Exporting report... This feature will generate a PDF report with all analytics data.');
  }

  // Load sidebar state
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed && sidebar) {
      sidebar.classList.add('collapsed');
    }

    // Initialize Charts
    initializeCharts();
  });

  function initializeCharts() {
    // Borrowing Trends Chart
    const borrowingCtx = document.getElementById('borrowingTrendsChart').getContext('2d');
    new Chart(borrowingCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Borrowings',
          data: [245, 289, 312, 278, 324, 356, 389, 412, 398, 445, 468, 492],
          borderColor: '#667eea',
          backgroundColor: 'rgba(102, 126, 234, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#e2e8f0'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
      type: 'doughnut',
      data: {
        labels: ['Fiction', 'Non-Fiction', 'Science', 'Technology', 'History', 'Other'],
        datasets: [{
          data: [35, 25, 15, 12, 8, 5],
          backgroundColor: [
            '#667eea',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#06b6d4',
            '#64748b'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Monthly Comparison Chart
    const monthlyCtx = document.getElementById('monthlyComparisonChart').getContext('2d');
    new Chart(monthlyCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: '2024',
          data: [245, 289, 312, 278, 324, 356],
          backgroundColor: '#667eea'
        }, {
          label: '2023',
          data: [198, 234, 267, 245, 289, 312],
          backgroundColor: '#10b981'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#e2e8f0'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'New Members',
          data: [45, 58, 62, 71, 85, 92, 108, 115, 128, 142, 156, 165],
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#e2e8f0'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }
</script>

</body>

</html>
