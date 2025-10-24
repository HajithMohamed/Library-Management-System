<?php
$pageTitle = 'Users Management';
include APP_ROOT . '/views/layouts/header.php';

// Get current admin's userId to prevent self-deletion
$currentAdminId = $_SESSION['userId'] ?? '';
?>

<style>
    .users-container {
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    .page-header-custom {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
    }

    .stat-card-custom {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .stat-card-custom:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .users-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .modal-custom {
        backdrop-filter: blur(5px);
    }

    .modal-content-custom {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
</style>

<div class="users-container">
    <!-- Page Header -->
    <div class="page-header-custom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1"><i class="fas fa-users"></i> Users Management</h1>
                <p class="mb-0 opacity-90">Manage system users and permissions</p>
            </div>
            <div>
                <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus"></i> Add User
                </button>
                <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            <?= count(array_filter($users, fn($u) => $u['userType'] === 'Student')) ?>
                        </h3>
                        <p class="mb-0 text-muted">Students</p>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.2; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            <?= count(array_filter($users, fn($u) => $u['userType'] === 'Faculty')) ?>
                        </h3>
                        <p class="mb-0 text-muted">Faculty</p>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.2; background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            <?= count(array_filter($users, fn($u) => $u['userType'] === 'Admin')) ?>
                        </h3>
                        <p class="mb-0 text-muted">Admins</p>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.2; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            <?= count(array_filter($users, fn($u) => $u['isVerified'])) ?>
                        </h3>
                        <p class="mb-0 text-muted">Verified</p>
                    </div>
                    <div style="font-size: 2.5rem; opacity: 0.2; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4" style="border-radius: 16px; border: none;">
        <div class="card-body p-4">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search Users</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="Search by ID, email, or phone">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">User Type</label>
                    <select class="form-control" name="userType">
                        <option value="">All Types</option>
                        <option value="Student" <?= ($_GET['userType'] ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                        <option value="Faculty" <?= ($_GET['userType'] ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                        <option value="Admin" <?= ($_GET['userType'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-control" name="verification">
                        <option value="">All</option>
                        <option value="verified" <?= ($_GET['verification'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="unverified" <?= ($_GET['verification'] ?? '') === 'unverified' ? 'selected' : '' ?>>Unverified</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-card">
        <div class="table-header-custom">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Users List
                <span class="badge bg-white text-primary ms-2"><?= count($users) ?></span>
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th>Contact</th>
                        <th>Type</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($user['username'] ?? 'U', 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($user['username'] ?? 'Unknown') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($user['userId']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div><i class="fas fa-envelope text-muted me-1"></i> <?= htmlspecialchars($user['emailId']) ?></div>
                                        <div><i class="fas fa-phone text-muted me-1"></i> <?= htmlspecialchars($user['phoneNumber']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $typeColors = [
                                        'Student' => 'primary',
                                        'Faculty' => 'success',
                                        'Admin' => 'warning'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $typeColors[$user['userType']] ?? 'secondary' ?>">
                                        <?= htmlspecialchars($user['userType']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['gender']) ?></td>
                                <td>
                                    <?php if ($user['isVerified']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick='viewUser(<?= json_encode($user) ?>)'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick='editUser(<?= json_encode($user) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['userId'] !== $currentAdminId): ?>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteUser('<?= htmlspecialchars($user['userId']) ?>', '<?= htmlspecialchars($user['username']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete yourself">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                <p class="mb-0">No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade modal-custom" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content-custom">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm" method="POST" action="<?= BASE_URL ?>admin/users/add">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="emailId" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phoneNumber" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">User Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="userType" required>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select class="form-control" name="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" class="form-control" name="dob">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isVerified" value="1" id="addVerified" checked>
                                <label class="form-check-label" for="addVerified">
                                    Verify user immediately
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade modal-custom" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content-custom">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST" action="<?= BASE_URL ?>admin/users/edit">
                <div class="modal-body p-4">
                    <input type="hidden" name="userId" id="edit_userId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="edit_username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="emailId" id="edit_emailId" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phoneNumber" id="edit_phoneNumber" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">User Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="userType" id="edit_userType" required>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select class="form-control" name="gender" id="edit_gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" id="edit_dob">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isVerified" value="1" id="edit_isVerified">
                                <label class="form-check-label" for="edit_isVerified">
                                    Account Verified
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <hr>
                            <label class="form-label fw-semibold">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Enter new password" minlength="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade modal-custom" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content-custom">
            <div class="modal-header" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-user"></i> User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="viewUserContent">
                <!-- Content loaded by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade modal-custom" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-content-custom">
            <div class="modal-header" style="background: #ef4444; color: white;">
                <h5 class="modal-title"><i class="fas fa-trash"></i> Delete User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/users/delete">
                <div class="modal-body p-4">
                    <input type="hidden" name="userId" id="delete_userId">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to delete user <strong id="delete_username"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewUser(user) {
    const content = `
        <div class="row g-3">
            <div class="col-md-12 text-center mb-3">
                <div class="user-avatar mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                    ${user.username.substring(0, 2).toUpperCase()}
                </div>
                <h4 class="mt-3">${user.username}</h4>
                <p class="text-muted">${user.userId}</p>
            </div>
            <div class="col-md-6">
                <strong>Email:</strong><br>
                ${user.emailId}
            </div>
            <div class="col-md-6">
                <strong>Phone:</strong><br>
                ${user.phoneNumber}
            </div>
            <div class="col-md-6">
                <strong>User Type:</strong><br>
                <span class="badge bg-primary">${user.userType}</span>
            </div>
            <div class="col-md-6">
                <strong>Gender:</strong><br>
                ${user.gender}
            </div>
            <div class="col-md-6">
                <strong>Date of Birth:</strong><br>
                ${user.dob || 'Not set'}
            </div>
            <div class="col-md-6">
                <strong>Status:</strong><br>
                ${user.isVerified ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning">Unverified</span>'}
            </div>
            <div class="col-md-12">
                <strong>Address:</strong><br>
                ${user.address || 'Not set'}
            </div>
        </div>
    `;
    document.getElementById('viewUserContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
}

function editUser(user) {
    document.getElementById('edit_userId').value = user.userId;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_emailId').value = user.emailId;
    document.getElementById('edit_phoneNumber').value = user.phoneNumber;
    document.getElementById('edit_userType').value = user.userType;
    document.getElementById('edit_gender').value = user.gender;
    document.getElementById('edit_dob').value = user.dob || '';
    document.getElementById('edit_address').value = user.address || '';
    document.getElementById('edit_isVerified').checked = user.isVerified == 1;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(userId, username) {
    document.getElementById('delete_userId').value = userId;
    document.getElementById('delete_username').textContent = username;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
