<?php
$pageTitle = 'Reports & Analytics';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Reports & Analytics
                </h1>
                <div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Report Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="type" class="form-label">Report Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="overview" <?= $reportType === 'overview' ? 'selected' : '' ?>>Overview</option>
                                <option value="borrowing" <?= $reportType === 'borrowing' ? 'selected' : '' ?>>Borrowing</option>
                                <option value="fines" <?= $reportType === 'fines' ? 'selected' : '' ?>>Fines</option>
                                <option value="users" <?= $reportType === 'users' ? 'selected' : '' ?>>Users</option>
                                <option value="books" <?= $reportType === 'books' ? 'selected' : '' ?>>Books</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $startDate ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $endDate ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Generate Report
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportReport()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> 
                        <?= ucfirst($reportType) ?> Report
                        <small class="text-muted">(<?= $startDate ?> to <?= $endDate ?>)</small>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($reportType === 'overview'): ?>
                        <!-- Overview Report -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['total_transactions'] ?? 0 ?></h3>
                                        <p class="mb-0">Total Transactions</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['total_users'] ?? 0 ?></h3>
                                        <p class="mb-0">Total Users</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['total_books'] ?? 0 ?></h3>
                                        <p class="mb-0">Total Books</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3>₹<?= number_format($report['total_fines'] ?? 0, 2) ?></h3>
                                        <p class="mb-0">Total Fines</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Active Borrowings</h6>
                                    </div>
                                    <div class="card-body">
                                        <h4><?= $report['active_borrowings'] ?? 0 ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Overdue Books</h6>
                                    </div>
                                    <div class="card-body">
                                        <h4><?= $report['overdue_books'] ?? 0 ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($reportType === 'borrowing'): ?>
                        <!-- Borrowing Report -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Total Borrowings</h6>
                                    </div>
                                    <div class="card-body">
                                        <h3><?= $report['total_borrowings'] ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Period</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><?= $report['period'] ?? '' ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($report['most_borrowed_books'])): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Most Borrowed Books</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Book Name</th>
                                                        <th>Borrow Count</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($report['most_borrowed_books'] as $bookName => $count): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($bookName) ?></td>
                                                            <td><span class="badge bg-primary"><?= $count ?></span></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($reportType === 'fines'): ?>
                        <!-- Fines Report -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h3>₹<?= number_format($report['total_fines'] ?? 0, 2) ?></h3>
                                        <p class="mb-0">Total Fines</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['overdue_books'] ?? 0 ?></h3>
                                        <p class="mb-0">Overdue Books</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['period'] ?? '' ?></h3>
                                        <p class="mb-0">Period</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($reportType === 'users'): ?>
                        <!-- Users Report -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['total_users'] ?? 0 ?></h3>
                                        <p class="mb-0">Total Users</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['active_users'] ?? 0 ?></h3>
                                        <p class="mb-0">Active Users</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['new_users'] ?? 0 ?></h3>
                                        <p class="mb-0">New Users</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['period'] ?? '' ?></h3>
                                        <p class="mb-0">Period</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($reportType === 'books'): ?>
                        <!-- Books Report -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['total_books'] ?? 0 ?></h3>
                                        <p class="mb-0">Total Books</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['available_books'] ?? 0 ?></h3>
                                        <p class="mb-0">Available</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['borrowed_books'] ?? 0 ?></h3>
                                        <p class="mb-0">Borrowed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3><?= $report['period'] ?? '' ?></h3>
                                        <p class="mb-0">Period</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($report['popular_books'])): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Popular Books</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
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
                                                            <td><?= htmlspecialchars($book['bookName']) ?></td>
                                                            <td><?= htmlspecialchars($book['authorName']) ?></td>
                                                            <td><span class="badge bg-primary"><?= $book['borrow_count'] ?? 0 ?></span></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport() {
    // Implementation for exporting reports
    alert('Export functionality coming soon!');
}

// Auto-refresh report every 5 minutes
setInterval(function() {
    if (window.location.pathname.includes('reports')) {
        // You can implement AJAX refresh here
        console.log('Auto-refreshing report...');
    }
}, 300000);
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
