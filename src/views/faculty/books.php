<?php
/**
 * Faculty Books View - Browse and search library catalog
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
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        
        .books-container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .books-container h1 { margin-bottom: 20px; color: #333; }
        
        .search-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-form { display: flex; gap: 10px; }
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .search-btn {
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .search-btn:hover { background: #2563eb; }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .book-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .book-image {
            width: 100%;
            height: 200px;
            background: #e5e7eb;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 48px;
        }
        
        .book-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 16px;
            color: #1f2937;
        }
        
        .book-author {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .book-info {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 10px;
        }
        
        .book-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        
        .available { color: #10b981; font-weight: bold; font-size: 14px; }
        .unavailable { color: #ef4444; font-weight: bold; font-size: 14px; }
        
        .borrow-btn {
            padding: 8px 15px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .borrow-btn:hover { background: #059669; }
        .borrow-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        .no-books {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        .no-books h3 { margin-bottom: 10px; font-size: 20px; }
    </style>
</head>
<body>
    <div class="books-container">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        
        <div class="search-section">
            <form class="search-form" method="GET" action="/faculty/search">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Search by title, author, or ISBN..."
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="search-btn">🔍 Search</button>
            </form>
        </div>
        
        <div class="books-grid">
            <?php if (!empty($books) && is_array($books)): ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-image">📚</div>
                        
                        <div class="book-title"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Title') ?></div>
                        <div class="book-author">by <?= htmlspecialchars($book['authorName'] ?? 'Unknown') ?></div>
                        
                        <?php if (!empty($book['category'])): ?>
                            <div class="book-info">
                                Category: <?= htmlspecialchars($book['category']) ?>
                                <?php if (!empty($book['publicationYear'])): ?>
                                    | Year: <?= htmlspecialchars($book['publicationYear']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="book-status">
                            <span class="<?= ($book['available'] ?? 0) > 0 ? 'available' : 'unavailable' ?>">
                                <?php if (($book['available'] ?? 0) > 0): ?>
                                    ✓ <?= $book['available'] ?> available
                                <?php else: ?>
                                    ✗ Not available
                                <?php endif; ?>
                            </span>
                            
                            <?php if (isset($_SESSION['userId'])): ?>
                                <form method="POST" action="/faculty/borrow-request" style="display: inline; margin: 0;">
                                    <input type="hidden" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">
                                    <button class="borrow-btn" 
                                            type="submit"
                                            <?= ($book['available'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                                        Request
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="/login" class="borrow-btn">Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-books">
                    <h3>📚 No books found</h3>
                    <p>Try adjusting your search or check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
