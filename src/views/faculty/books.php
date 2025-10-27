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
    .books-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 40px 20px;
        position: relative;
        overflow: hidden;
    }
    
    .books-page::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        animation: drift 60s linear infinite;
    }
    
    @keyframes drift {
        0% { transform: translate(0, 0); }
        100% { transform: translate(50px, 50px); }
    }
    
    .books-header {
        text-align: center;
        color: white;
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }
    
    .books-header h1 {
        font-size: 3.5rem;
        font-weight: 900;
        margin-bottom: 15px;
        text-shadow: 3px 3px 12px rgba(0,0,0,0.3);
        background: linear-gradient(45deg, #fff, #ffd700);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: textGlow 3s ease-in-out infinite;
    }
    
    @keyframes textGlow {
        0%, 100% { filter: drop-shadow(0 0 10px rgba(255,215,0,0.5)); }
        50% { filter: drop-shadow(0 0 20px rgba(255,215,0,0.8)); }
    }
    
    .books-header p {
        font-size: 1.3rem;
        opacity: 0.95;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .stats-banner {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        max-width: 1400px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        z-index: 1;
    }
    
    .stat-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .stat-badge:hover {
        transform: translateY(-10px) scale(1.05);
        background: rgba(255, 255, 255, 0.25);
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }
    
    .stat-badge .stat-number {
        font-size: 3rem;
        font-weight: 900;
        color: white;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        margin-bottom: 8px;
    }
    
    .stat-badge .stat-label {
        font-size: 0.95rem;
        color: rgba(255,255,255,0.9);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .search-container {
        max-width: 1400px;
        margin: 0 auto 30px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        padding: 35px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
        position: relative;
        z-index: 1;
    }
    
    .search-box-wrapper {
        position: relative;
        margin-bottom: 25px;
    }
    
    .search-input-main {
        width: 100%;
        padding: 20px 60px 20px 25px;
        border: 3px solid transparent;
        border-radius: 50px;
        font-size: 1.15rem;
        transition: all 0.3s ease;
        background: linear-gradient(white, white), linear-gradient(135deg, #667eea, #764ba2);
        background-origin: padding-box, border-box;
        background-clip: padding-box, border-box;
    }
    
    .search-input-main:focus {
        outline: none;
        transform: scale(1.02);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .search-icon {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.6rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        pointer-events: none;
    }
    
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 20px;
        margin-top: 10px;
        max-height: 400px;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 1000;
        display: none;
    }
    
    .autocomplete-dropdown.active {
        display: block;
    }
    
    .autocomplete-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .autocomplete-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    }
    
    .autocomplete-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .autocomplete-item-text {
        flex: 1;
    }
    
    .autocomplete-item-title {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 3px;
    }
    
    .autocomplete-item-author {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .filters-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-select-modern {
        width: 100%;
        padding: 14px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 15px;
        font-size: 1rem;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .filter-select-modern:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .search-btn-modern {
        padding: 14px 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 15px;
        font-size: 1.1rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .search-btn-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
    }
    
    .books-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 35px;
        max-width: 1400px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    .book-card-modern {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(0,0,0,0.15);
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .book-card-modern:hover {
        transform: translateY(-20px) scale(1.03);
        box-shadow: 0 25px 60px rgba(0,0,0,0.25);
    }
    
    .book-image-wrapper {
        position: relative;
        height: 380px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        overflow: hidden;
    }
    
    .book-image-wrapper::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 60%);
    }
    
    .book-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    
    .book-card-modern:hover .book-image-wrapper img {
        transform: scale(1.15) rotate(2deg);
    }
    
    .trending-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
        color: white;
        padding: 10px 18px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        box-shadow: 0 5px 20px rgba(255, 107, 107, 0.5);
        animation: bounce 2s infinite;
        z-index: 10;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    .special-badge {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #1f2937;
    }
    
    .book-content {
        padding: 28px;
        position: relative;
    }
    
    .book-title-modern {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 12px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    .book-author-modern {
        color: #6b7280;
        font-size: 1.05rem;
        margin-bottom: 18px;
        font-weight: 600;
        font-style: italic;
    }
    
    .book-meta-tags {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }
    
    .meta-tag {
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 700;
        border: 2px solid transparent;
    }
    
    .meta-tag.publisher {
        background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }
    
    .meta-tag.copies {
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .book-availability {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0;
        border-top: 3px solid #f3f4f6;
        margin-top: 18px;
    }
    
    .availability-status {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        font-size: 1.1rem;
    }
    
    .availability-status.available {
        color: #10b981;
    }
    
    .availability-status.unavailable {
        color: #ef4444;
    }
    
    .status-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        animation: pulse-dot 2s infinite;
    }
    
    .available .status-dot {
        background: #10b981;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.6);
    }
    
    .unavailable .status-dot {
        background: #ef4444;
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
    }
    
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.2); }
    }
    
    .action-buttons-group {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .action-button-modern {
        flex: 1;
        padding: 12px 20px;
        border-radius: 15px;
        border: none;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-borrow {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4);
    }
    
    .btn-borrow:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6);
    }
    
    .btn-reserve {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 5px 20px rgba(245, 158, 11, 0.4);
    }
    
    .btn-reserve:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.6);
    }
    
    .btn-view-details {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        box-shadow: 0 5px 20px rgba(99, 102, 241, 0.4);
    }
    
    .btn-view-details:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.6);
    }
    
    .empty-state-modern {
        grid-column: 1 / -1;
        text-align: center;
        padding: 120px 40px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        box-shadow: 0 15px 45px rgba(0,0,0,0.15);
    }
    
    .empty-state-modern .icon {
        font-size: 6rem;
        margin-bottom: 25px;
        opacity: 0.5;
        animation: float 4s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-30px); }
    }
    
    .empty-state-modern h3 {
        font-size: 2.3rem;
        color: #1f2937;
        margin-bottom: 15px;
        font-weight: 800;
    }
    
    .empty-state-modern p {
        font-size: 1.2rem;
        color: #6b7280;
    }
    
    .alert-modern {
        max-width: 1400px;
        margin: 0 auto 30px;
        padding: 22px 28px;
        border-radius: 20px;
        font-weight: 700;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        position: relative;
        z-index: 1;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 6px solid #10b981;
    }
    
    .alert-error {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left: 6px solid #ef4444;
    }
    
    @media (max-width: 768px) {
        .books-header h1 {
            font-size: 2.2rem;
        }
        
        .books-grid-modern {
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .filters-row {
            flex-direction: column;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .action-buttons-group {
            flex-direction: column;
        }
    }
</style>

<div class="books-page">
    <!-- Header -->
    <div class="books-header">
        <h1>
            ✨ Discover Amazing Books ✨
        </h1>
        <p>Thousands of knowledge treasures await you</p>
    </div>
    
    <!-- Alerts -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert-modern alert-success">
            ✓ <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert-modern alert-error">
            ✗ <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- Statistics Banner -->
    <div class="stats-banner">
        <div class="stat-badge">
            <div class="stat-number"><?= isset($totalBooks) ? $totalBooks : count($books ?? []) ?></div>
            <div class="stat-label">📚 Total Books</div>
        </div>
        <div class="stat-badge">
            <div class="stat-number">
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
            <div class="stat-label">✅ Available Now</div>
        </div>
        <div class="stat-badge">
            <div class="stat-number">
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
            <div class="stat-label">📖 In Circulation</div>
        </div>
    </div>
    
    <!-- Search & Filters -->
    <div class="search-container">
        <form method="GET" action="/faculty/search" id="searchForm">
            <div class="search-box-wrapper">
                <input type="text" 
                       name="q" 
                       id="searchInput"
                       class="search-input-main" 
                       placeholder="🔍 Type to search books, authors, ISBN..." 
                       value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
                       autocomplete="off">
                <span class="search-icon">🔍</span>
                <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
            </div>
            
            <div class="filters-row">
                <div class="filter-group">
                    <select name="category" class="filter-select-modern" onchange="this.form.submit()">
                        <option value="">📚 All Publishers</option>
                        <?php if (!empty($categories) && is_array($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>" 
                                        <?= (isset($_GET['category']) && $_GET['category'] === $category) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="status" class="filter-select-modern" onchange="this.form.submit()">
                        <option value="">📊 All Availability</option>
                        <option value="available" <?= (isset($_GET['status']) && $_GET['status'] === 'available') ? 'selected' : '' ?>>
                            ✓ Available Only
                        </option>
                        <option value="borrowed" <?= (isset($_GET['status']) && $_GET['status'] === 'borrowed') ? 'selected' : '' ?>>
                            ✗ Currently Borrowed
                        </option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="sort" class="filter-select-modern" onchange="this.form.submit()">
                        <option value="">🔄 Sort By</option>
                        <option value="title" <?= (isset($_GET['sort']) && $_GET['sort'] === 'title') ? 'selected' : '' ?>>
                            Title (A-Z)
                        </option>
                        <option value="author" <?= (isset($_GET['sort']) && $_GET['sort'] === 'author') ? 'selected' : '' ?>>
                            Author (A-Z)
                        </option>
                        <option value="available" <?= (isset($_GET['sort']) && $_GET['sort'] === 'available') ? 'selected' : '' ?>>
                            Most Available
                        </option>
                    </select>
                </div>
                
                <button type="submit" class="search-btn-modern">Search</button>
            </div>
        </form>
    </div>
    
    <!-- Books Grid -->
    <div class="books-grid-modern">
        <?php if (!empty($books) && is_array($books)): ?>
            <?php foreach ($books as $book): ?>
                <div class="book-card-modern">
                    <div class="book-image-wrapper">
                        <?php if (!empty($book['bookImage'])): ?>
                            <img src="/<?= htmlspecialchars($book['bookImage']) ?>" alt="<?= htmlspecialchars($book['bookName'] ?? 'Book cover') ?>">
                        <?php else: ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 7rem; color: white; text-shadow: 2px 2px 8px rgba(0,0,0,0.3);">
                                📚
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($book['isTrending'])): ?>
                            <div class="trending-badge">🔥 Trending</div>
                        <?php elseif (!empty($book['isSpecial'])): ?>
                            <div class="trending-badge special">⭐ <?= htmlspecialchars($book['specialBadge'] ?? 'Special') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-content">
                        <div class="book-title-modern"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Title') ?></div>
                        <div class="book-author-modern">by <?= htmlspecialchars($book['authorName'] ?? 'Unknown Author') ?></div>
                        
                        <div class="book-meta-tags">
                            <?php if (!empty($book['publisherName'])): ?>
                                <span class="meta-tag publisher">📘 <?= htmlspecialchars($book['publisherName']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($book['totalCopies'])): ?>
                                <span class="meta-tag copies"><?= htmlspecialchars($book['totalCopies']) ?> copies</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="book-availability">
                            <div class="availability-status <?= ($book['available'] ?? 0) > 0 ? 'available' : 'unavailable' ?>">
                                <span class="status-dot"></span>
                                <?php if (($book['available'] ?? 0) > 0): ?>
                                    <?= $book['available'] ?> Available
                                <?php else: ?>
                                    Not Available
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="action-buttons-group">
                            <a href="/faculty/book/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="action-button-modern btn-view-details">
                                👁️ Details
                            </a>
                            <?php if (isset($_SESSION['user_id']) || isset($_SESSION['userId'])): ?>
                                <?php if (($book['available'] ?? 0) > 0): ?>
                                    <a href="/faculty/reserve/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="action-button-modern btn-borrow">
                                        📖 Borrow
                                    </a>
                                <?php else: ?>
                                    <a href="/faculty/reserve/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="action-button-modern btn-reserve">
                                        🔖 Reserve
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/login" class="action-button-modern btn-borrow">🔐 Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-modern">
                <div class="icon">📚</div>
                <h3>No Books Found</h3>
                <p>Try adjusting your search criteria or explore our complete collection</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Autocomplete functionality
const searchInput = document.getElementById('searchInput');
const autocompleteDropdown = document.getElementById('autocompleteDropdown');
let debounceTimer;

searchInput.addEventListener('input', function(e) {
    const query = e.target.value.trim();
    
    clearTimeout(debounceTimer);
    
    if (query.length < 2) {
        autocompleteDropdown.classList.remove('active');
        return;
    }
    
    debounceTimer = setTimeout(() => {
        fetchAutocomplete(query);
    }, 300);
});

async function fetchAutocomplete(query) {
    try {
        const response = await fetch(`/api/books/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.books && data.books.length > 0) {
            displayAutocomplete(data.books);
        } else {
            autocompleteDropdown.classList.remove('active');
        }
    } catch (error) {
        console.error('Autocomplete error:', error);
    }
}

function displayAutocomplete(books) {
    autocompleteDropdown.innerHTML = books.slice(0, 5).map(book => `
        <div class="autocomplete-item" onclick="selectBook('${escapeHtml(book.bookName)}')">
            ${book.bookImage ? `<img src="/${escapeHtml(book.bookImage)}" alt="${escapeHtml(book.bookName)}">` : '<div style="width:50px;height:50px;background:#667eea;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;">📚</div>'}
            <div class="autocomplete-item-text">
                <div class="autocomplete-item-title">${escapeHtml(book.bookName)}</div>
                <div class="autocomplete-item-author">by ${escapeHtml(book.authorName)}</div>
            </div>
        </div>
    `).join('');
    
    autocompleteDropdown.classList.add('active');
}

function selectBook(bookName) {
    searchInput.value = bookName;
    autocompleteDropdown.classList.remove('active');
    document.getElementById('searchForm').submit();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
        autocompleteDropdown.classList.remove('active');
    }
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>