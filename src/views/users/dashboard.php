<?php
$pageTitle = 'Dashboard';
include APP_ROOT . '/views/layouts/header.php';

// Get user stats (this would come from a service in a real implementation)
$userStats = [
    'borrowed_books' => 2,
    'overdue_books' => 0,
    'total_fines' => 0,
    'max_books' => $_SESSION['userType'] === 'Faculty' ? 5 : 3
];
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Welcome back, <?= htmlspecialchars($_SESSION['userId']) ?>!</h1>
                <span class="badge bg-primary fs-6"><?= $_SESSION['userType'] ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $userStats['borrowed_books'] ?></h4>
                            <p class="card-text">Books Borrowed</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $userStats['overdue_books'] ?></h4>
                            <p class="card-text">Overdue Books</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">₹<?= $userStats['total_fines'] ?></h4>
                            <p class="card-text">Total Fines</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $userStats['max_books'] ?></h4>
                            <p class="card-text">Max Books</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bookmark fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <h3>Quick Actions</h3>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Browse Books</h5>
                    <p class="card-text">Search and browse available books</p>
                    <a href="<?= BASE_URL ?>user/books" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-undo fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Return Books</h5>
                    <p class="card-text">Return your borrowed books</p>
                    <a href="<?= BASE_URL ?>user/return" class="btn btn-success">
                        <i class="fas fa-undo"></i> Return
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Pay Fines</h5>
                    <p class="card-text">View and pay your fines</p>
                    <a href="<?= BASE_URL ?>user/fines" class="btn btn-warning">
                        <i class="fas fa-money-bill-wave"></i> Pay Fines
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-edit fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Profile</h5>
                    <p class="card-text">Update your profile information</p>
                    <a href="<?= BASE_URL ?>user/profile" class="btn btn-info">
                        <i class="fas fa-user-edit"></i> Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Book</th>
                                    <th>Author</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i> No recent activity
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
