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
    /* Modern Book Details Styling */
    .book-details-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
    }
    
    .book-details-wrapper::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-50px) rotate(180deg); }
    }
    
    .book-details-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }
    
    .book-card {
        background: white;
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.6s ease-out;
        display: grid;
        grid-template-columns: 350px 1fr;
        min-height: 600px;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Left Side - Book Image */
    .book-image-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
        padding: 3rem 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-right: 1px solid #f0f0f0;
    }
    
    .book-image-wrapper {
        width: 250px;
        height: 350px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        transition: transform 0.4s ease;
        margin-bottom: 2rem;
    }
    
    .book-image-wrapper:hover {
        transform: translateY(-10px) scale(1.03);
    }
    
    .book-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .book-isbn-badge {
        background: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    
    .book-isbn-badge .label {
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .book-isbn-badge .value {
        font-size: 1rem;
        color: #1f2937;
        font-weight: 700;
        font-family: 'Courier New', monospace;
    }
    
    /* Right Side - Book Details */
    .book-details-section {
        padding: 3rem;
        display: flex;
        flex-direction: column;
    }
    
    .book-header-top {
        margin-bottom: 2rem;
    }
    
    .book-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.75rem;
        line-height: 1.2;
    }
    
    .book-author {
        font-size: 1.5rem;
        color: #667eea;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .book-author::before {
        content: 'by ';
        font-weight: 400;
        color: #6b7280;
    }
    
    .book-publisher {
        font-size: 1.1rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    /* Meta Grid - Horizontal */
    .book-meta-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
        padding: 1.5rem;
        background: #f9fafb;
        border-radius: 16px;
    }
    
    .meta-item-inline {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }
    
    .meta-item-inline:hover {
        transform: translateY(-3px);
    }
    
    .meta-icon-inline {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        opacity: 0.8;
    }
    
    .meta-label-inline {
        font-size: 0.7rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .meta-value-inline {
        font-size: 1rem;
        color: #374151;
        font-weight: 700;
    }
    
    /* Availability Banner */
    .availability-banner {
        padding: 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.95; }
    }
    
    .availability-banner.available {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: 2px solid #6ee7b7;
    }
    
    .availability-banner.unavailable {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: 2px solid #fca5a5;
    }
    
    .availability-icon {
        font-size: 2.5rem;
        flex-shrink: 0;
    }
    
    .availability-content {
        flex: 1;
    }
    
    .availability-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .availability-banner.available .availability-title {
        color: #065f46;
    }
    
    .availability-banner.unavailable .availability-title {
        color: #991b1b;
    }
    
    .availability-text {
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    .availability-banner.available .availability-text {
        color: #047857;
    }
    
    .availability-banner.unavailable .availability-text {
        color: #b91c1c;
    }
    
    /* Description Section */
    .description-section {
        flex: 1;
        margin-bottom: 2rem;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid #667eea;
        display: inline-block;
    }
    
    .description-text {
        color: #4b5563;
        line-height: 1.8;
        font-size: 1.05rem;
        max-height: 200px;
        overflow-y: auto;
        padding-right: 1rem;
    }
    
    .description-text::-webkit-scrollbar {
        width: 6px;
    }
    
    .description-text::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 10px;
    }
    
    .description-text::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }
    
    /* Action Section */
    .action-section {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: auto;
    }
    
    .btn {
        flex: 1;
        min-width: 180px;
        padding: 1rem 2rem;
        border-radius: 14px;
        font-weight: 700;
        font-size: 1.05rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
    }
    
    .btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
    }
    
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    .btn-icon {
        font-size: 1.2rem;
        position: relative;
        z-index: 1;
    }
    
    .btn-text {
        position: relative;
        z-index: 1;
    }
    
    .waiting-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 4px solid #3b82f6;
        padding: 1.25rem;
        border-radius: 12px;
        margin-top: 1rem;
        display: flex;
        align-items: start;
        gap: 1rem;
    }
    
    .waiting-info-icon {
        font-size: 1.5rem;
        color: #1e40af;
        flex-shrink: 0;
    }
    
    .waiting-info-text {
        color: #1e3a8a;
        line-height: 1.6;
        font-size: 0.95rem;
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .book-card {
            grid-template-columns: 1fr;
        }
        
        .book-image-section {
            border-right: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 2rem;
        }
        
        .book-image-wrapper {
            width: 200px;
            height: 280px;
            margin-bottom: 1.5rem;
        }
        
        .book-details-section {
            padding: 2rem;
        }
    }
    
    @media (max-width: 768px) {
        .book-details-wrapper {
            padding: 2rem 0;
        }
        
        .book-details-container {
            padding: 0 1rem;
        }
        
        .book-title {
            font-size: 2rem;
        }
        
        .book-author {
            font-size: 1.2rem;
        }
        
        .book-meta-row {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .action-section {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            min-width: auto;
        }
    }
    
    @media (max-width: 480px) {
        .book-meta-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="book-details-wrapper">
    <div class="book-details-container">
        <div class="book-card">
            <!-- Left Side - Book Image -->
            <div class="book-image-section">
                <div class="book-image-wrapper">
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
                
                <div class="book-isbn-badge">
                    <div class="label">ISBN</div>
                    <div class="value"><?= htmlspecialchars($book['isbn']) ?></div>
                </div>
            </div>
            
            <!-- Right Side - Book Details -->
            <div class="book-details-section">
                <div class="book-header-top">
                    <h1 class="book-title"><?= htmlspecialchars($book['bookName']) ?></h1>
                    <p class="book-author"><?= htmlspecialchars($book['authorName']) ?></p>
                    <p class="book-publisher"><?= htmlspecialchars($book['publisherName']) ?></p>
                </div>
                
                <!-- Meta Information Row -->
                <div class="book-meta-row">
                    <?php if (!empty($book['category'])): ?>
                    <div class="meta-item-inline">
                        <span class="meta-icon-inline">📂</span>
                        <div class="meta-label-inline">Category</div>
                        <div class="meta-value-inline"><?= htmlspecialchars($book['category']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['publicationYear'])): ?>
                    <div class="meta-item-inline">
                        <span class="meta-icon-inline">📅</span>
                        <div class="meta-label-inline">Year</div>
                        <div class="meta-value-inline"><?= htmlspecialchars($book['publicationYear']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="meta-item-inline">
                        <span class="meta-icon-inline">📚</span>
                        <div class="meta-label-inline">Total Copies</div>
                        <div class="meta-value-inline"><?= $book['totalCopies'] ?></div>
                    </div>
                    
                    <div class="meta-item-inline">
                        <span class="meta-icon-inline">✅</span>
                        <div class="meta-label-inline">Available</div>
                        <div class="meta-value-inline"><?= $book['available'] ?></div>
                    </div>
                </div>
                
                <!-- Availability Banner -->
                <div class="availability-banner <?= $book['available'] > 0 ? 'available' : 'unavailable' ?>">
                    <span class="availability-icon"><?= $book['available'] > 0 ? '✅' : '❌' ?></span>
                    <div class="availability-content">
                        <div class="availability-title">
                            <?= $book['available'] > 0 ? 'Available for Reservation' : 'Currently Unavailable' ?>
                        </div>
                        <div class="availability-text">
                            <?php if ($book['available'] > 0): ?>
                                <?= $book['available'] ?> <?= $book['available'] == 1 ? 'copy' : 'copies' ?> ready to reserve
                            <?php else: ?>
                                All <?= $book['totalCopies'] ?> <?= $book['totalCopies'] == 1 ? 'copy is' : 'copies are' ?> currently borrowed
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Description Section -->
                <?php if (!empty($book['description'])): ?>
                <div class="description-section">
                    <h2 class="section-title">About This Book</h2>
                    <p class="description-text"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="action-section">
                    <?php if ($book['available'] > 0): ?>
                        <form method="POST" action="<?= BASE_URL ?>faculty/reserve/<?= urlencode($book['isbn']) ?>" style="flex: 1; min-width: 180px;">
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-icon">📖</span>
                                <span class="btn-text">Reserve This Book</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="<?= BASE_URL ?>faculty/reserve/<?= urlencode($book['isbn']) ?>" style="flex: 1; min-width: 180px;">
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-icon">🔔</span>
                                <span class="btn-text">Join Waiting List</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>faculty/books" class="btn btn-secondary">
                        <span class="btn-icon">←</span>
                        <span class="btn-text">Back to Library</span>
                    </a>
                </div>
                
                <!-- Waiting List Info -->
                <?php if ($book['available'] == 0): ?>
                <div class="waiting-info">
                    <span class="waiting-info-icon">💡</span>
                    <div class="waiting-info-text">
                        <strong>Join the waiting list</strong> to be notified when this book becomes available. You'll receive an email notification as soon as a copy is returned.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>