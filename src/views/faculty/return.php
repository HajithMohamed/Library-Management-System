<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Return Books</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Borrowed Books -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Currently Borrowed Books</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Book</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Author</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Borrow Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Due Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($borrowedBooks) && is_array($borrowedBooks)): ?>
                                    <?php foreach ($borrowedBooks as $book): ?>
                                        <?php
                                        $borrowDate = $book['borrowDate'] ?? '';
                                        $dueDate = $book['dueDate'] ?? $book['returnDate'] ?? '';
                                        $isOverdue = false;
                                        
                                        if (!empty($dueDate) && strtotime($dueDate) < time()) {
                                            $isOverdue = true;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <?php if (!empty($book['bookImage'])): ?>
                                                            <img src="/<?= htmlspecialchars($book['bookImage']) ?>" 
                                                                 class="avatar avatar-sm me-3" 
                                                                 alt="book image">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">
                                                            <?= htmlspecialchars($book['bookName'] ?? $book['title'] ?? 'Unknown') ?>
                                                        </h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            <?= htmlspecialchars($book['isbn'] ?? '') ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    <?= htmlspecialchars($book['authorName'] ?? $book['author'] ?? 'Unknown') ?>
                                                </p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    <?= htmlspecialchars($borrowDate) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    <?= htmlspecialchars($dueDate ?: 'N/A') ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php if ($isOverdue): ?>
                                                    <span class="badge badge-sm bg-gradient-danger">Overdue</span>
                                                <?php else: ?>
                                                    <span class="badge badge-sm bg-gradient-success">On Time</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <form method="POST" action="/faculty/return" style="display: inline;">
                                                    <input type="hidden" name="transaction_id" 
                                                           value="<?= htmlspecialchars($book['tid'] ?? $book['transactionId'] ?? '') ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary"
                                                            onclick="return confirm('Are you sure you want to return this book?')">
                                                        Return Book
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="text-secondary mb-0">No books currently borrowed</p>
                                            <a href="/faculty/books" class="btn btn-sm btn-primary mt-2">Browse Books</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
