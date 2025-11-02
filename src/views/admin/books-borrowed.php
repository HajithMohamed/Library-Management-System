<?php
$pageTitle = 'Borrowed Books Management';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
  /* Reuse existing admin dashboard styles */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    overflow-x: hidden;
  }

  .admin-layout {
    display: flex;
    min-height: 100vh;
    background: #f0f2f5;
  }

  .main-content {
    flex: 1;
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    min-height: 100vh;
  }

  .sidebar.collapsed~.main-content {
    margin-left: 80px;
  }

  .top-header {
    background: white;
    padding: 1.5rem 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .header-left h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.25rem;
  }

  .breadcrumb {
    display: flex;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.9rem;
  }

  .page-content {
    padding: 2rem;
  }

  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }

  .stat-card h3 {
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 0.5rem;
  }

  .stat-card .value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
  }

  .content-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
  }

  .btn {
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
  }

  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .btn-success {
    background: #10b981;
    color: white;
  }

  .btn-warning {
    background: #f59e0b;
    color: white;
  }

  .btn-danger {
    background: #ef4444;
    color: white;
  }

  .btn-secondary {
    background: #64748b;
    color: white;
  }

  .btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
  }

  .filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  .filter-group label {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 500;
  }

  .filter-group select {
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.9rem;
  }

  .table-container {
    overflow-x: auto;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  thead {
    background: #f8fafc;
  }

  th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #1e293b;
    font-size: 0.9rem;
    border-bottom: 2px solid #e2e8f0;
  }

  td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
  }

  tr:hover {
    background: #f8fafc;
  }

  .badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
  }

  .badge-active {
    background: #dbeafe;
    color: #1e40af;
  }

  .badge-returned {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-overdue {
    background: #fee2e2;
    color: #991b1b;
  }

  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
  }

  .modal.show {
    display: flex;
  }

  .modal-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }

  .modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
  }

  .close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #1e293b;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 0.625rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.9rem;
  }

  .form-group textarea {
    resize: vertical;
    min-height: 80px;
  }

  .form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
  }

  .alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
  }

  .alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
  }

  .alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
  }

  .empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
  }

  .empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
  }

  @media (max-width: 768px) {
    .main-content {
      margin-left: 0;
    }

    .filters {
      flex-direction: column;
    }

    .filter-group {
      width: 100%;
    }

    table {
      font-size: 0.85rem;
    }

    th,
    td {
      padding: 0.75rem 0.5rem;
    }
  }
</style>

