<?php
// Session checks, authentication, etc.
$pageTitle = 'System Maintenance';
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

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-block {
            width: 100%;
            justify-content: center;
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

        /* Health Status Cards */
        .health-item {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .health-item:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .health-item .d-flex {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .health-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .health-icon.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .health-icon.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .health-icon.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .health-info h6 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        /* Badge */
        .badge {
            padding: 0.375rem 0.875rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
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

        .bg-secondary {
            background: #64748b;
            color: white;
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
            align-items: start;
            margin-bottom: 1rem;
            padding: 1.25rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .form-check:hover {
            border-color: #667eea;
            background: white;
        }

        .form-check-input {
            width: 22px;
            height: 22px;
            margin-right: 1rem;
            cursor: pointer;
            accent-color: #667eea;
            flex-shrink: 0;
            margin-top: 0.15rem;
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
            font-weight: 600;
            color: #1e293b;
            flex: 1;
        }

        .form-check-label i {
            margin-right: 0.5rem;
        }

        .form-check small {
            display: block;
            font-weight: 400;
            color: #64748b;
            margin-top: 0.25rem;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.75rem;
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
        .col-md-6 { width: 50%; padding: 0 1rem; }

        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 2rem; }
        .mb-0 { margin-bottom: 0; }
        .me-3 { margin-right: 1rem; }

        .d-flex { display: flex; }
        .d-block { display: block; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }

        .text-muted { color: #64748b; }
        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }
        .text-warning { color: #f59e0b; }
        .text-primary { color: #667eea; }
        .text-center { text-align: center; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
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

            .col-md-3,
            .col-md-4,
            .col-md-6 {
                width: 100%;
            }

            .card-body {
                padding: 1.5rem;
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
                            <i class="fas fa-tools"></i>
                            System Maintenance
                        </h1>
                        <p>Monitor system health and perform maintenance tasks</p>
                    </div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- System Health Status -->
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-heartbeat"></i> System Health Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="health-item">
                                    <?php
                                    $dbStatus = $systemHealth['database'] ?? 'healthy';
                                    $dbClass = $dbStatus === 'healthy' ? 'success' : 'danger';
                                    ?>
                                    <div class="d-flex">
                                        <div class="health-icon <?= $dbClass ?>">
                                            <i class="fas fa-database"></i>
                                        </div>
                                        <div class="health-info">
                                            <h6>Database</h6>
                                            <span class="badge bg-<?= $dbClass ?>"><?= ucfirst($dbStatus) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="health-item">
                                    <?php
                                    $diskStatus = $systemHealth['disk_space'] ?? 'healthy';
                                    $diskClass = $diskStatus === 'healthy' ? 'success' : ($diskStatus === 'warning' ? 'warning' : 'danger');
                                    ?>
                                    <div class="d-flex">
                                        <div class="health-icon <?= $diskClass ?>">
                                            <i class="fas fa-hdd"></i>
                                        </div>
                                        <div class="health-info">
                                            <h6>Disk Space</h6>
                                            <span class="badge bg-<?= $diskClass ?>"><?= ucfirst($diskStatus) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="health-item">
                                    <?php
                                    $overdueCount = $systemHealth['overdue_books'] ?? 0;
                                    $overdueClass = $overdueCount > 50 ? 'danger' : ($overdueCount > 20 ? 'warning' : 'success');
                                    ?>
                                    <div class="d-flex">
                                        <div class="health-icon <?= $overdueClass ?>">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="health-info">
                                            <h6>Overdue Books</h6>
                                            <span class="badge bg-<?= $overdueClass ?>"><?= $overdueCount ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="health-item">
                                    <?php
                                    $overallStatus = $systemHealth['overall'] ?? 'healthy';
                                    $overallClass = $overallStatus === 'healthy' ? 'success' : ($overallStatus === 'warning' ? 'warning' : 'danger');
                                    ?>
                                    <div class="d-flex">
                                        <div class="health-icon <?= $overallClass ?>">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <div class="health-info">
                                            <h6>Overall Status</h6>
                                            <span class="badge bg-<?= $overallClass ?>"><?= ucfirst($overallStatus) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Backup Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-download"></i> Database Backup
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?= BASE_URL ?>admin/maintenance/backup">
                                    <div class="mb-3">
                                        <label for="backupType" class="form-label">Backup Type</label>
                                        <select class="form-control" id="backupType" name="backupType" required>
                                            <option value="manual">Manual Backup</option>
                                            <option value="scheduled">Scheduled Backup</option>
                                            <option value="system">System Backup</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="includeData" checked>
                                            <label class="form-check-label" for="includeData">
                                                <i class="fas fa-table"></i> Include Data
                                                <small>Backup all table data</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="includeStructure" checked>
                                            <label class="form-check-label" for="includeStructure">
                                                <i class="fas fa-sitemap"></i> Include Structure
                                                <small>Backup database structure</small>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-download"></i> Create Backup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-history"></i> Recent Backups
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($backupHistory)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Filename</th>
                                                    <th>Size</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($backupHistory as $backup): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($backup['filename']) ?></td>
                                                        <td><?= $backup['size'] ?></td>
                                                        <td><?= date('M j, Y', strtotime($backup['date'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open"></i>
                                        <p>No backup history available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Tasks -->
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-tasks"></i> Maintenance Tasks
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL ?>admin/maintenance/run">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="clear_cache" name="tasks[]" value="clear_cache">
                                        <label class="form-check-label" for="clear_cache">
                                            <i class="fas fa-broom text-primary"></i> Clear Cache
                                            <small>Remove temporary cache files</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="clear_logs" name="tasks[]" value="clear_logs">
                                        <label class="form-check-label" for="clear_logs">
                                            <i class="fas fa-file-alt text-warning"></i> Clear Old Logs
                                            <small>Remove logs older than 30 days</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="clear_notifications" name="tasks[]" value="clear_notifications">
                                        <label class="form-check-label" for="clear_notifications">
                                            <i class="fas fa-bell-slash text-danger"></i> Clear Old Notifications
                                            <small>Remove notifications older than 30 days</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="optimize_database" name="tasks[]" value="optimize_database">
                                        <label class="form-check-label" for="optimize_database">
                                            <i class="fas fa-database text-success"></i> Optimize Database
                                            <small>Optimize database tables for better performance</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="check_out_of_stock" name="tasks[]" value="check_out_of_stock">
                                        <label class="form-check-label" for="check_out_of_stock">
                                            <i class="fas fa-box-open text-danger"></i> Check Out of Stock
                                            <small>Generate notifications for out of stock books</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="check_overdue" name="tasks[]" value="check_overdue">
                                        <label class="form-check-label" for="check_overdue">
                                            <i class="fas fa-exclamation-triangle text-warning"></i> Check Overdue
                                            <small>Generate notifications for overdue books</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="update_statistics" name="tasks[]" value="update_statistics">
                                        <label class="form-check-label" for="update_statistics">
                                            <i class="fas fa-chart-bar text-primary"></i> Update Statistics
                                            <small>Update book borrowing statistics</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Run selected maintenance tasks?')">
                                        <i class="fas fa-play"></i> Run Selected Tasks
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="selectAllTasks()">
                                        <i class="fas fa-check-double"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearAllTasks()">
                                        <i class="fas fa-times"></i> Clear All
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Maintenance Log -->
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-list"></i> Maintenance Log
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($maintenanceLog)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Performed By</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenanceLog as $log): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($log['action']) ?></strong>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($log['description']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($log['performedBy']) ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'success' => 'success',
                                                        'failed' => 'danger',
                                                        'warning' => 'warning'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $statusClass[$log['status']] ?? 'secondary' ?>">
                                                        <?= ucfirst($log['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= date('M j, Y H:i', strtotime($log['createdAt'])) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <p>No maintenance log entries available</p>
                            </div>
                        <?php endif; ?>
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

        function selectAllTasks() {
            const checkboxes = document.querySelectorAll('input[name="tasks[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        function clearAllTasks() {
            const checkboxes = document.querySelectorAll('input[name="tasks[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        // Auto-refresh system health every 60 seconds
        setInterval(function() {
            if (window.location.pathname.includes('maintenance')) {
                console.log('Auto-refreshing system health...');
                // You can implement AJAX refresh here
            }
        }, 60000);

        // Load sidebar state
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed && sidebar) {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
