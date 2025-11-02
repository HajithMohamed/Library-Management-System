<?php
$pageTitle = 'Reports';

include APP_ROOT . '/views/admin/admin-navbar.php';
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


        /* Main Content Area - Adjusted for dark sidebar */
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

        .reports-container {
            padding: 2.5rem;
            flex: 1;
            width: 100%;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .page-title i {
            font-size: 2.25rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 4px rgba(102, 126, 234, 0.2));
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.875rem 1.75rem;
            background: white;
            color: #475569;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 600;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            font-size: 0.95rem;
        }

        .back-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.25);
        }

        .back-btn i {
            transition: transform 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(-3px);
        }

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 2.5rem;
            border: 1px solid rgba(226, 232, 240, 0.6);
            transition: all 0.3s ease;
        }

        .filter-card:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.08), 0 16px 24px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            margin-bottom: 2rem;
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .filter-header i {
            color: #667eea;
            font-size: 1.5rem;
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
            gap: 0.625rem;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.9rem;
            letter-spacing: -0.01em;
        }

        .form-control {
            padding: 0.875rem 1.125rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
            font-weight: 500;
            color: #1e293b;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 1px 2px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }

        .form-control:hover {
            border-color: #cbd5e1;
            background: white;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            padding: 0.875rem 1.875rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-size: 0.975rem;
            letter-spacing: -0.01em;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .btn:hover::before {
            transform: translateX(0);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5);
        }

        .btn-success:active {
            transform: translateY(-1px);
        }

        /* Report Content Card */
        .report-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 2.5rem;
            border: 1px solid rgba(226, 232, 240, 0.6);
            transition: all 0.3s ease;
        }

        .report-card:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.08), 0 16px 24px rgba(0, 0, 0, 0.05);
        }

        .report-header {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            margin-bottom: 2rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .report-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .report-title i {
            color: #667eea;
            margin-right: 0.5rem;
        }

        .report-period {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-left: 0.625rem;
            font-weight: 500;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.75rem;
            margin-bottom: 2.5rem;
        }

        @media (max-width: 1200px) {
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            padding: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.25);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.35);
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.25);
        }

        .stat-card.blue:hover {
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.35);
        }

        .stat-card.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.25);
        }

        .stat-card.green:hover {
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.35);
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.25);
        }

        .stat-card.orange:hover {
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.35);
        }

        .stat-card.purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.25);
        }

        .stat-card.purple:hover {
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.35);
        }

        .stat-card.cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            box-shadow: 0 10px 30px rgba(6, 182, 212, 0.25);
        }

        .stat-card.cyan:hover {
            box-shadow: 0 20px 40px rgba(6, 182, 212, 0.35);
        }

        .stat-card.pink {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            box-shadow: 0 10px 30px rgba(236, 72, 153, 0.25);
        }

        .stat-card.pink:hover {
            box-shadow: 0 20px 40px rgba(236, 72, 153, 0.35);
        }

        .stat-label {
            font-size: 0.95rem;
            opacity: 0.95;
            margin-bottom: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            letter-spacing: -0.02em;
        }

        /* Two Column Grid */
        .two-col-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.75rem;
            margin-bottom: 2.5rem;
        }

        @media (max-width: 768px) {
            .two-col-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 2rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            border-color: #667eea;
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.15);
            transform: translateY(-4px);
        }

        .info-card:hover::before {
            transform: scaleY(1);
        }

        .info-card-header {
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card-value {
            font-size: 2.75rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            letter-spacing: -0.02em;
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
                            <button type="button" class="btn btn-success" onclick="exportReport('csv')">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportReport('txt')" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                                <i class="fas fa-file-alt"></i> Export to Text
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
                            <div class="stat-number">LKR<?= number_format($report['total_fines'] ?? 0, 2) ?></div>
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
                            <div class="stat-number">LKR<?= number_format($report['total_fines'] ?? 0, 2) ?></div>
                        </div>
                        <div class="stat-card green">
                            <div class="stat-label">Collected Fines</div>
                            <div class="stat-number">LKR<?= number_format($report['collected_fines'] ?? 0, 2) ?></div>
                        </div>
                        <div class="stat-card pink">
                            <div class="stat-label">Pending Fines</div>
                            <div class="stat-number">LKR<?= number_format($report['pending_fines'] ?? 0, 2) ?></div>
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
           <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
    </main>
  
</div>

<script>
function exportReport(format) {
    const reportType = document.getElementById('report_type').value;
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Construct the URL for the export endpoint
    const exportUrl = `<?= BASE_URL ?>admin/reports/export?type=${reportType}&start_date=${startDate}&end_date=${endDate}&format=${format}`;
    
    // Redirect to the export endpoint, which will trigger the file download
    window.location.href = exportUrl;
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