<div class="admin-layout">
  <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

  <main class="main-content">
    <header class="top-header">
      <div class="header-left">
        <h1>Borrowed Books Management</h1>
        <div class="breadcrumb">
          <span>Admin</span>
          <span>/</span>
          <span>Borrowed Books</span>
        </div>
      </div>
    </header>

    <div class="page-content">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars(strval($_SESSION['success'] ?? '')) ?>
          <?php unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
          <?= htmlspecialchars(strval($_SESSION['error'] ?? '')) ?>
          <?php unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <!-- Statistics Cards -->
      <div class="stats-cards">
        <div class="stat-card">
          <h3>Total Active</h3>
          <div class="value"><?= (int)($stats['total_active'] ?? 0) ?></div>
        </div>
        <div class="stat-card">
          <h3>Total Returned</h3>
          <div class="value"><?= (int)($stats['total_returned'] ?? 0) ?></div>
        </div>
        <div class="stat-card">
          <h3>Total Overdue</h3>
          <div class="value"><?= (int)($stats['total_overdue'] ?? 0) ?></div>
        </div>
      </div>

      <!-- Main Content Card -->
      <div class="content-card">
        <div class="card-header">
          <h2 class="card-title">Borrowed Books Records</h2>
          <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add Borrowed Record
          </button>
        </div>

        <!-- Filters -->
        <div class="filters">
          <div class="filter-group">
            <label>Status</label>
            <select id="statusFilter" onchange="applyFilters()">
              <option value="">All Status</option>
              <option value="Active" <?= ($currentStatus ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
              <option value="Returned" <?= ($currentStatus ?? '') === 'Returned' ? 'selected' : '' ?>>Returned</option>
              <option value="Overdue" <?= ($currentStatus ?? '') === 'Overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>
          </div>
        </div>

        <!-- Table -->
        <div class="table-container">
          <?php if (!empty($borrowedBooks)): ?>
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User</th>
                  <th>Book</th>
                  <th>Borrow Date</th>
                  <th>Due Date</th>
                  <th>Return Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($borrowedBooks as $record): ?>
                  <tr>
                    <td><?= (int)($record['id'] ?? 0) ?></td>
                    <td>
                      <div><strong><?= htmlspecialchars(strval($record['username'] ?? 'Unknown')) ?></strong></div>
                      <div style="font-size: 0.85rem; color: #64748b;">
                        <?= htmlspecialchars(strval($record['emailId'] ?? '')) ?>
                      </div>
                    </td>
                    <td>
                      <div><strong><?= htmlspecialchars(strval($record['bookName'] ?? 'Unknown Book')) ?></strong></div>
                      <div style="font-size: 0.85rem; color: #64748b;">
                        <?= htmlspecialchars(strval($record['authorName'] ?? 'Unknown Author')) ?>
                      </div>
                    </td>
                    <td><?= htmlspecialchars(date('M j, Y', strtotime(strval($record['borrowDate'] ?? 'now')))) ?></td>
                    <td><?= htmlspecialchars(date('M j, Y', strtotime(strval($record['dueDate'] ?? 'now')))) ?></td>
                    <td><?= $record['returnDate'] ? htmlspecialchars(date('M j, Y', strtotime(strval($record['returnDate'])))) : '-' ?></td>
                    <td>
                      <span class="badge badge-<?= strtolower(strval($record['status'] ?? 'active')) ?>">
                        <?= htmlspecialchars(strval($record['status'] ?? 'Active')) ?>
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-warning btn-sm" onclick='openEditModal(<?= json_encode($record, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-danger btn-sm" onclick="deleteBorrowedBook(<?= (int)($record['id'] ?? 0) ?>)">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-inbox"></i>
              <p>No borrowed books records found</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Add/Edit Modal -->
<div id="borrowModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title" id="modalTitle">Add Borrowed Record</h2>
      <button class="close-modal" onclick="closeModal()">&times;</button>
    </div>
    <form id="borrowForm" method="POST" action="<?= BASE_URL ?>admin/books-borrowed">
      <input type="hidden" name="action" id="formAction" value="add">
      <input type="hidden" name="id" id="recordId">

      <div class="form-group">
        <label>User *</label>
        <select name="userId" id="userId" required>
          <option value="">Select User</option>
          <?php foreach ($users as $user): ?>
            <option value="<?= htmlspecialchars(strval($user['userId'] ?? '')) ?>">
              <?= htmlspecialchars(strval($user['username'] ?? $user['emailId'] ?? 'Unknown')) ?> 
              (<?= htmlspecialchars(strval($user['userType'] ?? 'Unknown')) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Book *</label>
        <select name="isbn" id="isbn" required>
          <option value="">Select Book</option>
          <?php foreach ($books as $book): ?>
            <option value="<?= htmlspecialchars(strval($book['isbn'] ?? '')) ?>">
              <?= htmlspecialchars(strval($book['bookName'] ?? 'Unknown Book')) ?> - 
              <?= htmlspecialchars(strval($book['authorName'] ?? 'Unknown Author')) ?>
              (Available: <?= (int)($book['available'] ?? 0) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Borrow Date *</label>
        <input type="date" name="borrowDate" id="borrowDate" required>
      </div>

      <div class="form-group">
        <label>Due Date *</label>
        <input type="date" name="dueDate" id="dueDate" required>
      </div>

      <div class="form-group" id="returnDateGroup" style="display: none;">
        <label>Return Date</label>
        <input type="date" name="returnDate" id="returnDate">
      </div>

      <div class="form-group" id="statusGroup" style="display: none;">
        <label>Status</label>
        <select name="status" id="status">
          <option value="Active">Active</option>
          <option value="Returned">Returned</option>
          <option value="Overdue">Overdue</option>
        </select>
      </div>

      <div class="form-group">
        <label>Notes</label>
        <textarea name="notes" id="notes"></textarea>
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Borrowed Record';
    document.getElementById('formAction').value = 'add';
    document.getElementById('borrowForm').reset();
    document.getElementById('recordId').value = '';
    document.getElementById('returnDateGroup').style.display = 'none';
    document.getElementById('statusGroup').style.display = 'none';

    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + 14);
    document.getElementById('borrowDate').value = today;
    document.getElementById('dueDate').value = dueDate.toISOString().split('T')[0];

    document.getElementById('borrowModal').classList.add('show');
  }

  function openEditModal(record) {
    document.getElementById('modalTitle').textContent = 'Edit Borrowed Record';
    document.getElementById('formAction').value = 'edit';

    document.getElementById('recordId').value = record.id || '';
    document.getElementById('userId').value = record.userId || '';
    document.getElementById('isbn').value = record.isbn || '';
    document.getElementById('borrowDate').value = record.borrowDate || '';
    document.getElementById('dueDate').value = record.dueDate || '';
    document.getElementById('returnDate').value = record.returnDate || '';
    document.getElementById('status').value = record.status || 'Active';
    document.getElementById('notes').value = record.notes || '';

    document.getElementById('returnDateGroup').style.display = 'block';
    document.getElementById('statusGroup').style.display = 'block';

    document.getElementById('borrowModal').classList.add('show');
  }

  function closeModal() {
    document.getElementById('borrowModal').classList.remove('show');
  }

  function deleteBorrowedBook(id) {
    if (confirm('Are you sure you want to delete this record? This will update the book availability.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '<?= BASE_URL ?>admin/books-borrowed';

      const actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'action';
      actionInput.value = 'delete';

      const idInput = document.createElement('input');
      idInput.type = 'hidden';
      idInput.name = 'id';
      idInput.value = id;

      form.appendChild(actionInput);
      form.appendChild(idInput);
      document.body.appendChild(form);
      form.submit();
    }
  }

  function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    let url = '<?= BASE_URL ?>admin/books-borrowed?';
    if (status) url += 'status=' + status;
    window.location.href = url;
  }

  // Close modal on outside click
  document.getElementById('borrowModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeModal();
    }
  });
</script>

<?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>