<?php
// Session checks, authentication, etc.
$pageTitle = 'Notifications Management';
include APP_ROOT . '/views/layouts/admin-header.php';
?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
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
            min-height: 100vh;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 80px;
        }

        /* Content Wrapper */
        .content-wrapper {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid;
            border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%) 1;
        }

        .page-header-content h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header-content h1 i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header-content p {
            color: #64748b;
            font-size: 0.95rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(100, 116, 139, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
        }

        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .stat-card.warning::before {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .stat-card.success::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .stat-card.danger::before {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .stat-card.info::before {
            background: linear-gradient(90deg, #06b6d4, #0891b2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-info h4 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-info p {
            color: #64748b;
            font-weight: 600;
            font-size: 1rem;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .stat-card.warning .stat-icon {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .stat-card.success .stat-icon {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .stat-card.danger .stat-icon {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .stat-card.info .stat-icon {
            background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
            color: #164e63;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.75rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e2e8f0;
        }

        .card-header h5 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header h5 i {
            color: #667eea;
        }

        .card-body {
            padding: 2rem;
        }

        /* Form Styles */
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1.125rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .form-check:hover {
            border-color: #667eea;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            cursor: pointer;
            accent-color: #667eea;
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
            font-weight: 500;
            color: #1e293b;
        }

        /* Badge */
        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .bg-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .bg-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .bg-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .bg-secondary {
            background: #64748b;
            color: white;
        }

        /* Notification Item */
        .notification-item {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .notification-item.unread {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #667eea;
            border-left: 4px solid #667eea;
        }

        .notification-item.read {
            background: #f8fafc;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .notification-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .notification-title h6 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .notification-title.unread h6 {
            font-weight: 700;
        }

        .notification-message {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #64748b;
            font-size: 0.85rem;
        }

        .notification-meta i {
            color: #667eea;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h5 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        /* Row and Column System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -1rem;
        }

        .col-12 { width: 100%; padding: 0 1rem; }
        .col-md-3 { width: 25%; padding: 0 1rem; }
        .col-md-4 { width: 33.333%; padding: 0 1rem; }

        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 2rem; }
        .mb-0 { margin-bottom: 0; }
        .ms-2 { margin-left: 0.5rem; }
        .ms-3 { margin-left: 1rem; }
        .me-2 { margin-right: 0.5rem; }

        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        .align-items-start { align-items: flex-start; }
        .flex-grow-1 { flex-grow: 1; }

        .text-muted { color: #64748b; }
        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }
        .text-warning { color: #f59e0b; }
        .text-info { color: #06b6d4; }
        .text-secondary { color: #64748b; }
        .text-center { text-align: center; }

        .fw-bold { font-weight: 700; }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
                padding: 1.5rem;
            }

            .page-header-content h1 {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .col-md-3,
            .col-md-4 {
                width: 100%;
            }

            .card-body {
                padding: 1.5rem;
            }

            .btn-block {
                width: 100%;
            }

            .notification-header {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>

    <div class="admin-layout">
        <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>
        
        <main class="main-content">
            <div class="content-wrapper">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-header-content">
                        <h1>
                            <i class="fas fa-bell"></i>
                            Notifications Management
                        </h1>
                        <p>Monitor and manage all system notifications</p>
                    </div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Notification Stats -->
                <div class="stats-grid">
                    <div class="stat-card warning">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h4><?= count(array_filter($notifications, fn($n) => $n['type'] === 'overdue')) ?></h4>
                                <p>Overdue</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h4><?= count(array_filter($notifications, fn($n) => $n['type'] === 'fine_paid')) ?></h4>
                                <p>Fines Paid</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h4><?= count(array_filter($notifications, fn($n) => $n['type'] === 'out_of_stock')) ?></h4>
                                <p>Out of Stock</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h4><?= count(array_filter($notifications, fn($n) => !$n['isRead'])) ?></h4>
                                <p>Unread</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5>
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

                <!-- Filters -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3 mb-3">
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
                            <div class="col-md-3 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" style="opacity: 0;">Unread</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="unread" name="unread" 
                                           <?= $unreadOnly ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="unread">
                                        Unread Only
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" style="opacity: 0;">Filter</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-list"></i> Notifications
                            <span class="badge bg-primary ms-2"><?= count($notifications) ?> records</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?= $notification['isRead'] ? 'read' : 'unread' ?>">
                                    <div class="notification-header">
                                        <div class="notification-title <?= $notification['isRead'] ? '' : 'unread' ?>">
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
                                            <i class="<?= $typeIcons[$notification['type']] ?? 'fas fa-info-circle text-secondary' ?>"></i>
                                            <div>
                                                <h6><?= htmlspecialchars($notification['title']) ?></h6>
                                                <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem;">
                                                    <span class="badge bg-<?= $priorityColors[$notification['priority']] ?? 'secondary' ?>">
                                                        <?= ucfirst($notification['priority']) ?>
                                                    </span>
                                                    <span class="badge bg-secondary">
                                                        <?= ucfirst(str_replace('_', ' ', $notification['type'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if (!$notification['isRead']): ?>
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="markAsRead(<?= $notification['id'] ?>)">
                                                    <i class="fas fa-check"></i> Mark Read
                                                </button>
                                            <?php else: ?>
                                                <span class="text-success" style="font-weight: 600;">
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
                                                User: <?= htmlspecialchars($notification['userId']) ?>
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
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        function markAsRead(notificationId) {
            if (confirm('Mark this notification as read?')) {
                // Implementation for marking as read
                alert('Marking notification #' + notificationId + ' as read...');
                // You can implement AJAX call here
                // After success, reload or update the UI
                window.location.reload();
            }
        }

        function markAllAsRead() {
            if (confirm('Mark all notifications as read?')) {
                alert('Mark all as read feature coming soon!');
            }
        }

        function checkOverdueNotifications() {
            if (confirm('Check for overdue notifications?')) {
                alert('Checking overdue notifications...');
            }
        }

        function checkOutOfStockNotifications() {
            if (confirm('Check for out of stock notifications?')) {
                alert('Checking out of stock notifications...');
            }
        }

        function clearOldNotifications() {
            if (confirm('Clear notifications older than 30 days?')) {
                alert('Clearing old notifications...');
            }
        }

        // Auto-refresh notifications every 30 seconds
        setInterval(function() {
            if (window.location.pathname.includes('notifications')) {
                console.log('Auto-refreshing notifications...');
                // You can implement AJAX refresh here
            }
        }, 30000);

        // Load sidebar state
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed && sidebar) {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
