<?php
$pageTitle = 'Fine Management';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-money-bill-wave"></i> Fine Management
                </h1>
                <div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Fine Settings Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cog"></i> Fine Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/fines">
                        <input type="hidden" name="action" value="update_settings">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="fine_per_day" class="form-label">Fine Per Day (₹)</label>
                                <input type="number" class="form-control" id="fine_per_day" name="settings[fine_per_day]" 
                                       value="<?= $fineSettings['fine_per_day'] ?? '5' ?>" min="0" step="0.01">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="max_borrow_days" class="form-label">Max Borrow Days</label>
                                <input type="number" class="form-control" id="max_borrow_days" name="settings[max_borrow_days]" 
                                       value="<?= $fineSettings['max_borrow_days'] ?? '14' ?>" min="1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="grace_period_days" class="form-label">Grace Period (Days)</label>
                                <input type="number" class="form-control" id="grace_period_days" name="settings[grace_period_days]" 
                                       value="<?= $fineSettings['grace_period_days'] ?? '0' ?>" min="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="max_fine_amount" class="form-label">Max Fine Amount (₹)</label>
                                <input type="number" class="form-control" id="max_fine_amount" name="settings[max_fine_amount]" 
                                       value="<?= $fineSettings['max_fine_amount'] ?? '500' ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fine_calculation_method" class="form-label">Calculation Method</label>
                                <select class="form-control" id="fine_calculation_method" name="settings[fine_calculation_method]">
                                    <option value="daily" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                    <option value="fixed" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <form method="POST" action="<?= BASE_URL ?>admin/fines" class="d-inline">
                                <input type="hidden" name="action" value="update_all_fines">
                                <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Update all fines?')">
                                    <i class="fas fa-sync"></i> Update All Fines
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= BASE_URL ?>admin/fines?status=pending" class="btn btn-info btn-block">
                                <i class="fas fa-clock"></i> View Pending Fines
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= BASE_URL ?>admin/fines?status=paid" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> View Paid Fines
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $currentStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="waived" <?= $currentStatus === 'waived' ? 'selected' : '' ?>>Waived</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="userId" class="form-label">User ID</label>
                            <input type="text" class="form-control" id="userId" name="userId" 
                                   value="<?= htmlspecialchars($currentUserId ?? '') ?>" placeholder="Enter User ID">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="<?= BASE_URL ?>admin/fines" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Fines Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Fines List
                        <span class="badge bg-primary ms-2"><?= count($fines) ?> records</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>User</th>
                                    <th>Book</th>
                                    <th>Borrow Date</th>
                                    <th>Fine Amount</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($fines)): ?>
                                    <?php foreach ($fines as $fine): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($fine['tid']) ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($fine['emailId']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($fine['userType']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($fine['bookName']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($fine['authorName']) ?></small>
                                                </div>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($fine['borrowDate'])) ?></td>
                                            <td>
                                                <span class="badge bg-danger">₹<?= number_format($fine['fineAmount'], 2) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'paid' => 'success',
                                                    'waived' => 'info'
                                                ];
                                                $status = $fine['fineStatus'] ?? 'pending';
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$status] ?? 'secondary' ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($fine['finePaymentDate']): ?>
                                                    <?= date('M j, Y', strtotime($fine['finePaymentDate'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($status === 'pending'): ?>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="updateFineStatus('<?= $fine['tid'] ?>', 'paid')">
                                                            <i class="fas fa-check"></i> Mark Paid
                                                        </button>
                                                        <button type="button" class="btn btn-info btn-sm" 
                                                                onclick="updateFineStatus('<?= $fine['tid'] ?>', 'waived')">
                                                            <i class="fas fa-gift"></i> Waive
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No actions</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No fines found
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

<!-- Update Fine Status Modal -->
<div class="modal fade" id="updateFineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Fine Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/fines">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="transactionId" id="modalTransactionId">
                    <input type="hidden" name="status" id="modalStatus">
                    
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-control" id="paymentMethod" name="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateFineStatus(transactionId, status) {
    document.getElementById('modalTransactionId').value = transactionId;
    document.getElementById('modalStatus').value = status;
    
    // Show/hide payment method based on status
    const paymentMethodDiv = document.querySelector('#paymentMethod').closest('.mb-3');
    if (status === 'paid') {
        paymentMethodDiv.style.display = 'block';
    } else {
        paymentMethodDiv.style.display = 'none';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('updateFineModal'));
    modal.show();
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
