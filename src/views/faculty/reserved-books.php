<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Reserved Books';
include APP_ROOT . '/views/layouts/header.php';

$requests = $requests ?? [];
?>

<style>
/* Modern reserved books page */
.reserved-modern-bg {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 0;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}
.reserved-modern-card {
    background: #fff;
    border-radius: 36px;
    box-shadow: 0 24px 64px rgba(102, 126, 234, 0.18);
    max-width: 1050px;
    width: 100%;
    margin: 0 auto;
    overflow: hidden;
    animation: fadeInModern 0.7s cubic-bezier(.4,0,.2,1);
}
@keyframes fadeInModern {
    from { opacity: 0; transform: translateY(50px);}
    to { opacity: 1; transform: translateY(0);}
}
.reserved-modern-header {
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
.reserved-modern-header h1 {
    font-size: 2.5rem;
    font-weight: 900;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 1.1rem;
    letter-spacing: 0.02em;
}
.reserved-modern-header p {
    font-size: 1.15rem;
    opacity: 0.97;
    margin: 0.6rem 0 0 0;
}
.reserved-modern-header .back-button {
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
.reserved-modern-header .back-button:hover {
    background: #fff;
    color: #667eea;
}
.reserved-modern-body {
    padding: 2.7rem 3.2rem 3.2rem 3.2rem;
}
.reserved-modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 14px;
    margin-top: 1.7rem;
}
.reserved-modern-table th, .reserved-modern-table td {
    padding: 1.2rem 1.1rem;
    text-align: left;
}
.reserved-modern-table th {
    background: #f3f4f6;
    color: #667eea;
    font-weight: 900;
    font-size: 1.08rem;
    text-transform: uppercase;
    border-radius: 12px 12px 0 0;
    letter-spacing: 0.07em;
    border-bottom: 2px solid #e5e7eb;
}
.reserved-modern-table tr {
    background: #f9fafb;
    border-radius: 16px;
    transition: box-shadow 0.2s, background 0.2s;
}
.reserved-modern-table tr:hover {
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
.status-badge-modern.Pending {
    background: #fef3c7;
    color: #b45309;
    border-color: #fde68a;
}
.status-badge-modern.Approved {
    background: #d1fae5;
    color: #065f46;
    border-color: #6ee7b7;
}
.status-badge-modern.Rejected {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}
@media (max-width: 1050px) {
    .reserved-modern-card, .reserved-modern-header, .reserved-modern-body { padding: 1.2rem !important; }
    .reserved-modern-table th, .reserved-modern-table td { padding: 0.7rem !important; font-size: 0.98rem; }
}
@media (max-width: 700px) {
    .reserved-modern-header h1 { font-size: 1.4rem; }
    .reserved-modern-body { padding: 1rem !important; }
    .reserved-modern-table th, .reserved-modern-table td { font-size: 0.92rem; }
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

<div class="reserved-modern-bg">
    <div class="reserved-modern-card">
        <div class="reserved-modern-header">
            <div>
                <h1><i class="fas fa-bookmark"></i> Reserved Books</h1>
                <p>Below is the list of books you have reserved.</p>
            </div>
            <a href="<?= BASE_URL ?>faculty/dashboard" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <div class="reserved-modern-body">
            <?php if (!empty($requests)): ?>
                <div style="overflow-x:auto;">
                <table class="reserved-modern-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Requested On</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Rejection Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['bookName'] ?? '') ?></td>
                            <td><?= htmlspecialchars($req['authorName'] ?? '') ?></td>
                            <td><?= htmlspecialchars($req['isbn'] ?? '') ?></td>
                            <td><?= !empty($req['requestDate']) ? date('M d, Y', strtotime($req['requestDate'])) : '-' ?></td>
                            <td>
                                <span class="status-badge-modern <?= htmlspecialchars($req['status']) ?>">
                                    <?= htmlspecialchars($req['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= !empty($req['dueDate']) ? date('M d, Y', strtotime($req['dueDate'])) : '-' ?>
                            </td>
                            <td>
                                <?= !empty($req['rejectionReason']) ? htmlspecialchars($req['rejectionReason']) : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="empty-modern-state">
                    <i class="fas fa-bookmark"></i>
                    <h4>No Reserved Books</h4>
                    <p>You have not reserved any books yet.</p>
                    <a href="<?= BASE_URL ?>faculty/books" class="browse-modern-btn">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
