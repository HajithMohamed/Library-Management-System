<?php
// Session checks, authentication, etc.

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
            background: #f0f2f5;
            overflow-x: hidden;
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
            min-height: 100vh;
        }

        .sidebar.collapsed ~ .main-content {
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.75rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e2e8f0;
        }

        .card-header h5 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header h5 i {
            color: #667eea;
        }

        .card-body {
            padding: 2rem;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 350px;
        }

        /* Grid Layout */
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .data-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }

        .data-table tbody tr {
            transition: all 0.3s ease;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Badge */
        .badge {
            padding: 0.375rem 0.875rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .badge-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        /* Progress Bar */
        .progress-bar {
            height: 8px;
            background: #e2e8f0;
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

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
                padding: 1.5rem;
            }

            .page-header-content h1 {
                font-size: 1.5rem;
            }

            .header-actions {
                flex-direction: column;
                width: 100%;
            }

            .header-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>

    <div class="admin-layout">
        <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>
        
        <main class="main-content">
            <div class="content-wrapper">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-header-content">
                        <h1>
                            <i class="fas fa-chart-line"></i>
                            Analytics Dashboard
                        </h1>
                        <p>Comprehensive insights and statistics for your library</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="exportReport()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                        <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" class="filter-grid">
                        <div class="form-group">
                            <label class="form-label">Date Range</label>
                            <select class="form-control" name="range">
                                <option value="7">Last 7 Days</option>
                                <option value="30" selected>Last 30 Days</option>
                                <option value="90">Last 90 Days</option>
                                <option value="365">Last Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category">
                                <option value="all">All Categories</option>
                                <option value="fiction">Fiction</option>
                                <option value="non-fiction">Non-Fiction</option>
                                <option value="science">Science</option>
                                <option value="technology">Technology</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h4>2,847</h4>
                                <p>Total Borrowings</p>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>12.5% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h4>1,245</h4>
                                <p>Active Members</p>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>8.2% from last month</span>
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
                                <h4>₹45,280</h4>
                                <p>Fines Collected</p>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>15.3% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h4>124</h4>
                                <p>Overdue Books</p>
                                <div class="stat-change negative">
                                    <i class="fas fa-arrow-down"></i>
                                    <span>5.1% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="chart-grid">
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                <i class="fas fa-chart-line"></i>
                                Borrowing Trends
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="borrowingTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                <i class="fas fa-chart-pie"></i>
                                Category Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="chart-grid">
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                <i class="fas fa-chart-bar"></i>
                                Monthly Comparison
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="monthlyComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                <i class="fas fa-chart-area"></i>
                                User Growth
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="userGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Books Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-trophy"></i>
                            Top 10 Most Borrowed Books
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Times Borrowed</th>
                                        <th>Availability</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#1</strong></td>
                                        <td>The Great Gatsby</td>
                                        <td>F. Scott Fitzgerald</td>
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
                                        <td>To Kill a Mockingbird</td>
                                        <td>Harper Lee</td>
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
                                        <td>1984</td>
                                        <td>George Orwell</td>
                                        <td>Fiction</td>
                                        <td>
                                            <strong>138</strong>
                                            <div class="progress-bar">
                                                <div class="progress-fill blue" style="width: 88%;"></div>
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
                                            <strong>124</strong>
                                            <div class="progress-bar">
                                                <div class="progress-fill green" style="width: 79%;"></div>
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
                                            <strong>118</strong>
                                            <div class="progress-bar">
                                                <div class="progress-fill green" style="width: 76%;"></div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-danger">Out of Stock</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>#6</strong></td>
                                        <td>Harry Potter and the Sorcerer's Stone</td>
                                        <td>J.K. Rowling</td>
                                        <td>Fantasy</td>
                                        <td>
                                            <strong>112</strong>
                                            <div class="progress-bar">
                                                <div class="progress-fill green" style="width: 72%;"></div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-success">Available</span></td>
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
            </div>
            <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
        </main>
    </div>

    <script>
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
