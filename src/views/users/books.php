<?php
$pageTitle = 'Browse Books';
include APP_ROOT . '/views/layouts/header.php';

// Fetch books from the database
global $mysqli;
$search = $_GET['search'] ?? '';
$books = [];

if ($mysqli) {
    $sql = "SELECT isbn, bookName, authorName, publisherName, description, category, publicationYear, totalCopies, bookImage, available, borrowed, isTrending, isSpecial, specialBadge FROM books";
    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE bookName LIKE ? OR authorName LIKE ? OR publisherName LIKE ? OR isbn LIKE ?";
        $like = '%' . $search . '%';
        $params = [$like, $like, $like, $like];
    }
    $sql .= " ORDER BY bookName ASC";
    $stmt = $mysqli->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param('ssss', ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $stmt->close();
}
?>

<style>
  .books-container {
    padding: 2rem 0;
    animation: fadeIn 0.6s ease-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }

  /* Page Header */
  .books-header {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
    animation: slideInDown 0.6s ease-out;
  }

  @keyframes slideInDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .books-header h1 {
    font-size: clamp(1.75rem, 3vw, 2.5rem);
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .books-header p {
    color: #6b7280;
    font-size: 1.05rem;
    margin: 0;
  }

  /* Search Bar */
  .search-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2.5rem;
    animation: slideInUp 0.6s ease-out 0.2s both;
  }

  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .search-form {
    display: flex;
    gap: 1rem;
    align-items: stretch;
  }

  .search-input-wrapper {
    flex: 1;
    position: relative;
  }

  .search-icon {
    position: absolute;
    left: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1.1rem;
  }

  .search-input {
    width: 100%;
    padding: 1rem 1rem 1rem 3.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
  }

  .search-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }

  .search-input:focus~.search-icon {
    color: #667eea;
  }

  .btn-search {
    padding: 1rem 2.5rem;
    border: none;
    border-radius: 14px;
    font-size: 1.05rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
  }

  .btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
  }

  .btn-search:active {
    transform: translateY(0);
  }

  /* Section Header */
  .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 2.5rem 0 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .section-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .section-title i {
    color: #667eea;
  }

  .results-count {
    padding: 0.5rem 1.25rem;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.95rem;
  }

  /* Books Grid */
  .books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    animation: fadeIn 0.8s ease-out 0.4s both;
  }

  /* Book Card */
  .book-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
    border: 2px solid transparent;
  }

  .book-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    border-color: rgba(102, 126, 234, 0.3);
  }

  .book-cover {
    height: 180px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }

  .book-cover::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: rotate(45deg);
    transition: all 0.5s ease;
  }

  .book-card:hover .book-cover::before {
    left: 100%;
  }

  .book-cover-icon {
    font-size: 4rem;
    color: #667eea;
    transition: all 0.3s ease;
  }

  .book-card:hover .book-cover-icon {
    transform: scale(1.1) rotate(5deg);
  }

  .book-availability {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.85rem;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .book-availability.available {
    background: rgba(16, 185, 129, 0.9);
    color: white;
  }

  .book-availability.unavailable {
    background: rgba(239, 68, 68, 0.9);
    color: white;
  }

  .book-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    flex: 1;
  }

  .book-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #1f2937;
    line-height: 1.3;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .book-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .book-meta-item {
    display: flex;
    align-items: start;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #6b7280;
  }

  .book-meta-item i {
    color: #667eea;
    margin-top: 2px;
    flex-shrink: 0;
  }

  .book-meta-label {
    font-weight: 600;
    color: #374151;
    min-width: 70px;
  }

  .book-isbn {
    padding: 0.5rem 0.75rem;
    background: rgba(102, 126, 234, 0.05);
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    color: #667eea;
    font-weight: 600;
    margin-top: 0.5rem;
  }

  .book-actions {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 2px solid #f3f4f6;
  }

  .btn-borrow {
    width: 100%;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    font-size: 1rem;
  }

  .btn-borrow:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
    color: white;
  }

  .btn-borrow:active {
    transform: translateY(0);
  }

  .btn-borrow:disabled,
  .btn-unavailable {
    background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
    cursor: not-allowed;
    opacity: 0.6;
    box-shadow: none;
  }

  .btn-unavailable:hover {
    transform: none;
    box-shadow: none;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    animation: slideInUp 0.6s ease-out 0.4s both;
  }

  .empty-state-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 50%;
    font-size: 3.5rem;
    color: #667eea;
  }

  .empty-state h3 {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.75rem;
  }

  .empty-state p {
    color: #6b7280;
    font-size: 1.1rem;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
  }

  .empty-state .search-term {
    color: #667eea;
    font-weight: 700;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .books-header {
      padding: 1.5rem;
    }

    .search-card {
      padding: 1.5rem;
    }

    .search-form {
      flex-direction: column;
    }

    .btn-search {
      width: 100%;
      justify-content: center;
    }

    .books-grid {
      grid-template-columns: 1fr;
    }

    .section-header {
      flex-direction: column;
      align-items: start;
    }
  }

  @media (max-width: 576px) {
    .book-cover {
      height: 150px;
    }

    .book-cover-icon {
      font-size: 3rem;
    }

    .book-body {
      padding: 1.25rem;
    }
  }
</style>

