<?php
// Session checks, authentication, etc.
$pageTitle = 'System Settings';
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

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        /* Settings Grid */
        .settings-grid {
            display: grid;
            gap: 2rem;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
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
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 2rem;
        }

        .card.border-danger {
            border: 2px solid #ef4444;
            position: relative;
            overflow: visible;
        }

        .card.border-danger::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 16px;
            z-index: -1;
            opacity: 0.1;
        }

        .card-header.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            border: none;
        }

        .card-header.bg-danger h5,
        .card-header.bg-danger h5 i {
            color: white;
            background: transparent;
            -webkit-text-fill-color: white;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

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

        .form-control:hover {
            border-color: #cbd5e1;
            background: white;
        }

        .form-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .form-text i {
            font-size: 0.75rem;
        }

        .text-muted {
            color: #64748b;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .form-check:hover {
            background: white;
            border-color: #667eea;
        }

        .form-check-input {
            width: 22px;
            height: 22px;
            margin-right: 1rem;
            cursor: pointer;
            accent-color: #667eea;
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
            font-weight: 500;
            color: #1e293b;
            font-size: 0.95rem;
        }

        /* Row and Column System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -1rem;
        }

        .col-12 { width: 100%; padding: 0 1rem; }
        .col-6 { width: 50%; padding: 0 1rem; }
        .col-md-6 { width: 50%; padding: 0 1rem; }

        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 2rem; }
        .mb-0 { margin-bottom: 0; }

        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        .align-items-end { align-items: flex-end; }

        .text-white { color: white; }

        hr {
            border: none;
            border-top: 2px solid #e2e8f0;
            margin: 1.5rem 0;
        }

        /* Info Display */
        .info-grid {
            display: grid;
            gap: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .info-row:hover {
            border-color: #667eea;
            transform: translateX(4px);
        }

        .info-row strong {
            color: #1e293b;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row strong i {
            color: #667eea;
        }

        .info-row span {
            color: #64748b;
            font-weight: 500;
            background: white;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
        }

        /* Danger Section */
        .danger-section {
            padding: 1.5rem;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-radius: 10px;
            border: 2px solid #fecaca;
            margin-bottom: 1rem;
        }

        .danger-section h6 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #991b1b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .danger-section h6 i {
            color: #dc2626;
        }

        .danger-section p {
            color: #7f1d1d;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        /* Settings Section Spacing */
        .settings-section {
            margin-bottom: 2rem;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 2px solid #e2e8f0;
            margin-top: 1rem;
        }

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

            .col-md-6 {
                width: 100%;
            }

            .card-body {
                padding: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
                            <i class="fas fa-cog"></i>
                            System Settings
                        </h1>
                        <p>Configure and manage your library system preferences</p>
                    </div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Fine Settings -->
                <div class="settings-section">
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                <i class="fas fa-money-bill-wave"></i>
                                Fine Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>admin/settings">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fine_per_day" class="form-label">Fine Per Day (LKR)</label>
                                            <input type="number" class="form-control" id="fine_per_day" 
                                                   name="settings[fine_per_day]" 
                                                   value="<?= $fineSettings['fine_per_day'] ?? '5' ?>" 
                                                   min="0" step="0.01">
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Amount charged per day for overdue books
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="max_borrow_days" class="form-label">Maximum Borrow Days</label>
                                            <input type="number" class="form-control" id="max_borrow_days" 
                                                   name="settings[max_borrow_days]" 
                                                   value="<?= $fineSettings['max_borrow_days'] ?? '14' ?>" 
                                                   min="1">
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Maximum days a book can be borrowed
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="grace_period_days" class="form-label">Grace Period (Days)</label>
                                            <input type="number" class="form-control" id="grace_period_days" 
                                                   name="settings[grace_period_days]" 
                                                   value="<?= $fineSettings['grace_period_days'] ?? '0' ?>" 
                                                   min="0">
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Days before fines start accumulating
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="max_fine_amount" class="form-label">Maximum Fine Amount (LKR)</label>
                                            <input type="number" class="form-control" id="max_fine_amount" 
                                                   name="settings[max_fine_amount]" 
                                                   value="<?= $fineSettings['max_fine_amount'] ?? '500' ?>" 
                                                   min="0" step="0.01">
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Maximum fine amount per book
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fine_calculation_method" class="form-label">Fine Calculation Method</label>
                                            <select class="form-control" id="fine_calculation_method" 
                                                    name="settings[fine_calculation_method]">
                                                <option value="daily" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                                <option value="fixed" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                                            </select>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Method for calculating fines
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Fine Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Settings Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-bell"></i>
                                    Notification Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?= BASE_URL ?>admin/settings">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                        <label class="form-check-label" for="email_notifications">
                                            Enable Email Notifications
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="overdue_reminders" checked>
                                        <label class="form-check-label" for="overdue_reminders">
                                            Send Overdue Reminders
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="low_stock_alerts" checked>
                                        <label class="form-check-label" for="low_stock_alerts">
                                            Low Stock Alerts
                                        </label>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-shield-alt"></i>
                                    Security Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?= BASE_URL ?>admin/settings">
                                    <div class="form-group">
                                        <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                                        <input type="number" class="form-control" id="session_timeout" 
                                               name="settings[session_timeout]" value="30" min="5" max="1440">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Auto-logout after inactivity
                                        </small>
                                    </div>
                                    <div class="form-group">
                                        <label for="max_login_attempts" class="form-label">Max Login Attempts</label>
                                        <input type="number" class="form-control" id="max_login_attempts" 
                                               name="settings[max_login_attempts]" value="5" min="3" max="10">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Before account lockout
                                        </small>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="password_requirements" checked>
                                        <label class="form-check-label" for="password_requirements">
                                            Enforce Password Requirements
                                        </label>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Settings Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-download"></i>
                                    Backup Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?= BASE_URL ?>admin/settings">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_backup" checked>
                                        <label class="form-check-label" for="auto_backup">
                                            Enable Automatic Backups
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                        <select class="form-control" id="backup_frequency" name="settings[backup_frequency]">
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="backup_retention" class="form-label">Backup Retention (days)</label>
                                        <input type="number" class="form-control" id="backup_retention" 
                                               name="settings[backup_retention]" value="30" min="7" max="365">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            How long to keep backup files
                                        </small>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-chart-line"></i>
                                    System Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <strong>
                                            <i class="fab fa-php"></i>
                                            PHP Version:
                                        </strong>
                                        <span><?= PHP_VERSION ?></span>
                                    </div>
                                    <div class="info-row">
                                        <strong>
                                            <i class="fas fa-database"></i>
                                            Database:
                                        </strong>
                                        <span>MySQL</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>
                                            <i class="fas fa-server"></i>
                                            Server:
                                        </strong>
                                        <span><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
                                    </div>
                                    <div class="info-row">
                                        <strong>
                                            <i class="fas fa-code-branch"></i>
                                            Application Version:
                                        </strong>
                                        <span>1.0.0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5>
                            <i class="fas fa-exclamation-triangle"></i>
                            Danger Zone
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="danger-section">
                                    <h6>
                                        <i class="fas fa-undo"></i>
                                        Reset All Settings
                                    </h6>
                                    <p>Reset all system settings to default values.</p>
                                    <button type="button" class="btn btn-warning" onclick="resetSettings()">
                                        <i class="fas fa-undo"></i> Reset Settings
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="danger-section">
                                    <h6>
                                        <i class="fas fa-trash-alt"></i>
                                        Clear All Data
                                    </h6>
                                    <p>⚠️ This will permanently delete all data!</p>
                                    <button type="button" class="btn btn-danger" onclick="clearAllData()">
                                        <i class="fas fa-trash"></i> Clear All Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                

            </div>
             <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        function resetSettings() {
            if (confirm('Are you sure you want to reset all settings to default values?')) {
                alert('Settings reset functionality coming soon!');
            }
        }

        function clearAllData() {
            if (confirm('⚠️ WARNING: This will permanently delete ALL data including users, books, and transactions. This action cannot be undone!\n\nAre you absolutely sure?')) {
                if (confirm('This is your final warning. Type "DELETE ALL" to confirm.')) {
                    alert('Clear all data functionality coming soon!');
                }
            }
        }

        // Auto-save settings
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    console.log('Setting changed:', this.name, this.value);
                });
            });

            // Load sidebar state
            const sidebar = document.getElementById('sidebar');
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed && sidebar) {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
