<?php
$pageTitle = 'Notifications Management';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-bell"></i> Notifications Management
                </h1>
                <div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= count(array_filter($notifications, fn($n) => $n['type'] === 'overdue')) ?></h4>
                            <p class="card-text">Overdue</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= count(array_filter($notifications, fn($n) => $n['type'] === 'fine_paid')) ?></h4>
                            <p class="card-text">Fines Paid</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
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
                            <h4 class="card-title"><?= count(array_filter($notifications, fn($n) => $n['type'] === 'out_of_stock')) ?></h4>
                            <p class="card-text">Out of Stock</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box-open fa-2x"></i>
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
                            <h4 class="card-title"><?= count(array_filter($notifications, fn($n) => !$n['isRead'])) ?></h4>
                            <p class="card-text">Unread</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
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
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-success btn-block" onclick="markAllAsRead()">
                                <i class="fas fa-check-double"></i> Mark All as Read
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-warning btn-block" onclick="checkOverdueNotifications()">
                                <i class="fas fa-sync"></i> Check Overdue
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-info btn-block" onclick="checkOutOfStockNotifications()">
                                <i class="fas fa-search"></i> Check Stock
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-danger btn-block" onclick="clearOldNotifications()">
                                <i class="fas fa-trash"></i> Clear Old
                            </button>
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
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="overdue" <?= $currentType === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                                <option value="fine_paid" <?= $currentType === 'fine_paid' ? 'selected' : '' ?>>Fine Paid</option>
                                <option value="out_of_stock" <?= $currentType === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                <option value="system" <?= $currentType === 'system' ? 'selected' : '' ?>>System</option>
                                <option value="reminder" <?= $currentType === 'reminder' ? 'selected' : '' ?>>Reminder</option>
                                <option value="approval" <?= $currentType === 'approval' ? 'selected' : '' ?>>Approval</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="">All Priorities</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="unread" name="unread" 
                                       <?= $unreadOnly ? 'checked' : '' ?>>
                                <label class="form-check-label" for="unread">
                                    Unread Only
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="<?= BASE_URL ?>admin/notifications" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Notifications
                        <span class="badge bg-primary ms-2"><?= count($notifications) ?> records</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item mb-3 p-3 border rounded <?= $notification['isRead'] ? 'bg-light' : 'bg-white border-primary' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <?php
                                            $typeIcons = [
                                                'overdue' => 'fas fa-exclamation-triangle text-danger',
                                                'fine_paid' => 'fas fa-money-bill-wave text-success',
                                                'out_of_stock' => 'fas fa-box-open text-warning',
                                                'system' => 'fas fa-cog text-info',
                                                'reminder' => 'fas fa-bell text-warning',
                                                'approval' => 'fas fa-check-circle text-success'
                                            ];
                                            $priorityColors = [
                                                'high' => 'danger',
                                                'medium' => 'warning',
                                                'low' => 'info'
                                            ];
                                            ?>
                                            <i class="<?= $typeIcons[$notification['type']] ?? 'fas fa-info-circle text-secondary' ?> me-2"></i>
                                            <h6 class="mb-0 <?= $notification['isRead'] ? '' : 'fw-bold' ?>">
                                                <?= htmlspecialchars($notification['title']) ?>
                                            </h6>
                                            <span class="badge bg-<?= $priorityColors[$notification['priority']] ?? 'secondary' ?> ms-2">
                                                <?= ucfirst($notification['priority']) ?>
                                            </span>
                                            <span class="badge bg-secondary ms-1">
                                                <?= ucfirst(str_replace('_', ' ', $notification['type'])) ?>
                                            </span>
                                        </div>
                                        <p class="mb-2 <?= $notification['isRead'] ? 'text-muted' : '' ?>">
                                            <?= htmlspecialchars($notification['message']) ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            <?= date('M j, Y H:i', strtotime($notification['createdAt'])) ?>
                                            <?php if ($notification['userId']): ?>
                                                | <i class="fas fa-user"></i> User: <?= htmlspecialchars($notification['userId']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <?php if (!$notification['isRead']): ?>
                                            <button type="button" class="btn btn-success btn-sm" 
                                                    onclick="markAsRead(<?= $notification['id'] ?>)">
                                                <i class="fas fa-check"></i> Mark Read
                                            </button>
                                        <?php else: ?>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> Read
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h5>No notifications found</h5>
                            <p>There are no notifications matching your criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Read Modal -->
<div class="modal fade" id="markReadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark as Read</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/notifications/mark-read">
                <div class="modal-body">
                    <input type="hidden" name="notificationId" id="markReadNotificationId">
                    <p>Are you sure you want to mark this notification as read?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Read</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    document.getElementById('markReadNotificationId').value = notificationId;
    const modal = new bootstrap.Modal(document.getElementById('markReadModal'));
    modal.show();
}

function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
        // Implementation for marking all as read
        alert('Mark all as read feature coming soon!');
    }
}

function checkOverdueNotifications() {
    if (confirm('Check for overdue notifications?')) {
        // Implementation for checking overdue notifications
        alert('Checking overdue notifications...');
        // You can implement AJAX call here
    }
}

function checkOutOfStockNotifications() {
    if (confirm('Check for out of stock notifications?')) {
        // Implementation for checking out of stock notifications
        alert('Checking out of stock notifications...');
        // You can implement AJAX call here
    }
}

function clearOldNotifications() {
    if (confirm('Clear notifications older than 30 days?')) {
        // Implementation for clearing old notifications
        alert('Clearing old notifications...');
        // You can implement AJAX call here
    }
}

// Auto-refresh notifications every 30 seconds
setInterval(function() {
    // Only refresh if we're on the notifications page and not in a modal
    if (window.location.pathname.includes('notifications') && !document.querySelector('.modal.show')) {
        // You can implement AJAX refresh here
        console.log('Auto-refreshing notifications...');
    }
}, 30000);
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
