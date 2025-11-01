<?php
$pageTitle = 'Notifications';
include APP_ROOT . '/views/layouts/header.php';

$notifications = $notifications ?? [];
$userType = $userType ?? 'Student';
?>

<style>
    .notifications-container {
        padding: 2rem 0;
        animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .notifications-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        animation: slideInDown 0.6s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .notifications-header h1 {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .notifications-header p {
        color: #6b7280;
        font-size: 1.05rem;
        margin: 0;
    }
    
    .notifications-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
    
    .notification-item {
        padding: 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .notification-item:hover {
        background: rgba(102, 126, 234, 0.02);
    }
    
    .notification-item.unread {
        background: rgba(102, 126, 234, 0.05);
        border-left: 4px solid #667eea;
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.75rem;
        gap: 1rem;
    }
    
    .notification-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }
    
    .notification-time {
        font-size: 0.85rem;
        color: #9ca3af;
        white-space: nowrap;
    }
    
    .notification-message {
        color: #4b5563;
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }
    
    .notification-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .priority-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .priority-badge.high {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
        color: #ef4444;
    }
    
    .priority-badge.medium {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        color: #f59e0b;
    }
    
    .priority-badge.low {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.1), rgba(75, 85, 99, 0.1));
        color: #6b7280;
    }
    
    .type-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .mark-read-btn {
        padding: 0.5rem 1rem;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .mark-read-btn:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-state-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 50%;
        font-size: 3rem;
        color: #667eea;
    }
    
    .empty-state h4 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #6b7280;
        font-size: 1.05rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) {
        .notification-header {
            flex-direction: column;
            align-items: start;
        }
        
        .notification-meta {
            flex-direction: column;
            align-items: start;
        }
    }
</style>

<div class="notifications-container">
    <div class="container">
        <!-- Notifications Header -->
        <div class="notifications-header">
            <h1>🔔 Notifications</h1>
            <p>Stay updated with your library activities and important announcements</p>
            <p style="font-size: 0.95rem; color: #667eea; font-weight: 600; margin-top: 0.5rem;">
                <i class="fas fa-user-circle"></i> Viewing as: <?= htmlspecialchars($userType) ?>
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="notifications-card">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?= !$notification['isRead'] ? 'unread' : '' ?>">
                                <div class="notification-header">
                                    <h3 class="notification-title">
                                        <?= htmlspecialchars($notification['title'] ?? 'Notification') ?>
                                    </h3>
                                    <span class="notification-time">
                                        <?= date('M d, Y H:i', strtotime($notification['createdAt'])) ?>
                                    </span>
                                </div>
                                
                                <p class="notification-message">
                                    <?= htmlspecialchars($notification['message']) ?>
                                </p>
                                
                                <div class="notification-meta">
                                    <?php if (isset($notification['priority'])): ?>
                                        <span class="priority-badge <?= strtolower($notification['priority']) ?>">
                                            <?= ucfirst($notification['priority']) ?> Priority
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($notification['type'])): ?>
                                        <span class="type-badge">
                                            <?= ucfirst(str_replace('_', ' ', $notification['type'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($notification['userId'] === null): ?>
                                        <span class="type-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                            <i class="fas fa-globe"></i> System-wide
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!$notification['isRead']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="mark_read" value="1">
                                            <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                            <button type="submit" class="mark-read-btn">
                                                ✓ Mark as Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <h4>No Notifications</h4>
                            <p>You're all caught up! No new notifications at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
