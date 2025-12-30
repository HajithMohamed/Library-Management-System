<?php
/**
 * Faculty Upload View - Glassmorphism Design
 */
$title = 'Upload E-Resource';
// Using standard header to bring in styles, but overriding body bg
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .upload-page-container {
        min-height: calc(100vh - 80px);
        /* Adjust for header */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        position: relative;
    }

    /* Animated Background Overlay */
    .upload-page-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .upload-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 30px;
        padding: 50px;
        width: 100%;
        max-width: 700px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.6s ease-out;
        position: relative;
        z-index: 10;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .card-icon {
        font-size: 3.5rem;
        color: white;
        margin-bottom: 15px;
        text-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .card-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: white;
        margin-bottom: 10px;
    }

    .card-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.1rem;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        color: white;
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 1rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-control {
        width: 100%;
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        color: white;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
        /* Ensure padding doesn't overflow */
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.3);
        border-color: white;
        outline: none;
        box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
    }

    .file-drop-area {
        border: 2px dashed rgba(255, 255, 255, 0.4);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.05);
    }

    .file-drop-area:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
    }

    .file-icon {
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 15px;
    }

    .file-text {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        margin-bottom: 5px;
    }

    .file-hint {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
    }

    .actions-row {
        display: flex;
        gap: 20px;
        margin-top: 40px;
    }

    .btn-submit {
        flex: 2;
        padding: 16px;
        background: white;
        color: #764ba2;
        border: none;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    }

    .btn-cancel {
        flex: 1;
        padding: 16px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        text-align: center;
        text-decoration: none;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-cancel:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: white;
    }

    /* Error/Success Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        color: white;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .upload-card {
            padding: 30px;
        }

        .card-title {
            font-size: 1.8rem;
        }

        .actions-row {
            flex-direction: column;
        }
    }
</style>

<div class="upload-page-container">
    <div class="upload-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h1 class="card-title">Share Knowledge</h1>
            <p class="card-subtitle">Upload digital resources to the faculty repository</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>e-resources/upload" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="title">Resource Title</label>
                <input type="text" name="title" id="title" class="form-control" required
                    placeholder="Ex: Advanced Calculus Notes 2024">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Content Description</label>
                <textarea name="description" id="description" rows="4" class="form-control"
                    placeholder="Provide a brief summary of the resource content..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Resource File</label>
                <div class="file-drop-area" onclick="document.getElementById('resourceFile').click()">
                    <input type="file" name="resourceFile" id="resourceFile" required hidden
                        onchange="updateFileName(this)">
                    <div class="file-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="file-text">Click to browse or drag file here</div>
                    <div class="file-hint" id="fileNameDisplay">Supported: PDF, EPUB, Images (Max 10MB)</div>
                </div>
            </div>

            <div class="actions-row">
                <a href="<?= BASE_URL ?>e-resources" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Upload Now
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        if (input.files && input.files[0]) {
            display.textContent = "Selected: " + input.files[0].name;
            display.style.color = "#fff";
            display.style.fontWeight = "bold";
        } else {
            display.textContent = "Supported: PDF, EPUB, Images (Max 10MB)";
        }
    }
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>