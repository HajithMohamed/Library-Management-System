<?php
/**
 * Admin E-Resources Approvals Queue View
 * Route: GET /admin/eresources/approvals
 */
$pageTitle = 'E-Resource Approvals';
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

  .page-content { padding: 2rem; }

  .flash-msg {
    padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
  }
  .flash-success { background: #d1fae5; color: #065f46; }
  .flash-error { background: #fee2e2; color: #991b1b; }

  .pending-count-banner {
    background: linear-gradient(135deg, #fef3c7, #fde68a); border: 2px solid #fcd34d;
    border-radius: 16px; padding: 1.5rem 2rem; margin-bottom: 2rem;
    display: flex; align-items: center; gap: 1.25rem;
  }
  .pending-icon {
    width: 55px; height: 55px; background: white; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #d97706;
    box-shadow: 0 4px 12px rgba(217,119,6,0.15);
  }
  .pending-text h3 { font-size: 1.2rem; font-weight: 800; color: #92400e; }
  .pending-text p { font-size: 0.9rem; color: #b45309; }

  /* Resource cards */
  .approval-grid { display: flex; flex-direction: column; gap: 1.5rem; }

  .approval-card {
    background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 2rem; border-left: 5px solid var(--warning-color); transition: all 0.3s;
    animation: fadeIn 0.5s ease-out;
  }
  .approval-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.1); transform: translateY(-2px); }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

  .card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }

  .card-info { flex: 1; }
  .card-title { font-size: 1.25rem; font-weight: 800; color: var(--dark-color); margin-bottom: 0.5rem; }
  .card-desc { color: var(--gray-500); font-size: 0.9rem; line-height: 1.5; margin-bottom: 0.75rem; }

  .card-meta { display: flex; gap: 1.5rem; flex-wrap: wrap; }
  .meta-item { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--gray-500); font-weight: 600; }
  .meta-item i { color: var(--primary-light); }

  .type-badge {
    display: inline-flex; align-items: center; gap: 4px; padding: 5px 14px;
    border-radius: 10px; font-size: 0.8rem; font-weight: 700;
  }
  .type-pdf { background: #fee2e2; color: #dc2626; }
  .type-link { background: #dbeafe; color: #2563eb; }
  .type-video { background: #d1fae5; color: #059669; }

  .card-actions { display: flex; gap: 10px; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--gray-100); flex-wrap: wrap; }

  .action-btn-lg {
    padding: 10px 24px; border: none; border-radius: 12px; font-weight: 700;
    cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center;
    gap: 8px; font-size: 0.9rem; text-decoration: none;
  }
  .btn-approve { background: var(--success-color); color: white; box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
  .btn-approve:hover { background: #059669; transform: translateY(-2px); }
  .btn-reject-a { background: white; border: 2px solid var(--danger-color); color: var(--danger-color); }
  .btn-reject-a:hover { background: var(--danger-color); color: white; }
  .btn-view-a { background: var(--gray-100); color: var(--gray-700); }
  .btn-view-a:hover { background: var(--gray-200); }
  .btn-download-a { background: #dbeafe; color: #2563eb; }
  .btn-download-a:hover { background: #2563eb; color: white; }

  /* Empty state */
  .empty-state {
    text-align: center; padding: 4rem 2rem; background: white; border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  }
  .empty-state .icon { font-size: 4rem; color: var(--success-color); margin-bottom: 1rem; }
  .empty-state h3 { font-size: 1.5rem; font-weight: 800; color: var(--dark-color); margin-bottom: 0.5rem; }
  .empty-state p { color: var(--gray-500); margin-bottom: 1.5rem; }
  .btn-back-manage {
    display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px;
    background: var(--primary-color); color: white; text-decoration: none;
    border-radius: 12px; font-weight: 700; transition: all 0.3s;
  }
  .btn-back-manage:hover { background: var(--primary-dark); color: white; }

  /* Reject inline form */
  .reject-form {
    display: none; margin-top: 1rem; padding: 1rem; background: #fef2f2; border-radius: 12px;
    border: 1px solid #fecaca;
  }
  .reject-form.show { display: block; animation: fadeIn 0.3s ease-out; }
  .reject-form textarea {
    width: 100%; padding: 10px 14px; border: 2px solid #fecaca; border-radius: 10px;
    font-size: 0.9rem; resize: vertical; margin-bottom: 1rem; font-family: inherit;
    box-sizing: border-box;
  }
  .reject-form textarea:focus { border-color: var(--danger-color); outline: none; }
  .reject-actions { display: flex; gap: 8px; justify-content: flex-end; }
  .reject-cancel {
    padding: 8px 16px; background: white; border: 1px solid var(--gray-200); border-radius: 8px;
    cursor: pointer; font-weight: 600; color: var(--gray-700); transition: all 0.3s;
  }
  .reject-confirm {
    padding: 8px 20px; background: var(--danger-color); color: white; border: none; border-radius: 8px;
    cursor: pointer; font-weight: 700; transition: all 0.3s;
  }
  .reject-confirm:hover { background: #dc2626; }

  @media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .page-content { padding: 1rem; }
    .card-top { flex-direction: column; }
    .card-meta { flex-direction: column; gap: 0.5rem; }
    .card-actions { flex-direction: column; }
    .action-btn-lg { justify-content: center; }
  }
</style>

<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<div class="admin-layout">
  <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

  <main class="main-content">
    <header class="top-header">
      <div class="header-left">
        <h1><i class="fas fa-clock" style="color:var(--warning-color);"></i> Pending Approvals</h1>
        <div class="breadcrumb">
          <span>Home</span><span>/</span>
          <a href="<?= BASE_URL ?>admin/eresources/manage" style="color:var(--primary-color);text-decoration:none;">E-Resources</a>
          <span>/</span><span>Approvals</span>
        </div>
      </div>
      <div class="header-right">
        <a href="<?= BASE_URL ?>admin/eresources/manage" class="header-btn btn-outline-h">
          <i class="fas fa-arrow-left"></i> Back to Manage
        </a>
      </div>
    </header>

    <div class="page-content">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="flash-msg flash-success">
          <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="flash-msg flash-error">
          <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <?php $pendingResources = $resources ?? []; ?>

      <?php if (!empty($pendingResources)): ?>
        <div class="pending-count-banner">
          <div class="pending-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="pending-text">
            <h3><?= count($pendingResources) ?> Resource<?= count($pendingResources) !== 1 ? 's' : '' ?> Awaiting Review</h3>
            <p>These submissions are waiting for your approval before they become available to users.</p>
          </div>
        </div>

        <div class="approval-grid">
          <?php foreach ($pendingResources as $r): ?>
          <div class="approval-card" id="card-<?= $r['id'] ?>">
            <div class="card-top">
              <div class="card-info">
                <h3 class="card-title"><?= htmlspecialchars($r['title']) ?></h3>
                <?php if (!empty($r['description'])): ?>
                  <p class="card-desc"><?= htmlspecialchars($r['description']) ?></p>
                <?php endif; ?>
                <div class="card-meta">
                  <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars($r['submitterName'] ?? $r['submitted_by']) ?>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <?= date('M d, Y \a\t g:i A', strtotime($r['created_at'])) ?>
                  </div>
                  <?php if (!empty($r['category'])): ?>
                  <div class="meta-item">
                    <i class="fas fa-folder"></i>
                    <?= htmlspecialchars($r['category']) ?>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($r['file_path'])): ?>
                  <div class="meta-item">
                    <i class="fas fa-file"></i>
                    <?= htmlspecialchars(basename($r['file_path'])) ?>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
              <span class="type-badge type-<?= $r['resource_type'] ?>">
                <i class="fas fa-<?= $r['resource_type'] === 'pdf' ? 'file-pdf' : ($r['resource_type'] === 'video' ? 'video' : 'link') ?>"></i>
                <?= strtoupper($r['resource_type']) ?>
              </span>
            </div>

            <div class="card-actions">
              <a href="<?= BASE_URL ?>eresources/view/<?= $r['id'] ?>" class="action-btn-lg btn-view-a">
                <i class="fas fa-eye"></i> Preview
              </a>
              <?php if ($r['resource_type'] === 'pdf' && !empty($r['file_path'])): ?>
              <a href="<?= BASE_URL ?>eresources/download/<?= $r['id'] ?>" class="action-btn-lg btn-download-a">
                <i class="fas fa-download"></i> Download
              </a>
              <?php endif; ?>
              <form method="POST" action="<?= BASE_URL ?>admin/eresources/approve/<?= $r['id'] ?>" style="display:inline;">
                <button type="submit" class="action-btn-lg btn-approve">
                  <i class="fas fa-check"></i> Approve
                </button>
              </form>
              <button type="button" class="action-btn-lg btn-reject-a"
                      onclick="toggleRejectForm(<?= $r['id'] ?>)">
                <i class="fas fa-times"></i> Reject
              </button>
            </div>

            <!-- Inline Reject Form -->
            <div class="reject-form" id="reject-form-<?= $r['id'] ?>">
              <form method="POST" action="<?= BASE_URL ?>admin/eresources/reject/<?= $r['id'] ?>">
                <label style="font-weight:700;color:var(--gray-700);display:block;margin-bottom:8px;">
                  <i class="fas fa-comment-dots"></i> Reason for Rejection
                </label>
                <textarea name="rejection_reason" rows="3"
                          placeholder="Provide feedback to the submitter (optional)..."></textarea>
                <div class="reject-actions">
                  <button type="button" class="reject-cancel" onclick="toggleRejectForm(<?= $r['id'] ?>)">
                    Cancel
                  </button>
                  <button type="submit" class="reject-confirm">
                    <i class="fas fa-times"></i> Confirm Rejection
                  </button>
                </div>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="empty-state">
          <div class="icon"><i class="fas fa-check-circle"></i></div>
          <h3>All Caught Up!</h3>
          <p>There are no pending e-resource submissions to review.</p>
          <a href="<?= BASE_URL ?>admin/eresources/manage" class="btn-back-manage">
            <i class="fas fa-arrow-left"></i> Back to Manage
          </a>
        </div>
      <?php endif; ?>
    </div>

    <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
  </main>
</div>

<script>
function toggleRejectForm(id) {
  const form = document.getElementById('reject-form-' + id);
  form.classList.toggle('show');
  if (form.classList.contains('show')) {
    form.querySelector('textarea').focus();
  }
}
</script>

</body>
</html>
