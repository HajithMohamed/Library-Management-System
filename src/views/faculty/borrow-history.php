<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Borrow History';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <h2>📖 Borrow History</h2>
    
    <?php if (!empty($history)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['bookName']) ?></td>
                            <td><?= htmlspecialchars($item['authorName']) ?></td>
                            <td><?= date('M d, Y', strtotime($item['borrowDate'])) ?></td>
                            <td><?= $item['returnDate'] ? date('M d, Y', strtotime($item['returnDate'])) : '<span class="badge bg-warning">Borrowed</span>' ?></td>
                            <td>
                                <?php if ($item['returnDate']): ?>
                                    <span class="badge bg-success">Returned</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No borrow history found.</p>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>