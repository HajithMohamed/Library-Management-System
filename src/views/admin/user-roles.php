<?php
$pageTitle = 'User Role Management';
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
                <h1>User Role Management</h1>
                <div class="breadcrumb">
                    <span>System</span>
                    <span>/</span>
                    <span>User Roles</span>
                </div>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchUsers()">
                </div>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="filterRole" class="form-label">Filter by Role</label>
                            <select class="form-select" id="filterRole" onchange="filterByRole()">
                                <option value="">All Roles</option>
                                <?php foreach ($allRoles as $role): ?>
                                    <option value="<?= htmlspecialchars($role['slug']) ?>">
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filterType" class="form-label">Filter by User Type</label>
                            <select class="form-select" id="filterType" onchange="filterByType()">
                                <option value="">All Types</option>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Users and Their Roles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>User Type</th>
                                    <th>Assigned Roles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p>No users found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr data-user-id="<?= htmlspecialchars($user['userId']) ?>"
                                            data-user-type="<?= htmlspecialchars($user['userType'] ?? '') ?>">
                                            <td><?= htmlspecialchars($user['userId']) ?></td>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['emailId']) ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($user['userType'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($user['roles'] ?? [] as $role): ?>
                                                        <span class="badge bg-primary">
                                                            <?= htmlspecialchars($role['name']) ?>
                                                            <a href="#" onclick="removeUserRole('<?= htmlspecialchars($user['userId']) ?>', <?= $role['id'] ?>); return false;" 
                                                               class="text-white ms-1" style="text-decoration: none;">×</a>
                                                        </span>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($user['roles'])): ?>
                                                        <span class="text-muted small">No roles assigned</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" 
                                                        onclick="showAssignRoleModal('<?= htmlspecialchars($user['userId']) ?>', '<?= htmlspecialchars($user['username']) ?>')">
                                                    <i class="fas fa-plus"></i> Assign Role
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Assign Role Modal -->
<div class="modal fade" id="assignRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Assigning role to: <strong id="assignUserName"></strong></p>
                <input type="hidden" id="assignUserId">
                
                <div class="mb-3">
                    <label for="roleSelect" class="form-label">Select Role</label>
                    <select class="form-select" id="roleSelect" required>
                        <option value="">Choose a role...</option>
                        <?php foreach ($allRoles as $role): ?>
                            <option value="<?= htmlspecialchars($role['slug']) ?>">
                                <?= htmlspecialchars($role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignRole()">Assign Role</button>
            </div>
        </div>
    </div>
</div>

<script>
// Search users
function searchUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Filter by role
function filterByRole() {
    const roleSlug = document.getElementById('filterRole').value;
    
    // Reload page with filter parameter
    if (roleSlug) {
        window.location.href = window.location.pathname + '?role=' + roleSlug;
    } else {
        window.location.href = window.location.pathname;
    }
}

// Filter by user type
function filterByType() {
    const userType = document.getElementById('filterType').value;
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        if (!userType) {
            row.style.display = '';
        } else {
            const rowType = row.getAttribute('data-user-type');
            row.style.display = rowType === userType ? '' : 'none';
        }
    });
}

// Show assign role modal
function showAssignRoleModal(userId, userName) {
    document.getElementById('assignUserId').value = userId;
    document.getElementById('assignUserName').textContent = userName;
    new bootstrap.Modal(document.getElementById('assignRoleModal')).show();
}

// Assign role to user
function assignRole() {
    const userId = document.getElementById('assignUserId').value;
    const roleSlug = document.getElementById('roleSelect').value;
    
    if (!roleSlug) {
        showNotification('Please select a role', 'error');
        return;
    }
    
    fetch('/admin/users/assign-role', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ userId, roleSlug })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Role assigned successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (data.message || 'Failed to assign role'), 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
    });
}

// Remove role from user
function removeUserRole(userId, roleId) {
    // Show confirmation using Bootstrap modal would be better, but using native confirm for simplicity
    if (!confirm('Are you sure you want to remove this role?')) {
        return;
    }
    
    fetch('/admin/users/remove-role', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ userId, roleId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Role removed successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (data.message || 'Failed to remove role'), 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
    });
}

// Simple notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
