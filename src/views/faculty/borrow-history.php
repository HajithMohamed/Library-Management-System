<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}
$pageTitle = 'Borrow History';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
.borrow-history-bg {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 0;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}
.borrow-history-card {
    background: #fff;
    border-radius: 36px;
    box-shadow: 0 24px 64px rgba(102, 126, 234, 0.18);
    max-width: 1100px;
    width: 100%;
    margin: 0 auto;
    overflow: hidden;
    animation: fadeInModern 0.7s cubic-bezier(.4,0,.2,1);
}
@keyframes fadeInModern {
    from { opacity: 0; transform: translateY(50px);}
    to { opacity: 1; transform: translateY(0);}
}
.borrow-history-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 2.7rem 3.2rem 2.2rem 3.2rem;
    border-radius: 36px 36px 0 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    position: relative;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.10);
}
.borrow-history-header h1 {
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 1.1rem;
    letter-spacing: 0.02em;
}
.borrow-history-header p {
    font-size: 1.08rem;
    opacity: 0.97;
    margin: 0.6rem 0 0 0;
}
.borrow-history-header .back-button {
    background: rgba(255,255,255,0.12);
    border: 2px solid #fff;
    color: #fff;
    border-radius: 14px;
    padding: 0.8rem 1.7rem;
    font-weight: 700;
    font-size: 1.05rem;
    text-decoration: none;
    transition: all 0.2s;
    margin-left: auto;
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.08);
    display: flex;
    align-items: center;
    gap: 0.7rem;
}
.borrow-history-header .back-button:hover {
    background: #fff;
    color: #667eea;
}
.borrow-history-body {
    padding: 2.7rem 3.2rem 3.2rem 3.2rem;
}
.borrow-history-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 14px;
    margin-top: 1.7rem;
}
.borrow-history-table th, .borrow-history-table td {
    padding: 1.2rem 1.1rem;
    text-align: left;
}
.borrow-history-table th {
    background: #f3f4f6;
    color: #667eea;
    font-weight: 900;
    font-size: 1.08rem;
    text-transform: uppercase;
    border-radius: 12px 12px 0 0;
    letter-spacing: 0.07em;
    border-bottom: 2px solid #e5e7eb;
}
.borrow-history-table tr {
    background: #f9fafb;
    border-radius: 16px;
    transition: box-shadow 0.2s, background 0.2s;
}
.borrow-history-table tr:hover {
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.10);
    background: #f1f5f9;
}
.status-badge-modern {
    padding: 0.6rem 1.5rem;
    border-radius: 22px;
    font-weight: 800;
    font-size: 1.05rem;
    display: inline-block;
    letter-spacing: 0.04em;
    border: 2px solid transparent;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.07);
}
.status-badge-modern.Returned {
    background: #d1fae5;
    color: #065f46;
    border-color: #6ee7b7;
}
.status-badge-modern.Borrowed, .status-badge-modern.Active {
    background: #fef3c7;
    color: #b45309;
    border-color: #fde68a;
}
.status-badge-modern.Overdue {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}
.extend-btn {
    padding: 0.5rem 1.3rem;
    border-radius: 10px;
    border: none;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.13);
    font-size: 0.98rem;
    cursor: pointer;
}
.extend-btn:disabled {
    background: #e5e7eb;
    color: #a1a1aa;
    cursor: not-allowed;
}
.extend-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px) scale(1.03);
}
.empty-modern-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}
.empty-modern-state i {
    font-size: 3.7rem;
    margin-bottom: 20px;
    opacity: 0.5;
}
.empty-modern-state h4 {
    font-size: 1.8rem;
    font-weight: 900;
    margin-bottom: 0.7rem;
}
.empty-modern-state p {
    font-size: 1.13rem;
    margin-bottom: 2.2rem;
}
.browse-modern-btn {
    padding: 1.1rem 2.1rem;
    border-radius: 14px;
    border: none;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.8rem;
    transition: all 0.3s;
    box-shadow: 0 12px 28px rgba(102, 126, 234, 0.25);
    font-size: 1.08rem;
}
.browse-modern-btn:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 18px 40px rgba(102, 126, 234, 0.32);
    color: white;
}
</style>

