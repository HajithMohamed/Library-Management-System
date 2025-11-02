<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Notifications';
include APP_ROOT . '/views/layouts/header.php';

$notifications = $notifications ?? [];
$userType = $userType ?? 'Faculty';

// Separate read and unread notifications
$unreadNotifications = array_filter($notifications, fn($n) => !$n['isRead']);
$readNotifications = array_filter($notifications, fn($n) => $n['isRead']);
?>

<style>
    .notifications-wrapper {
        min-height: 100vh;
        padding: 2rem 0 4rem;
        position: relative;
        overflow: hidden;
    }
    
    .notifications-wrapper::before {
        content: '';
        position: absolute;
        top: -20%;
        right: -5%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 25s infinite ease-in-out;
        pointer-events: none;
    }
    
    .notifications-wrapper::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -5%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(118, 75, 162, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 30s infinite ease-in-out reverse;
        pointer-events: none;
    }
    
    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        25% { transform: translate(30px, -30px) rotate(90deg); }
        50% { transform: translate(-20px, 20px) rotate(180deg); }
        75% { transform: translate(20px, 30px) rotate(270deg); }
    }
    
    .notifications-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }
    
    /* Header Section */
    .notifications-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.5);
        animation: slideInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-40px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 2rem;
    }
    
    .header-title-section {
        flex: 1;
        min-width: 300px;
    }
    
    .header-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .title-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        animation: ring 3s ease-in-out infinite;
    }
    
    @keyframes ring {
        0%, 100% { transform: rotate(0deg); }
        5%, 15% { transform: rotate(-15deg); }
        10%, 20% { transform: rotate(15deg); }
        25% { transform: rotate(0deg); }
    }
    
    .header-title h1 {
        font-size: 2.25rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        letter-spacing: -0.5px;
    }
    
    .header-subtitle {
        color: #6b7280;
        font-size: 1.05rem;
        margin-bottom: 0.5rem;
    }
    
    .user-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: #667eea;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid rgba(102, 126, 234, 0.2);
    }
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .stat-badge {
        padding: 1rem 1.5rem;
        border-radius: 16px;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }
    
    .stat-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }
    
    .stat-badge i {
        font-size: 1.1rem;
    }
    
    .stat-badge.unread {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3); }
        50% { box-shadow: 0 8px 30px rgba(239, 68, 68, 0.5); }
    }
    
    .stat-badge.total {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }
    
    .back-button {
        padding: 0.875rem 1.75rem;
        border-radius: 14px;
        background: white;
        color: #667eea;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        border: 2px solid #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }
    
    .back-button:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: translateX(-5px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    .back-button i {
        transition: transform 0.3s ease;
    }
    
    .back-button:hover i {
        transform: translateX(-4px);
    }
    
    /* Tabs Section */
    .notifications-tabs {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        padding: 0.75rem 2rem;
        border-radius: 20px;
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.5);
        animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .tab-button {
        padding: 1rem 1.75rem;
        border: none;
        background: transparent;
        font-weight: 700;
        font-size: 1rem;
        color: #6b7280;
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        border-radius: 14px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .tab-button.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .tab-button:hover:not(.active) {
        background: rgba(102, 126, 234, 0.1);
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
        background: rgba(255, 255, 255, 0.3);
        font-size: 0.75rem;
        font-weight: 800;
        transition: all 0.3s ease;
    }
    
    .tab-button.active .tab-count {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }
    
    .tab-button:not(.active) .tab-count {
        background: rgba(102, 126, 234, 0.15);
        color: #667eea;
    }
    
    /* Notifications Body */
    .notifications-body {
        animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
    }
    
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
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
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Notification Card */
    .notification-card {
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 1.25rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
    }
    
    .notification-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
        opacity: 0;
        transition: opacity 0.4s ease;
        pointer-events: none;
    }
    
    .notification-card:hover::before {
        opacity: 1;
    }
    
    .notification-card.unread {
        border-color: rgba(102, 126, 234, 0.3);
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.15);
    }
    
    .notification-card.read {
        background: rgba(249, 250, 251, 0.95);
        border-color: rgba(229, 231, 235, 0.5);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .notification-card:hover {
        transform: translateY(-4px) translateX(4px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        border-color: rgba(102, 126, 234, 0.5);
    }
    
    .notification-header {
        display: flex;
        gap: 1.5rem;
        align-items: start;
    }
    
    .notification-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
        transition: transform 0.3s ease;
        position: relative;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .notification-card:hover .notification-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }
    
    .notification-card.priority-high .notification-icon-wrapper {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .notification-card.priority-medium .notification-icon-wrapper {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
    }
    
    .notification-card.priority-low .notification-icon-wrapper {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
    }
    
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    
    .notification-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }
    
    .notification-message {
        color: #4b5563;
        line-height: 1.7;
        margin-bottom: 1.25rem;
        font-size: 1rem;
    }
    
    .notification-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .meta-badge {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .meta-badge:hover {
        transform: translateY(-2px);
    }
    
    .meta-badge.type-overdue {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    
    .meta-badge.type-reminder {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(249, 115, 22, 0.15));
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    
    .meta-badge.type-approval {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .meta-badge.type-system,
    .meta-badge.type-fine_paid {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(6, 182, 212, 0.15));
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }
    
    .meta-badge.type-out_of_stock {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    
    .notification-time {
        font-size: 0.85rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
    }
    
    .notification-time i {
        font-size: 0.85rem;
    }
    
    .notification-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }
    
    .mark-read-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .mark-read-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
    }
    
    .mark-read-btn:active {
        transform: translateY(-1px);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
    }
    
    .empty-state-icon {
        width: 140px;
        height: 140px;
        margin: 0 auto 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 50%;
        font-size: 4rem;
        color: #667eea;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.2);
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    .empty-state h4 {
        font-size: 1.85rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 450px;
        margin: 0 auto;
        line-height: 1.7;
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .notifications-container {
            padding: 0 1.5rem;
        }
        
        .header-content {
            flex-direction: column;
            align-items: stretch;
        }
        
        .header-actions {
            justify-content: space-between;
        }
    }
    
    @media (max-width: 768px) {
        .notifications-wrapper {
            padding: 1.5rem 0 3rem;
        }
        
        .notifications-container {
            padding: 0 1rem;
        }
        
        .notifications-header {
            padding: 2rem 1.5rem;
        }
        
        .notifications-tabs {
            padding: 0.5rem;
            gap: 0.25rem;
            overflow-x: auto;
            scrollbar-width: none;
        }
        
        .notifications-tabs::-webkit-scrollbar {
            display: none;
        }
        
        .header-title h1 {
            font-size: 1.75rem;
        }
        
        .title-icon {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }
        
        .header-actions {
            width: 100%;
            flex-direction: column;
        }
        
        .stat-badge {
            width: 100%;
            justify-content: center;
        }
        
        .back-button {
            width: 100%;
            justify-content: center;
        }
        
        .notification-card {
            padding: 1.5rem;
        }
        
        .notification-header {
            flex-direction: column;
            gap: 1rem;
        }
        
        .notification-icon-wrapper {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }
        
        .tab-button {
            font-size: 0.9rem;
            padding: 0.875rem 1.25rem;
            white-space: nowrap;
        }
        
        .empty-state {
            padding: 3rem 1rem;
        }
        
        .empty-state-icon {
            width: 100px;
            height: 100px;
            font-size: 3rem;
        }
    }
    
    @media (max-width: 480px) {
        .header-title h1 {
            font-size: 1.5rem;
        }
        
        .notification-title {
            font-size: 1.05rem;
        }
        
        .notification-message {
            font-size: 0.95rem;
        }
        
        .meta-badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }
    }
</style>

<div class="notifications-wrapper">
    <div class="notifications-container">
        <!-- Header -->
        <div class="notifications-header">
            <div class="header-content">
                <div class="header-title-section">
                    <div class="header-title">
                        <div class="title-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h1>Notifications</h1>
                    </div>
                    <p class="header-subtitle">Stay updated with your library activities</p>
                    <span class="user-type-badge">
                        <i class="fas fa-user-graduate"></i>
                        <?= htmlspecialchars($userType) ?>
                    </span>
                </div>
                <div class="header-actions">
                    <a href="<?= BASE_URL ?>faculty/dashboard" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <?php if (count($unreadNotifications) > 0): ?>
                        <div class="stat-badge unread">
                            <i class="fas fa-circle-dot"></i>
                            <span><?= count($unreadNotifications) ?> Unread</span>
                        </div>
                    <?php endif; ?>
                    <div class="stat-badge total">
                        <i class="fas fa-inbox"></i>
                        <span><?= count($notifications) ?> Total</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="notifications-tabs">
            <button class="tab-button active" onclick="switchTab(event, 'all')">
                <i class="fas fa-list"></i>
                All
                <span class="tab-count"><?= count($notifications) ?></span>
            </button>
            <button class="tab-button" onclick="switchTab(event, 'unread')">
                <i class="fas fa-envelope"></i>
                Unread
                <span class="tab-count"><?= count($unreadNotifications) ?></span>
            </button>
            <button class="tab-button" onclick="switchTab(event, 'read')">
                <i class="fas fa-envelope-open"></i>
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
                                        <?php if ($notification['userId'] === null): ?>
                                            <span class="meta-badge" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15)); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                                                <i class="fas fa-globe"></i> System-wide
                                            </span>
                                        <?php endif; ?>
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
                                        <?php if ($notification['userId'] === null): ?>
                                            <span class="meta-badge" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15)); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                                                <i class="fas fa-globe"></i> System-wide
                                            </span>
                                        <?php endif; ?>
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
function switchTab(event, tabName) {
    // Remove active class from all tabs and buttons
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked button and corresponding content
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>