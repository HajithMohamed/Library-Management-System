<?php
$pageTitle = 'Permissions Management';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<div class="admin-layout">
    <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Permissions Management</h1>
                <div class="breadcrumb">
                    <span>System</span>
                    <span>/</span>
                    <span>Permissions</span>
                </div>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="mb-0"><?= count($allPermissions ?? []) ?></h3>
                            <p class="text-muted mb-0">Total Permissions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="mb-0"><?= count($permissionsByModule ?? []) ?></h3>
                            <p class="text-muted mb-0">Permission Modules</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="mb-0"><?= count($allRoles ?? []) ?></h3>
                            <p class="text-muted mb-0">Total Roles</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions by Module -->
            <div class="accordion" id="permissionsAccordion">
                <?php foreach ($permissionsByModule as $module => $permissions): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= ucfirst($module) ?>">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?= ucfirst($module) ?>">
                                <strong><?= ucfirst($module) ?> Module</strong>
                                <span class="badge bg-primary ms-2"><?= count($permissions) ?> permissions</span>
                            </button>
                        </h2>
                        <div id="collapse<?= ucfirst($module) ?>" 
                             class="accordion-collapse collapse" 
                             data-bs-parent="#permissionsAccordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 30%;">Permission</th>
                                                <th style="width: 40%;">Description</th>
                                                <th style="width: 30%;">Roles with Access</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($permissions as $perm): ?>
                                                <tr>
                                                    <td>
                                                        <code><?= htmlspecialchars($perm['slug']) ?></code>
                                                    </td>
                                                    <td><?= htmlspecialchars($perm['description']) ?></td>
                                                    <td>
                                                        <?php
                                                        // Get roles for this permission
                                                        $rolesForPerm = $permissionRoles[$perm['id']] ?? [];
                                                        if (empty($rolesForPerm)):
                                                        ?>
                                                            <span class="text-muted small">No roles assigned</span>
                                                        <?php else: ?>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                <?php foreach ($rolesForPerm as $role): ?>
                                                                    <span class="badge bg-secondary">
                                                                        <?= htmlspecialchars($role['name']) ?>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- All Permissions Table (Alternative View) -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Permissions</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportPermissions()">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="allPermissionsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Permission Slug</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Module</th>
                                    <th>Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPermissions as $perm): ?>
                                    <?php
                                    $parts = explode('.', $perm['slug']);
                                    $permModule = $parts[0] ?? 'other';
                                    $rolesForPerm = $permissionRoles[$perm['id']] ?? [];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($perm['id']) ?></td>
                                        <td><code><?= htmlspecialchars($perm['slug']) ?></code></td>
                                        <td><?= htmlspecialchars($perm['name']) ?></td>
                                        <td><?= htmlspecialchars($perm['description']) ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($permModule) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= count($rolesForPerm) ?> roles</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Permission Matrix (Roles vs Permissions) -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Permission Matrix</h5>
                    <small class="text-muted">Shows which roles have which permissions</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Permission</th>
                                    <?php foreach ($allRoles as $role): ?>
                                        <th class="text-center" style="min-width: 80px;">
                                            <?= htmlspecialchars($role['name']) ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPermissions as $perm): ?>
                                    <tr>
                                        <td>
                                            <small><code><?= htmlspecialchars($perm['slug']) ?></code></small>
                                        </td>
                                        <?php foreach ($allRoles as $role): ?>
                                            <?php
                                            $rolePerms = $rolePermissions[$role['id']] ?? [];
                                            $hasPermission = in_array($perm['id'], array_column($rolePerms, 'id'));
                                            ?>
                                            <td class="text-center">
                                                <?php if ($hasPermission): ?>
                                                    <i class="fas fa-check text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function exportPermissions() {
    // Simple CSV export
    const table = document.getElementById('allPermissionsTable');
    let csv = [];
    
    // Headers
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
    csv.push(headers.join(','));
    
    // Rows
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cols = Array.from(row.querySelectorAll('td')).map(td => {
            return '"' + td.textContent.trim().replace(/"/g, '""') + '"';
        });
        csv.push(cols.join(','));
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'permissions_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
