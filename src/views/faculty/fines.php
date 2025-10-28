<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'My Fines';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <h2>💰 My Fines</h2>
    
    <?php if (!empty($fines)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Book Name</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Fine Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fines as $fine): ?>
                        <tr>
                            <td><?= htmlspecialchars($fine['bookName']) ?></td>
                            <td><?= date('M d, Y', strtotime($fine['borrowDate'])) ?></td>
                            <td><?= $fine['returnDate'] ? date('M d, Y', strtotime($fine['returnDate'])) : 'Not returned' ?></td>
                            <td>$<?= number_format($fine['fineAmount'], 2) ?></td>
                            <td><span class="badge bg-<?= $fine['fineStatus'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($fine['fineStatus']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No fines found.</p>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
