<?php
/**
 * Faculty Books View - Browse and search library catalog
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Browse Books';
$currentPage = 'books';

// Include header
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .books-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
    .books-container h1 { margin-bottom: 25px; color: #1f2937; font-size: 32px; }
    
    .search-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .search-form { display: flex; gap: 12px; margin-bottom: 15px; }
    .search-input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 15px;
        transition: border-color 0.2s;
    }
    .search-input:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    .search-btn {
        padding: 12px 24px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    .filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .filter-select {
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 14px;
        background: white;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }
    
    .book-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        overflow: hidden;
    }
    .book-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    
    .book-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .book-badge.special {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }
    
    .book-image {
        width: 100%;
        height: 240px;
        background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
        border-radius: 8px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 56px;
        position: relative;
        overflow: hidden;
    }
    
    .book-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .book-image::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
        transform: rotate(45deg);
        animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    .book-title {
        font-weight: 700;
        margin-bottom: 8px;
        font-size: 18px;
        color: #1f2937;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .book-author {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 12px;
        font-style: italic;
    }
    
    .book-info {
        font-size: 13px;
        color: #9ca3af;
        margin-bottom: 12px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .book-category {
        background: #f3f4f6;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        color: #4b5563;
        font-weight: 500;
    }
    
    .book-publisher {
        background: #ede9fe;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        color: #7c3aed;
        font-weight: 500;
    }
    
    .book-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 2px solid #f3f4f6;
    }
    
    .available {
        color: #10b981;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .unavailable {
        color: #ef4444;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .request-btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .request-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    .request-btn:disabled {
        background: #d1d5db;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .reserve-btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .reserve-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }
    
    .view-btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .view-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }
    
    .no-books {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 20px;
        color: #6b7280;
    }
    .no-books h3 {
        margin-bottom: 12px;
        font-size: 24px;
        color: #4b5563;
    }
    .no-books p {
        font-size: 16px;
    }
    
    .stats-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .stat-card {
        background: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        flex: 1;
        min-width: 200px;
    }
    
    .stat-label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 5px;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }
    
    .success-message {
        background: #d1fae5;
        border-left: 4px solid #10b981;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        color: #065f46;
    }
    
    .error-message {
        background: #fee2e2;
        border-left: 4px solid #ef4444;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        color: #991b1b;
    }
    
    @media (max-width: 768px) {
        .books-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .search-form {
            flex-direction: column;
        }
        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="books-container">
    <h1>📚 Browse Books</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            ✓ <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            ✗ <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-label">Total Books</div>
            <div class="stat-value"><?= isset($totalBooks) ? $totalBooks : count($books ?? []) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Available Now</div>
            <div class="stat-value" style="color: #10b981;">
                <?php 
                $availableCount = 0;
                if (!empty($books) && is_array($books)) {
                    foreach ($books as $book) {
                        if (($book['available'] ?? 0) > 0) $availableCount++;
                    }
                }
                echo $availableCount;
                ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Currently Borrowed</div>
            <div class="stat-value" style="color: #f59e0b;">
                <?php 
                $borrowedCount = 0;
                if (!empty($books) && is_array($books)) {
                    foreach ($books as $book) {
                        $borrowedCount += ($book['borrowed'] ?? 0);
                    }
                }
                echo $borrowedCount;
                ?>
            </div>
        </div>
    </div>
    
    <div class="search-section">
        <form class="search-form" method="GET" action="/faculty/search">
            <input type="text" 
                   name="q" 
                   class="search-input" 
                   placeholder="🔍 Search by title, author, ISBN, or publisher..."
                   value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            <button type="submit" class="search-btn">Search Books</button>
        </form>
        
        <div class="filters">
            <select name="category" class="filter-select" onchange="filterBooks(this.value, 'category')">
                <option value="">All Publishers</option>
                <?php if (!empty($categories) && is_array($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" 
                                <?= (isset($_GET['category']) && $_GET['category'] === $category) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <select name="status" class="filter-select" onchange="filterBooks(this.value, 'status')">
                <option value="">All Availability</option>
                <option value="available" <?= (isset($_GET['status']) && $_GET['status'] === 'available') ? 'selected' : '' ?>>
                    ✓ Available Only
                </option>
                <option value="borrowed" <?= (isset($_GET['status']) && $_GET['status'] === 'borrowed') ? 'selected' : '' ?>>
                    ✗ Currently Borrowed
                </option>
            </select>
            
            <select name="sort" class="filter-select" onchange="filterBooks(this.value, 'sort')">
                <option value="">Sort by: Latest</option>
                <option value="title" <?= (isset($_GET['sort']) && $_GET['sort'] === 'title') ? 'selected' : '' ?>>
                    📖 Title (A-Z)
                </option>
                <option value="author" <?= (isset($_GET['sort']) && $_GET['sort'] === 'author') ? 'selected' : '' ?>>
                    ✍️ Author (A-Z)
                </option>
                <option value="available" <?= (isset($_GET['sort']) && $_GET['sort'] === 'available') ? 'selected' : '' ?>>
                    📊 Most Available
                </option>
            </select>
        </div>
    </div>
    
    <div class="books-grid">
        <?php if (!empty($books) && is_array($books)): ?>
            <?php foreach ($books as $book): ?>
                <div class="book-card">
                    <?php if (!empty($book['isTrending'])): ?>
                        <div class="book-badge">🔥 Trending</div>
                    <?php elseif (!empty($book['isSpecial'])): ?>
                        <div class="book-badge special">⭐ <?= htmlspecialchars($book['specialBadge'] ?? 'Special') ?></div>
                    <?php endif; ?>
                    
                    <div class="book-image">
                        <?php if (!empty($book['bookImage'])): ?>
                            <img src="/<?= htmlspecialchars($book['bookImage']) ?>" alt="<?= htmlspecialchars($book['bookName'] ?? 'Book cover') ?>">
                        <?php else: ?>
                            📚
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-title"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Title') ?></div>
                    <div class="book-author">by <?= htmlspecialchars($book['authorName'] ?? 'Unknown Author') ?></div>
                    
                    <div class="book-info">
                        <?php if (!empty($book['publisherName'])): ?>
                            <span class="book-publisher">📘 <?= htmlspecialchars($book['publisherName']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($book['totalCopies'])): ?>
                            <span class="book-category">📚 <?= htmlspecialchars($book['totalCopies']) ?> copies</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-status">
                        <span class="<?= ($book['available'] ?? 0) > 0 ? 'available' : 'unavailable' ?>">
                            <?php if (($book['available'] ?? 0) > 0): ?>
                                <span>✓</span> <?= $book['available'] ?> available
                            <?php else: ?>
                                <span>✗</span> Not available
                            <?php endif; ?>
                        </span>
                        
                        <div class="action-buttons">
                            <?php if (isset($_SESSION['user_id']) || isset($_SESSION['userId'])): ?>
                                <?php if (($book['available'] ?? 0) > 0): ?>
                                    <a href="/faculty/reserve/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="request-btn">
                                        Borrow
                                    </a>
                                <?php else: ?>
                                    <a href="/faculty/reserve/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="reserve-btn">
                                        Reserve
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/login" class="request-btn">Login to Borrow</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-books">
                <h3>📚 No books found</h3>
                <p>Try adjusting your search criteria or check back later for new additions.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function filterBooks(value, type) {
        const url = new URL(window.location.href);
        
        // Update or remove the parameter
        if (value) {
            url.searchParams.set(type, value);
        } else {
            url.searchParams.delete(type);
        }
        
        // Redirect to the updated URL
        window.location.href = url.toString();
    }
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>