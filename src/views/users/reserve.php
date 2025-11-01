<?php
/**
 * User Reserve Book View
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Reserve Book';
include APP_ROOT . '/views/layouts/header.php';

$book = $book ?? [];
?>

<style>
/* Hide scrollbars globally */
body {
    overflow-x: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

body::-webkit-scrollbar {
    display: none;
}

/* Reserve page background with animations */
.reserve-bg {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    position: relative;
    overflow: hidden;
}

/* Animated background particles */
.reserve-bg::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 20s infinite ease-in-out;
}

.reserve-bg::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 25s infinite ease-in-out reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); }
    33% { transform: translateY(-30px) translateX(30px) rotate(120deg); }
    66% { transform: translateY(30px) translateX(-30px) rotate(240deg); }
}

/* Main card */
.reserve-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    border-radius: 32px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
    max-width: 600px;
    width: 100%;
    padding: 3rem;
    margin: 0 auto;
    animation: slideUpFade 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    z-index: 1;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

@keyframes slideUpFade {
    from { 
        opacity: 0; 
        transform: translateY(50px) scale(0.95);
    }
    to { 
        opacity: 1; 
        transform: translateY(0) scale(1);
    }
}

/* Header with icon */
.reserve-header {
    text-align: center;
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #f3f4f6;
}

.reserve-icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    border-radius: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4); }
    50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5); }
}

.reserve-icon-wrapper i {
    font-size: 2.5rem;
    color: white;
}

.reserve-title {
    font-size: 2.2rem;
    font-weight: 900;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.reserve-subtitle {
    font-size: 1rem;
    color: #6b7280;
    font-weight: 500;
}

/* Book details section */
.book-details-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border: 1px solid rgba(102, 126, 234, 0.1);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.reserve-book-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 1.25rem;
    line-height: 1.3;
}

.book-meta-grid {
    display: grid;
    gap: 1rem;
}

.reserve-book-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #374151;
    font-size: 1.05rem;
    padding: 0.75rem;
    background: white;
    border-radius: 12px;
    border: 1px solid rgba(102, 126, 234, 0.1);
    transition: all 0.3s ease;
}

.reserve-book-meta:hover {
    transform: translateX(5px);
    border-color: rgba(102, 126, 234, 0.3);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
}

.meta-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.meta-icon i {
    font-size: 1.1rem;
    color: white;
}

.meta-content {
    flex: 1;
}

.meta-label {
    font-size: 0.75rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.meta-value {
    font-weight: 700;
    color: #1f2937;
}

/* Info banner */
.reserve-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-left: 5px solid #3b82f6;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    color: #1e3a8a;
    font-size: 1rem;
    line-height: 1.7;
    display: flex;
    align-items: start;
    gap: 1rem;
    box-shadow: 0 5px 20px rgba(59, 130, 246, 0.2);
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-icon i {
    font-size: 1.2rem;
    color: white;
}

.info-text {
    flex: 1;
    font-weight: 600;
}

.info-text b {
    color: #1e40af;
    font-weight: 800;
}

/* Warning banner (for existing reservations) */
.warning-banner {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 5px solid #f59e0b;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    color: #92400e;
    font-size: 1rem;
    line-height: 1.7;
    display: flex;
    align-items: start;
    gap: 1rem;
    box-shadow: 0 5px 20px rgba(245, 158, 11, 0.2);
}

.warning-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #f59e0b;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.warning-icon i {
    font-size: 1.2rem;
    color: white;
}

/* Action buttons */
.reserve-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.reserve-btn {
    padding: 1.25rem 2rem;
    border-radius: 16px;
    border: none;
    font-weight: 800;
    font-size: 1.05rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.reserve-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
}

.reserve-btn:hover::before {
    width: 400px;
    height: 400px;
}

.reserve-btn i,
.reserve-btn span {
    position: relative;
    z-index: 1;
}

.reserve-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.reserve-btn-primary:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 18px 45px rgba(102, 126, 234, 0.5);
}

