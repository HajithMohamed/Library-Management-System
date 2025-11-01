<?php
// Session checks, authentication, etc.
$pageTitle = 'Notifications Management';
include APP_ROOT . '/views/admin/admin-navbar.php';

// Safe extraction of variables with defaults
$notifications = $notifications ?? [];
$currentType = $currentType ?? null;
$unreadOnly = $unreadOnly ?? false;
$userTypeFilter = $userTypeFilter ?? null;
$viewMode = $viewMode ?? 'own';
$totalNotifications = count($notifications);
$unreadCount = count(array_filter($notifications, fn($n) => !$n['isRead']));
?>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        overflow-x: hidden;
    }

    .admin-layout {
        display: flex;
        min-height: 100vh;
    }

    /* Main Content Area */
    .main-content {
        flex: 1;
        margin-left: 280px;
        transition: margin-left 0.3s ease;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .sidebar.collapsed ~ .main-content {
        margin-left: 80px;
    }

    /* Content Wrapper */
    .content-wrapper {
        padding: 2.5rem;
        flex: 1;
    }

    /* Page Header - Dashboard Style */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .page-header-left h1 {
        font-size: 2rem;
        margin: 0 0 0.5rem 0;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }

    .breadcrumb a {
        color: #64748b;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb a:hover {
        color: #667eea;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
    }

    .breadcrumb .active {
        color: #0f172a;
        font-weight: 500;
    }

    /* Two Column Layout */
    .notifications-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .notifications-layout {
            grid-template-columns: 1fr;
        }
        
        .sidebar-panel {
            order: -1;
        }
    }

    /* Notifications Panel (Left) */
    .notifications-panel {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border-radius: 18px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: all 0.3s ease;
    }

    .filter-card:hover {
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.08), 0 16px 24px rgba(0, 0, 0, 0.05);
    }

    .filter-card h3 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        letter-spacing: -0.01em;
    }

    .filter-card h3 i {
        color: #667eea;
        font-size: 1.25rem;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-radius: 10px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
    }

    .form-label {
        font-weight: 700;
        color: #334155;
        font-size: 0.9rem;
        letter-spacing: -0.01em;
    }

    .form-control {
        padding: 0.875rem 1.125rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #f8fafc;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        transform: translateY(-1px);
    }

    .form-control:hover {
        border-color: #cbd5e1;
        background: white;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.875rem 1.125rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .form-check:hover {
        border-color: #667eea;
        background: white;
    }

    .form-check-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #667eea;
    }

    .form-check-label {
        cursor: pointer;
        font-weight: 600;
        color: #475569;
        font-size: 0.95rem;
    }

    /* Notifications List Card */
    .notifications-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(226, 232, 240, 0.6);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .notifications-card:hover {
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.08), 0 16px 24px rgba(0, 0, 0, 0.05);
    }

    .notifications-header {
        padding: 2rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notifications-header h3 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        letter-spacing: -0.01em;
    }

    .notifications-header i {
        color: #667eea;
        font-size: 1.5rem;
        animation: bellRing 3s ease-in-out infinite;
    }

    @keyframes bellRing {
        0%, 100% {
            transform: rotate(0deg);
        }
        10%, 30% {
            transform: rotate(-10deg);
        }
        20%, 40% {
            transform: rotate(10deg);
        }
        50% {
            transform: rotate(0deg);
        }
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-block;
        letter-spacing: 0.3px;
    }

    .badge-primary {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .notifications-body {
        max-height: calc(100vh - 400px);
        overflow-y: auto;
        padding: 1.5rem;
        background: #fafbfc;
    }

    .notifications-body::-webkit-scrollbar {
        width: 8px;
    }

    .notifications-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .notifications-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
        border-radius: 10px;
    }

    .notifications-body::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
    }

    /* Notification Item */
    .notification-item {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .notification-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .notification-item:hover {
        transform: translateX(8px);
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
        border-color: #667eea;
    }

    .notification-item:hover::before {
        transform: scaleY(1);
    }

    .notification-item.unread {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-color: #667eea;
        border-left-width: 4px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }

    .notification-item.unread::after {
        content: '';
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 10px;
        height: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.5;
            transform: scale(1.2);
        }
    }

    .notification-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .notification-title-section {
        flex: 1;
    }

    .notification-title-section h6 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.625rem;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        line-height: 1.4;
    }

    .notification-title-section h6 i {
        font-size: 1.15rem;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .notification-title-section h6 i.fa-exclamation-triangle {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
        color: #dc2626;
    }

    .notification-title-section h6 i.fa-money-bill-wave {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
        color: #059669;
    }

    .notification-title-section h6 i.fa-box-open {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
        color: #d97706;
    }

    .notification-title-section h6 i.fa-cog {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.15) 0%, rgba(8, 145, 178, 0.15) 100%);
        color: #0891b2;
    }

    .notification-title-section h6 i.fa-bell {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
        color: #f59e0b;
    }

    .notification-title-section h6 i.fa-check-circle {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
        color: #10b981;
    }

    .notification-title-section h6 i.fa-info-circle {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
        color: #6366f1;
    }

    .notification-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .badge-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    }

    .badge-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
    }

    .badge-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        box-shadow: 0 2px 4px rgba(6, 182, 212, 0.2);
    }

    .badge-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }

    .badge-secondary {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #475569;
        box-shadow: 0 2px 4px rgba(71, 85, 105, 0.15);
    }

    .notification-message {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.7;
        margin-bottom: 1rem;
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%);
        border-radius: 10px;
        border-left: 3px solid #e2e8f0;
    }

    .notification-item.unread .notification-message {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(240, 249, 255, 0.8) 50%);
        border-left-color: #667eea;
    }

    .notification-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        font-size: 0.85rem;
        color: #94a3b8;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 8px;
    }

    .notification-meta span {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-weight: 500;
    }

    .notification-meta i {
        color: #cbd5e1;
    }

    /* Sidebar Panel (Right) */
    .sidebar-panel {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        position: sticky;
        top: 2rem;
    }

    /* Stats Card */
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .stats-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }

    .stats-card h3 i {
        color: #667eea;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.875rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-label {
        font-weight: 600;
        color: #475569;
        font-size: 0.9rem;
    }

    .stat-value {
        font-weight: 700;
        font-size: 1.25rem;
        color: #0f172a;
    }

    .stat-value.unread {
        color: #667eea;
    }

    /* Actions Card */
    .actions-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .actions-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }

    .actions-card h3 i {
        color: #667eea;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    /* Buttons */
    .btn {
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 0.9rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
    }

    .btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
    }

    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(6, 182, 212, 0.5);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .btn-block {
        width: 100%;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 5rem;
        opacity: 0.2;
        margin-bottom: 2rem;
        display: block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    .empty-state h5 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #475569;
        margin-bottom: 0.75rem;
        letter-spacing: -0.02em;
    }

    .empty-state p {
        font-size: 1rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Read Status Button */
    .read-status {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .read-status.read {
        color: #10b981;
    }

    .read-status.unread {
        color: #667eea;
    }
</style>

<main class="main-content">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Notifications Management</h1>
                <div class="breadcrumb">
                    <a href="<?= BASE_URL ?>admin/dashboard">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="active">Notifications</span>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="notifications-layout">
            <!-- Left Panel - Notifications -->
            <div class="notifications-panel">
                <!-- Filter Card -->
                <div class="filter-card">
                    <h3><i class="fas fa-filter"></i> Filter Notifications</h3>
                    <form method="GET" action="">
                        <div class="filter-row">
                            <div class="form-group">
                                <label for="viewMode" class="form-label">View Mode</label>
                                <select class="form-control" id="viewMode" name="viewMode">
                                    <option value="own" <?= $viewMode === 'own' ? 'selected' : '' ?>>My Notifications</option>
                                    <option value="all" <?= $viewMode === 'all' ? 'selected' : '' ?>>All Users (Management)</option>
                                </select>
                            </div>
                            <div class="form-group">
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
                            <div class="form-group">
                                <label for="userType" class="form-label">User Type</label>
                                <select class="form-control" id="userType" name="userType">
                                    <option value="">All User Types</option>
                                    <option value="Student" <?= $userTypeFilter === 'Student' ? 'selected' : '' ?>>Student</option>
                                    <option value="Faculty" <?= $userTypeFilter === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                                    <option value="Librarian" <?= $userTypeFilter === 'Librarian' ? 'selected' : '' ?>>Librarian</option>
                                    <option value="Admin" <?= $userTypeFilter === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="filter-row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="unread" name="unread" 
                                       <?= $unreadOnly ? 'checked' : '' ?>>
                                <label class="form-check-label" for="unread">
                                    Unread Only
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Notifications List -->
                <div class="notifications-card">
                    <div class="notifications-header">
                        <h3>
                            <i class="fas fa-bell"></i> Notifications
                        </h3>
                        <span class="badge badge-primary"><?= $totalNotifications ?> total</span>
                    </div>
                    <div class="notifications-body">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?= $notification['isRead'] ? '' : 'unread' ?>">
                                    <div class="notification-header-row">
                                        <div class="notification-title-section">
                                            <h6>
                                                <?php
                                                $typeIcons = [
                                                    'overdue' => 'fas fa-exclamation-triangle',
                                                    'fine_paid' => 'fas fa-money-bill-wave',
                                                    'out_of_stock' => 'fas fa-box-open',
                                                    'system' => 'fas fa-cog',
                                                    'reminder' => 'fas fa-bell',
                                                    'approval' => 'fas fa-check-circle'
                                                ];
                                                ?>
                                                <i class="<?= $typeIcons[$notification['type']] ?? 'fas fa-info-circle' ?>"></i>
                                                <?= htmlspecialchars($notification['title']) ?>
                                            </h6>
                                            <div class="notification-badges">
                                                <?php
                                                $priorityColors = [
                                                    'high' => 'danger',
                                                    'medium' => 'warning',
                                                    'low' => 'info'
                                                ];
                                                ?>
                                                <span class="badge badge-<?= $priorityColors[$notification['priority']] ?? 'secondary' ?>">
                                                    <?= ucfirst($notification['priority']) ?>
                                                </span>
                                                <span class="badge badge-secondary">
                                                    <?= ucfirst(str_replace('_', ' ', $notification['type'])) ?>
                                                </span>
                                                <?php if (isset($notification['userType']) && $notification['userType']): ?>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-user"></i> <?= htmlspecialchars($notification['userType']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if (!$notification['isRead']): ?>
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="markAsRead(<?= $notification['id'] ?>)">
                                                    <i class="fas fa-check"></i> Mark Read
                                                </button>
                                            <?php else: ?>
                                                <span class="read-status read">
                                                    <i class="fas fa-check-circle"></i> Read
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="notification-message">
                                        <?= htmlspecialchars($notification['message']) ?>
                                    </p>
                                    <div class="notification-meta">
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            <?= date('M j, Y H:i', strtotime($notification['createdAt'])) ?>
                                        </span>
                                        <?php if ($notification['userId']): ?>
                                            <span>
                                                <i class="fas fa-user"></i>
                                                <?= htmlspecialchars($notification['username'] ?? $notification['emailId'] ?? $notification['userId']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span>
                                                <i class="fas fa-globe"></i>
                                                System-wide
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h5>No notifications found</h5>
                                <p>There are no notifications matching your criteria.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Stats & Actions -->
            <div class="sidebar-panel">
                <!-- Stats Card -->
                <div class="stats-card">
                    <h3><i class="fas fa-chart-bar"></i> Statistics</h3>
                    <div class="stat-item">
                        <span class="stat-label">Total Notifications</span>
                        <span class="stat-value"><?= $totalNotifications ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Unread</span>
                        <span class="stat-value unread"><?= $unreadCount ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Read</span>
                        <span class="stat-value"><?= $totalNotifications - $unreadCount ?></span>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="actions-card">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-success btn-block" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                        <button type="button" class="btn btn-warning btn-block" onclick="checkOverdueNotifications()">
                            <i class="fas fa-exclamation-triangle"></i> Check Overdue
                        </button>
                        <button type="button" class="btn btn-info btn-block" onclick="checkOutOfStockNotifications()">
                            <i class="fas fa-box-open"></i> Check Stock
                        </button>
                        <button type="button" class="btn btn-danger btn-block" onclick="clearOldNotifications()">
                            <i class="fas fa-trash-alt"></i> Clear Old (30d+)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
</main>

<script>
    function markAsRead(notificationId) {
        if (confirm('Mark this notification as read?')) {
            // AJAX implementation here
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>admin/notifications/mark-read';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'notificationId';
            input.value = notificationId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function markAllAsRead() {
        if (confirm('Mark all notifications as read?')) {
            window.location.href = '<?= BASE_URL ?>admin/notifications/mark-all-read';
        }
    }

    function checkOverdueNotifications() {
        if (confirm('Check for overdue notifications?')) {
            window.location.href = '<?= BASE_URL ?>admin/notifications/check-overdue';
        }
    }

    function checkOutOfStockNotifications() {
        if (confirm('Check for out of stock notifications?')) {
            window.location.href = '<?= BASE_URL ?>admin/notifications/check-stock';
        }
    }

    function clearOldNotifications() {
        if (confirm('Clear notifications older than 30 days? This cannot be undone.')) {
            window.location.href = '<?= BASE_URL ?>admin/notifications/clear-old';
        }
    }

    // Auto-refresh notifications every 60 seconds
    let autoRefreshInterval;
    
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(function() {
            if (window.location.pathname.includes('notifications')) {
                console.log('Auto-refreshing notifications...');
                // Uncomment for AJAX refresh
                // location.reload();
            }
        }, 60000);
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    }

    // Start auto-refresh on page load
    document.addEventListener('DOMContentLoaded', function() {
        startAutoRefresh();
    });

    // Stop auto-refresh when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
</script>