<?php
$pageTitle = 'All E-Resources';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    /* Reuse table styles from My Library for consistency */
    body {
        overflow-x: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    body::-webkit-scrollbar {
        display: none;
    }

    .eresources-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .eresources-container {
        max-width: 1800px;
        width: 98%;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* Header Section */
    .eresources-header {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 32px 32px 0 0;
        padding: 2.5rem 3rem;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .eresources-header-content h1 {
        font-size: 2.2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    .eresources-header-content p {
        color: #6b7280;
        font-size: 1rem;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-view-switch {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        text-decoration: none;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    .btn-view-switch:hover {
        background: #667eea;
        color: white;
    }

    /* Body Section */
    .eresources-body {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 0 0 32px 32px;
        padding: 2.5rem 3rem 3rem;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
    }

    /* Table Styles */
    .table-wrapper {
        overflow-x: auto;
        border-radius: 20px;
        margin-bottom: 2rem;
        max-height: 600px;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead th {
        padding: 1.25rem;
        text-align: left;
        font-weight: 800;
        color: white;
        text-transform: uppercase;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .modern-table tbody tr {
        background: white;
        transition: all 0.2s;
        border-bottom: 1px solid #f3f4f6;
    }

    .modern-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
    }

    .modern-table td {
        padding: 1.25rem;
        color: #374151;
        font-weight: 600;
        vertical-align: middle;
    }

    .resource-desc {
        color: #6b7280;
        font-size: 0.9rem;
        max-width: 400px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-right: 0.5rem;
        transition: transform 0.2s;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }

    .btn-view {
        background: #10b981;
        color: white;
    }

    .btn-save {
        background: #6366f1;
        color: white;
    }
</style>

<div class="eresources-wrapper">
    <div class="eresources-container">
        <!-- Header -->
        <div class="eresources-header">
            <div class="eresources-header-content">
                <h1><i class="fas fa-list-ul"></i> All E-Resources</h1>
                <p>Browse the complete library collection</p>
            </div>
            <div class="header-actions">
                <a href="<?= BASE_URL ?>e-resources" class="btn-view-switch">
                    <i class="fas fa-th-large"></i> Grid View
                </a>
            </div>
        </div>

        <!-- Body -->
        <div class="eresources-body">
            <div class="table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Uploaded By</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resources)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 2rem;">No resources found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resources as $resource): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <div
                                                style="width: 40px; height: 55px; border-radius: 6px; overflow: hidden; background: #f3f4f6; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                                <?php
                                                $thumbUrl = isset($cloudinaryService) ? $cloudinaryService->getThumbnailUrl($resource['fileUrl'], 40, 55) : '';
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
                                            <span><?= htmlspecialchars($resource['title']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="resource-desc" title="<?= htmlspecialchars($resource['description']) ?>">
                                            <?= htmlspecialchars($resource['description']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($resource['uploaderName'] ?? ($resource['uploadedBy'] ?? 'Library')) ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($resource['createdAt'])) ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($resource['fileUrl']) ?>" target="_blank"
                                            class="btn-action btn-view">
                                            <i class="fas fa-external-link-alt"></i> View
                                        </a>
                                        <a href="<?= BASE_URL ?>e-resources/obtain/<?= $resource['resourceId'] ?>"
                                            class="btn-action btn-save">
                                            <i class="fas fa-plus"></i> Save
                                        </a>
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

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>