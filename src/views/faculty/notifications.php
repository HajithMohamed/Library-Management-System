<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Notifications';
include APP_ROOT . '/views/layouts/header.php';

$notifications = $notifications ?? [];

// Separate read and unread notifications
$unreadNotifications = array_filter($notifications, fn($n) => !$n['isRead']);
$readNotifications = array_filter($notifications, fn($n) => $n['isRead']);
?>

<style>
    .notifications-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
    }
    
    .notifications-wrapper::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-50px) rotate(180deg); }
    }
    
    .notifications-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }
    
    /* Header Section */
    .notifications-header {
        background: white;
        border-radius: 30px 30px 0 0;
        padding: 2.5rem 3rem;
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.1);
        animation: slideInDown 0.6s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    
    .header-title h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .header-title h1 i {
        color: #667eea;
        animation: ring 2s ease-in-out infinite;
    }
    
    @keyframes ring {
        0%, 100% { transform: rotate(0deg); }
        10%, 30% { transform: rotate(-10deg); }
        20%, 40% { transform: rotate(10deg); }
    }
    
    .header-title p {
        color: #6b7280;
        font-size: 1.1rem;
        margin: 0;
    }
    
    .header-stats {
        display: flex;
        gap: 1.5rem;
    }
    
    .stat-badge {
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .stat-badge.unread {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .stat-badge.total {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: #667eea;
        border: 2px solid #667eea;
    }
    
    /* Tabs Section */
    .notifications-tabs {
        background: white;
        padding: 0 3rem;
        display: flex;
        gap: 2rem;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .tab-button {
        padding: 1.25rem 0;
        border: none;
        background: none;
        font-weight: 700;
        font-size: 1.05rem;
        color: #6b7280;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .tab-button.active {
        color: #667eea;
    }
    
    .tab-button::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    
    .tab-button.active::after {
        transform: scaleX(1);
    }
    
    .tab-button:hover {
        color: #667eea;
    }
    
    .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 12px;
        background: #ef4444;
        color: white;
        font-size: 0.75rem;
        margin-left: 0.5rem;
        font-weight: 700;
    }
    
    .tab-button.active .tab-count {
        background: #667eea;
    }
    
    /* Notifications Body */
    .notifications-body {
        background: white;
        border-radius: 0 0 30px 30px;
        padding: 2rem 3rem 3rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideInUp 0.6s ease-out 0.2s both;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.4s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    /* Notification Card */
    .notification-card {
        padding: 1.75rem;
        border-radius: 16px;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .notification-card.unread {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-left: 4px solid #667eea;
    }
    
    .notification-card.read {
        background: #f9fafb;
        border-left: 4px solid #e5e7eb;
    }
    
    .notification-card:hover {
        transform: translateX(5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-color: rgba(102, 126, 234, 0.3);
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
        gap: 1rem;
    }
    
    .notification-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .notification-card.priority-high .notification-icon-wrapper {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
        color: #ef4444;
    }
    
    .notification-card.priority-medium .notification-icon-wrapper {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        color: #f59e0b;
    }
    
    .notification-card.priority-low .notification-icon-wrapper {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.1), rgba(75, 85, 99, 0.1));
        color: #6b7280;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .notification-message {
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .notification-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .meta-badge {
        padding: 0.35rem 0.85rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .meta-badge.type-overdue {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
        color: #ef4444;
    }
    
    .meta-badge.type-reminder {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        color: #f59e0b;
    }
    
    .meta-badge.type-approval {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        color: #10b981;
    }
    
    .meta-badge.type-system {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
        color: #3b82f6;
    }
    
    .notification-time {
        font-size: 0.85rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    
    .notification-time i {
        font-size: 0.75rem;
    }
    
    .notification-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .mark-read-btn {
        padding: 0.65rem 1.25rem;
        border-radius: 10px;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .mark-read-btn:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    /* Back Button */
    .back-button {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    
    .back-button:hover {
        background: #667eea;
        color: white;
        transform: translateX(-5px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .back-button i {
        transition: transform 0.3s ease;
    }
    
    .back-button:hover i {
        transform: translateX(-3px);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-state-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 50%;
        font-size: 3.5rem;
        color: #667eea;
    }
    
    .empty-state h4 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.75rem;
    }
    
    .empty-state p {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 400px;
        margin: 0 auto;
        line-height: 1.6;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .notifications-wrapper {
            padding: 2rem 0;
        }
        
        .notifications-container {
            padding: 0 1rem;
        }
        
        .notifications-header {
            padding: 2rem 1.5rem;
        }
        
        .notifications-body {
            padding: 1.5rem;
        }
        
        .notifications-tabs {
            padding: 0 1.5rem;
            gap: 1rem;
        }
        
        .header-content {
            flex-direction: column;
            align-items: start;
        }
        
        .header-title h1 {
            font-size: 2rem;
        }
        
        .header-stats {
            width: 100%;
            justify-content: space-between;
        }
        
        .notification-card {
            padding: 1.25rem;
        }
        
        .notification-header {
            flex-direction: column;
        }
        
        .tab-button {
            font-size: 0.95rem;
            padding: 1rem 0;
        }
    }
</style>

<div class="notifications-wrapper">
    <div class="notifications-container">
        <!-- Header -->
        <div class="notifications-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>
                        <i class="fas fa-bell"></i>
                        Notifications
                    </h1>
                    <p>Stay updated with your library activities</p>
                </div>
                <div class="header-stats">
                    <a href="<?= BASE_URL ?>faculty/dashboard" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
                    <?php if (count($unreadNotifications) > 0): ?>
                        <div class="stat-badge unread">
                            <i class="fas fa-circle"></i>
                            <?= count($unreadNotifications) ?> Unread
                        </div>
                    <?php endif; ?>
                    <div class="stat-badge total">
                        <i class="fas fa-inbox"></i>
                        <?= count($notifications) ?> Total
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="notifications-tabs">
            <button class="tab-button active" onclick="switchTab('all')">
                All Notifications
                <span class="tab-count"><?= count($notifications) ?></span>
            </button>
            <button class="tab-button" onclick="switchTab('unread')">
                Unread
                <span class="tab-count"><?= count($unreadNotifications) ?></span>
            </button>
            <button class="tab-button" onclick="switchTab('read')">
                Read
                <span class="tab-count"><?= count($readNotifications) ?></span>
            </button>
        </div>

        <!-- Body -->
        <div class="notifications-body">
            <!-- All Tab -->
            <div class="tab-content active" id="tab-all">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h4>No Notifications</h4>
                        <p>You're all caught up! Check back later for new updates.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                        $priority = $notification['priority'] ?? 'medium';
                        $type = $notification['type'] ?? 'system';
                        $isRead = $notification['isRead'] ?? 0;
                        ?>
                        <div class="notification-card priority-<?= $priority ?> <?= $isRead ? 'read' : 'unread' ?>">
                            <div class="notification-header">
                                <div class="notification-icon-wrapper">
                                    <?php
                                    $icon = match($type) {
                                        'overdue' => 'fa-exclamation-triangle',
                                        'reminder' => 'fa-clock',
                                        'approval' => 'fa-check-circle',
                                        'fine_paid' => 'fa-money-bill-wave',
                                        'out_of_stock' => 'fa-box-open',
                                        default => 'fa-info-circle'
                                    };
                                    ?>
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <h5 class="notification-title"><?= htmlspecialchars($notification['title'] ?? 'Notification') ?></h5>
                                    <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                                    
                                    <div class="notification-meta">
                                        <span class="meta-badge type-<?= $type ?>">
                                            <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                        </span>
                                        <span class="notification-time">
                                            <i class="fas fa-clock"></i>
                                            <?= date('M d, Y • H:i', strtotime($notification['createdAt'])) ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!$isRead): ?>
                                        <div class="notification-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="mark_read" value="1">
                                                <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                <button type="submit" class="mark-read-btn">
                                                    <i class="fas fa-check"></i>
                                                    Mark as Read
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Unread Tab -->
            <div class="tab-content" id="tab-unread">
                <?php if (empty($unreadNotifications)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4>All Caught Up!</h4>
                        <p>You have no unread notifications at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($unreadNotifications as $notification): ?>
                        <?php
                        $priority = $notification['priority'] ?? 'medium';
                        $type = $notification['type'] ?? 'system';
                        ?>
                        <div class="notification-card priority-<?= $priority ?> unread">
                            <!-- ...existing code... (same structure as above) -->
                            <div class="notification-header">
                                <div class="notification-icon-wrapper">
                                    <?php
                                    $icon = match($type) {
                                        'overdue' => 'fa-exclamation-triangle',
                                        'reminder' => 'fa-clock',
                                        'approval' => 'fa-check-circle',
                                        'fine_paid' => 'fa-money-bill-wave',
                                        'out_of_stock' => 'fa-box-open',
                                        default => 'fa-info-circle'
                                    };
                                    ?>
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <h5 class="notification-title"><?= htmlspecialchars($notification['title'] ?? 'Notification') ?></h5>
                                    <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                                    
                                    <div class="notification-meta">
                                        <span class="meta-badge type-<?= $type ?>">
                                            <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                        </span>
                                        <span class="notification-time">
                                            <i class="fas fa-clock"></i>
                                            <?= date('M d, Y • H:i', strtotime($notification['createdAt'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="notification-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="mark_read" value="1">
                                            <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                            <button type="submit" class="mark-read-btn">
                                                <i class="fas fa-check"></i>
                                                Mark as Read
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Read Tab -->
            <div class="tab-content" id="tab-read">
                <?php if (empty($readNotifications)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <h4>No Read Notifications</h4>
                        <p>Notifications you mark as read will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($readNotifications as $notification): ?>
                        <?php
                        $priority = $notification['priority'] ?? 'medium';
                        $type = $notification['type'] ?? 'system';
                        ?>
                        <div class="notification-card priority-<?= $priority ?> read">
                            <div class="notification-header">
                                <div class="notification-icon-wrapper">
                                    <?php
                                    $icon = match($type) {
                                        'overdue' => 'fa-exclamation-triangle',
                                        'reminder' => 'fa-clock',
                                        'approval' => 'fa-check-circle',
                                        'fine_paid' => 'fa-money-bill-wave',
                                        'out_of_stock' => 'fa-box-open',
                                        default => 'fa-info-circle'
                                    };
                                    ?>
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <h5 class="notification-title"><?= htmlspecialchars($notification['title'] ?? 'Notification') ?></h5>
                                    <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                                    
                                    <div class="notification-meta">
                                        <span class="meta-badge type-<?= $type ?>">
                                            <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                        </span>
                                        <span class="notification-time">
                                            <i class="fas fa-clock"></i>
                                            <?= date('M d, Y • H:i', strtotime($notification['createdAt'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Remove active class from all tabs and buttons
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked button and corresponding content
    event.target.classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>