.reserve-btn-secondary {
    background: white;
    color: #374151;
    border: 2px solid #e5e7eb;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.reserve-btn-secondary:hover {
    background: #f9fafb;
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
    color: #667eea;
}

/* Responsive design */
@media (max-width: 768px) {
    .reserve-card {
        padding: 2rem 1.5rem;
    }
    
    .reserve-title {
        font-size: 1.8rem;
    }
    
    .reserve-book-title {
        font-size: 1.3rem;
    }
    
    .reserve-actions {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .reserve-bg {
        padding: 2rem 1rem;
    }
    
    .reserve-card {
        padding: 1.5rem;
    }
    
    .reserve-icon-wrapper {
        width: 60px;
        height: 60px;
    }
    
    .reserve-icon-wrapper i {
        font-size: 2rem;
    }
    
    .reserve-title {
        font-size: 1.5rem;
    }
    
    .reserve-book-title {
        font-size: 1.2rem;
    }
    
    .reserve-btn {
        padding: 1rem 1.5rem;
        font-size: 0.95rem;
    }
}
</style>

<div class="reserve-bg">
    <div class="reserve-card">
        <!-- Header Section -->
        <div class="reserve-header">
            <div class="reserve-icon-wrapper">
                <i class="fas fa-bookmark"></i>
            </div>
            <h1 class="reserve-title">Reserve Book</h1>
            <p class="reserve-subtitle">Complete your reservation request</p>
        </div>

        <!-- Book Details Section -->
        <div class="book-details-section">
            <h2 class="reserve-book-title"><?= htmlspecialchars($book['bookName'] ?? 'Unknown Book') ?></h2>
            
            <div class="book-meta-grid">
                <div class="reserve-book-meta">
                    <div class="meta-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="meta-content">
                        <div class="meta-label">Author</div>
                        <div class="meta-value"><?= htmlspecialchars($book['authorName'] ?? 'N/A') ?></div>
                    </div>
                </div>
                
                <?php if (!empty($book['publisherName'])): ?>
                <div class="reserve-book-meta">
                    <div class="meta-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="meta-content">
                        <div class="meta-label">Publisher</div>
                        <div class="meta-value"><?= htmlspecialchars($book['publisherName']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="reserve-book-meta">
                    <div class="meta-icon">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <div class="meta-content">
                        <div class="meta-label">ISBN</div>
                        <div class="meta-value"><?= htmlspecialchars($book['isbn'] ?? 'N/A') ?></div>
                    </div>
                </div>
                
                <?php if (isset($book['available']) && isset($book['totalCopies'])): ?>
                <div class="reserve-book-meta">
                    <div class="meta-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="meta-content">
                        <div class="meta-label">Availability</div>
                        <div class="meta-value"><?= $book['available'] ?> / <?= $book['totalCopies'] ?> copies</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form or Warning -->
        <?php if (isset($existingReservation) && $existingReservation): ?>
            <!-- Existing Reservation Warning -->
            <div class="warning-banner">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="info-text">
                    You already have an active reservation for this book. 
                    <?php if (isset($existingReservation['expiryDate'])): ?>
                        <b>Expires on: <?= date('F j, Y', strtotime($existingReservation['expiryDate'])) ?></b>
                    <?php endif; ?>
                </div>
            </div>

            <div class="reserve-actions">
                <a href="<?= BASE_URL ?>user/books" class="reserve-btn reserve-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Books</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Form -->
            <form method="POST">
                <!-- Info Banner -->
                <div class="reserve-info">
                    <div class="info-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="info-text">
                        Are you sure you want to reserve this book? Your reservation will be valid for <b>1 day</b> and requires admin approval before checkout.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="reserve-actions">
                    <button type="submit" class="reserve-btn reserve-btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        <span>Confirm</span>
                    </button>
                    <a href="<?= BASE_URL ?>user/books" class="reserve-btn reserve-btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
