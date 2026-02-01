<?php
// Admin E-Resources View
if (!defined('APP_ROOT')) {
    exit('Direct access not allowed');
}

$pageTitle = 'E-Resources Management';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
    /* Reuse Admin Styles from books.php */
    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --dark-color: #1f2937;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-700: #374151;
        --gray-800: #1f2937;
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

    /* Sidebar (Simplified for this view, ideally shared) */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        z-index: 1000;
        overflow-y: auto;
    }

    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 2rem;
    }

    /* Header */
    .page-header {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-title h1 {
        font-size: 1.5rem;
        color: var(--gray-800);
        margin-bottom: 0.5rem;
    }

    .breadcrumb {
        color: var(--gray-700);
        font-size: 0.9rem;
    }

    /* Table */
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: var(--gray-50);
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-700);
        border-bottom: 2px solid var(--gray-200);
    }

    .data-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
        color: var(--gray-800);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        margin-right: 5px;
    }

    .btn-approve {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-approve:hover {
        background: #10b981;
        color: white;
    }

    .btn-reject {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-reject:hover {
        background: #ef4444;
        color: white;
    }

    .btn-delete {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .btn-delete:hover {
        background: var(--gray-700);
        color: white;
    }

    .add-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(99, 102, 241, 0.3);
    }

    .add-btn:hover {
        transform: translateY(-2px);
    }
</style>

<div class="admin-layout">
    <!-- Include the shared admin sidebar -->
    <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>E-Resources Management</h1>
                <div class="breadcrumb">Dashboard / Library Management / E-Resources</div>
            </div>
            <a href="<?= BASE_URL ?>e-resources/upload" class="add-btn">
                <i class="fas fa-plus"></i> Add New Resource
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resources)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No resources found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($resources as $resource): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; gap: 15px; align-items: center;">
                                        <div
                                            style="width: 50px; height: 70px; border-radius: 8px; overflow: hidden; background: #e5e7eb; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                            <?php
                                            $thumbUrl = isset($cloudinaryService) ? $cloudinaryService->getThumbnailUrl($resource['fileUrl'], 50, 70) : '';
                                            if (!empty($thumbUrl)):
                                                ?>
                                                <img src="<?= $thumbUrl ?>" alt=""
                                                    style="width: 100%; height: 100%; object-fit: cover;"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <i class="fas fa-file-pdf" style="display: none; color: #9ca3af;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-file-pdf" style="color: #9ca3af;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($resource['title']) ?></strong><br>
                                            <small
                                                style="color: #6b7280;"><?= substr(htmlspecialchars($resource['description']), 0, 50) ?>...</small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($resource['uploadedBy']) ?></td>
                                <td><?= date('M d, Y', strtotime($resource['createdAt'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $resource['status'] ?>">
                                        <?= ucfirst($resource['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($resource['status'] === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>e-resources/approve/<?= $resource['resourceId'] ?>"
                                            class="btn-action btn-approve" title="Approve"><i class="fas fa-check"></i></a>
                                        <a href="<?= BASE_URL ?>e-resources/reject/<?= $resource['resourceId'] ?>"
                                            class="btn-action btn-reject" title="Reject"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>e-resources/edit/<?= $resource['resourceId'] ?>" class="btn-action"
                                        style="background:#fef3c7; color:#d97706;" title="Edit"><i class="fas fa-edit"></i></a>

                                    <a href="<?= htmlspecialchars($resource['fileUrl']) ?>" target="_blank" class="btn-action"
                                        style="background:#e0f2fe; color:#0369a1;" title="View"><i class="fas fa-eye"></i></a>

                                    <a href="<?= BASE_URL ?>e-resources/delete/<?= $resource['resourceId'] ?>"
                                        class="btn-action btn-delete" title="Delete"
                                        onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>