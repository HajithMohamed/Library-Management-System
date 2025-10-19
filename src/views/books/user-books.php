<?php
$pageTitle = 'Browse Books';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .books-container { padding: 24px 0; }
    .search-bar { background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,.08); padding: 16px; margin-bottom: 16px; display:flex; gap:10px; align-items:center; }
    .search-input { flex:1; padding: 12px 14px; border:1px solid #e5e7eb; border-radius: 10px; background:#f9fafb; }
    .btn-search { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; border:0; padding: 10px 14px; border-radius: 10px; font-weight:700; cursor:pointer; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:16px; }
    .card { background:#fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,.08); overflow:hidden; display:flex; flex-direction:column; }
    .card-cover { height: 140px; background: linear-gradient(135deg, rgba(102,126,234,.15), rgba(118,75,162,.15)); display:flex; align-items:center; justify-content:center; font-size:42px; color:#5b6ef5; }
    .card-body { padding: 14px; display:flex; flex-direction:column; gap:6px; }
    .title { font-weight:800; color:#111827; }
    .meta { color:#6b7280; font-size:.9rem; }
    .actions { margin-top:auto; display:flex; gap:8px; }
    .btn { background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; border:0; padding:8px 10px; border-radius: 8px; cursor:pointer; font-weight:700; width:100%; }
    .section-title { margin: 16px 0 8px; font-weight:800; color:#1f2937; }
</style>

<div class="container books-container">
    <form method="GET" action="<?= BASE_URL ?>user/books" class="search-bar">
        <input class="search-input" type="text" name="search" placeholder="Search by title, author, or publisher" value="<?= htmlspecialchars($search ?? '') ?>">
        <button class="btn-search" type="submit"><i class="fas fa-search"></i> Search</button>
    </form>

    <?php if (!empty($books)) { ?>
        <h4 class="section-title">Search Results</h4>
        <div class="grid">
            <?php foreach ($books as $book) { ?>
                <div class="card">
                    <div class="card-cover"><i class="fas fa-book"></i></div>
                    <div class="card-body">
                        <div class="title"><?= htmlspecialchars($book['bookName']) ?></div>
                        <div class="meta">Author: <?= htmlspecialchars($book['authorName']) ?></div>
                        <div class="meta">Publisher: <?= htmlspecialchars($book['publisherName']) ?></div>
                        <div class="meta">ISBN: <?= htmlspecialchars($book['isbn']) ?></div>
                        <div class="meta">Available: <?= (int)$book['available'] ?></div>
                        <div class="actions">
                            <a class="btn" href="<?= BASE_URL ?>user/borrow?isbn=<?= urlencode($book['isbn']) ?>"><i class="fas fa-hand-holding"></i> Borrow</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if (empty($books)) { ?>
        <h4 class="section-title">Featured Books</h4>
        <div class="grid">
            <?php
            $featured = [
                ['isbn' => '9780134685991', 'bookName' => 'Effective Java', 'authorName' => 'Joshua Bloch', 'publisherName' => 'Addison-Wesley', 'available' => 3],
                ['isbn' => '9781492056355', 'bookName' => 'Designing Data-Intensive Applications', 'authorName' => 'Martin Kleppmann', 'publisherName' => "O'Reilly", 'available' => 2],
                ['isbn' => '9780135957059', 'bookName' => 'Clean Code', 'authorName' => 'Robert C. Martin', 'publisherName' => 'Prentice Hall', 'available' => 5],
                ['isbn' => '9781098132410', 'bookName' => 'Learning PHP, MySQL & JavaScript', 'authorName' => 'Robin Nixon', 'publisherName' => "O'Reilly", 'available' => 4],
            ];
            foreach ($featured as $book) { ?>
                <div class="card">
                    <div class="card-cover"><i class="fas fa-book"></i></div>
                    <div class="card-body">
                        <div class="title"><?= htmlspecialchars($book['bookName']) ?></div>
                        <div class="meta">Author: <?= htmlspecialchars($book['authorName']) ?></div>
                        <div class="meta">Publisher: <?= htmlspecialchars($book['publisherName']) ?></div>
                        <div class="meta">ISBN: <?= htmlspecialchars($book['isbn']) ?></div>
                        <div class="meta">Available: <?= (int)$book['available'] ?></div>
                        <div class="actions">
                            <a class="btn" href="#" onclick="return false;"><i class="fas fa-hand-holding"></i> Borrow</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

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
