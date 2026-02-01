<?php
// Admin E-Resources Edit View
if (!defined('APP_ROOT')) {
    exit('Direct access not allowed');
}

$pageTitle = 'Edit E-Resource';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
    /* Reuse Admin Styles */
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

    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 2rem;
    }

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

    .form-container {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        max-width: 800px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .submit-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
        font-size: 1rem;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
    }

    .cancel-btn {
        margin-left: 1rem;
        color: var(--gray-700);
        text-decoration: none;
        font-weight: 600;
    }

    .current-file-preview {
        background: var(--gray-50);
        padding: 1rem;
        border-radius: 12px;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .file-info {
        color: var(--gray-700);
    }

    .help-text {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 0.5rem;
    }
</style>

<div class="admin-layout">
    <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Edit E-Resource</h1>
                <div class="breadcrumb">Dashboard / Library Management / E-Resources / Edit</div>
            </div>
        </div>

        <div class="form-container">
            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>e-resources/update/<?= $resource['resourceId'] ?>" method="POST"
                enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" for="title">Resource Title</label>
                    <input type="text" name="title" id="title" class="form-control" required
                        value="<?= htmlspecialchars($resource['title']) ?>" placeholder="Enter resource title">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description (Optional)</label>
                    <textarea name="description" id="description" rows="4" class="form-control"
                        placeholder="Brief description of the resource"><?= htmlspecialchars($resource['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Current File</label>
                    <div class="current-file-preview">
                        <?php
                        $thumbUrl = isset($cloudinaryService) ? $cloudinaryService->getThumbnailUrl($resource['fileUrl'], 80, 100) : '';
                        if (!empty($thumbUrl)):
                            ?>
                            <img src="<?= $thumbUrl ?>" alt="Current file preview"
                                style="width: 80px; height: 100px; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                            <i class="fas fa-file-pdf" style="font-size: 3rem; color: #9ca3af;"></i>
                        <?php endif; ?>
                        <div class="file-info">
                            <strong>
                                <?= htmlspecialchars($resource['title']) ?>
                            </strong><br>
                            <a href="<?= htmlspecialchars($resource['fileUrl']) ?>" target="_blank"
                                style="color: var(--primary-color);">
                                <i class="fas fa-external-link-alt"></i> View Current File
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="resourceFile">Replace File (Optional)</label>
                    <div
                        style="border: 2px dashed var(--gray-300); padding: 2rem; text-align: center; border-radius: 12px; background: var(--gray-50);">
                        <input type="file" name="resourceFile" id="resourceFile" style="display: none;"
                            onchange="updateFileName(this)">
                        <label for="resourceFile"
                            style="cursor: pointer; color: var(--primary-color); font-weight: 600;">
                            <i class="fas fa-cloud-upload-alt"
                                style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>
                            Click to Browse or Drag File Here
                        </label>
                        <p id="fileName" style="margin-top: 1rem; color: var(--gray-500); font-size: 0.9rem;">No file
                            selected</p>
                    </div>
                    <p class="help-text">Leave empty to keep the current file. Upload a new file to replace it.</p>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Update Resource</button>
                    <a href="<?= BASE_URL ?>admin/eresources" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : 'No file selected';
        document.getElementById('fileName').textContent = fileName;
    }
</script>