<div class="books-container">
  <div class="container">
    <!-- Page Header -->
    <div class="books-header">
      <h1>📚 Browse Our Collection</h1>
      <p>Discover thousands of books across various categories</p>
    </div>

    <!-- Search Bar -->
    <div class="search-card">
      <form method="GET" action="<?= BASE_URL ?>user/books" class="search-form">
        <div class="search-input-wrapper">
          <input type="text"
            class="search-input"
            name="search"
            placeholder="Search by book name, author, or publisher..."
            value="<?= htmlspecialchars($search ?? '') ?>">
          <i class="fas fa-search search-icon"></i>
        </div>
        <button type="submit" class="btn-search">
          <i class="fas fa-search"></i>
          <span>Search Books</span>
        </button>
      </form>
    </div>

    <!-- Books Section -->
    <?php if (!empty($books)): ?>
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-book-open"></i>
          <?= !empty($search) ? 'Search Results' : 'Available Books' ?>
        </h2>
        <span class="results-count">
          <?= count($books) ?> <?= count($books) === 1 ? 'Book' : 'Books' ?> Found
        </span>
      </div>

      <div class="books-grid">
        <?php foreach ($books as $book): ?>
          <div class="book-card">
            <div class="book-cover">
              <i class="fas fa-book book-cover-icon"></i>
              <span class="book-availability <?= $book['available'] > 0 ? 'available' : 'unavailable' ?>">
                <i class="fas fa-<?= $book['available'] > 0 ? 'check-circle' : 'times-circle' ?>"></i>
                <?= $book['available'] > 0 ? $book['available'] . ' Available' : 'Not Available' ?>
              </span>
            </div>

            <div class="book-body">
              <h3 class="book-title"><?= htmlspecialchars($book['bookName']) ?></h3>

              <div class="book-meta">
                <div class="book-meta-item">
                  <i class="fas fa-user"></i>
                  <div>
                    <span class="book-meta-label">Author:</span>
                    <?= htmlspecialchars($book['authorName']) ?>
                  </div>
                </div>

                <div class="book-meta-item">
                  <i class="fas fa-building"></i>
                  <div>
                    <span class="book-meta-label">Publisher:</span>
                    <?= htmlspecialchars($book['publisherName']) ?>
                  </div>
                </div>
              </div>

              <div class="book-isbn">
                ISBN: <?= htmlspecialchars($book['isbn']) ?>
              </div>

              <div class="book-actions">
                <a href="<?= BASE_URL ?>user/book?isbn=<?= urlencode($book['isbn']) ?>"
                  class="btn-borrow" style="background: #6c757d; margin-bottom: 10px; box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);">
                  <i class="fas fa-info-circle"></i>
                  <span>View Details</span>
                </a>
                <?php if ($book['available'] > 0): ?>
                  <a href="<?= BASE_URL ?>user/reserve?isbn=<?= urlencode($book['isbn']) ?>"
                    class="btn-borrow">
                    <i class="fas fa-bookmark"></i>
                    <span>Reserve Book</span>
                  </a>
                <?php else: ?>
                  <button class="btn-borrow btn-unavailable" disabled>
                    <i class="fas fa-times-circle"></i>
                    <span>Currently Unavailable</span>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif (!empty($search)): ?>
      <!-- No Results Found -->
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-search"></i>
        </div>
        <h3>No Books Found</h3>
        <p>
          We couldn't find any books matching
          <span class="search-term">"<?= htmlspecialchars($search) ?>"</span>.
          Try searching with different keywords.
        </p>
      </div>

    <?php else: ?>
      <!-- Featured Books -->
      <div class="section-header">
        <h2 class="section-title">
          <i class="fas fa-star"></i>
          Featured Books
        </h2>
        <span class="results-count">4 Books</span>
      </div>

      <div class="books-grid">
        <?php
        $featured = [
          ['isbn' => '9780134685991', 'bookName' => 'Effective Java', 'authorName' => 'Joshua Bloch', 'publisherName' => 'Addison-Wesley', 'available' => 3],
          ['isbn' => '9781492056355', 'bookName' => 'Designing Data-Intensive Applications', 'authorName' => 'Martin Kleppmann', 'publisherName' => "O'Reilly", 'available' => 2],
          ['isbn' => '9780135957059', 'bookName' => 'Clean Code', 'authorName' => 'Robert C. Martin', 'publisherName' => 'Prentice Hall', 'available' => 5],
          ['isbn' => '9781098132410', 'bookName' => 'Learning PHP, MySQL & JavaScript', 'authorName' => 'Robin Nixon', 'publisherName' => "O'Reilly", 'available' => 4],
        ];
        foreach ($featured as $book): ?>
          <div class="book-card">
            <div class="book-cover">
              <i class="fas fa-book book-cover-icon"></i>
              <span class="book-availability available">
                <i class="fas fa-check-circle"></i>
                <?= $book['available'] ?> Available
              </span>
            </div>

            <div class="book-body">
              <h3 class="book-title"><?= htmlspecialchars($book['bookName']) ?></h3>

              <div class="book-meta">
                <div class="book-meta-item">
                  <i class="fas fa-user"></i>
                  <div>
                    <span class="book-meta-label">Author:</span>
                    <?= htmlspecialchars($book['authorName']) ?>
                  </div>
                </div>

                <div class="book-meta-item">
                  <i class="fas fa-building"></i>
                  <div>
                    <span class="book-meta-label">Publisher:</span>
                    <?= htmlspecialchars($book['publisherName']) ?>
                  </div>
                </div>
              </div>

              <div class="book-isbn">
                ISBN: <?= htmlspecialchars($book['isbn']) ?>
              </div>

              <div class="book-actions">
                <a href="<?= BASE_URL ?>user/reserve?isbn=<?= urlencode($book['isbn']) ?>" class="btn-borrow">
                  <i class="fas fa-bookmark"></i>
                  <span>Reserve Book</span>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
