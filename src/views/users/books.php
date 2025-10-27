<?php
/**
 * User Books View - Browse and search library catalog
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Browse Books';
$currentPage = 'books';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Library System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        
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
        
        .borrow-btn {
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
        .borrow-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        .borrow-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        
        @media (max-width: 768px) {
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 16px;
            }
            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="books-container">
        <h1>📚 <?= htmlspecialchars($pageTitle) ?></h1>
        
        <div class="search-section">
            <form class="search-form" method="GET" action="/user/books">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="🔍 Search by title, author, ISBN, or category..."
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="search-btn">Search Books</button>
            </form>
            
            <div class="filters">
                <select name="category" class="filter-select" onchange="filterBooks(this.value, 'category')">
                    <option value="">All Categories</option>
                    <option value="Computer Science">💻 Computer Science</option>
                    <option value="Literature">📖 Literature</option>
                    <option value="Science">🔬 Science</option>
                    <option value="History">📜 History</option>
                    <option value="Business">💼 Business</option>
                    <option value="Psychology">🧠 Psychology</option>
                </select>
                
                <select name="availability" class="filter-select" onchange="filterBooks(this.value, 'availability')">
                    <option value="">All Availability</option>
                    <option value="available">✓ Available Only</option>
                    <option value="borrowed">✗ Borrowed</option>
                </select>
            </div>
        </div>
        
        <div class="books-grid">
            <?php if (!empty($books) && is_array($books)): ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <?php if (!empty($book['isTrending'])): ?>
                            <div class="book-badge">🔥 Trending</div>
                        <?php endif; ?>
                        
                        <div class="book-image">📚</div>
                        
                        <div class="book-title"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Title') ?></div>
                        <div class="book-author">by <?= htmlspecialchars($book['authorName'] ?? 'Unknown Author') ?></div>
                        
                        <div class="book-info">
                            <?php if (!empty($book['category'])): ?>
                                <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($book['publicationYear'])): ?>
                                <span class="book-category">📅 <?= htmlspecialchars($book['publicationYear']) ?></span>
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
                            
                            <?php if (isset($_SESSION['userId'])): ?>
                                <form method="POST" action="/user/borrow" style="display: inline; margin: 0;">
                                    <input type="hidden" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">
                                    <button class="borrow-btn" 
                                            type="submit"
                                            <?= ($book['available'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                                        Borrow Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="/login" class="borrow-btn">Login to Borrow</a>
                            <?php endif; ?>
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
            if (value) {
                url.searchParams.set(type, value);
            } else {
                url.searchParams.delete(type);
            }
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
