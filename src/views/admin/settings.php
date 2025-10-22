<?php
$pageTitle = 'System Settings';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-cog"></i> System Settings
                </h1>
                <div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Fine Settings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave"></i> Fine Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/settings">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fine_per_day" class="form-label">Fine Per Day (₹)</label>
                                <input type="number" class="form-control" id="fine_per_day" name="settings[fine_per_day]" 
                                       value="<?= $fineSettings['fine_per_day'] ?? '5' ?>" min="0" step="0.01">
                                <small class="form-text text-muted">Amount charged per day for overdue books</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_borrow_days" class="form-label">Maximum Borrow Days</label>
                                <input type="number" class="form-control" id="max_borrow_days" name="settings[max_borrow_days]" 
                                       value="<?= $fineSettings['max_borrow_days'] ?? '14' ?>" min="1">
                                <small class="form-text text-muted">Maximum days a book can be borrowed</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="grace_period_days" class="form-label">Grace Period (Days)</label>
                                <input type="number" class="form-control" id="grace_period_days" name="settings[grace_period_days]" 
                                       value="<?= $fineSettings['grace_period_days'] ?? '0' ?>" min="0">
                                <small class="form-text text-muted">Days before fines start accumulating</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_fine_amount" class="form-label">Maximum Fine Amount (₹)</label>
                                <input type="number" class="form-control" id="max_fine_amount" name="settings[max_fine_amount]" 
                                       value="<?= $fineSettings['max_fine_amount'] ?? '500' ?>" min="0" step="0.01">
                                <small class="form-text text-muted">Maximum fine amount per book</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fine_calculation_method" class="form-label">Fine Calculation Method</label>
                                <select class="form-control" id="fine_calculation_method" name="settings[fine_calculation_method]">
                                    <option value="daily" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                    <option value="fixed" <?= ($fineSettings['fine_calculation_method'] ?? 'daily') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                                </select>
                                <small class="form-text text-muted">Method for calculating fines</small>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Fine Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- System Settings -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bell"></i> Notification Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/settings">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                <label class="form-check-label" for="email_notifications">
                                    Enable Email Notifications
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overdue_reminders" checked>
                                <label class="form-check-label" for="overdue_reminders">
                                    Send Overdue Reminders
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="low_stock_alerts" checked>
                                <label class="form-check-label" for="low_stock_alerts">
                                    Low Stock Alerts
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Notification Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt"></i> Security Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/settings">
                        <div class="mb-3">
                            <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-control" id="session_timeout" name="settings[session_timeout]" 
                                   value="30" min="5" max="1440">
                            <small class="form-text text-muted">Auto-logout after inactivity</small>
                        </div>
                        <div class="mb-3">
                            <label for="max_login_attempts" class="form-label">Max Login Attempts</label>
                            <input type="number" class="form-control" id="max_login_attempts" name="settings[max_login_attempts]" 
                                   value="5" min="3" max="10">
                            <small class="form-text text-muted">Before account lockout</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="password_requirements" checked>
                                <label class="form-check-label" for="password_requirements">
                                    Enforce Password Requirements
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Security Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Settings -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-download"></i> Backup Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/settings">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_backup" checked>
                                <label class="form-check-label" for="auto_backup">
                                    Enable Automatic Backups
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="backup_frequency" class="form-label">Backup Frequency</label>
                            <select class="form-control" id="backup_frequency" name="settings[backup_frequency]">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="backup_retention" class="form-label">Backup Retention (days)</label>
                            <input type="number" class="form-control" id="backup_retention" name="settings[backup_retention]" 
                                   value="30" min="7" max="365">
                            <small class="form-text text-muted">How long to keep backup files</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Backup Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> System Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <strong>PHP Version:</strong>
                        </div>
                        <div class="col-6">
                            <?= PHP_VERSION ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Database:</strong>
                        </div>
                        <div class="col-6">
                            MySQL
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Server:</strong>
                        </div>
                        <div class="col-6">
                            <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Application Version:</strong>
                        </div>
                        <div class="col-6">
                            1.0.0
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="row">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Danger Zone
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Reset All Settings</h6>
                            <p class="text-muted">Reset all system settings to default values.</p>
                            <button type="button" class="btn btn-warning" onclick="resetSettings()">
                                <i class="fas fa-undo"></i> Reset Settings
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Clear All Data</h6>
                            <p class="text-muted">⚠️ This will permanently delete all data!</p>
                            <button type="button" class="btn btn-danger" onclick="clearAllData()">
                                <i class="fas fa-trash"></i> Clear All Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetSettings() {
    if (confirm('Are you sure you want to reset all settings to default values?')) {
        // Implementation for resetting settings
        alert('Settings reset functionality coming soon!');
    }
}

function clearAllData() {
    if (confirm('⚠️ WARNING: This will permanently delete ALL data including users, books, and transactions. This action cannot be undone!\n\nAre you absolutely sure?')) {
        if (confirm('This is your final warning. Type "DELETE ALL" to confirm.')) {
            // Implementation for clearing all data
            alert('Clear all data functionality coming soon!');
        }
    }
}

// Auto-save settings as user types
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // You can implement auto-save functionality here
            console.log('Setting changed:', this.name, this.value);
        });
    });
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
