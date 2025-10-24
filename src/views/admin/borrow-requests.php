<?php
$pageTitle = 'Borrow Requests Management';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-envelope-open-text"></i> Borrow Requests Management
                </h1>
                <div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="statusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $currentStatus === 'pending' ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>admin/borrow-requests?status=pending">
                                <i class="fas fa-clock"></i> Pending
                                <span class="badge bg-warning ms-1"><?= count(array_filter($requests, fn($r) => $r['status'] === 'Pending')) ?></span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $currentStatus === 'approved' ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>admin/borrow-requests?status=approved">
                                <i class="fas fa-check"></i> Approved
                                <span class="badge bg-success ms-1"><?= count(array_filter($requests, fn($r) => $r['status'] === 'Approved')) ?></span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $currentStatus === 'rejected' ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>admin/borrow-requests?status=rejected">
                                <i class="fas fa-times"></i> Rejected
                                <span class="badge bg-danger ms-1"><?= count(array_filter($requests, fn($r) => $r['status'] === 'Rejected')) ?></span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $currentStatus === 'all' ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>admin/borrow-requests?status=all">
                                <i class="fas fa-list"></i> All
                                <span class="badge bg-primary ms-1"><?= count($requests) ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <?php if ($currentStatus === 'pending'): ?>
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
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-success btn-block" onclick="approveAllPending()">
                                <i class="fas fa-check-double"></i> Approve All Pending
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-info btn-block" onclick="checkAvailability()">
                                <i class="fas fa-search"></i> Check Availability
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-warning btn-block" onclick="sendReminders()">
                                <i class="fas fa-bell"></i> Send Reminders
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-secondary btn-block" onclick="exportRequests()">
                                <i class="fas fa-download"></i> Export Requests
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Borrow Requests
                        <span class="badge bg-primary ms-2"><?= count($requests) ?> records</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>User</th>
                                    <th>Book</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?= $request['id'] ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($request['emailId']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($request['userType']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($request['bookName']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($request['authorName']) ?></small>
                                                    <br>
                                                    <small class="text-info">ISBN: <?= htmlspecialchars($request['isbn']) ?></small>
                                                </div>
                                            </td>
                                            <td><?= date('M j, Y H:i', strtotime($request['requestDate'])) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'Pending' => 'warning',
                                                    'Approved' => 'success',
                                                    'Rejected' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$request['status']] ?? 'secondary' ?>">
                                                    <?= $request['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($request['approvedBy']): ?>
                                                    <?= htmlspecialchars($request['approvedBy']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($request['dueDate']): ?>
                                                    <?= date('M j, Y', strtotime($request['dueDate'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($request['status'] === 'Pending'): ?>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="approveRequest(<?= $request['id'] ?>)">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="rejectRequest(<?= $request['id'] ?>)">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                        <button type="button" class="btn btn-info btn-sm" 
                                                                onclick="viewDetails(<?= $request['id'] ?>)">
                                                            <i class="fas fa-eye"></i> Details
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            onclick="viewDetails(<?= $request['id'] ?>)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No requests found
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

<!-- Approve Request Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Borrow Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/borrow-requests/handle">
                <div class="modal-body">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="requestId" id="approveRequestId">
                    
                    <div class="mb-3">
                        <label for="dueDate" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="dueDate" name="dueDate" 
                               value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This will create a transaction record and decrease the book's available count.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Request Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Borrow Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/borrow-requests/handle">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="requestId" id="rejectRequestId">
                    
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" 
                                  placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        The user will be notified about the rejection.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function approveRequest(requestId) {
    document.getElementById('approveRequestId').value = requestId;
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectRequest(requestId) {
    document.getElementById('rejectRequestId').value = requestId;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function viewDetails(requestId) {
    // Load request details via AJAX
    fetch(`<?= BASE_URL ?>admin/borrow-requests/details/${requestId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('requestDetails').innerHTML = data.html;
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load request details');
        });
}

function approveAllPending() {
    if (confirm('Approve all pending requests?')) {
        // Implementation for bulk approval
        alert('Bulk approval feature coming soon!');
    }
}

function checkAvailability() {
    alert('Availability check feature coming soon!');
}

function sendReminders() {
    alert('Reminder feature coming soon!');
}

function exportRequests() {
    alert('Export feature coming soon!');
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
