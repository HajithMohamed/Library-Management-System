<?php
use App\Helpers\ImageHelper;

if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Book Details';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .book-details-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 60px 20px;
        position: relative;
        overflow: hidden;
    }
    
    .book-details-page::before {
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
    
    .back-button-container {
        max-width: 1200px;
        margin: 0 auto 30px;
        position: relative;
        z-index: 1;
    }
    
    .back-btn-modern {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 28px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50px;
        color: white;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .back-btn-modern:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateX(-5px);
        color: white;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    
    .book-details-container {
        max-width: 1200px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        position: relative;
        z-index: 1;
    }
    
    .book-details-grid {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 0;
    }
    
    .book-image-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .book-image-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 70%);
    }
    
    .book-image-wrapper-detail {
        width: 100%;
        max-width: 300px;
        aspect-ratio: 2/3;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        position: relative;
        z-index: 1;
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .book-image-wrapper-detail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .book-placeholder-detail {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
        font-size: 8rem;
        color: white;
    }
    
    .availability-badge-large {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        z-index: 2;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: translateX(-50%) scale(1); }
        50% { transform: translateX(-50%) scale(1.05); }
    }
    
    .badge-available {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .badge-unavailable {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .book-info-section {
        padding: 50px 60px;
    }
    
    .book-title-large {
        font-size: 3rem;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 15px;
        line-height: 1.2;
        background: linear-gradient(135deg, #1f2937 0%, #667eea 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .book-author-large {
        font-size: 1.5rem;
        color: #6b7280;
        font-weight: 600;
        font-style: italic;
        margin-bottom: 30px;
    }
    
    .book-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 35px;
        padding: 35px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-radius: 20px;
        border: 2px solid rgba(102, 126, 234, 0.1);
    }
    
    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .meta-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .meta-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .meta-icon {
        font-size: 1.5rem;
    }
    
    .book-description-section {
        margin-bottom: 35px;
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title::before {
        content: '';
        width: 5px;
        height: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }
    
    .description-text {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #4b5563;
        padding: 25px;
        background: #f9fafb;
        border-radius: 15px;
        border-left: 5px solid #667eea;
    }
    
    .availability-section {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 35px;
    }
    
    .availability-card {
        padding: 25px;
        border-radius: 20px;
        text-align: center;
        border: 3px solid;
        position: relative;
        overflow: hidden;
    }
    
    .availability-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.5) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .availability-card:hover::before {
        opacity: 1;
    }
    
    .availability-card.total {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-color: #3b82f6;
    }
    
    .availability-card.available {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-color: #10b981;
    }
    
    .availability-number {
        font-size: 3.5rem;
        font-weight: 900;
        margin-bottom: 10px;
    }
    
    .availability-card.total .availability-number {
        color: #1e40af;
    }
    
    .availability-card.available .availability-number {
        color: #065f46;
    }
    
    .availability-label {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .availability-card.total .availability-label {
        color: #1e3a8a;
    }
    
    .availability-card.available .availability-label {
        color: #064e3b;
    }
    
    .action-buttons-large {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .action-btn-large {
        padding: 20px 40px;
        border-radius: 20px;
        border: none;
        font-size: 1.2rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .btn-reserve-large {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-reserve-large:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        color: white;
    }
    
    .btn-borrow-large {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-borrow-large:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.5);
        color: white;
    }
    
    .btn-unavailable {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
        color: white;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    @media (max-width: 992px) {
        .book-details-grid {
            grid-template-columns: 1fr;
        }
        
        .book-image-section {
            padding: 40px;
        }
        
        .book-info-section {
            padding: 40px 30px;
        }
        
        .book-title-large {
            font-size: 2.2rem;
        }
        
        .book-meta-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons-large {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="book-details-page">
    <!-- Back Button -->
    <div class="back-button-container">
        <a href="/faculty/books" class="back-btn-modern">
            ← Back to Books
        </a>
    </div>
    
    <!-- Book Details Container -->
    <div class="book-details-container">
        <div class="book-details-grid">
            <!-- Book Image Section -->
            <div class="book-image-section">
                <div class="book-image-wrapper-detail">
                    <?= ImageHelper::renderBookCover($book['bookImage'] ?? null, $book['bookName'] ?? 'Book cover', 'book-cover-detail') ?>
                </div>
                
                <div class="availability-badge-large <?= ($book['available'] ?? 0) > 0 ? 'badge-available' : 'badge-unavailable' ?>">
                    <?= ($book['available'] ?? 0) > 0 ? '✓ Available' : '✗ Not Available' ?>
                </div>
            </div>
            
            <!-- Book Info Section -->
            <div class="book-info-section">
                <h1 class="book-title-large"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Title') ?></h1>
                <p class="book-author-large">by <?= htmlspecialchars($book['authorName'] ?? 'Unknown Author') ?></p>
                
                <!-- Meta Information Grid -->
                <div class="book-meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">📘 Publisher</div>
                        <div class="meta-value">
                            <span class="meta-icon">🏢</span>
                            <?= htmlspecialchars($book['publisherName'] ?? 'N/A') ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">🔢 ISBN</div>
                        <div class="meta-value">
                            <span class="meta-icon">📋</span>
                            <?= htmlspecialchars($book['isbn'] ?? 'N/A') ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">📚 Category</div>
                        <div class="meta-value">
                            <span class="meta-icon">🏷️</span>
                            <?= htmlspecialchars($book['category'] ?? 'N/A') ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">📅 Publication Year</div>
                        <div class="meta-value">
                            <span class="meta-icon">🗓️</span>
                            <?= htmlspecialchars($book['publicationYear'] ?? 'N/A') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <?php if (!empty($book['description'])): ?>
                    <div class="book-description-section">
                        <h3 class="section-title">📖 Description</h3>
                        <div class="description-text">
                            <?= htmlspecialchars($book['description']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Availability Stats -->
                <div class="availability-section">
                    <div class="availability-card total">
                        <div class="availability-number"><?= htmlspecialchars($book['totalCopies'] ?? 0) ?></div>
                        <div class="availability-label">📚 Total Copies</div>
                    </div>
                    
                    <div class="availability-card available">
                        <div class="availability-number"><?= htmlspecialchars($book['available'] ?? 0) ?></div>
                        <div class="availability-label">✓ Available Now</div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons-large">
                    <?php if (($book['available'] ?? 0) > 0): ?>
                        <a href="/faculty/reserve/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="action-btn-large btn-borrow-large">
                            📖 Borrow Now
                        </a>
                        <a href="/faculty/reserve/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="action-btn-large btn-reserve-large">
                            🔖 Reserve Book
                        </a>
                    <?php else: ?>
                        <button class="action-btn-large btn-unavailable" disabled>
                            ✗ Currently Unavailable
                        </button>
                        <a href="/faculty/reserve/<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="action-btn-large btn-reserve-large">
                            🔔 Join Waitlist
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>