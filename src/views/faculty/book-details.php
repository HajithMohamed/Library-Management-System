<?php
$pageTitle = 'Book Details';
include APP_ROOT . '/views/layouts/header.php';

// Get book data (passed from controller)
$book = $book ?? null;

if (!$book) {
    echo '<div class="container"><p class="error">Book not found.</p></div>';
    include APP_ROOT . '/views/layouts/footer.php';
    exit;
}
?>

<style>
    .book-details-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
    }
    
    .book-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
        padding: 2rem;
    }
    
    .book-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 12px;
    }
    
    .book-info h1 {
        font-size: 2rem;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .book-author {
        color: #6b7280;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    
    .book-meta {
        display: flex;
        gap: 1rem;
        margin: 1.5rem 0;
        flex-wrap: wrap;
    }
    
    .meta-badge {
        padding: 0.5rem 1rem;
        background: #f3f4f6;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    .availability {
        padding: 1rem;
        border-radius: 12px;
        margin: 1.5rem 0;
    }
    
    .availability.available {
        background: #d1fae5;
        color: #065f46;
    }
    
    .availability.unavailable {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    
    .description {
        margin: 2rem 0;
        line-height: 1.6;
        color: #374151;
    }
    
    .reservation-info {
        background: #dbeafe;
        border-left: 4px solid #3b82f6;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
    }
</style>

<div class="book-details-container">
    <div class="book-card">
        <div class="book-image-section">
            <?php if (!empty($book['bookImage'])): ?>
                <img src="<?= BASE_URL ?>assets/images/books/<?= htmlspecialchars($book['bookImage']) ?>" 
                     alt="<?= htmlspecialchars($book['bookName']) ?>" 
                     class="book-image">
            <?php else: ?>
                <img src="<?= BASE_URL ?>assets/images/no-book-cover.jpg" 
                     alt="No cover" 
                     class="book-image">
            <?php endif; ?>
        </div>
        
        <div class="book-info">
            <h1><?= htmlspecialchars($book['bookName']) ?></h1>
            <p class="book-author">by <?= htmlspecialchars($book['authorName']) ?></p>
            
            <div class="book-meta">
                <span class="meta-badge">📚 ISBN: <?= htmlspecialchars($book['isbn']) ?></span>
                <span class="meta-badge">🏢 Publisher: <?= htmlspecialchars($book['publisherName']) ?></span>
                <?php if (!empty($book['category'])): ?>
                    <span class="meta-badge">📂 <?= htmlspecialchars($book['category']) ?></span>
                <?php endif; ?>
                <?php if (!empty($book['publicationYear'])): ?>
                    <span class="meta-badge">📅 <?= htmlspecialchars($book['publicationYear']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="availability <?= $book['available'] > 0 ? 'available' : 'unavailable' ?>">
                <?php if ($book['available'] > 0): ?>
                    <strong>✅ Available</strong> - <?= $book['available'] ?> copies available out of <?= $book['totalCopies'] ?>
                <?php else: ?>
                    <strong>❌ Currently Unavailable</strong> - All <?= $book['totalCopies'] ?> copies are borrowed
                <?php endif; ?>
            </div>
            
            <?php if (!empty($book['description'])): ?>
                <div class="description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <?php if ($book['available'] > 0): ?>
                    <form method="POST" action="<?= BASE_URL ?>faculty/reserve/<?= urlencode($book['isbn']) ?>">
                        <button type="submit" class="btn btn-primary">📖 Reserve This Book</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= BASE_URL ?>faculty/reserve/<?= urlencode($book['isbn']) ?>">
                        <button type="submit" class="btn btn-primary">🔔 Join Waiting List</button>
                    </form>
                    <div class="reservation-info">
                        <p>This book is currently unavailable. Reserve it to be notified when it becomes available.</p>
                    </div>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>faculty/books" class="btn btn-secondary">← Back to Library</a>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>