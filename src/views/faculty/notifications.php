<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Notifications';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <h2>🔔 Notifications</h2>
    
    <?php if (!empty($notifications)): ?>
        <div class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <div class="list-group-item <?= $notification['isRead'] ? '' : 'list-group-item-info' ?>">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h5>
                        <small><?= date('M d, Y H:i', strtotime($notification['createdAt'])) ?></small>
                    </div>
                    <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                    <small class="text-muted">
                        <span class="badge bg-<?= $notification['priority'] === 'high' ? 'danger' : ($notification['priority'] === 'medium' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst($notification['priority']) ?>
                        </span>
                    </small>
                    
                    <?php if (!$notification['isRead']): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="mark_read" value="1">
                            <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-2">Mark as Read</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No notifications found.</p>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>