<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}
$pageTitle = 'Book Details';
include APP_ROOT . '/views/layouts/header.php';

// Simple authentication check - just verify user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: ' . BASE_URL . 'login');
    exit();
}
?>

<style>
.book-details-user-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 60px 20px;
    position: relative;
    overflow: hidden;
}
.book-details-user-container {
    max-width: 900px;
    margin: 0 auto;
    background: rgba(255,255,255,0.97);
    border-radius: 30px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
}
.book-image-user-section {
    flex: 0 0 320px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}
.book-image-user-wrapper {
    width: 220px;
    aspect-ratio: 2/3;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}
.book-image-user-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.book-placeholder-user {
    font-size: 5rem;
    color: #667eea;
}
.book-info-user-section {
    flex: 1 1 400px;
    padding: 40px 40px 40px 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.book-title-user {
    font-size: 2.2rem;
    font-weight: 900;
    color: #1f2937;
    margin-bottom: 10px;
}
.book-author-user {
    font-size: 1.2rem;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 25px;
}
.book-meta-user {
    display: flex;
    flex-wrap: wrap;
    gap: 25px 40px;
    margin-bottom: 25px;
}
.book-meta-user-item {
    font-size: 1rem;
    color: #374151;
}
.book-meta-user-label {
    font-weight: 700;
    color: #667eea;
    margin-right: 6px;
}
.book-description-user-section {
    margin-bottom: 25px;
}
.book-description-user-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}
.book-description-user-text {
    font-size: 1rem;
    color: #4b5563;
    background: #f9fafb;
    border-radius: 10px;
    padding: 18px;
    border-left: 4px solid #667eea;
}
.book-availability-user {
    margin-bottom: 25px;
    font-size: 1.05rem;
    font-weight: 700;
    color: <?= ($book['available'] ?? 0) > 0 ? '#059669' : '#dc2626' ?>;
}
.book-actions-user {
    display: flex;
    gap: 18px;
    margin-top: 10px;
}
.btn-user-action {
    padding: 14px 32px;
    border-radius: 14px;
    border: none;
    font-size: 1rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 6px 18px rgba(102, 126, 234, 0.18);
}
.btn-user-reserve {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-user-reserve:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}
.btn-user-back {
    background: #6c757d;
    color: white;
}
.btn-user-back:hover {
    background: #495057;
}
.btn-user-unavailable {
    background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
    color: white;
    cursor: not-allowed;
    opacity: 0.7;
}
@media (max-width: 900px) {
    .book-details-user-container {
        flex-direction: column;
    }
    .book-image-user-section {
        justify-content: flex-start;
        padding: 30px 10px;
    }
    .book-info-user-section {
        padding: 30px 20px;
    }
}
</style>

<div class="book-details-user-page">
    <div class="book-details-user-container">
        <!-- Book Image -->
        <div class="book-image-user-section">
            <div class="book-image-user-wrapper">
                <?php if (!empty($book['bookImage'])): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($book['bookImage']) ?>" alt="<?= htmlspecialchars($book['bookName'] ?? 'Book cover') ?>">
                <?php else: ?>
                    <div class="book-placeholder-user">📚</div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Book Info -->
        <div class="book-info-user-section">
            <h1 class="book-title-user"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Title') ?></h1>
            <div class="book-author-user">by <?= htmlspecialchars($book['authorName'] ?? 'Unknown Author') ?></div>
            <div class="book-meta-user">
                <div class="book-meta-user-item">
                    <span class="book-meta-user-label">Publisher:</span>
                    <?= htmlspecialchars($book['publisherName'] ?? 'N/A') ?>
                </div>
                <div class="book-meta-user-item">
                    <span class="book-meta-user-label">ISBN:</span>
                    <?= htmlspecialchars($book['isbn'] ?? 'N/A') ?>
                </div>
                <div class="book-meta-user-item">
                    <span class="book-meta-user-label">Category:</span>
                    <?= htmlspecialchars($book['category'] ?? 'N/A') ?>
                </div>
                <div class="book-meta-user-item">
                    <span class="book-meta-user-label">Year:</span>
                    <?= htmlspecialchars($book['publicationYear'] ?? 'N/A') ?>
                </div>
            </div>
            <?php if (!empty($book['description'])): ?>
                <div class="book-description-user-section">
                    <div class="book-description-user-title">Description</div>
                    <div class="book-description-user-text"><?= htmlspecialchars($book['description']) ?></div>
                </div>
            <?php endif; ?>
            <div class="book-availability-user">
                <?= ($book['available'] ?? 0) > 0 ? '✓ Available: ' . htmlspecialchars($book['available']) . ' of ' . htmlspecialchars($book['totalCopies']) : '✗ Not Available' ?>
            </div>
            <div class="book-actions-user">
                <a href="<?= BASE_URL ?>user/books" class="btn-user-action btn-user-back">
                    ← Back to Books
                </a>
                <?php if (($book['available'] ?? 0) > 0): ?>
                    <a href="<?= BASE_URL ?>user/reserve?isbn=<?= urlencode($book['isbn']) ?>" class="btn-user-action btn-user-reserve">
                        <i class="fas fa-bookmark"></i>
                        Reserve Book
                    </a>
                <?php else: ?>
                    <button class="btn-user-action btn-user-unavailable" disabled>
                        <i class="fas fa-times-circle"></i>
                        Currently Unavailable
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>