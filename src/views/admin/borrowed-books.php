<?php
$pageTitle = 'Borrowed Books Management';
include APP_ROOT . '/views/layouts/admin-header.php';

// Fetch borrowed books data with user and book details
global $mysqli;

// Get filters
$statusFilter = $_GET['status'] ?? '';
$userTypeFilter = $_GET['userType'] ?? '';

// Build query
$sql = "SELECT 
    t.id,
    t.userId,
    u.userType,
    t.isbn,
    t.borrowDate,
    t.dueDate,
    t.returnDate,
    t.status,
    t.notes,
    t.addedBy,
    u.username,
    u.emailId,
    b.bookName,
    b.authorName,
    b.barcode,
    DATEDIFF(CURDATE(), t.dueDate) as daysOverdue
FROM books_borrowed t
LEFT JOIN users u ON t.userId = u.userId
LEFT JOIN books b ON t.isbn = b.isbn
WHERE 1=1";

if ($statusFilter) {
    $sql .= " AND t.status = '$statusFilter'";
}

if ($userTypeFilter) {
    $sql .= " AND t.userType = '$userTypeFilter'";
}

$sql .= " ORDER BY t.borrowDate DESC";

$result = $mysqli->query($sql);
$borrowedBooks = [];
while ($row = $result->fetch_assoc()) {
    $borrowedBooks[] = $row;
}

