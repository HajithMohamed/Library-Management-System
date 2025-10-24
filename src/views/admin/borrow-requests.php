<?php
$pageTitle = 'Borrow Requests Management';
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
        overflow-x: hidden;
    }

    /* Modern Color Palette */
    :root {
        --primary-color: #6366f1;
        --primary-dark: #4f46e5;
        --primary-light: #818cf8;
        --secondary-color: #8b5cf6;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #06b6d4;
        --dark-color: #1f2937;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
    }

    /* Main Layout Container */
    .admin-layout {
        display: flex;
        min-height: 100vh;
        background: #f0f2f5;
    }

    /* Left Sidebar */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar.collapsed {
        width: 80px;
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .sidebar-logo i {
        font-size: 1.8rem;
        color: #667eea;
    }

    .sidebar-logo h2 {
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        white-space: nowrap;
    }

    .sidebar.collapsed .sidebar-logo h2 {
        display: none;
    }

    .sidebar-toggle {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .sidebar-nav {
        padding: 1rem 0;
    }

    .nav-section {
        margin-bottom: 1.5rem;
    }

    .nav-section-title {
        padding: 0.5rem 1.5rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.5);
        font-weight: 600;
    }

    .sidebar.collapsed .nav-section-title {
        display: none;
    }

    .nav-item {
        position: relative;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1.5rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .nav-link:hover,
    .nav-link.active {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .nav-link:hover::before,
    .nav-link.active::before {
        transform: scaleY(1);
    }

    .nav-link i {
        font-size: 1.25rem;
        width: 24px;
        text-align: center;
    }

    .nav-link span {
        white-space: nowrap;
    }

    .sidebar.collapsed .nav-link span {
        display: none;
    }

    .nav-badge {
        margin-left: auto;
        background: #ef4444;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .sidebar.collapsed .nav-badge {
        display: none;
    }

    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.2);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .user-role {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
    }

    .sidebar.collapsed .user-info {
        display: none;
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

    /* Top Header */
    .top-header {
        background: white;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-left h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .breadcrumb {
        display: flex;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.9rem;
    }

    .header-right {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .header-btn {
        background: white;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        color: #64748b;
        text-decoration: none;
    }

    .header-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #64748b;
    }

    /* Page Content */
    .page-content {
        padding: 2rem;
    }

    /* Status Tabs */
    .status-tabs {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .tab-list {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        border-bottom: 2px solid var(--gray-200);
        padding-bottom: 1rem;
    }

    .tab-link {
        padding: 0.75rem 1.5rem;
        border-radius: 8px 8px 0 0;
        text-decoration: none;
        color: var(--gray-600);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        position: relative;
    }

    .tab-link:hover {
        background: var(--gray-50);
        color: var(--gray-900);
    }

    .tab-link.active {
        color: var(--primary-color);
        background: rgba(99, 102, 241, 0.1);
    }

    .tab-link.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary-color);
    }

    .tab-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .tab-badge.warning {
        background: var(--warning-color);
    }

    .tab-badge.success {
        background: var(--success-color);
    }

    .tab-badge.danger {
        background: var(--danger-color);
    }

    .tab-badge.primary {
        background: var(--primary-color);
    }

    /* Quick Actions */
    .quick-actions {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .quick-actions h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .action-btn {
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .action-btn.success {
        background: var(--success-color);
        color: white;
    }

    .action-btn.info {
        background: var(--info-color);
        color: white;
    }

    .action-btn.warning {
        background: var(--warning-color);
        color: white;
    }

    .action-btn.secondary {
        background: var(--gray-600);
        color: white;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .table-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .record-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: var(--primary-color);
        color: white;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: var(--gray-50);
        color: var(--gray-700);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--gray-200);
        white-space: nowrap;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--gray-100);
    }

    .table tbody tr:hover {
        background: var(--gray-50);
    }

    .table tbody td {
        padding: 1rem;
        color: var(--gray-800);
        vertical-align: middle;
    }

    .request-id {
        font-weight: 700;
        color: var(--primary-color);
    }

    .user-info-cell {
        line-height: 1.4;
    }

    .user-info-cell strong {
        color: #1e293b;
    }

    .user-info-cell .text-muted {
        color: var(--gray-500);
        font-size: 0.85rem;
    }

    .book-info-cell {
        line-height: 1.4;
    }

    .book-info-cell strong {
        color: #1e293b;
    }

    .book-info-cell .text-muted {
        color: var(--gray-500);
        font-size: 0.85rem;
    }

    .book-info-cell .text-info {
        color: var(--info-color);
        font-size: 0.85rem;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.success {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-action.success {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-action.success:hover {
        background: #10b981;
        color: white;
    }

    .btn-action.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-action.danger:hover {
        background: #ef4444;
        color: white;
    }

    .btn-action.info {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-action.info:hover {
        background: #3b82f6;
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--gray-500);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    /* Modal Styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(17, 24, 39, 0.7);
        backdrop-filter: blur(4px);
    }

    .modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .modal-dialog {
        position: relative;
        width: 90%;
        max-width: 500px;
        margin: 2rem auto;
    }

    .modal-dialog.modal-lg {
        max-width: 800px;
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }

    .btn-close {
        background: var(--gray-200);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.5rem;
        line-height: 1;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-secondary {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .btn-secondary:hover {
        background: var(--gray-300);
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-danger {
        background: var(--danger-color);
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.mobile-open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 0;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .mobile-overlay.show {
            display: block;
        }

        .top-header {
            padding: 1rem;
        }

        .header-left h1 {
            font-size: 1.25rem;
        }

        .page-content {
            padding: 1rem;
        }

        .tab-list {
            overflow-x: auto;
        }

        .action-grid {
            grid-template-columns: 1fr;
        }
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #1e293b;
        cursor: pointer;
        padding: 0.5rem;
    }

    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: block;
        }
    }
</style>

<!-- Mobile Overlay -->
<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Admin Layout -->
<div class="admin-layout">
    <!-- Left Sidebar -->
<?include APP_ROOT . '/views/admin/admin-navbar.php' ?>;

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Borrow Requests Management</h1>
                <div class="breadcrumb">
                    <span>Home</span>
                    <span>/</span>
                    <span>Borrow Requests</span>
                </div>
            </div>
            <div class="header-right">
                <a href="<?= BASE_URL ?>admin/dashboard" class="header-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Status Filter Tabs -->
            <div class="status-tabs">
                <div class="tab-list">
                    <a class="tab-link <?= ($currentStatus ?? 'pending') === 'pending' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>admin/borrow-requests?status=pending">
                        <i class="fas fa-clock"></i>
                        <span>Pending</span>
                        <span class="tab-badge warning"><?= count(array_filter($requests ?? [], fn($r) => $r['status'] === 'Pending')) ?></span>
                    </a>
                    <a class="tab-link <?= ($currentStatus ?? '') === 'approved' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>admin/borrow-requests?status=approved">
                        <i class="fas fa-check"></i>
                        <span>Approved</span>
                        <span class="tab-badge success"><?= count(array_filter($requests ?? [], fn($r) => $r['status'] === 'Approved')) ?></span>
                    </a>
                    <a class="tab-link <?= ($currentStatus ?? '') === 'rejected' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>admin/borrow-requests?status=rejected">
                        <i class="fas fa-times"></i>
                        <span>Rejected</span>
                        <span class="tab-badge danger"><?= count(array_filter($requests ?? [], fn($r) => $r['status'] === 'Rejected')) ?></span>
                    </a>
                    <a class="tab-link <?= ($currentStatus ?? '') === 'all' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>admin/borrow-requests?status=all">
                        <i class="fas fa-list"></i>
                        <span>All</span>
                        <span class="tab-badge primary"><?= count($requests ?? []) ?></span>
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <?php if (($currentStatus ?? 'pending') === 'pending'): ?>
            <div class="quick-actions">
                <h5>
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h5>
                <div class="action-grid">
                    <button class="action-btn success" onclick="approveAllPending()">
                        <i class="fas fa-check-double"></i>
                        Approve All Pending
                    </button>
                    <button class="action-btn info" onclick="checkAvailability()">
                        <i class="fas fa-search"></i>
                        Check Availability
                    </button>
                    <button class="action-btn warning" onclick="sendReminders()">
                        <i class="fas fa-bell"></i>
                        Send Reminders
                    </button>
                    <button class="action-btn secondary" onclick="exportRequests()">
                        <i class="fas fa-download"></i>
                        Export Requests
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Requests Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">
                        <i class="fas fa-list"></i>
                        Borrow Requests
                        <span class="record-badge"><?= count($requests ?? []) ?> records</span>
                    </h2>
                </div>

                <div class="table-wrapper">
                    <table class="table">
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
                                            <span class="request-id">#<?= $request['id'] ?></span>
                                        </td>
                                        <td>
                                            <div class="user-info-cell">
                                                <strong><?= htmlspecialchars($request['emailId']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($request['userType']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="book-info-cell">
                                                <strong><?= htmlspecialchars($request['bookName']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($request['authorName']) ?></small><br>
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
                                            <span class="status-badge <?= $statusClass[$request['status']] ?? 'secondary' ?>">
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
                                                <div class="action-buttons">
                                                    <button class="btn-action success" onclick="approveRequest(<?= $request['id'] ?>)">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="btn-action danger" onclick="rejectRequest(<?= $request['id'] ?>)">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                    <button class="btn-action info" onclick="viewDetails(<?= $request['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <button class="btn-action info" onclick="viewDetails(<?= $request['id'] ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h3>No Requests Found</h3>
                                            <p>There are no borrow requests to display</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>

        </div>
    </main>
</div>

<!-- Approve Request Modal -->
<div class="modal" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Borrow Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeModal('approveModal')">×</button>
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
                    <button type="button" class="btn btn-secondary" onclick="closeModal('approveModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Request Modal -->
<div class="modal" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Borrow Request</h5>
                <button type="button" class="btn-close" onclick="closeModal('rejectModal')">×</button>
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
                    <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" onclick="closeModal('detailsModal')">×</button>
            </div>
            <div class="modal-body" id="requestDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('detailsModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Sidebar functions
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.mobile-overlay');
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('show');
}

// Load sidebar state
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }
});

// Modal functions
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
}

function approveRequest(requestId) {
    document.getElementById('approveRequestId').value = requestId;
    document.getElementById('approveModal').classList.add('show');
}

function rejectRequest(requestId) {
    document.getElementById('rejectRequestId').value = requestId;
    document.getElementById('rejectModal').classList.add('show');
}

function viewDetails(requestId) {
    // Load request details via AJAX
    fetch(`<?= BASE_URL ?>admin/borrow-requests/details/${requestId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('requestDetails').innerHTML = data.html;
            document.getElementById('detailsModal').classList.add('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load request details');
        });
}

function approveAllPending() {
    if (confirm('Are you sure you want to approve all pending requests?')) {
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

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});
</script>

</body>
</html>