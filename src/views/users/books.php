<?php
/**
 * User Books View - Browse and search library catalog
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Browse Books';
$currentPage = 'books';

// Include header
include APP_ROOT . '/views/layouts/header.php';
?>

<!-- Font Awesome CDN for modern icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        animation: drift 60s linear infinite;
    }

    @keyframes drift {
        0% {
            transform: translate(0, 0);
        }

        100% {
            transform: translate(50px, 50px);
        }
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
        text-shadow: 3px 3px 12px rgba(0, 0, 0, 0.3);
        background: linear-gradient(45deg, #fff, #ffd700);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: textGlow 3s ease-in-out infinite;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
    }

    .books-header h1 i {
        background: linear-gradient(45deg, #fff, #ffd700);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    @keyframes textGlow {

        0%,
        100% {
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.5));
        }

        50% {
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
        }
    }

    .books-header p {
        font-size: 1.3rem;
        opacity: 0.95;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
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
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .stat-badge:hover {
        transform: translateY(-10px) scale(1.05);
        background: rgba(255, 255, 255, 0.25);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .stat-badge .stat-number {
        font-size: 3rem;
        font-weight: 900;
        color: white;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .stat-badge .stat-number i {
        font-size: 2.5rem;
        opacity: 0.8;
    }

    .stat-badge .stat-label {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .search-container {
        max-width: 1400px;
        margin: 0 auto 30px;
        background: white;
        border-radius: 25px;
        padding: 35px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        position: relative;
        z-index: 1;
    }

    .search-box-wrapper {
        position: relative;
        margin-bottom: 25px;
    }

    .search-input-main {
        width: 100%;
        padding: 18px 60px 18px 25px;
        border: 2px solid #667eea;
        border-radius: 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        color: #6b7280;
    }

    .search-input-main::placeholder {
        color: #9ca3af;
    }

    .search-input-main:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-icon {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.3rem;
        color: #667eea;
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
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
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
        margin-bottom: 4px;
    }

    .autocomplete-item-author {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .filters-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr) auto;
        gap: 15px;
        align-items: center;
    }

    .filter-group {
        position: relative;
    }

    .filter-select-modern {
        width: 100%;
        padding: 18px 45px 18px 50px;
        border: 2px solid #d1d5db;
        border-radius: 15px;
        font-size: 1rem;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        appearance: none;
        color: #6b7280;
        font-weight: 500;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%236b7280' d='M6 8L0 2h12z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 20px center;
    }

    .filter-group::before {
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        pointer-events: none;
        z-index: 1;
        font-size: 1.1rem;
    }

    .filter-group:nth-child(1)::before {
        content: '\f02d';
    }

    .filter-group:nth-child(2)::before {
        content: '\f201';
    }

    .filter-group:nth-child(3)::before {
        content: '\f0c9';
    }

    .filter-select-modern:hover {
        border-color: #9ca3af;
    }

    .filter-select-modern:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-btn-modern {
        padding: 18px 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 15px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }

    .search-btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
    }

    .books-grid-modern {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        padding: 20px;
        position: relative;
        z-index: 1;
    }

    .book-card-modern {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .book-card-modern:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        border-color: #667eea;
    }

    .book-image-wrapper {
        position: relative;
        height: 400px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        overflow: hidden;
    }

    .book-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .book-card-modern:hover .book-image-wrapper img {
        transform: scale(1.1);
    }

    .book-placeholder-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        font-size: 7rem;
        color: white;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
    }

    .trending-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
        color: white;
        padding: 8px 18px;
        border-radius: 25px;
        font-weight: 800;
        font-size: 0.85rem;
        box-shadow: 0 5px 20px rgba(255, 107, 107, 0.4);
        animation: pulse 2s ease-in-out infinite;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .trending-badge.special {
        background: linear-gradient(135deg, #ffd700, #ffed4e);
        color: #000;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .book-content {
        padding: 25px;
    }

    .book-title-modern {
        font-size: 1.4rem;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 10px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .book-author-modern {
        color: #6b7280;
        font-size: 1rem;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .book-meta-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
    }

    .meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #667eea;
    }

    .meta-tag.publisher {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        color: #059669;
    }

    .meta-tag.copies {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
        color: #d97706;
    }

    .book-availability {
        margin-bottom: 20px;
    }

    .availability-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 25px;
        font-weight: 800;
        font-size: 0.95rem;
    }

    .availability-status.available {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
    }

    .availability-status.unavailable {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        animation: blink 2s ease-in-out infinite;
    }

    .availability-status.available .status-dot {
        background: #10b981;
    }

    .availability-status.unavailable .status-dot {
        background: #ef4444;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .action-buttons-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .action-button-modern {
        padding: 12px 20px;
        border-radius: 15px;
        font-weight: 800;
        font-size: 0.95rem;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-view-details {
        background: linear-gradient(135deg, #e5e7eb, #d1d5db);
        color: #1f2937;
    }

    .btn-view-details:hover {
        background: linear-gradient(135deg, #d1d5db, #9ca3af);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-borrow {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-borrow:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
    }

    .btn-reserve {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 5px 20px rgba(245, 158, 11, 0.4);
    }

    .btn-reserve:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(245, 158, 11, 0.6);
    }

    .empty-state-modern {
        grid-column: 1 / -1;
        text-align: center;
        padding: 100px 20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 30px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
    }

    .empty-state-modern .icon {
        font-size: 6rem;
        margin-bottom: 25px;
        opacity: 0.3;
        color: #667eea;
    }

    .empty-state-modern h3 {
        font-size: 2rem;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 15px;
    }

    .empty-state-modern p {
        font-size: 1.2rem;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .books-page {
            padding: 20px 15px;
        }

        .books-header h1 {
            font-size: 2rem;
            flex-direction: column;
            gap: 10px;
        }

        .books-header p {
            font-size: 1rem;
        }

        .stats-banner {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .stat-badge {
            padding: 20px 15px;
        }

        .stat-badge .stat-number {
            font-size: 2rem;
        }

        .stat-badge .stat-number i {
            font-size: 1.8rem;
        }

        .stat-badge .stat-label {
            font-size: 0.8rem;
        }

        .search-container {
            padding: 20px;
            border-radius: 20px;
        }

        .search-input-main {
            padding: 15px 50px 15px 20px;
            font-size: 0.95rem;
        }

        .search-icon {
            right: 20px;
            font-size: 1.1rem;
        }

        .filters-row {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .filter-select-modern {
            padding: 15px 40px 15px 45px;
            font-size: 0.95rem;
        }

        .filter-group::before {
            left: 15px;
            font-size: 1rem;
        }

        .search-btn-modern {
            padding: 15px 30px;
            font-size: 0.95rem;
            width: 100%;
        }

        .books-grid-modern {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 15px;
        }

        .book-card-modern {
            border-radius: 20px;
        }

        .book-image-wrapper {
            height: 350px;
        }

        .book-placeholder-icon {
            font-size: 5rem;
        }

        .book-content {
            padding: 20px;
        }

        .book-title-modern {
            font-size: 1.2rem;
        }

        .book-author-modern {
            font-size: 0.95rem;
        }

        .book-meta-tags {
            gap: 8px;
        }

        .meta-tag {
            font-size: 0.8rem;
            padding: 5px 12px;
        }

        .availability-status {
            font-size: 0.9rem;
            padding: 8px 15px;
        }

        .action-buttons-group {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .action-button-modern {
            padding: 12px 18px;
            font-size: 0.9rem;
        }

        .trending-badge {
            font-size: 0.8rem;
            padding: 6px 14px;
        }

        .autocomplete-dropdown {
            border-radius: 15px;
            max-height: 300px;
        }

        .autocomplete-item {
            padding: 12px 15px;
        }

        .autocomplete-item img {
            width: 40px;
            height: 40px;
        }

        .autocomplete-item-title {
            font-size: 0.95rem;
        }

        .autocomplete-item-author {
            font-size: 0.8rem;
        }

        .empty-state-modern {
            padding: 60px 20px;
            border-radius: 20px;
        }

        .empty-state-modern .icon {
            font-size: 4rem;
        }

        .empty-state-modern h3 {
            font-size: 1.5rem;
        }

        .empty-state-modern p {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .books-header h1 {
            font-size: 1.6rem;
        }

        .books-header p {
            font-size: 0.9rem;
        }

        .stats-banner {
            grid-template-columns: 1fr;
        }

        .book-image-wrapper {
            height: 300px;
        }

        .book-title-modern {
            font-size: 1.1rem;
        }
    }
</style>

<div class="books-page">
    <!-- Header -->
    <div class="books-header">
        <h1><i class="fas fa-book-reader"></i> Digital Library</h1>
        <p>Discover, Search, and Borrow from Our Vast Collection</p>
    </div>

    <!-- Stats Banner -->
    <div class="stats-banner">
        <div class="stat-badge">
            <div class="stat-number">
                <i class="fas fa-book"></i>
                <?php
                if (isset($totalBooks)) {
                    echo number_format($totalBooks);
                } elseif (isset($books) && is_array($books)) {
                    echo count($books);
                } else {
                    echo '0';
                }
                ?>
            </div>
            <div class="stat-label">Total Books</div>
        </div>
        <div class="stat-badge">
            <div class="stat-number">
                <i class="fas fa-check-circle"></i>
                <?php
                if (isset($totalAvailable)) {
                    echo number_format($totalAvailable);
                } elseif (isset($books) && is_array($books)) {
                    $available = 0;
                    foreach ($books as $book) {
                        if (isset($book['available']) && $book['available'] > 0) {
                            $available++;
                        }
                    }
                    echo $available;
                } else {
                    echo '0';
                }
                ?>
            </div>
            <div class="stat-label">Available Now</div>
        </div>
        <div class="stat-badge">
            <div class="stat-number">
                <i class="fas fa-building"></i>
                <?php
                if (isset($totalCategories)) {
                    echo number_format($totalCategories);
                } elseif (isset($categories) && is_array($categories)) {
                    echo count($categories);
                } else {
                    echo '0';
                }
                ?>
            </div>
            <div class="stat-label">Publishers</div>
        </div>
        <div class="stat-badge">
            <div class="stat-number">
                <i class="fas fa-users"></i>
                <?= isset($totalBorrowed) ? number_format($totalBorrowed) : '0' ?>
            </div>
            <div class="stat-label">Total Borrowed</div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="search-container">
        <form action="<?= BASE_URL ?>user/books" method="GET" id="searchForm">
            <div class="search-box-wrapper">
                <input type="text" name="q" id="searchInput" class="search-input-main"
                    placeholder="Type to search books, authors, ISBN..."
                    value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" autocomplete="off">
                <span class="search-icon"><i class="fas fa-search"></i></span>
                <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
            </div>

            <div class="filters-row">
                <div class="filter-group">
                    <select name="category" class="filter-select-modern" onchange="this.form.submit()">
                        <option value="">All Publishers</option>
                        <?php if (!empty($categories) && is_array($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $category) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="status" class="filter-select-modern" onchange="this.form.submit()">
                        <option value="">All Availability</option>
                        <option value="available" <?= (isset($_GET['status']) && $_GET['status'] === 'available') ? 'selected' : '' ?>>
                            Available Only
                        </option>
                        <option value="borrowed" <?= (isset($_GET['status']) && $_GET['status'] === 'borrowed') ? 'selected' : '' ?>>
                            Currently Borrowed
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="sort" class="filter-select-modern" onchange="this.form.submit()">
                        <option value="">Sort By</option>
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

                <button type="submit" class="search-btn-modern">SEARCH</button>
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
                            <?php
                            $imgSrc = $book['bookImage'];
                            if (strpos($imgSrc, 'uploads/') === false) {
                                $imgSrc = 'uploads/books/' . $imgSrc;
                            }
                            ?>
                            <img src="<?= rtrim(BASE_URL, '/') . '/' . htmlspecialchars($imgSrc) ?>"
                                alt="<?= htmlspecialchars($book['bookName'] ?? 'Book cover') ?>">
                        <?php else: ?>
                            <div class="book-placeholder-icon">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($book['isTrending'])): ?>
                            <div class="trending-badge">
                                <i class="fas fa-fire"></i> Trending
                            </div>
                        <?php elseif (!empty($book['isSpecial'])): ?>
                            <div class="trending-badge special">
                                <i class="fas fa-star"></i> <?= htmlspecialchars($book['specialBadge'] ?? 'Special') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="book-content">
                        <div class="book-title-modern"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Title') ?></div>
                        <div class="book-author-modern">by <?= htmlspecialchars($book['authorName'] ?? 'Unknown Author') ?>
                        </div>

                        <div class="book-meta-tags">
                            <?php if (!empty($book['publisherName'])): ?>
                                <span class="meta-tag publisher">
                                    <i class="fas fa-building"></i> <?= htmlspecialchars($book['publisherName']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($book['totalCopies'])): ?>
                                <span class="meta-tag copies">
                                    <i class="fas fa-copy"></i> <?= htmlspecialchars($book['totalCopies']) ?> copies
                                </span>
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
                            <a href="<?= BASE_URL ?>user/book?isbn=<?= urlencode($book['isbn'] ?? '') ?>"
                                class="action-button-modern btn-view-details">
                                <i class="fas fa-eye"></i> Details
                            </a>
                            <?php if (isset($_SESSION['userId'])): ?>
                                <?php if (($book['available'] ?? 0) > 0): ?>
                                    <a href="<?= BASE_URL ?>user/reserve?isbn=<?= urlencode($book['isbn'] ?? '') ?>"
                                        class="action-button-modern btn-reserve">
                                        <i class="fas fa-bookmark"></i> Reserve
                                    </a>
                                <?php else: ?>
                                    <button class="action-button-modern btn-reserve" disabled
                                        style="opacity: 0.6; cursor: not-allowed;">
                                        <i class="fas fa-times-circle"></i> Unavailable
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>login" class="action-button-modern btn-borrow">
                                    <i class="fas fa-lock"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-modern">
                <div class="icon"><i class="fas fa-book-open"></i></div>
                <h3>No Books Found</h3>
                <p>Try adjusting your search criteria or explore our complete collection</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/form-validation.js"></script>
<script>
    // Autocomplete functionality with debouncing
    const searchInput = document.getElementById('searchInput');
    const autocompleteDropdown = document.getElementById('autocompleteDropdown');
    let debounceTimer;
    let currentRequest = null;

    searchInput.addEventListener('input', function (e) {
        const query = e.target.value.trim();

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            autocompleteDropdown.classList.remove('active');
            autocompleteDropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchAutocomplete(query);
        }, 300);
    });

    async function fetchAutocomplete(query) {
        // Cancel previous request
        if (currentRequest) {
            currentRequest.abort();
        }

        // Create abort controller
        const controller = new AbortController();
        currentRequest = controller;

        try {
            const response = await fetch(`<?= BASE_URL ?>api/books/search?q=${encodeURIComponent(query)}`, {
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error('Search request failed');
            }

            const data = await response.json();

            if (data.success && data.books && data.books.length > 0) {
                displayAutocomplete(data.books);
            } else {
                showNoResults();
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Autocomplete error:', error);
                showError();
            }
        } finally {
            currentRequest = null;
        }
    }

    function displayAutocomplete(books) {
        const baseUrl = '<?= rtrim(BASE_URL, "/") ?>';

        autocompleteDropdown.innerHTML = books.slice(0, 8).map(book => {
            const imagePath = book.bookImage
                ? `${baseUrl}/uploads/books/${escapeHtml(book.bookImage)}`
                : null;

            const imageHtml = imagePath
                ? `<img src="${imagePath}" alt="${escapeHtml(book.bookName)}" onerror="this.parentElement.innerHTML='<div style=\\'width:50px;height:50px;background:#667eea;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;\\'><i class=\\'fas fa-book\\'></i></div>'">`
                : '<div style="width:50px;height:50px;background:#667eea;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;"><i class="fas fa-book"></i></div>';

            const availabilityBadge = (book.available > 0)
                ? `<span style="background:#10b981;color:white;padding:2px 8px;border-radius:10px;font-size:0.75rem;margin-left:auto;font-weight:700;">${book.available} Available</span>`
                : `<span style="background:#ef4444;color:white;padding:2px 8px;border-radius:10px;font-size:0.75rem;margin-left:auto;font-weight:700;">Unavailable</span>`;

            return `
            <div class="autocomplete-item" onclick="selectBook('<?= BASE_URL ?>user/book?isbn=${encodeURIComponent(book.isbn)}')">
                ${imageHtml}
                <div class="autocomplete-item-text">
                    <div class="autocomplete-item-title">${escapeHtml(book.bookName)}</div>
                    <div class="autocomplete-item-author">by ${escapeHtml(book.authorName)} • ${escapeHtml(book.publisherName || 'Unknown Publisher')}</div>
                </div>
                ${availabilityBadge}
            </div>
        `;
        }).join('');

        autocompleteDropdown.classList.add('active');
    }

    function showNoResults() {
        autocompleteDropdown.innerHTML = `
        <div style="padding:30px;text-align:center;color:#6b7280;">
            <i class="fas fa-search" style="font-size:2rem;margin-bottom:10px;opacity:0.3;"></i>
            <p style="margin:0;">No books found matching your search</p>
        </div>
    `;
        autocompleteDropdown.classList.add('active');
    }

    function showError() {
        autocompleteDropdown.innerHTML = `
        <div style="padding:30px;text-align:center;color:#ef4444;">
            <i class="fas fa-exclamation-triangle" style="font-size:2rem;margin-bottom:10px;"></i>
            <p style="margin:0;">Error loading suggestions. Please try again.</p>
        </div>
    `;
        autocompleteDropdown.classList.add('active');
    }

    function selectBook(url) {
        window.location.href = url;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
            autocompleteDropdown.classList.remove('active');
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function (e) {
        const items = autocompleteDropdown.querySelectorAll('.autocomplete-item');

        if (e.key === 'Escape') {
            autocompleteDropdown.classList.remove('active');
        }
    });
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>