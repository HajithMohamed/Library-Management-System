<?php
$pageTitle = 'Renewal Requests';
include APP_ROOT . '/views/admin/admin-navbar.php';

$requests = $requests ?? [];
$currentStatus = $currentStatus ?? 'Pending';
$statusCounts = $statusCounts ?? ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'all' => 0];
?>

<style>
    body {
        margin: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
    }

    .admin-layout {
        display: flex;
        min-height: 100vh;
    }

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

    .container {
        padding: 2.5rem;
        flex: 1;
        width: 100%;
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
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .page-title i {
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
        color: #475569;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .back-btn:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    /* Status Tabs */
    .status-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .status-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .status-tab:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .status-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }

    .tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 11px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .status-tab.active .tab-badge {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .status-tab:not(.active) .tab-badge {
        background: #e2e8f0;
        color: #64748b;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .responsive-table {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    th {
        padding: 1rem 1.25rem;
        text-align: left;
        font-weight: 700;
        color: white;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.925rem;
    }

    tbody tr {
        transition: all 0.2s ease;
    }

    tbody tr:hover {
        background: rgba(102, 126, 234, 0.04);
    }

    .badge {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-user {
        background: #e0e7ff;
        color: #3730a3;
    }

    /* Action Buttons */
    .action-btns {
        display: flex;
        gap: 0.5rem;
    }

    .btn-approve {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-reject {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        opacity: 0.4;
    }

    .empty-state h4 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.5rem;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 9998;
    }

    .modal-box {
        display: none;
        position: fixed;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 20px;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        z-index: 9999;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-box h3 {
        margin: 0 0 1.5rem;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .modal-box textarea {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        resize: vertical;
        min-height: 80px;
        font-family: inherit;
    }

    .modal-box textarea:focus {
        outline: none;
        border-color: #667eea;
    }

    .modal-box label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #334155;
    }

    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        margin-top: 1.5rem;
    }

    .btn-cancel {
        padding: 0.6rem 1.25rem;
        background: #e2e8f0;
        color: #475569;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; }
        .container { padding: 1.5rem; }
    }
</style>

<div class="admin-layout">
<main class="main-content">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-sync-alt"></i>
                Renewal Requests
            </h1>
            <a href="<?= BASE_URL ?>admin/dashboard" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Status Tabs -->
        <div class="status-tabs">
            <a href="<?= BASE_URL ?>admin/renewal-requests?status=Pending" 
               class="status-tab <?= $currentStatus === 'Pending' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> Pending
                <span class="tab-badge"><?= $statusCounts['Pending'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>admin/renewal-requests?status=Approved" 
               class="status-tab <?= $currentStatus === 'Approved' ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i> Approved
                <span class="tab-badge"><?= $statusCounts['Approved'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>admin/renewal-requests?status=Rejected" 
               class="status-tab <?= $currentStatus === 'Rejected' ? 'active' : '' ?>">
                <i class="fas fa-times-circle"></i> Rejected
                <span class="tab-badge"><?= $statusCounts['Rejected'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>admin/renewal-requests?status=all" 
               class="status-tab <?= $currentStatus === 'all' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> All
                <span class="tab-badge"><?= $statusCounts['all'] ?? 0 ?></span>
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div style="background:#d1fae5; color:#065f46; padding:1rem 1.5rem; border-radius:12px; margin-bottom:1.5rem; font-weight:600;">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:1rem 1.5rem; border-radius:12px; margin-bottom:1.5rem; font-weight:600;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Table -->
        <?php if (!empty($requests)): ?>
        <div class="table-card">
            <div class="responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Book</th>
                            <th>Transaction ID</th>
                            <th>Current Due</th>
                            <th>Requested Due</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Requested On</th>
                            <?php if ($currentStatus === 'Pending'): ?>
                            <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $req): ?>
                            <tr>
                                <td><strong><?= $i + 1 ?></strong></td>
                                <td>
                                    <div><strong><?= htmlspecialchars($req['username'] ?? $req['userId']) ?></strong></div>
                                    <div style="font-size:0.8rem; color:#94a3b8;"><?= htmlspecialchars($req['emailId'] ?? '') ?></div>
                                    <span class="badge badge-user"><?= htmlspecialchars($req['userType'] ?? '') ?></span>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($req['bookName'] ?? '') ?></strong></div>
                                    <div style="font-size:0.8rem; color:#94a3b8;"><?= htmlspecialchars($req['authorName'] ?? '') ?></div>
                                </td>
                                <td><code style="background:#f1f5f9; padding:2px 8px; border-radius:6px;"><?= htmlspecialchars($req['tid']) ?></code></td>
                                <td><?= date('M d, Y', strtotime($req['currentDueDate'])) ?></td>
                                <td><strong style="color:#059669;"><?= date('M d, Y', strtotime($req['requestedDueDate'])) ?></strong></td>
                                <td><?= htmlspecialchars($req['reason'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($req['status']) {
                                        'Pending' => 'badge-pending',
                                        'Approved' => 'badge-approved',
                                        'Rejected' => 'badge-rejected',
                                        default => 'badge-pending'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $req['status'] ?></span>
                                    <?php if ($req['adminNote']): ?>
                                        <div style="font-size:0.75rem; color:#94a3b8; margin-top:4px;">
                                            <i class="fas fa-comment"></i> <?= htmlspecialchars($req['adminNote']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($req['createdAt'])) ?></td>
                                <?php if ($currentStatus === 'Pending'): ?>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-approve" onclick="showActionModal(<?= $req['id'] ?>, 'approve', '<?= htmlspecialchars(addslashes($req['bookName'] ?? '')) ?>')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn-reject" onclick="showActionModal(<?= $req['id'] ?>, 'reject', '<?= htmlspecialchars(addslashes($req['bookName'] ?? '')) ?>')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="table-card">
            <div class="empty-state">
                <i class="fas fa-sync-alt"></i>
                <h4>No <?= $currentStatus !== 'all' ? htmlspecialchars($currentStatus) : '' ?> Renewal Requests</h4>
                <p>There are no renewal requests to display.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
</main>
</div>

<!-- Action Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
<div class="modal-box" id="actionModal">
    <h3 id="modalTitle">Process Renewal Request</h3>
    <p id="modalBookInfo" style="color:#667eea; font-weight:600;"></p>
    <form method="POST" action="<?= BASE_URL ?>admin/renewal-requests/handle">
        <input type="hidden" name="requestId" id="modalRequestId">
        <input type="hidden" name="action" id="modalAction">
        <div>
            <label for="adminNote">Admin Note (Optional)</label>
            <textarea name="adminNote" id="adminNote" placeholder="Add a note for the user..."></textarea>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button type="submit" class="btn-approve" id="modalSubmitBtn">
                <i class="fas fa-check"></i> Confirm
            </button>
        </div>
    </form>
</div>

<script>
function showActionModal(requestId, action, bookName) {
    document.getElementById('modalRequestId').value = requestId;
    document.getElementById('modalAction').value = action;
    document.getElementById('modalBookInfo').textContent = 'Book: ' + bookName;
    
    const submitBtn = document.getElementById('modalSubmitBtn');
    if (action === 'approve') {
        document.getElementById('modalTitle').textContent = 'Approve Renewal Request';
        submitBtn.className = 'btn-approve';
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Approve';
    } else {
        document.getElementById('modalTitle').textContent = 'Reject Renewal Request';
        submitBtn.className = 'btn-reject';
        submitBtn.innerHTML = '<i class="fas fa-times"></i> Reject';
    }
    
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('actionModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
    document.getElementById('actionModal').style.display = 'none';
    document.getElementById('adminNote').value = '';
}
</script>
