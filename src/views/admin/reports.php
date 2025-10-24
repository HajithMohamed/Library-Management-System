<?php
$pageTitle = 'Reports & Analytics';

include APP_ROOT . '/views/layouts/admin-header.php';
?>
    <style>
        /* Layout Structure */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }


        /* Main Content Area - Adjusted for dark sidebar */
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
            background: #f8fafc;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 80px;
        }

        .reports-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .page-title i {
            font-size: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: white;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
            transform: translateY(-2px);
        }

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .filter-header i {
            color: #667eea;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
        }

        .form-control {
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
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .button-group {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }

        /* Report Content Card */
        .report-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .report-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .report-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .report-title i {
            color: #667eea;
        }

        .report-period {
            color: #94a3b8;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .stat-card.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-card.purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .stat-card.cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .stat-card.pink {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
        }

        /* Two Column Grid */
        .two-col-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid #e2e8f0;
        }

        .info-card-header {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .info-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Table Styles */
        .table-card {
            margin-top: 2rem;
        }

        .table-header {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-header i {
            color: #667eea;
        }

        .responsive-table {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #64748b;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-primary {
            background: #e0e7ff;
            color: #667eea;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="admin-layout">

<?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

        <main class="main-content">
            <div class="reports-container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-chart-bar"></i>
                        Reports & Analytics
                    </h1>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Filter Card -->
                <div class="filter-card">
                    <div class="filter-header">
                        <i class="fas fa-filter"></i>
                        Report Filters
                    </div>
                    <form method="GET" action="" class="filter-form">
                        <div class="form-group">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-control" id="report_type" name="type">
                                <option value="overview" <?= $reportType === 'overview' ? 'selected' : '' ?>>Overview</option>
                                <option value="borrowing" <?= $reportType === 'borrowing' ? 'selected' : '' ?>>Borrowing Activity</option>
                                <option value="fines" <?= $reportType === 'fines' ? 'selected' : '' ?>>Fines</option>
                                <option value="users" <?= $reportType === 'users' ? 'selected' : '' ?>>Users</option>
                                <option value="books" <?= $reportType === 'books' ? 'selected' : '' ?>>Books</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $startDate ?>">
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $endDate ?>">
                        </div>
                        <div class="form-group">
                            <div class="button-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Generate Report
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportReport()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Report Content -->
                <div class="report-card">
                    <div class="report-header">
                        <h2 class="report-title">
                            <i class="fas fa-chart-line"></i>
                            <?= ucfirst($reportType) ?> Report
                            <span class="report-period">(<?= $startDate ?> to <?= $endDate ?>)</span>
                        </h2>
                    </div>

                    <?php if ($reportType === 'overview'): ?>
                        <!-- Overview Report -->
                        <div class="stats-grid">
                            <div class="stat-card blue">
                                <div class="stat-label">Total Transactions</div>
                                <div class="stat-number"><?= $report['total_transactions'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card green">
                                <div class="stat-label">Total Users</div>
                                <div class="stat-number"><?= $report['total_users'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card cyan">
                                <div class="stat-label">Total Books</div>
                                <div class="stat-number"><?= $report['total_books'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card orange">
                                <div class="stat-label">Total Fines</div>
                                <div class="stat-number">₹<?= number_format($report['total_fines'] ?? 0, 2) ?></div>
                            </div>
                        </div>
                        
                        <div class="two-col-grid">
                            <div class="info-card">
                                <div class="info-card-header">Active Borrowings</div>
                                <div class="info-card-value"><?= $report['active_borrowings'] ?? 0 ?></div>
                            </div>
                            <div class="info-card">
                                <div class="info-card-header">Overdue Books</div>
                                <div class="info-card-value"><?= $report['overdue_books'] ?? 0 ?></div>
                            </div>
                        </div>

                    <?php elseif ($reportType === 'borrowing'): ?>
                        <!-- Borrowing Report -->
                        <div class="stats-grid">
                            <div class="stat-card blue">
                                <div class="stat-label">Total Borrowings</div>
                                <div class="stat-number"><?= $report['total_borrowings'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card green">
                                <div class="stat-label">Returned Books</div>
                                <div class="stat-number"><?= $report['returned_books'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card purple">
                                <div class="stat-label">Active Loans</div>
                                <div class="stat-number"><?= $report['active_loans'] ?? 0 ?></div>
                            </div>
                        </div>

                    <?php elseif ($reportType === 'fines'): ?>
                        <!-- Fines Report -->
                        <div class="stats-grid">
                            <div class="stat-card orange">
                                <div class="stat-label">Total Fines</div>
                                <div class="stat-number">₹<?= number_format($report['total_fines'] ?? 0, 2) ?></div>
                            </div>
                            <div class="stat-card green">
                                <div class="stat-label">Collected Fines</div>
                                <div class="stat-number">₹<?= number_format($report['collected_fines'] ?? 0, 2) ?></div>
                            </div>
                            <div class="stat-card pink">
                                <div class="stat-label">Pending Fines</div>
                                <div class="stat-number">₹<?= number_format($report['pending_fines'] ?? 0, 2) ?></div>
                            </div>
                            <div class="stat-card orange">
                                <div class="stat-label">Overdue Books</div>
                                <div class="stat-number"><?= $report['overdue_books'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card cyan">
                                <div class="stat-label">Period</div>
                                <div class="stat-number" style="font-size: 1.5rem;"><?= $report['period'] ?? '' ?></div>
                            </div>
                        </div>

                    <?php elseif ($reportType === 'users'): ?>
                        <!-- Users Report -->
                        <div class="stats-grid">
                            <div class="stat-card blue">
                                <div class="stat-label">Total Users</div>
                                <div class="stat-number"><?= $report['total_users'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card green">
                                <div class="stat-label">Active Users</div>
                                <div class="stat-number"><?= $report['active_users'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card cyan">
                                <div class="stat-label">New Users</div>
                                <div class="stat-number"><?= $report['new_users'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card purple">
                                <div class="stat-label">Period</div>
                                <div class="stat-number" style="font-size: 1.5rem;"><?= $report['period'] ?? '' ?></div>
                            </div>
                        </div>

                    <?php elseif ($reportType === 'books'): ?>
                        <!-- Books Report -->
                        <div class="stats-grid">
                            <div class="stat-card blue">
                                <div class="stat-label">Total Books</div>
                                <div class="stat-number"><?= $report['total_books'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card green">
                                <div class="stat-label">Available Books</div>
                                <div class="stat-number"><?= $report['available_books'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card orange">
                                <div class="stat-label">Borrowed Books</div>
                                <div class="stat-number"><?= $report['borrowed_books'] ?? 0 ?></div>
                            </div>
                            <div class="stat-card cyan">
                                <div class="stat-label">Period</div>
                                <div class="stat-number" style="font-size: 1.5rem;"><?= $report['period'] ?? '' ?></div>
                            </div>
                        </div>

                        <?php if (!empty($report['popular_books'])): ?>
                        <div class="table-card">
                            <div class="table-header">
                                <i class="fas fa-star"></i> Popular Books
                            </div>
                            <div class="responsive-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Book Name</th>
                                            <th>Author</th>
                                            <th>Borrow Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report['popular_books'] as $book): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($book['bookName']) ?></strong></td>
                                                <td><?= htmlspecialchars($book['authorName']) ?></td>
                                                <td><span class="badge badge-primary"><?= $book['borrow_count'] ?? 0 ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }

        function exportReport() {
            const reportType = '<?= $reportType ?>';
            const startDate = '<?= $startDate ?>';
            const endDate = '<?= $endDate ?>';
            
            alert('Exporting ' + reportType + ' report from ' + startDate + ' to ' + endDate);
            
            // Example: Redirect to export endpoint
            // window.location.href = '<?= BASE_URL ?>admin/reports/export?type=' + reportType + '&start=' + startDate + '&end=' + endDate;
        }

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>