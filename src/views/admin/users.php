<?php
$pageTitle = 'Users Management';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-users"></i> Users Management
                </h1>
                <div>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= count(array_filter($users, fn($u) => $u['userType'] === 'Student')) ?></h4>
                            <p class="card-text">Students</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= count(array_filter($users, fn($u) => $u['userType'] === 'Faculty')) ?></h4>
                            <p class="card-text">Faculty</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= count(array_filter($users, fn($u) => $u['userType'] === 'Admin')) ?></h4>
                            <p class="card-text">Admins</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= count(array_filter($users, fn($u) => $u['isVerified'])) ?></h4>
                            <p class="card-text">Verified</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Users</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by User ID, Email, or Phone">
                        </div>
                        <div class="col-md-3">
                            <label for="userType" class="form-label">User Type</label>
                            <select class="form-control" id="userType" name="userType">
                                <option value="">All Types</option>
                                <option value="Student" <?= ($_GET['userType'] ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                                <option value="Faculty" <?= ($_GET['userType'] ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                                <option value="Admin" <?= ($_GET['userType'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="verification" class="form-label">Verification Status</label>
                            <select class="form-control" id="verification" name="verification">
                                <option value="">All</option>
                                <option value="verified" <?= ($_GET['verification'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                                <option value="unverified" <?= ($_GET['verification'] ?? '') === 'unverified' ? 'selected' : '' ?>>Unverified</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Users List
                        <span class="badge bg-primary ms-2"><?= count($users) ?> records</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Gender</th>
                                    <th>Phone</th>
                                    <th>Verified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($user['userId']) ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($user['emailId']) ?>
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
                                            <td><?= htmlspecialchars($user['phoneNumber']) ?></td>
                                            <td>
                                                <?php if ($user['isVerified']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Verified
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Unverified
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            onclick="viewUserDetails('<?= $user['userId'] ?>')">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <?php if (!$user['isVerified']): ?>
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="verifyUser('<?= $user['userId'] ?>')">
                                                            <i class="fas fa-check"></i> Verify
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteUser('<?= $user['userId'] ?>')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No users found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- User details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Verify User Modal -->
<div class="modal fade" id="verifyUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/users/verify">
                <div class="modal-body">
                    <input type="hidden" name="userId" id="verifyUserId">
                    <p>Are you sure you want to verify this user?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This will allow the user to access the system.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Verify User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/users/delete">
                <div class="modal-body">
                    <input type="hidden" name="userId" id="deleteUserId">
                    <p>Are you sure you want to delete this user?</p>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action cannot be undone. The user must not have any active transactions.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewUserDetails(userId) {
    // Load user details via AJAX
    fetch(`<?= BASE_URL ?>admin/users/details/${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('userDetailsContent').innerHTML = data.html;
            const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load user details');
        });
}

function verifyUser(userId) {
    document.getElementById('verifyUserId').value = userId;
    const modal = new bootstrap.Modal(document.getElementById('verifyUserModal'));
    modal.show();
}

function deleteUser(userId) {
    document.getElementById('deleteUserId').value = userId;
    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}

// Auto-refresh user list every 30 seconds
setInterval(function() {
    if (window.location.pathname.includes('users') && !document.querySelector('.modal.show')) {
        // You can implement AJAX refresh here
        console.log('Auto-refreshing user list...');
    }
}, 30000);
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