<div class="borrow-history-bg">
    <div class="borrow-history-card">
        <div class="borrow-history-header">
            <div>
                <h1><i class="fas fa-history"></i> Borrow History</h1>
                <p>Below is your complete borrow history. You can request to extend due dates for active borrows.</p>
            </div>
            <a href="<?= BASE_URL ?>faculty/dashboard" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <div class="borrow-history-body">
            <?php if (!empty($history)): ?>
                <div style="overflow-x:auto;">
                <table class="borrow-history-table">
                    <thead>
                        <tr>
                            <th>Book Name</th>
                            <th>Author</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['bookName'] ?? $item['title'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($item['authorName'] ?? $item['author'] ?? 'Unknown') ?></td>
                            <td><?= !empty($item['borrowDate']) ? date('M d, Y', strtotime($item['borrowDate'])) : '-' ?></td>
                            <td>
                                <?php if (!empty($item['returnDate'])): ?>
                                    <?= date('M d, Y', strtotime($item['returnDate'])) ?>
                                <?php else: ?>
                                    <span class="status-badge-modern Borrowed">Borrowed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['returnDate'])): ?>
                                    <span class="status-badge-modern Returned">Returned</span>
                                <?php elseif (!empty($item['dueDate']) && strtotime($item['dueDate']) < time()): ?>
                                    <span class="status-badge-modern Overdue">Overdue</span>
                                <?php else: ?>
                                    <span class="status-badge-modern Active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Action Section: Extend Due Date -->
                                <?php if (empty($item['returnDate'])): ?>
                                    <div style="display:flex; flex-direction:column; gap:8px;">
                                        <button class="extend-btn"
                                            onclick="openExtendModal('<?= htmlspecialchars($item['id'] ?? '') ?>', '<?= htmlspecialchars($item['bookName'] ?? '') ?>', '<?= htmlspecialchars($item['dueDate'] ?? '') ?>')"
                                            <?= !empty($item['extend_requested']) && $item['extend_requested'] ? 'disabled title="Already requested"' : '' ?>>
                                            <i class="fas fa-clock"></i>
                                            <?= !empty($item['extend_requested']) && $item['extend_requested'] ? 'Requested' : 'Extend Due Date' ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#a1a1aa;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="empty-modern-state">
                    <i class="fas fa-history"></i>
                    <h4>No Borrow History</h4>
                    <p>You have not borrowed any books yet.</p>
                    <a href="<?= BASE_URL ?>faculty/books" class="browse-modern-btn">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Extend Due Date Modal -->
<div id="extendDueModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
    <div style="background:white; border-radius:20px; max-width:400px; margin:auto; padding:2rem; position:relative;">
        <button onclick="closeExtendModal()" style="position:absolute; top:10px; right:15px; background:none; border:none; font-size:1.5rem; color:#888; cursor:pointer;">&times;</button>
        <h3 style="margin-bottom:1rem;">Request Due Date Extension</h3>
        <form id="extendDueForm" method="POST" action="<?= BASE_URL ?>faculty/borrow-history">
            <input type="hidden" name="tid" id="extend_tid">
            <div style="margin-bottom:1rem;">
                <label>Book</label>
                <input type="text" id="extend_book" class="form-control" readonly>
            </div>
            <div style="margin-bottom:1rem;">
                <label>Current Due Date</label>
                <input type="text" id="extend_due" class="form-control" readonly>
            </div>
            <div style="margin-bottom:1rem;">
                <label>Reason for Extension <span style="color:red;">*</span></label>
                <textarea name="reason" class="form-control" required placeholder="Why do you need an extension?" maxlength="200"></textarea>
            </div>
            <button type="submit" class="extend-btn" style="width:100%;">
                <i class="fas fa-paper-plane"></i> Submit Request
            </button>
        </form>
    </div>
</div>

<script>
function openExtendModal(tid, book, due) {
    document.getElementById('extendDueModal').style.display = 'flex';
    document.getElementById('extend_tid').value = tid;
    document.getElementById('extend_book').value = book;
    document.getElementById('extend_due').value = due;
}
function closeExtendModal() {
    document.getElementById('extendDueModal').style.display = 'none';
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

<?php
// --- Handle Extend Due Date Request (Controller logic suggestion) ---
// In FacultyController::borrowHistory():
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tid'], $_POST['reason'])) {
//     // Insert into a new table 'due_extension_requests' with status 'Pending'
//     // Notify admin for approval
//     // Show success message to user
// }
// In admin panel, show pending extension requests for approval.
// On approval, update due date in transactions and notify user.
?>