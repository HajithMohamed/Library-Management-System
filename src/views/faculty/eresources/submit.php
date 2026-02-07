<?php
/**
 * Faculty E-Resource Submit/Edit Form
 * Routes: GET /faculty/eresources/submit
 *         GET /faculty/eresources/edit/{id}
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$isEdit = $isEdit ?? false;
$resource = $resource ?? null;

$pageTitle = $isEdit ? 'Edit Submission' : 'Submit E-Resource';
include APP_ROOT . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); min-height: 100vh; }

    .upload-page-container {
        min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center;
        padding: 40px 20px; position: relative;
    }

    .upload-page-container::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .upload-card {
        background: rgba(255,255,255,0.15); backdrop-filter: blur(25px);
        border: 1px solid rgba(255,255,255,0.3); border-radius: 30px;
        padding: 50px; width: 100%; max-width: 750px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.2); animation: slideUp 0.6s ease-out; position: relative; z-index: 10;
    }
    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

    .card-header { text-align: center; margin-bottom: 35px; }
    .card-icon { font-size: 3.5rem; color: white; margin-bottom: 15px; }
    .card-title { font-size: 2.2rem; font-weight: 800; color: white; margin-bottom: 10px; }
    .card-subtitle { color: rgba(255,255,255,0.8); font-size: 1.1rem; }

    .form-group { margin-bottom: 22px; }
    .form-label { display: block; color: white; font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }
    .form-label .required { color: #ff6b6b; }

    .form-control {
        width: 100%; padding: 14px 18px; background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.2); border-radius: 15px; color: white;
        font-size: 1rem; transition: all 0.3s ease; box-sizing: border-box;
    }
    .form-control::placeholder { color: rgba(255,255,255,0.6); }
    .form-control:focus { background: rgba(255,255,255,0.3); border-color: white; outline: none; box-shadow: 0 0 0 4px rgba(255,255,255,0.1); }

    select.form-control { cursor: pointer; appearance: none; -webkit-appearance: none; }
    select.form-control option { color: #333; background: white; }

    .form-row { display: flex; gap: 15px; }
    .form-row .form-group { flex: 1; }

    .file-drop-area {
        border: 2px dashed rgba(255,255,255,0.4); border-radius: 20px; padding: 35px;
        text-align: center; transition: all 0.3s; cursor: pointer; background: rgba(255,255,255,0.05);
    }
    .file-drop-area:hover { background: rgba(255,255,255,0.1); border-color: white; }
    .file-drop-area.has-file { border-color: #4ECDC4; background: rgba(78,205,196,0.1); }
    .file-icon { font-size: 2.5rem; color: rgba(255,255,255,0.8); margin-bottom: 12px; }
    .file-text { color: rgba(255,255,255,0.9); font-weight: 500; margin-bottom: 5px; }
    .file-hint { color: rgba(255,255,255,0.6); font-size: 0.85rem; }

    .url-section { display: none; }
    .url-section.active { display: block; }

    .resource-type-selector { display: flex; gap: 10px; margin-bottom: 5px; }
    .type-option {
        flex: 1; padding: 12px; text-align: center; border-radius: 15px; cursor: pointer;
        background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.8); font-weight: 600; transition: all 0.3s;
    }
    .type-option:hover { background: rgba(255,255,255,0.2); }
    .type-option.active { background: rgba(255,255,255,0.3); border-color: white; color: white; }
    .type-option i { display: block; font-size: 1.5rem; margin-bottom: 5px; }

    .info-box {
        background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
        border-radius: 15px; padding: 15px 20px; margin-bottom: 20px;
        color: rgba(255,255,255,0.9); font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
    }
    .info-box i { color: #FFD700; }

    .actions-row { display: flex; gap: 20px; margin-top: 35px; }
    .btn-submit {
        flex: 2; padding: 16px; background: white; color: #764ba2; border: none;
        border-radius: 50px; font-size: 1.1rem; font-weight: 700; cursor: pointer;
        transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
    .btn-cancel {
        flex: 1; padding: 16px; background: rgba(255,255,255,0.2); color: white;
        text-align: center; text-decoration: none; border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50px; font-weight: 600; transition: all 0.3s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-cancel:hover { background: rgba(255,255,255,0.3); border-color: white; color: white; }

    .alert {
        padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; color: white;
        font-weight: 500; display: flex; align-items: center; gap: 10px;
    }
    .alert-error { background: rgba(239,68,68,0.8); border: 1px solid rgba(255,255,255,0.2); }
    .alert-success { background: rgba(16,185,129,0.8); border: 1px solid rgba(255,255,255,0.2); }

    @media (max-width: 768px) {
        .upload-card { padding: 30px 20px; }
        .card-title { font-size: 1.8rem; }
        .actions-row { flex-direction: column; }
        .form-row { flex-direction: column; gap: 0; }
    }
</style>

<div class="upload-page-container">
    <div class="upload-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-<?= $isEdit ? 'edit' : 'cloud-upload-alt' ?>"></i>
            </div>
            <h1 class="card-title"><?= $isEdit ? 'Edit Submission' : 'Share Knowledge' ?></h1>
            <p class="card-subtitle">
                <?= $isEdit ? 'Update your pending resource submission' : 'Submit a digital resource for review and approval' ?>
            </p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <span>Your submission will be reviewed by an administrator before being published.</span>
        </div>

        <form action="<?= BASE_URL ?><?= $isEdit ? 'faculty/eresources/edit/' . $resource['id'] : 'faculty/eresources/submit' ?>"
              method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label class="form-label">Resource Type <span class="required">*</span></label>
                <div class="resource-type-selector">
                    <label class="type-option <?= ($resource['resource_type'] ?? 'pdf') === 'pdf' ? 'active' : '' ?>" data-type="pdf">
                        <input type="radio" name="resource_type" value="pdf" <?= ($resource['resource_type'] ?? 'pdf') === 'pdf' ? 'checked' : '' ?> style="display:none;">
                        <i class="fas fa-file-pdf"></i> PDF
                    </label>
                    <label class="type-option <?= ($resource['resource_type'] ?? '') === 'link' ? 'active' : '' ?>" data-type="link">
                        <input type="radio" name="resource_type" value="link" <?= ($resource['resource_type'] ?? '') === 'link' ? 'checked' : '' ?> style="display:none;">
                        <i class="fas fa-link"></i> Link
                    </label>
                    <label class="type-option <?= ($resource['resource_type'] ?? '') === 'video' ? 'active' : '' ?>" data-type="video">
                        <input type="radio" name="resource_type" value="video" <?= ($resource['resource_type'] ?? '') === 'video' ? 'checked' : '' ?> style="display:none;">
                        <i class="fas fa-video"></i> Video
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Resource Title <span class="required">*</span></label>
                <input type="text" name="title" id="title" class="form-control" required
                       placeholder="e.g., Advanced Calculus Notes 2024"
                       value="<?= htmlspecialchars($resource['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea name="description" id="description" rows="4" class="form-control"
                          placeholder="Provide a brief summary of the resource content..."><?= htmlspecialchars($resource['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="category">Category</label>
                <select name="category" id="category" class="form-control">
                    <option value="">Select a category</option>
                    <?php
                    $categories = ['Mathematics', 'Science', 'Engineering', 'Computer Science', 'Literature',
                                   'History', 'Business', 'Arts', 'Medicine', 'Law', 'Education', 'Research Papers', 'Other'];
                    foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($resource['category'] ?? '') === $cat ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- PDF File Upload Section -->
            <div class="form-group file-section" id="fileSection">
                <label class="form-label">Upload PDF File</label>
                <div class="file-drop-area" id="fileDropArea" onclick="document.getElementById('resource_file').click()">
                    <input type="file" name="resource_file" id="resource_file" accept=".pdf" hidden
                           onchange="updateFileName(this)">
                    <div class="file-icon"><i class="fas fa-file-pdf"></i></div>
                    <div class="file-text" id="fileText">Click to browse or drag file here</div>
                    <div class="file-hint" id="fileHint">
                        <?php if ($isEdit && !empty($resource['file_path'])): ?>
                            Current file: <?= basename($resource['file_path']) ?> (upload new to replace)
                        <?php else: ?>
                            PDF only, max 20MB
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- URL Section -->
            <div class="form-group url-section <?= in_array($resource['resource_type'] ?? '', ['link', 'video']) ? 'active' : '' ?>" id="urlSection">
                <label class="form-label" for="resource_url">Resource URL <span class="required">*</span></label>
                <input type="url" name="resource_url" id="resource_url" class="form-control"
                       placeholder="https://example.com/resource"
                       value="<?= htmlspecialchars($resource['resource_url'] ?? '') ?>">
            </div>

            <div class="actions-row">
                <a href="<?= BASE_URL ?>faculty/eresources/my-submissions" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i>
                    <?= $isEdit ? 'Update Submission' : 'Submit for Review' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Resource type switching
    document.querySelectorAll('.type-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.type-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.type;
            const fileSection = document.getElementById('fileSection');
            const urlSection = document.getElementById('urlSection');

            if (type === 'pdf') {
                fileSection.style.display = 'block';
                urlSection.classList.remove('active');
            } else {
                fileSection.style.display = 'none';
                urlSection.classList.add('active');
            }
        });
    });

    // File name display
    function updateFileName(input) {
        const fileText = document.getElementById('fileText');
        const fileHint = document.getElementById('fileHint');
        const dropArea = document.getElementById('fileDropArea');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

            if (file.size > 20 * 1024 * 1024) {
                fileText.textContent = 'File too large!';
                fileHint.textContent = `${file.name} (${sizeMB}MB) exceeds 20MB limit`;
                dropArea.style.borderColor = '#ef4444';
                input.value = '';
                return;
            }

            if (!file.name.toLowerCase().endsWith('.pdf')) {
                fileText.textContent = 'Invalid file type!';
                fileHint.textContent = 'Only PDF files are accepted';
                dropArea.style.borderColor = '#ef4444';
                input.value = '';
                return;
            }

            fileText.textContent = file.name;
            fileHint.textContent = `Size: ${sizeMB} MB`;
            dropArea.classList.add('has-file');
            dropArea.style.borderColor = '';
        }
    }

    // Initialize visibility based on current type
    (function() {
        const currentType = document.querySelector('input[name="resource_type"]:checked')?.value || 'pdf';
        if (currentType !== 'pdf') {
            document.getElementById('fileSection').style.display = 'none';
            document.getElementById('urlSection').classList.add('active');
        }
    })();
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
