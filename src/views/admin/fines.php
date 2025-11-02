<?php
$pageTitle = 'Fine Management';
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

    /* Settings Card */
    .settings-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .settings-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .settings-header h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.9rem;
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
        text-decoration: none;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .action-btn.warning {
        background: var(--warning-color);
        color: white;
    }

    .action-btn.info {
        background: var(--info-color);
        color: white;
    }

    .action-btn.success {
        background: var(--success-color);
        color: white;
    }

    /* Filters Card */
    .filters-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
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

    .user-info-cell,
    .book-info-cell {
        line-height: 1.4;
    }

    .user-info-cell strong,
    .book-info-cell strong {
        color: #1e293b;
    }

    .text-muted {
        color: var(--gray-500);
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

    .status-badge.info {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .fine-amount {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 700;
        background: #fee2e2;
        color: #991b1b;
        display: inline-block;
    }

    /* Action Buttons */
    .btn-group {
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

    /* Button Styling */
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px -1px rgba(99, 102, 241, 0.4);
    }

    .btn-secondary {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .btn-secondary:hover {
        background: var(--gray-300);
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

        .settings-grid,
        .action-grid,
        .filter-grid {
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
<?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Fine Management</h1>
                <div class="breadcrumb">
                    <span>Home</span>
                    <span>/</span>
                    <span>Fines</span>
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
            <!-- Fine Settings Card -->
            <div class="settings-card">
                <div class="settings-header">
                    <i class="fas fa-cog" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                    <h5>Fine Settings</h5>
                </div>
                
                <form method="POST" action="<?= BASE_URL ?>admin/fines">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="settings-grid">
                        <div class="form-group">
                            <label for="fine_per_day" class="form-label">Fine Per Day (LKR)</label>
                            <input type="number" class="form-control" id="fine_per_day" name="settings[fine_per_day]" 
                                   value="<?= $fineSettings['fine_per_day'] ?? '5' ?>" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="max_borrow_days" class="form-label">Max Borrow Days</label>
                            <input type="number" class="form-control" id="max_borrow_days" name="settings[max_borrow_days]" 
                                   value="<?= $fineSettings['max_borrow_days'] ?? '14' ?>" min="1">
                        </div>
                        <div class="form-group">
                            <label for="grace_period_days" class="form-label">Grace Period (Days)</label>
                            <input type="number" class="form-control" id="grace_period_days" name="settings[grace_period_days]" 
                                   value="<?= $fineSettings['grace_period_days'] ?? '0' ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="max_fine_amount" class="form-label">Max Fine Amount (LKR)</label>
                            <input type="number" class="form-control" id="max_fine_amount" name="settings[max_fine_amount]" 
                                   value="<?= $fineSettings['max_fine_amount'] ?? '500' ?>" min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="settings-grid" style="grid-template-columns: 2fr 1fr;">
                        <div class="form-group">
                            <label for="fine_calculation_method" class="form-label">Calculation Method</label>
                            <select class="form-control" id="fine_calculation_method" name="settings[fine_calculation_method]">
                                <option value="daily" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                <option value="fixed" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-save"></i>
                                Update Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h5>
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h5>
                <div class="action-grid">
                    <form method="POST" action="<?= BASE_URL ?>admin/fines" style="margin: 0;">
                        <input type="hidden" name="action" value="update_all_fines">
                        <button type="submit" class="action-btn warning" onclick="return confirm('Update all fines?')" style="width: 100%;">
                            <i class="fas fa-sync"></i>
                            Update All Fines
                        </button>
                    </form>
                    <a href="<?= BASE_URL ?>admin/fines?status=pending" class="action-btn info">
                        <i class="fas fa-clock"></i>
                        View Pending Fines
                    </a>
                    <a href="<?= BASE_URL ?>admin/fines?status=paid" class="action-btn success">
                        <i class="fas fa-check"></i>
                        View Paid Fines
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <form method="GET" action="<?= BASE_URL ?>admin/fines">
                    <div class="filter-grid">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?= ($currentStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= ($currentStatus ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="waived" <?= ($currentStatus ?? '') === 'waived' ? 'selected' : '' ?>>Waived</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="userId" class="form-label">User ID</label>
                            <input type="text" class="form-control" id="userId" name="userId" 
                                   value="<?= htmlspecialchars($currentUserId ?? '') ?>" placeholder="Enter User ID">
                        </div>
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Filter
                            </button>
                            <a href="<?= BASE_URL ?>admin/fines" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Fines Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">
                        <i class="fas fa-list"></i>
                        Fine Records
                        <span class="record-badge"><?= count($fines ?? []) ?> records</span>
                    </h2>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>User</th>
                                <th>Book</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Fine Amount</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($fines)): ?>
                                <?php 
                                // Get fine settings for calculation
                                $maxBorrowDays = (int)($fineSettings['max_borrow_days'] ?? 14);
                                
                                foreach ($fines as $fine): 
                                    // Calculate due date and days overdue
                                    $borrowDate = new DateTime($fine['borrowDate']);
                                    $dueDate = clone $borrowDate;
                                    $dueDate->add(new DateInterval("P{$maxBorrowDays}D"));
                                    
                                    $currentDate = $fine['returnDate'] ? new DateTime($fine['returnDate']) : new DateTime();
                                    $interval = $dueDate->diff($currentDate);
                                    $daysOverdue = $currentDate > $dueDate ? $interval->days : 0;
                                    
                                    $status = $fine['fineStatus'] ?? 'pending';
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($fine['tid']) ?></strong></td>
                                        <td>
                                            <div class="user-info-cell">
                                                <strong><?= htmlspecialchars($fine['emailId']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($fine['userType']) ?> - <?= htmlspecialchars($fine['userId']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="book-info-cell">
                                                <strong><?= htmlspecialchars($fine['bookName']) ?></strong><br>
                                                <small class="text-muted">ISBN: <?= htmlspecialchars($fine['isbn']) ?></small>
                                            </div>
                                        </td>
                                        <td><?= $borrowDate->format('M j, Y') ?></td>
                                        <td>
                                            <span class="status-badge <?= $daysOverdue > 0 ? 'danger' : 'success' ?>">
                                                <?= $dueDate->format('M j, Y') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($daysOverdue > 0): ?>
                                                <span class="status-badge danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <?= $daysOverdue ?> days
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($fine['fineAmount'] > 0): ?>
                                                <span class="fine-amount">LKR<?= number_format($fine['fineAmount'], 2) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">LKR0.00</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'waived' => 'info'
                                            ];
                                            ?>
                                            <span class="status-badge <?= $statusClass[$status] ?? 'secondary' ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($fine['finePaymentDate']): ?>
                                                <?= date('M j, Y', strtotime($fine['finePaymentDate'])) ?>
                                                <?php if ($fine['finePaymentMethod']): ?>
                                                    <br><small class="text-muted">(<?= ucfirst($fine['finePaymentMethod']) ?>)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($status === 'pending' && $fine['fineAmount'] > 0): ?>
                                                <div class="btn-group">
                                                    <button class="btn-action success" onclick="updateFineStatus('<?= $fine['tid'] ?>', 'paid')">
                                                        <i class="fas fa-check"></i> Mark Paid
                                                    </button>
                                                    <button class="btn-action info" onclick="updateFineStatus('<?= $fine['tid'] ?>', 'waived')">
                                                        <i class="fas fa-gift"></i> Waive
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No actions</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h3>No Fines Found</h3>
                                            <p>There are no fine records to display</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
          

        </div>



        <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?> 
            </div>
            

    </main>
</div>

<!-- Update Fine Status Modal -->
<div class="modal" id="updateFineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Fine Status</h5>
                <button type="button" class="btn-close" onclick="closeModal('updateFineModal')">×</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/fines">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="transactionId" id="modalTransactionId">
                    <input type="hidden" name="status" id="modalStatus">
                    
                    <div class="form-group" id="paymentMethodGroup">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-control" id="paymentMethod" name="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="online">Online</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('updateFineModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
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

function updateFineStatus(transactionId, status) {
    document.getElementById('modalTransactionId').value = transactionId;
    document.getElementById('modalStatus').value = status;
    
    // Show/hide payment method based on status
    const paymentMethodGroup = document.getElementById('paymentMethodGroup');
    if (status === 'paid') {
        paymentMethodGroup.style.display = 'block';
    } else {
        paymentMethodGroup.style.display = 'none';
    }
    
    document.getElementById('updateFineModal').classList.add('show');
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
