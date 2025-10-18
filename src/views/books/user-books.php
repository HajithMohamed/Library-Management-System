<?php
$pageTitle = 'Browse Books';
include APP_ROOT . '/views/layouts/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Browse Books</h1>
            
            <!-- Search Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>user/books" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search by book name, author, or publisher..." 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Books Grid -->
            <?php if (!empty($books)): ?>
                <div class="row">
                    <?php foreach ($books as $book): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($book['bookName']) ?></h5>
                                    <p class="card-text">
                                        <strong>Author:</strong> <?= htmlspecialchars($book['authorName']) ?><br>
                                        <strong>Publisher:</strong> <?= htmlspecialchars($book['publisherName']) ?><br>
                                        <strong>ISBN:</strong> <?= htmlspecialchars($book['isbn']) ?>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?= $book['available'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $book['available'] > 0 ? 'Available (' . $book['available'] . ')' : 'Not Available' ?>
                                        </span>
                                        <?php if ($book['available'] > 0): ?>
                                            <a href="<?= BASE_URL ?>user/borrow?isbn=<?= $book['isbn'] ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-book"></i> Borrow
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-times"></i> Unavailable
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php if (!empty($search)): ?>
                        No books found matching "<?= htmlspecialchars($search) ?>".
                    <?php else: ?>
                        No books available at the moment.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
