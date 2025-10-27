<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Fines</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Fines Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-secondary">Total Fines</h5>
                            <h2 class="card-text text-danger">$<?= number_format($totalFines ?? 0, 2) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-secondary">Pending Fines</h5>
                            <h2 class="card-text text-warning">$<?= number_format($pendingFines ?? 0, 2) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-secondary">Paid Fines</h5>
                            <h2 class="card-text text-success">$<?= number_format($paidFines ?? 0, 2) ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fines Table -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Fine Details</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Transaction ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Book</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Borrow Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Return Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fine Amount</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($fines) && is_array($fines)): ?>
                                    <?php foreach ($fines as $fine): ?>
                                        <tr>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0 ps-3">
                                                    <?= htmlspecialchars($fine['tid'] ?? $fine['transactionId'] ?? 'N/A') ?>
                                                </p>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($fine['bookName'] ?? 'Unknown Book') ?></h6>
                                                        <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($fine['isbn'] ?? '') ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    <?= htmlspecialchars($fine['borrowDate'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    <?= htmlspecialchars($fine['returnDate'] ?? 'Not Returned') ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm bg-gradient-danger">
                                                    $<?= number_format((float)($fine['fineAmount'] ?? 0), 2) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php
                                                $status = $fine['fineStatus'] ?? 'pending';
                                                $badgeClass = $status === 'paid' ? 'bg-gradient-success' : 'bg-gradient-warning';
                                                ?>
                                                <span class="badge badge-sm <?= $badgeClass ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($status === 'pending'): ?>
                                                    <button class="btn btn-sm btn-primary" onclick="alert('Please contact the librarian to pay fines')">
                                                        Pay Fine
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-secondary mb-0">No fines found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
