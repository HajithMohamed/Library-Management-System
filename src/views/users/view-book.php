<?php
$pageTitle = 'Book Details';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
  .book-details-container {
    padding: 2rem 0;
    animation: fadeIn 0.6s ease-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
    transition: all 0.3s ease;
  }

  .back-link:hover {
    color: #764ba2;
    transform: translateX(-5px);
  }

  .book-details-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }

  .book-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 3rem;
    margin-bottom: 2rem;
  }

  .book-image-wrapper {
    width: 100%;
    height: 400px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }

  .book-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .book-image-wrapper i {
    font-size: 100px;
    color: #667eea;
  }

  .book-info h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .book-author {
    font-size: 1.2rem;
    color: #6b7280;
    margin-bottom: 2rem;
  }

  .book-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .meta-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 10px;
  }

  .meta-label {
    color: #9ca3af;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
  }

  .meta-value {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
  }

  .availability {
    color: #10b981;
  }

  .availability.unavailable {
    color: #ef4444;
  }

  .description-section {
    margin-bottom: 2rem;
  }

  .description-section h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #1f2937;
  }

  .description-text {
    color: #6b7280;
    line-height: 1.6;
  }

  .action-buttons {
    display: flex;
    gap: 1rem;
  }

  .btn-action {
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .btn-reserve {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  }

  .btn-reserve:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
    color: white;
  }

  .btn-reserved {
    background: #6c757d;
    color: white;
    cursor: not-allowed;
  }

  @media (max-width: 768px) {
    .book-grid {
      grid-template-columns: 1fr;
      gap: 2rem;
    }

    .book-image-wrapper {
      height: 300px;
    }

    .book-info h1 {
      font-size: 2rem;
    }

    .book-meta-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="book-details-container">
  <div class="container">
    <a href="<?= BASE_URL ?>user/books" class="back-link">
      <i class="fas fa-arrow-left"></i>
      Back to Books
    </a>

    <div class="book-details-card">
      <div class="book-grid">
        <div class="book-image-wrapper">
          <?php if (!empty($book['bookImage'])): ?>
            <img src="<?= BASE_URL . $book['bookImage'] ?>" alt="<?= htmlspecialchars($book['bookName']) ?>">
          <?php else: ?>
            <i class="fas fa-book"></i>
          <?php endif; ?>
        </div>

        <div class="book-info">
          <h1><?= htmlspecialchars($book['bookName']) ?></h1>
          <p class="book-author">by <?= htmlspecialchars($book['authorName']) ?></p>

          <div class="book-meta-grid">
            <div class="meta-item">
              <div class="meta-label">Publisher</div>
              <div class="meta-value"><?= htmlspecialchars($book['publisherName']) ?></div>
            </div>

            <div class="meta-item">
              <div class="meta-label">ISBN</div>
              <div class="meta-value" style="font-family: monospace;"><?= htmlspecialchars($book['isbn']) ?></div>
            </div>

            <div class="meta-item">
              <div class="meta-label">Category</div>
              <div class="meta-value"><?= htmlspecialchars($book['category'] ?? 'N/A') ?></div>
            </div>

            <div class="meta-item">
              <div class="meta-label">Publication Year</div>
              <div class="meta-value"><?= htmlspecialchars($book['publicationYear'] ?? 'N/A') ?></div>
            </div>

            <div class="meta-item">
              <div class="meta-label">Availability</div>
              <div class="meta-value availability <?= $book['available'] > 0 ? '' : 'unavailable' ?>">
                <?= $book['available'] ?> / <?= $book['totalCopies'] ?> Available
              </div>
            </div>
          </div>

          <?php if (!empty($book['description'])): ?>
            <div class="description-section">
              <h3>Description</h3>
              <p class="description-text"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
            </div>
          <?php endif; ?>

          <div class="action-buttons">
            <?php if ($book['available'] > 0): ?>
              <?php if ($hasReservation): ?>
                <button class="btn-action btn-reserved" disabled>
                  <i class="fas fa-check-circle"></i>
                  Already Reserved
                </button>
              <?php else: ?>
                <a href="<?= BASE_URL ?>user/reserve?isbn=<?= urlencode($book['isbn']) ?>" class="btn-action btn-reserve">
                  <i class="fas fa-bookmark"></i>
                  Reserve Book
                </a>
              <?php endif; ?>
            <?php else: ?>
              <button class="btn-action btn-reserved" disabled>
                <i class="fas fa-times-circle"></i>
                Currently Unavailable
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
