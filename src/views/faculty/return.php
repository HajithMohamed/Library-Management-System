<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Return Books';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <h2>📚 Return Books</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <?php if (!empty($borrowedBooks)): ?>
        <div class="row">
            <?php foreach ($borrowedBooks as $book): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($book['bookName']) ?></h5>
                            <p class="text-muted"><?= htmlspecialchars($book['authorName']) ?></p>
                            <p><strong>Borrowed:</strong> <?= date('M d, Y', strtotime($book['borrowDate'])) ?></p>
                            
                            <form method="POST">
                                <input type="hidden" name="transaction_id" value="<?= $book['tid'] ?>">
                                <button type="submit" class="btn btn-primary">Return Book</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No borrowed books to return.</p>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
