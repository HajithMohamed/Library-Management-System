<?php
/**
 * Admin E-Resources Management View
 * Route: GET /admin/eresources/manage
 */
$pageTitle = 'E-Resources Management';
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
  .header-right { display: flex; gap: 10px; }
  .header-btn {
    padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 600;
    font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s;
    border: none; cursor: pointer;
  }
  .btn-primary-h { background: var(--primary-color); color: white; }
  .btn-primary-h:hover { background: var(--primary-dark); color: white; }
  .btn-outline-h { background: white; border: 2px solid var(--gray-200); color: var(--gray-700); }
  .btn-outline-h:hover { border-color: var(--primary-color); color: var(--primary-color); }
  .btn-warning-h { background: var(--warning-color); color: white; }
  .btn-warning-h:hover { background: #d97706; color: white; }

  .page-content { padding: 2rem; }

  /* Flash messages */
  .flash-msg {
    padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
  }
  .flash-success { background: #d1fae5; color: #065f46; }
  .flash-error { background: #fee2e2; color: #991b1b; }

  /* Stats */
  .stats-row { display: flex; gap: 1.25rem; margin-bottom: 2rem; flex-wrap: wrap; }
  .stat-card {
    flex: 1; min-width: 180px; background: white; border-radius: 16px; padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem;
  }
  .stat-icon {
    width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center;
    justify-content: center; font-size: 1.3rem;
  }
  .stat-icon.total { background: #ede9fe; color: #7c3aed; }
  .stat-icon.approved { background: #d1fae5; color: #059669; }
  .stat-icon.pending { background: #fef3c7; color: #d97706; }
  .stat-icon.rejected { background: #fee2e2; color: #dc2626; }
  .stat-value { font-size: 1.75rem; font-weight: 800; color: var(--dark-color); }
  .stat-label { font-size: 0.85rem; color: var(--gray-500); font-weight: 500; }

  /* Filter bar */
  .filter-bar {
    display: flex; gap: 10px; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: center;
  }
  .filter-select, .filter-input {
    padding: 10px 14px; border: 2px solid var(--gray-200); border-radius: 10px;
    font-size: 0.9rem; font-weight: 500; background: white; color: var(--gray-700);
    transition: all 0.3s;
  }
  .filter-select:focus, .filter-input:focus { border-color: var(--primary-color); outline: none; }
  .filter-input { flex: 1; min-width: 200px; }
  .filter-btn {
    padding: 10px 20px; border: none; border-radius: 10px; font-weight: 600;
    cursor: pointer; transition: all 0.3s; background: var(--primary-color); color: white;
    display: inline-flex; align-items: center; gap: 6px;
  }
  .filter-btn:hover { background: var(--primary-dark); }

  /* Table */
  .table-card { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
  .table-wrapper { overflow-x: auto; }

  .data-table { width: 100%; border-collapse: collapse; }
  .data-table thead th {
    padding: 1rem 1.25rem; text-align: left; font-weight: 700; font-size: 0.8rem;
    text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500);
    border-bottom: 2px solid var(--gray-200); background: var(--gray-50); white-space: nowrap;
  }
  .data-table tbody tr { border-bottom: 1px solid var(--gray-100); transition: all 0.2s; }
  .data-table tbody tr:hover { background: #f0f0ff; }
  .data-table tbody td { padding: 1rem 1.25rem; color: var(--gray-700); font-weight: 500; vertical-align: middle; }

  .res-info { display: flex; flex-direction: column; gap: 0.3rem; }
  .res-title { font-weight: 700; color: var(--dark-color); }
  .res-desc { font-size: 0.8rem; color: var(--gray-400); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

  .status-badge {
    display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px;
    border-radius: 20px; font-size: 0.8rem; font-weight: 700;
  }
  .badge-approved { background: #d1fae5; color: #065f46; }
  .badge-pending { background: #fef3c7; color: #92400e; }
  .badge-rejected { background: #fee2e2; color: #991b1b; }

  .type-badge {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 8px; font-size: 0.78rem; font-weight: 700;
  }
  .type-pdf { background: #fee2e2; color: #dc2626; }
  .type-link { background: #dbeafe; color: #2563eb; }
  .type-video { background: #d1fae5; color: #059669; }

  .action-btn {
    padding: 7px 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
    transition: all 0.3s; display: inline-flex; align-items: center; gap: 4px;
    font-size: 0.8rem; text-decoration: none;
  }
  .btn-app { background: #d1fae5; color: #065f46; }
  .btn-app:hover { background: #065f46; color: white; }
  .btn-rej { background: #fee2e2; color: #991b1b; }
  .btn-rej:hover { background: #991b1b; color: white; }
  .btn-del { background: #fef3c7; color: #92400e; }
  .btn-del:hover { background: #92400e; color: white; }
  .btn-view-t { background: #dbeafe; color: #2563eb; }
  .btn-view-t:hover { background: #2563eb; color: white; }

  .actions-cell { display: flex; gap: 5px; flex-wrap: wrap; }

  .empty-state { text-align: center; padding: 3rem; }
  .empty-state i { font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem; display: block; }
  .empty-state h4 { font-weight: 700; color: var(--gray-700); margin-bottom: 0.5rem; }
  .empty-state p { color: var(--gray-500); }

  /* Reject modal */
  .modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000;
    align-items: center; justify-content: center;
  }
  .modal-overlay.show { display: flex; }
  .modal-box {
    background: white; border-radius: 20px; padding: 2rem; width: 500px; max-width: 95%;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25); animation: popIn 0.3s ease-out;
  }
  @keyframes popIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
  .modal-box h3 { font-weight: 800; margin-bottom: 1rem; color: var(--dark-color); }
  .modal-box textarea {
    width: 100%; padding: 12px; border: 2px solid var(--gray-200); border-radius: 12px;
    font-size: 0.95rem; resize: vertical; margin-bottom: 1.5rem; font-family: inherit;
  }
  .modal-box textarea:focus { border-color: var(--primary-color); outline: none; }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
  .modal-btn {
    padding: 10px 24px; border: none; border-radius: 10px; font-weight: 700;
    cursor: pointer; transition: all 0.3s; font-size: 0.9rem;
  }
  .modal-cancel { background: var(--gray-100); color: var(--gray-700); }
  .modal-reject { background: var(--danger-color); color: white; }
  .modal-reject:hover { background: #dc2626; }

  /* Delete confirm modal */
  .confirm-text { margin-bottom: 1.5rem; color: var(--gray-700); line-height: 1.6; }
  .confirm-title { font-weight: 700; color: var(--danger-color); }

  @media (max-width: 768px) {
    .main-content { margin-left: 0; }
    .page-content { padding: 1rem; }
    .stats-row { flex-direction: column; }
    .filter-bar { flex-direction: column; }
  }
</style>

<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<div class="admin-layout">
  <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

  <main class="main-content">
    <header class="top-header">
      <div class="header-left">
        <button class="mobile-menu-btn" onclick="toggleMobileSidebar()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--gray-700);display:none;">
          <i class="fas fa-bars"></i>
        </button>
        <h1><i class="fas fa-file-pdf" style="color:var(--primary-color);"></i> E-Resources Management</h1>
        <div class="breadcrumb">
          <span>Home</span><span>/</span><span>E-Resources</span>
        </div>
      </div>
      <div class="header-right">
        <a href="<?= BASE_URL ?>admin/eresources/approvals" class="header-btn btn-warning-h">
          <i class="fas fa-clock"></i> Pending Approvals
          <?php
          $pendingCount = 0;
          if (!empty($resources)) {
              $pendingCount = count(array_filter($resources, fn($r) => $r['status'] === 'pending'));
          }
          if ($pendingCount > 0): ?>
            <span style="background:white;color:var(--warning-color);padding:2px 8px;border-radius:20px;font-size:0.75rem;margin-left:4px;">
              <?= $pendingCount ?>
            </span>
          <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>admin/eresources/add" class="header-btn btn-primary-h">
          <i class="fas fa-plus"></i> Add Resource
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

      <!-- Stats -->
      <?php
      $totalCount = count($resources ?? []);
      $approvedCount = count(array_filter($resources ?? [], fn($r) => $r['status'] === 'approved'));
      $pendingC = count(array_filter($resources ?? [], fn($r) => $r['status'] === 'pending'));
      $rejectedCount = count(array_filter($resources ?? [], fn($r) => $r['status'] === 'rejected'));
      ?>
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-icon total"><i class="fas fa-layer-group"></i></div>
          <div><div class="stat-value"><?= $totalCount ?></div><div class="stat-label">Total Resources</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon approved"><i class="fas fa-check-circle"></i></div>
          <div><div class="stat-value"><?= $approvedCount ?></div><div class="stat-label">Approved</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
          <div><div class="stat-value"><?= $pendingC ?></div><div class="stat-label">Pending</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon rejected"><i class="fas fa-times-circle"></i></div>
          <div><div class="stat-value"><?= $rejectedCount ?></div><div class="stat-label">Rejected</div></div>
        </div>
      </div>

      <!-- Filter -->
      <form method="GET" action="<?= BASE_URL ?>admin/eresources/manage" class="filter-bar">
        <select name="status" class="filter-select">
          <option value="">All Statuses</option>
          <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <input type="text" name="search" class="filter-input" placeholder="Search by title..."
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Filter</button>
      </form>

      <!-- Table -->
      <div class="table-card">
        <?php if (!empty($resources)): ?>
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Resource</th>
                <th>Type</th>
                <th>Category</th>
                <th>Submitted By</th>
                <th>Status</th>
                <th>Downloads</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($resources as $i => $r): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <div class="res-info">
                    <span class="res-title"><?= htmlspecialchars($r['title']) ?></span>
                    <span class="res-desc"><?= htmlspecialchars($r['description'] ?? '') ?></span>
                  </div>
                </td>
                <td>
                  <span class="type-badge type-<?= $r['resource_type'] ?>">
                    <i class="fas fa-<?= $r['resource_type'] === 'pdf' ? 'file-pdf' : ($r['resource_type'] === 'video' ? 'video' : 'link') ?>"></i>
                    <?= strtoupper($r['resource_type']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($r['category'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($r['submitterName'] ?? $r['submitted_by'] ?? 'Unknown') ?></td>
                <td>
                  <span class="status-badge badge-<?= $r['status'] ?>">
                    <i class="fas fa-<?= $r['status'] === 'approved' ? 'check-circle' : ($r['status'] === 'pending' ? 'clock' : 'times-circle') ?>"></i>
                    <?= ucfirst($r['status']) ?>
                  </span>
                </td>
                <td><?= $r['download_count'] ?? 0 ?></td>
                <td style="white-space:nowrap;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                <td>
                  <div class="actions-cell">
                    <a href="<?= BASE_URL ?>eresources/view/<?= $r['id'] ?>" class="action-btn btn-view-t">
                      <i class="fas fa-eye"></i>
                    </a>
                    <?php if ($r['status'] === 'pending'): ?>
                    <form method="POST" action="<?= BASE_URL ?>admin/eresources/approve/<?= $r['id'] ?>" style="display:inline;">
                      <button type="submit" class="action-btn btn-app" title="Approve">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    <button type="button" class="action-btn btn-rej" title="Reject"
                            onclick="openRejectModal(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['title'])) ?>')">
                      <i class="fas fa-times"></i>
                    </button>
                    <?php endif; ?>
                    <button type="button" class="action-btn btn-del" title="Delete"
                            onclick="openDeleteModal(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['title'])) ?>')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <h4>No Resources Found</h4>
          <p>No e-resources match your filter criteria.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
  </main>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal">
  <div class="modal-box">
    <h3><i class="fas fa-times-circle" style="color:var(--danger-color);"></i> Reject Resource</h3>
    <p style="margin-bottom:1rem;color:var(--gray-500);">Resource: <strong id="rejectResourceTitle"></strong></p>
    <form method="POST" id="rejectForm">
      <textarea name="rejection_reason" rows="4" placeholder="Provide a reason for rejection (optional)..."></textarea>
      <div class="modal-actions">
        <button type="button" class="modal-btn modal-cancel" onclick="closeRejectModal()">Cancel</button>
        <button type="submit" class="modal-btn modal-reject"><i class="fas fa-times"></i> Reject</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <h3><i class="fas fa-trash" style="color:var(--danger-color);"></i> Delete Resource</h3>
    <p class="confirm-text">Are you sure you want to permanently delete <span class="confirm-title" id="deleteResourceTitle"></span>? This action cannot be undone.</p>
    <form method="POST" id="deleteForm">
      <div class="modal-actions">
        <button type="button" class="modal-btn modal-cancel" onclick="closeDeleteModal()">Cancel</button>
        <button type="submit" class="modal-btn modal-reject"><i class="fas fa-trash"></i> Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRejectModal(id, title) {
  document.getElementById('rejectResourceTitle').textContent = title;
  document.getElementById('rejectForm').action = '<?= BASE_URL ?>admin/eresources/reject/' + id;
  document.getElementById('rejectModal').classList.add('show');
}
function closeRejectModal() { document.getElementById('rejectModal').classList.remove('show'); }

function openDeleteModal(id, title) {
  document.getElementById('deleteResourceTitle').textContent = title;
  document.getElementById('deleteForm').action = '<?= BASE_URL ?>admin/eresources/delete/' + id;
  document.getElementById('deleteModal').classList.add('show');
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('show'); }

document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', function(e) { if (e.target === this) { this.classList.remove('show'); } });
});
</script>

</body>
</html>
