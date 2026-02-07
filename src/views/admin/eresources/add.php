<?php
/**
 * Admin Add E-Resource View
 * Route: GET /admin/eresources/add
 */
$pageTitle = 'Add E-Resource';
$currentPage = 'e-resources';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }

  :root {
    --primary-color: #6366f1; --primary-dark: #4f46e5; --primary-light: #818cf8;
    --secondary-color: #8b5cf6; --success-color: #10b981; --danger-color: #ef4444;
    --warning-color: #f59e0b; --info-color: #06b6d4; --dark-color: #1f2937;
    --gray-50: #f9fafb; --gray-100: #f3f4f6; --gray-200: #e5e7eb;
    --gray-400: #9ca3af; --gray-500: #6b7280; --gray-700: #374151;
  }

  .admin-layout { display: flex; min-height: 100vh; background: #f0f2f5; }
  .main-content { flex: 1; margin-left: 280px; transition: all 0.3s ease; }
  .sidebar.collapsed ~ .main-content { margin-left: 80px; }

  .top-header {
    background: white; padding: 1.25rem 2rem; display: flex; justify-content: space-between;
    align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100;
  }
  .header-left h1 { font-size: 1.5rem; font-weight: 700; color: var(--dark-color); }
  .header-left .breadcrumb {
    display: flex; gap: 0.5rem; font-size: 0.85rem; color: var(--gray-500); margin-top: 0.25rem;
  }
  .header-btn {
    padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 600;
    font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s;
  }
  .btn-outline-h { background: white; border: 2px solid var(--gray-200); color: var(--gray-700); }
  .btn-outline-h:hover { border-color: var(--primary-color); color: var(--primary-color); }

  .page-content { padding: 2rem; display: flex; justify-content: center; }

  /* Flash messages */
  .flash-msg {
    padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
  }
  .flash-success { background: #d1fae5; color: #065f46; }
  .flash-error { background: #fee2e2; color: #991b1b; }

  .form-card {
    background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    padding: 2.5rem; width: 100%; max-width: 800px;
  }
  .form-card h2 {
    font-size: 1.5rem; font-weight: 800; color: var(--dark-color); margin-bottom: 0.5rem;
  }
  .form-card .subtitle { color: var(--gray-500); margin-bottom: 2rem; }

  .info-note {
    background: #ede9fe; border: 1px solid #c4b5fd; border-radius: 12px; padding: 1rem 1.25rem;
    margin-bottom: 1.5rem; color: #5b21b6; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
  }

  .form-group { margin-bottom: 1.5rem; }
  .form-label { display: block; font-weight: 700; color: var(--gray-700); margin-bottom: 0.5rem; font-size: 0.9rem; }
  .form-label .req { color: var(--danger-color); }

  .form-control-a {
    width: 100%; padding: 12px 16px; border: 2px solid var(--gray-200); border-radius: 12px;
    font-size: 0.95rem; font-weight: 500; color: var(--dark-color); transition: all 0.3s;
    font-family: inherit; box-sizing: border-box;
  }
  .form-control-a:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
  select.form-control-a { cursor: pointer; appearance: auto; }

  .form-row { display: flex; gap: 1.25rem; }
  .form-row .form-group { flex: 1; }

  .type-selector { display: flex; gap: 10px; }
  .type-opt {
    flex: 1; padding: 14px 10px; text-align: center; border-radius: 12px; cursor: pointer;
    background: var(--gray-50); border: 2px solid var(--gray-200); color: var(--gray-500); font-weight: 700;
    transition: all 0.3s;
  }
  .type-opt:hover { border-color: var(--primary-light); }
  .type-opt.active { background: #ede9fe; border-color: var(--primary-color); color: var(--primary-color); }
  .type-opt i { display: block; font-size: 1.5rem; margin-bottom: 4px; }

  .file-upload-area {
    border: 2px dashed var(--gray-200); border-radius: 16px; padding: 2rem;
    text-align: center; cursor: pointer; transition: all 0.3s; background: var(--gray-50);
  }
  .file-upload-area:hover { border-color: var(--primary-color); background: #f5f3ff; }
  .file-upload-area.has-file { border-color: var(--success-color); background: #ecfdf5; }
  .file-upload-area i { font-size: 2rem; color: var(--gray-400); display: block; margin-bottom: 8px; }
  .file-upload-area .text { font-weight: 600; color: var(--gray-700); }
  .file-upload-area .hint { font-size: 0.8rem; color: var(--gray-400); margin-top: 4px; }

  .url-field { display: none; }
  .url-field.show { display: block; }

  .form-actions { display: flex; gap: 15px; margin-top: 2rem; justify-content: flex-end; }
  .btn-cancel-a {
    padding: 12px 28px; background: var(--gray-100); color: var(--gray-700); border: none;
    border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.3s;
    text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
  }
  .btn-cancel-a:hover { background: var(--gray-200); color: var(--gray-700); }
  .btn-save {
    padding: 12px 32px; background: var(--primary-color); color: white; border: none;
    border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.3s;
    display: inline-flex; align-items: center; gap: 8px; font-size: 0.95rem;
    box-shadow: 0 8px 20px rgba(99,102,241,0.25);
  }
  .btn-save:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 12px 25px rgba(99,102,241,0.3); }

  @media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .form-row { flex-direction: column; gap: 0; }
    .type-selector { flex-wrap: wrap; }
  }
</style>

<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<div class="admin-layout">
  <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

  <main class="main-content">
    <header class="top-header">
      <div class="header-left">
        <h1><i class="fas fa-plus-circle" style="color:var(--primary-color);"></i> Add E-Resource</h1>
        <div class="breadcrumb">
          <span>Home</span><span>/</span>
          <a href="<?= BASE_URL ?>admin/eresources/manage" style="color:var(--primary-color);text-decoration:none;">E-Resources</a>
          <span>/</span><span>Add</span>
        </div>
      </div>
      <div class="header-right">
        <a href="<?= BASE_URL ?>admin/eresources/manage" class="header-btn btn-outline-h">
          <i class="fas fa-arrow-left"></i> Back to Manage
        </a>
      </div>
    </header>

    <div class="page-content">
      <div class="form-card">
        <h2><i class="fas fa-file-upload"></i> Upload New E-Resource</h2>
        <p class="subtitle">Add a new digital resource to the library. It will be automatically approved.</p>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="flash-msg flash-error">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
          <div class="flash-msg flash-success">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
          </div>
        <?php endif; ?>

        <div class="info-note">
          <i class="fas fa-shield-alt"></i>
          Resources uploaded by admins are automatically approved and published.
        </div>

        <form action="<?= BASE_URL ?>admin/eresources/add" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <label class="form-label">Resource Type <span class="req">*</span></label>
            <div class="type-selector">
              <label class="type-opt active" data-type="pdf">
                <input type="radio" name="resource_type" value="pdf" checked style="display:none;">
                <i class="fas fa-file-pdf"></i> PDF
              </label>
              <label class="type-opt" data-type="link">
                <input type="radio" name="resource_type" value="link" style="display:none;">
                <i class="fas fa-link"></i> Link
              </label>
              <label class="type-opt" data-type="video">
                <input type="radio" name="resource_type" value="video" style="display:none;">
                <i class="fas fa-video"></i> Video
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="title">Title <span class="req">*</span></label>
            <input type="text" name="title" id="title" class="form-control-a" required
                   placeholder="Enter resource title">
          </div>

          <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea name="description" id="description" rows="4" class="form-control-a"
                      placeholder="Describe the resource content..."></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="category">Category</label>
            <select name="category" id="category" class="form-control-a">
              <option value="">Select category</option>
              <?php
              $categories = ['Mathematics', 'Science', 'Engineering', 'Computer Science', 'Literature',
                             'History', 'Business', 'Arts', 'Medicine', 'Law', 'Education', 'Research Papers', 'Other'];
              foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>"><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- PDF File Upload -->
          <div class="form-group" id="fileGroup">
            <label class="form-label">Upload PDF File</label>
            <div class="file-upload-area" id="fileDropArea" onclick="document.getElementById('resource_file').click()">
              <input type="file" name="resource_file" id="resource_file" accept=".pdf" hidden
                     onchange="updateFile(this)">
              <i class="fas fa-cloud-upload-alt"></i>
              <div class="text" id="fileText">Click to select a PDF file</div>
              <div class="hint" id="fileHint">PDF only, max 20MB</div>
            </div>
          </div>

          <!-- URL -->
          <div class="form-group url-field" id="urlGroup">
            <label class="form-label" for="resource_url">Resource URL <span class="req">*</span></label>
            <input type="url" name="resource_url" id="resource_url" class="form-control-a"
                   placeholder="https://example.com/resource">
          </div>

          <div class="form-actions">
            <a href="<?= BASE_URL ?>admin/eresources/manage" class="btn-cancel-a">
              <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn-save">
              <i class="fas fa-save"></i> Save & Publish
            </button>
          </div>
        </form>
      </div>
    </div>

    <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
  </main>
</div>

<script>
document.querySelectorAll('.type-opt').forEach(opt => {
  opt.addEventListener('click', function() {
    document.querySelectorAll('.type-opt').forEach(o => o.classList.remove('active'));
    this.classList.add('active');
    const type = this.dataset.type;
    document.getElementById('fileGroup').style.display = type === 'pdf' ? 'block' : 'none';
    const urlGroup = document.getElementById('urlGroup');
    if (type !== 'pdf') { urlGroup.classList.add('show'); } else { urlGroup.classList.remove('show'); }
  });
});

function updateFile(input) {
  const fileText = document.getElementById('fileText');
  const fileHint = document.getElementById('fileHint');
  const area = document.getElementById('fileDropArea');
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
    if (file.size > 20 * 1024 * 1024) {
      fileText.textContent = 'File too large!';
      fileHint.textContent = `${sizeMB}MB exceeds 20MB limit`;
      area.style.borderColor = '#ef4444'; input.value = ''; return;
    }
    if (!file.name.toLowerCase().endsWith('.pdf')) {
      fileText.textContent = 'Invalid format!';
      fileHint.textContent = 'Only PDF files accepted';
      area.style.borderColor = '#ef4444'; input.value = ''; return;
    }
    fileText.textContent = file.name;
    fileHint.textContent = `Size: ${sizeMB} MB`;
    area.classList.add('has-file'); area.style.borderColor = '';
  }
}
</script>

</body>
</html>