// Get statistics
$statsQuery = "SELECT 
    COUNT(t.id) as total,
    SUM(CASE WHEN t.status = 'Active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN t.status = 'Returned' THEN 1 ELSE 0 END) as returned,
    SUM(CASE WHEN t.status = 'Overdue' THEN 1 ELSE 0 END) as overdue,
    SUM(CASE WHEN u.userType = 'Student' THEN 1 ELSE 0 END) as students,
    SUM(CASE WHEN u.userType = 'Faculty' THEN 1 ELSE 0 END) as faculty
FROM books_borrowed t
LEFT JOIN users u ON t.userId = u.userId";
$statsResult = $mysqli->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get all users for dropdown
$usersQuery = "SELECT userId, username, emailId, userType FROM users WHERE isVerified = 1 ORDER BY username";
$usersResult = $mysqli->query($usersQuery);
$users = [];
while ($row = $usersResult->fetch_assoc()) {
    $users[] = $row;
}

// Get all books for dropdown
$booksQuery = "SELECT isbn, bookName, authorName, available FROM books WHERE available > 0 ORDER BY bookName";
$booksResult = $mysqli->query($booksQuery);
$books = [];
while ($row = $booksResult->fetch_assoc()) {
    $books[] = $row;
}
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: hidden;
    }

    :root {
        --primary-color: #6366f1;
        --secondary-color: #8b5cf6;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #06b6d4;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --gray-800: #1f2937;
    }

    .admin-layout {
        display: flex;
        min-height: 100vh;
        background: #f0f2f5;
    }

    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar.collapsed {
        width: 80px;
    }

    .main-content {
        flex: 1;
        margin-left: 280px;
        transition: margin-left 0.3s ease;
        min-height: 100vh;
    }

    .sidebar.collapsed ~ .main-content {
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

    .header-right {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .header-btn {
        background: white;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        color: #64748b;
        text-decoration: none;
    }

    .header-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .page-content {
        padding: 2rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--card-gradient);
    }

    .stat-card.blue { --card-gradient: linear-gradient(90deg, #3b82f6, #2563eb); }
    .stat-card.green { --card-gradient: linear-gradient(90deg, #10b981, #059669); }
    .stat-card.orange { --card-gradient: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.red { --card-gradient: linear-gradient(90deg, #ef4444, #dc2626); }
    .stat-card.purple { --card-gradient: linear-gradient(90deg, #8b5cf6, #7c3aed); }
    .stat-card.cyan { --card-gradient: linear-gradient(90deg, #06b6d4, #0891b2); }

    .stat-info h3 {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* Control Bar */
    .control-bar {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-500);
    }

    .search-box input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        font-size: 1rem;
    }

    .search-box input:focus {
        border-color: var(--primary-color);
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .filter-group {
        display: flex;
        gap: 1rem;
    }

    .filter-select {
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        background: white;
        cursor: pointer;
    }

    .btn-add {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px -1px rgba(99, 102, 241, 0.4);
    }

    /* Table */
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: var(--gray-50);
        color: var(--gray-700);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        font-size: 0.9rem;
        text-transform: uppercase;
        border-bottom: 2px solid var(--gray-200);
    }

    .table tbody tr {
        border-bottom: 1px solid var(--gray-100);
    }

    .table tbody tr:hover {
        background: var(--gray-50);
    }

    .table tbody td {
        padding: 1rem;
        color: var(--gray-800);
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-badge.active {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.returned {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.overdue {
        background: #fee2e2;
        color: #991b1b;
    }

    .user-type-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .user-type-badge.student {
        background: #dbeafe;
        color: #1e40af;
    }

    .user-type-badge.faculty {
        background: #e0e7ff;
        color: #4338ca;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-edit:hover {
        background: #f59e0b;
        color: white;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: white;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(17, 24, 39, 0.75);
        backdrop-filter: blur(8px);
    }

    .modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .modal-dialog {
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        padding: 2rem 2rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border: 0;
        border-radius: 10px;
        width: 36px;
        height: 36px;
        color: white;
        cursor: pointer;
        font-size: 1.25rem;
    }

    .btn-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--gray-200);
        background: var(--gray-50);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        font-size: 1rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .btn-secondary {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>

<div class="admin-layout">
    <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>
    
    <main class="main-content">
        <header class="top-header">
            <div class="header-left">
                <h1>Borrowed Books Management</h1>
                <div class="breadcrumb">
                    <span>Home</span> / <span>Borrowed Books</span>
                </div>
            </div>
            <div class="header-right">
                <a href="<?= BASE_URL ?>admin/dashboard" class="header-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </header>
        
        <div class="page-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h3>Total Borrowed</h3>
                        <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-info">
                        <h3>Active Loans</h3>
                        <div class="stat-number"><?= $stats['active'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-info">
                        <h3>Returned</h3>
                        <div class="stat-number"><?= $stats['returned'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-info">
                        <h3>Overdue</h3>
                        <div class="stat-number"><?= $stats['overdue'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-info">
                        <h3>Students</h3>
                        <div class="stat-number"><?= $stats['students'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="stat-card cyan">
                    <div class="stat-info">
                        <h3>Faculty</h3>
                        <div class="stat-number"><?= $stats['faculty'] ?? 0 ?></div>
                    </div>
                </div>
            </div>

            <!-- Control Bar -->
            <div class="control-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by user, book, or ISBN...">
                </div>
                
                <div class="filter-group">
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Returned">Returned</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                    
                    <select id="userTypeFilter" class="filter-select">
                        <option value="">All User Types</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                    </select>
                </div>

                <button class="btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Borrowed Book
                </button>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Book</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($borrowedBooks)): ?>
                            <?php foreach ($borrowedBooks as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['username']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($item['userId']) ?></small>
                                    </td>
                                    <td>
                                        <span class="user-type-badge <?= strtolower($item['userType']) ?>">
                                            <?= htmlspecialchars($item['userType']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['bookName']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($item['authorName']) ?></small>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($item['borrowDate'])) ?></td>
                                    <td><?= date('M j, Y', strtotime($item['dueDate'])) ?></td>
                                    <td>
                                        <?php if ($item['returnDate']): ?>
                                            <?= date('M j, Y', strtotime($item['returnDate'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= strtolower($item['status']) ?>">
                                            <?= htmlspecialchars($item['status']) ?>
                                            <?php if ($item['daysOverdue'] > 0 && !$item['returnDate']): ?>
                                                (+<?= $item['daysOverdue'] ?> days)
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" onclick='editItem(<?= json_encode($item) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteItem(<?= $item['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                                    <p style="margin-top: 1rem; color: #64748b;">No borrowed books found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
    </main>
</div>

<!-- Add Modal -->
<div class="modal" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add Borrowed Book</h5>
                <button class="btn-close" onclick="closeModal('addModal')">×</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/borrowed-books">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">User <span style="color: red;">*</span></label>
                            <select name="userId" class="form-select" required onchange="updateDueDate('add')">
                                <option value="">Select User</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['userId'] ?>" data-type="<?= $user['userType'] ?>">
                                        <?= htmlspecialchars($user['username']) ?> (<?= $user['userType'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Book <span style="color: red;">*</span></label>
                            <select name="isbn" class="form-select" required>
                                <option value="">Select Book</option>
                                <?php foreach ($books as $book): ?>
                                    <option value="<?= $book['isbn'] ?>">
                                        <?= htmlspecialchars($book['bookName']) ?> (Available: <?= $book['available'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Borrow Date <span style="color: red;">*</span></label>
                            <input type="date" name="borrowDate" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="updateDueDate('add')">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date <span style="color: red;">*</span></label>
                            <input type="date" name="dueDate" id="add_dueDate" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required readonly>
                            <small style="color: #64748b;">Auto-calculated: Student=14 days, Faculty=21 days</small>
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Borrowed Book</h5>
                <button class="btn-close" onclick="closeModal('editModal')">×</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/borrowed-books">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">User</label>
                            <input type="text" id="edit_user" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Book</label>
                            <input type="text" id="edit_book" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Borrow Date</label>
                            <input type="date" name="borrowDate" id="edit_borrowDate" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="dueDate" id="edit_dueDate" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Return Date</label>
                            <input type="date" name="returnDate" id="edit_returnDate" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Returned">Returned</option>
                                <option value="Overdue">Overdue</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}

function toggleMobileSidebar() {
    document.getElementById('sidebar').classList.toggle('mobile-open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

function openAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function updateDueDate(mode) {
    const form = mode === 'add' ? document.querySelector('#addModal form') : null;
    if (!form) return;
    
    const userSelect = form.querySelector('select[name="userId"]');
    const borrowDateInput = form.querySelector('input[name="borrowDate"]');
    const dueDateInput = document.getElementById('add_dueDate');
    
    const selectedOption = userSelect.options[userSelect.selectedIndex];
    const userType = selectedOption.getAttribute('data-type');
    const borrowDate = borrowDateInput.value;
    
    if (userType && borrowDate) {
        const days = userType === 'Faculty' ? 21 : 14;
        const due = new Date(borrowDate);
        due.setDate(due.getDate() + days);
        dueDateInput.value = due.toISOString().split('T')[0];
    }
}

function editItem(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_user').value = item.username + ' (' + item.userId + ')';
    document.getElementById('edit_book').value = item.bookName;
    document.getElementById('edit_borrowDate').value = item.borrowDate;
    document.getElementById('edit_dueDate').value = item.dueDate;
    document.getElementById('edit_returnDate').value = item.returnDate || '';
    document.getElementById('edit_status').value = item.status;
    document.getElementById('edit_notes').value = item.notes || '';
    
    document.getElementById('editModal').classList.add('show');
}

function deleteItem(id) {
    if (confirm('Are you sure you want to delete this record?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>admin/borrowed-books';
        
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

// Search and filters
document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('userTypeFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const userType = document.getElementById('userTypeFilter').value;
    
    const rows = document.querySelectorAll('.table tbody tr');
    rows.forEach(row => {
        if (row.cells.length === 1) return;
        
        const text = row.textContent.toLowerCase();
        const rowStatus = row.cells[7].textContent.trim().split('\n')[0].trim();
        const rowUserType = row.cells[2].textContent.trim();
        
        const matchSearch = !search || text.includes(search);
        const matchStatus = !status || rowStatus === status;
        const matchUserType = !userType || rowUserType === userType;
        
        row.style.display = (matchSearch && matchStatus && matchUserType) ? '' : 'none';
    });
}
</script>
</body>
</html>
