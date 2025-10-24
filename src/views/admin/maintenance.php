<?php
$pageTitle = 'System Maintenance';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-tools"></i> System Maintenance
                </h1>
                <div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat"></i> System Health Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $dbStatus = $systemHealth['database'] ?? 'unknown';
                                    $dbClass = $dbStatus === 'healthy' ? 'success' : 'danger';
                                    ?>
                                    <i class="fas fa-database fa-2x text-<?= $dbClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Database</h6>
                                    <span class="badge bg-<?= $dbClass ?>"><?= ucfirst($dbStatus) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $diskStatus = $systemHealth['disk_space'] ?? 'unknown';
                                    $diskClass = $diskStatus === 'healthy' ? 'success' : ($diskStatus === 'warning' ? 'warning' : 'danger');
                                    ?>
                                    <i class="fas fa-hdd fa-2x text-<?= $diskClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Disk Space</h6>
                                    <span class="badge bg-<?= $diskClass ?>"><?= ucfirst($diskStatus) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $overdueCount = $systemHealth['overdue_books'] ?? 0;
                                    $overdueClass = $overdueCount > 50 ? 'danger' : ($overdueCount > 20 ? 'warning' : 'success');
                                    ?>
                                    <i class="fas fa-exclamation-triangle fa-2x text-<?= $overdueClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Overdue Books</h6>
                                    <span class="badge bg-<?= $overdueClass ?>"><?= $overdueCount ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php
                                    $overallStatus = $systemHealth['overall'] ?? 'unknown';
                                    $overallClass = $overallStatus === 'healthy' ? 'success' : ($overallStatus === 'warning' ? 'warning' : 'danger');
                                    ?>
                                    <i class="fas fa-shield-alt fa-2x text-<?= $overallClass ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Overall Status</h6>
                                    <span class="badge bg-<?= $overallClass ?>"><?= ucfirst($overallStatus) ?></span>
                                </div>
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
                    <h5 class="mb-0">
                        <i class="fas fa-download"></i> Database Backup
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/backup">
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
                                    Include Data
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeStructure" checked>
                                <label class="form-check-label" for="includeStructure">
                                    Include Structure
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
                    <h5 class="mb-0">
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
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backupHistory as $backup): ?>
                                        <tr>
                                            <td>
                                                <small><?= htmlspecialchars($backup['filename']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= ucfirst($backup['backupType']) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = $backup['status'] === 'success' ? 'success' : 'danger';
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($backup['status']) ?></span>
                                            </td>
                                            <td>
                                                <small><?= date('M j, Y H:i', strtotime($backup['createdAt'])) ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">
                            <i class="fas fa-info-circle"></i> No backup history available
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Tasks -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> Maintenance Tasks
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/maintenance/perform">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="update_fines" name="tasks[]" value="update_fines">
                                    <label class="form-check-label" for="update_fines">
                                        <i class="fas fa-money-bill-wave text-warning"></i> Update All Fines
                                    </label>
                                    <small class="d-block text-muted">Recalculate fines for overdue books</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="clean_notifications" name="tasks[]" value="clean_notifications">
                                    <label class="form-check-label" for="clean_notifications">
                                        <i class="fas fa-bell text-info"></i> Clean Old Notifications
                                    </label>
                                    <small class="d-block text-muted">Remove notifications older than 30 days</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="optimize_database" name="tasks[]" value="optimize_database">
                                    <label class="form-check-label" for="optimize_database">
                                        <i class="fas fa-database text-success"></i> Optimize Database
                                    </label>
                                    <small class="d-block text-muted">Optimize database tables for better performance</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check_out_of_stock" name="tasks[]" value="check_out_of_stock">
                                    <label class="form-check-label" for="check_out_of_stock">
                                        <i class="fas fa-box-open text-danger"></i> Check Out of Stock
                                    </label>
                                    <small class="d-block text-muted">Generate notifications for out of stock books</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check_overdue" name="tasks[]" value="check_overdue">
                                    <label class="form-check-label" for="check_overdue">
                                        <i class="fas fa-exclamation-triangle text-warning"></i> Check Overdue
                                    </label>
                                    <small class="d-block text-muted">Generate notifications for overdue books</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="update_statistics" name="tasks[]" value="update_statistics">
                                    <label class="form-check-label" for="update_statistics">
                                        <i class="fas fa-chart-bar text-primary"></i> Update Statistics
                                    </label>
                                    <small class="d-block text-muted">Update book borrowing statistics</small>
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
        </div>
    </div>

    <!-- Maintenance Log -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Maintenance Log
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($maintenanceLog)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                        <p class="text-muted text-center">
                            <i class="fas fa-info-circle"></i> No maintenance log entries available
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
        // You can implement AJAX refresh here
        console.log('Auto-refreshing system health...');
    }
}, 60000);
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
