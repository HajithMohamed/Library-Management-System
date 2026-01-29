<?php
// Admin E-Resources Upload View
if (!defined('APP_ROOT')) {
    exit('Direct access not allowed');
}

$pageTitle = 'Upload E-Resource';
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

    /* Sidebar */
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

    /* Form Container */
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
</style>

<div class="admin-layout">
    <!-- Include the shared admin sidebar -->
    <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Upload Resource</h1>
                <div class="breadcrumb">Dashboard / Library Management / E-Resources / Upload</div>
            </div>
        </div>

        <div class="form-container">
            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>e-resources/upload" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" for="title">Resource Title</label>
                    <input type="text" name="title" id="title" class="form-control" required
                        placeholder="Enter resource title">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description (Optional)</label>
                    <textarea name="description" id="description" rows="4" class="form-control"
                        placeholder="Brief description of the resource"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="resourceFile">Upload File</label>
                    <div
                        style="border: 2px dashed var(--gray-300); padding: 2rem; text-align: center; border-radius: 12px; background: var(--gray-50);">
                        <input type="file" name="resourceFile" id="resourceFile" required style="display: none;"
                            onchange="updateFileName(this)">
                        <label for="resourceFile"
                            style="cursor: pointer; color: var(--primary-color); font-weight: 600;">
                            <i class="fas fa-cloud-upload-alt"
                                style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>
                            Click to Browse or Drag File Here
                        </label>
                        <p id="fileName" style="margin-top: 1rem; color: var(--gray-500); font-size: 0.9rem;">Supported:
                            PDF, EPUB, Images, ZIP</p>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="submit-btn"><i class="fas fa-upload"></i> Upload Resource</button>
                    <a href="<?= BASE_URL ?>e-resources" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : 'Supported: PDF, EPUB, Images, ZIP';
        document.getElementById('fileName').textContent = fileName;
    }
</script>