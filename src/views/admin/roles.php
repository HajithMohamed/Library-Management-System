<?php
$pageTitle = 'Roles & Permissions';
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
                <h1>Roles & Permissions</h1>
                <div class="breadcrumb">
                    <span>System</span>
                    <span>/</span>
                    <span>Roles</span>
                </div>
            </div>
            <div class="header-right">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="fas fa-plus"></i> Add New Role
                </button>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Roles Grid -->
            <div class="row">
                <?php if (empty($roles)): ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-user-shield"></i>
                            <h3>No Roles Found</h3>
                            <p>Get started by creating a new role.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm border-0 rounded-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title fw-bold mb-1">
                                                <?= htmlspecialchars($role['name']) ?>
                                            </h5>
                                            <span class="badge bg-light text-dark border">
                                                <?= count($role['permissions'] ?? []) ?> Permissions
                                            </span>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="editRole(<?= htmlspecialchars(json_encode($role)) ?>)">
                                                        <i class="fas fa-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        onclick="deleteRole(<?= $role['id'] ?>)">
                                                        <i class="fas fa-trash-alt me-2"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mb-4">
                                        <?= htmlspecialchars($role['description'] ?? 'No description provided') ?>
                                    </p>

                                    <div class="permissions-preview mb-3">
                                        <h6 class="text-uppercase text-muted very-small fw-bold">Effective Permissions</h6>
                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            <?php
                                            $shownPermissions = array_slice($role['permissions'] ?? [], 0, 5);
                                            foreach ($shownPermissions as $perm):
                                                ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                                    <?= htmlspecialchars($perm['name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($role['permissions'] ?? []) > 5): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    +
                                                    <?= count($role['permissions']) - 5 ?> more
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pb-3 pt-0">
                                    <small class="text-muted">Slug: <code><?= htmlspecialchars($role['slug']) ?></code></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>admin/roles" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="roleAction" value="add">
                    <input type="hidden" name="role_id" id="roleId">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Role Name</label>
                            <input type="text" class="form-control" name="name" id="roleName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" id="roleSlug" required>
                            <div class="form-text">Unique identifier (e.g., student, library_manager)</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="roleDescription" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            Permissions
                            <div class="form-check list-group-item-action">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label small" for="selectAll" style="cursor: pointer;">Select
                                    All</label>
                            </div>
                        </label>
                        <div class="card bg-light">
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <div class="row g-3">
                                    <?php foreach ($allPermissions as $perm): ?>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input permission-check" type="checkbox"
                                                    name="permissions[]" value="<?= $perm['id'] ?>"
                                                    id="perm_<?= $perm['id'] ?>">
                                                <label class="form-check-label" for="perm_<?= $perm['id'] ?>">
                                                    <strong>
                                                        <?= htmlspecialchars($perm['name']) ?>
                                                    </strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($perm['slug']) ?>
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .very-small {
        font-size: 0.7rem;
        letter-spacing: 0.5px;
    }

    .card {
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-3px);
    }
</style>

<script>
    function editRole(role) {
        document.getElementById('modalTitle').textContent = 'Edit Role';
        document.getElementById('roleAction').value = 'edit';
        document.getElementById('roleId').value = role.id;
        document.getElementById('roleName').value = role.name;
        document.getElementById('roleSlug').value = role.slug;
        document.getElementById('roleDescription').value = role.description;

        // Reset permissions
        document.querySelectorAll('.permission-check').forEach(cb => cb.checked = false);

        // Check active permissions
        if (role.permissions) {
            role.permissions.forEach(perm => {
                const cb = document.getElementById('perm_' + perm.id); // Assuming perm object has id, need to verify
                // If perm object doesn't have id from the controller query, we might need slug
                if (cb) cb.checked = true;
                // Fallback to searching by slug if passed differently
                else {
                    const slugCb = document.querySelector(`input[value="${perm.id}"]`);
                    if (slugCb) slugCb.checked = true;
                }
            });
        }

        new bootstrap.Modal(document.getElementById('addRoleModal')).show();
    }

    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.permission-check').forEach(cb => cb.checked = this.checked);
    });
</script>

<?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>