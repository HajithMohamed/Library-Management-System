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
        padding: 4rem 0;
        position: relative;
        overflow: hidden;
    }
    
    /* Animated background particles */
    .book-details-wrapper::before {
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
    
    .book-details-wrapper::after {
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
    
    .book-details-container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }
    
    .book-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 32px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: grid;
        grid-template-columns: 400px 1fr;
        min-height: 650px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    /* Left Side - Book Image */
    .book-image-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
        padding: 3.5rem 2.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        border-right: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
    }
    
    .book-image-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(102,126,234,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.5;
    }
    
    .book-image-wrapper {
        width: 280px;
        height: 400px;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin-bottom: 2rem;
        position: relative;
        z-index: 1;
    }
    
    .book-image-wrapper::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 24px;
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .book-image-wrapper:hover::before {
        opacity: 1;
    }
    
    .book-image-wrapper:hover {
        transform: translateY(-12px) scale(1.05);
        box-shadow: 0 35px 80px rgba(102, 126, 234, 0.4);
    }
    
    .book-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .book-image-wrapper:hover .book-image {
        transform: scale(1.05);
    }
    
    /* ISBN Badge - Left Side (Below Image) */
    .book-isbn-badge-left {
        width: 280px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.25);
        text-align: center;
        z-index: 10;
        border: 2px solid rgba(102, 126, 234, 0.15);
        transition: all 0.3s ease;
        animation: fadeInUp 0.8s ease-out 0.4s both;
        margin-bottom: 2rem;
    }

    .book-isbn-badge-left:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.35);
    }

    .book-isbn-badge-left .label {
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .book-isbn-badge-left .label i {
        font-size: 0.9rem;
    }

    .book-isbn-badge-left .value {
        font-size: 1.1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 800;
        font-family: 'Courier New', monospace;
        letter-spacing: 1px;
    }
    
    /* Decorative Elements Container */
    .decorative-elements {
        position: relative;
        width: 100%;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2rem;
    }
    
    /* Floating Book Icons */
    .floating-icon {
        position: absolute;
        font-size: 2rem;
        opacity: 0.15;
        animation: floatAround 15s infinite ease-in-out;
    }
    
    .floating-icon-1 {
        top: 10%;
        left: 15%;
        color: #667eea;
        animation-delay: 0s;
    }
    
    .floating-icon-2 {
        top: 50%;
        right: 20%;
        color: #764ba2;
        animation-delay: 5s;
    }
    
    .floating-icon-3 {
        bottom: 20%;
        left: 25%;
        color: #667eea;
        animation-delay: 10s;
    }
    
    @keyframes floatAround {
        0%, 100% {
            transform: translate(0, 0) rotate(0deg);
        }
        25% {
            transform: translate(15px, -15px) rotate(5deg);
        }
        50% {
            transform: translate(-10px, 10px) rotate(-5deg);
        }
        75% {
            transform: translate(10px, 15px) rotate(3deg);
        }
    }
    
    /* Reading Quote */
    .reading-quote {
        text-align: center;
        padding: 2rem;
        max-width: 280px;
        position: relative;
        z-index: 1;
        animation: fadeInUp 1s ease-out 0.8s both;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .quote-icon {
        font-size: 2rem;
        color: #667eea;
        opacity: 0.3;
        margin-bottom: 1rem;
    }
    
    .quote-text {
        font-size: 1.1rem;
        font-weight: 600;
        color: #4b5563;
        font-style: italic;
        line-height: 1.6;
        margin-bottom: 1rem;
        opacity: 0.7;
    }
    
    .quote-author {
        font-size: 0.9rem;
        color: #667eea;
        font-weight: 700;
        opacity: 0.8;
    }
    
    /* Decorative Circles */
    .deco-circle {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        animation: pulse 4s infinite ease-in-out;
    }
    
    .deco-circle-1 {
        width: 120px;
        height: 120px;
        top: 5%;
        right: 10%;
        animation-delay: 0s;
    }
    
    .deco-circle-2 {
        width: 80px;
        height: 80px;
        bottom: 15%;
        right: 15%;
        animation-delay: 1.5s;
    }
    
    .deco-circle-3 {
        width: 100px;
        height: 100px;
        top: 40%;
        left: 5%;
        animation-delay: 3s;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 0.3;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.5;
        }
    }
    
    /* Right Side - Book Details */
    .book-details-section {
        padding: 3.5rem;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .book-header-top {
        margin-bottom: 2.5rem;
    }
    
    .book-title {
        font-size: 2.8rem;
        font-weight: 900;
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }
    
    .book-author {
        font-size: 1.6rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .book-author::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    .book-publisher {
        font-size: 1.15rem;
        color: #6b7280;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* Meta Grid - Horizontal */
    .book-meta-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1.25rem;
        margin: 2.5rem 0;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-radius: 20px;
        border: 1px solid rgba(102, 126, 234, 0.1);
    }
    
    .meta-item-inline {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1rem;
        background: white;
        border-radius: 16px;
        transition: all 0.3s ease;
        border: 1px solid rgba(102, 126, 234, 0.05);
    }
    
    .meta-item-inline:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .meta-icon-inline {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .meta-label-inline {
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .meta-value-inline {
        font-size: 1.25rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 800;
    }
    
    /* Availability Banner */
    .availability-banner {
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 2.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .availability-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(45deg);
        animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    .availability-banner.available {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: 2px solid #6ee7b7;
        box-shadow: 0 10px 30px rgba(110, 231, 183, 0.3);
    }
    
    .availability-banner.unavailable {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: 2px solid #fca5a5;
        box-shadow: 0 10px 30px rgba(252, 165, 165, 0.3);
    }
    
    .availability-icon {
        font-size: 3rem;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }
    
    .availability-content {
        flex: 1;
        position: relative;
        z-index: 1;
    }
    
    .availability-title {
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        letter-spacing: -0.3px;
    }
    
    .availability-banner.available .availability-title {
        color: #065f46;
    }
    
    .availability-banner.unavailable .availability-title {
        color: #991b1b;
    }
    
    .availability-text {
        font-size: 1rem;
        font-weight: 600;
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
        margin-bottom: 2.5rem;
    }
    
    .section-title {
        font-size: 1.6rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid;
        border-image: linear-gradient(90deg, #667eea, #764ba2) 1;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .description-text {
        color: #4b5563;
        line-height: 1.9;
        font-size: 1.05rem;
        max-height: 220px;
        overflow-y: auto;
        padding-right: 1.5rem;
        font-weight: 500;
    }
    
    .description-text::-webkit-scrollbar {
        width: 8px;
    }
    
    .description-text::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 10px;
    }
    
    .description-text::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }
    
    .description-text::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8e 100%);
    }
    
    /* Action Section */
    .action-section {
        display: flex;
        gap: 1.25rem;
        flex-wrap: wrap;
        margin-top: auto;
    }
    
    .btn {
        flex: 1;
        min-width: 200px;
        padding: 1.25rem 2.5rem;
        border-radius: 16px;
        font-weight: 800;
        font-size: 1.1rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.3px;
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
        transition: width 0.6s ease, height 0.6s ease;
    }
    
    .btn:hover::before {
        width: 400px;
        height: 400px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    }
    
    .btn-primary:hover {
        transform: translateY(-5px);
        box-shadow: 0 18px 45px rgba(102, 126, 234, 0.6);
    }
    
    .btn-secondary {
        background: white;
        color: #374151;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        border: 2px solid #e5e7eb;
    }
    
    .btn-secondary:hover {
        background: #f9fafb;
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
        border-color: #667eea;
    }
    
    .btn-icon {
        font-size: 1.4rem;
        position: relative;
        z-index: 1;
    }
    
    .btn-text {
        position: relative;
        z-index: 1;
    }
    
    .waiting-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 5px solid #3b82f6;
        padding: 1.75rem;
        border-radius: 16px;
        margin-top: 1.5rem;
        display: flex;
        align-items: start;
        gap: 1.25rem;
        box-shadow: 0 5px 20px rgba(59, 130, 246, 0.2);
    }
    
    .waiting-info-icon {
        font-size: 2rem;
        color: #1e40af;
        flex-shrink: 0;
    }
    
    .waiting-info-text {
        color: #1e3a8a;
        line-height: 1.7;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .waiting-info-text strong {
        font-weight: 800;
    }
    
    /* Responsive Design */
    @media (max-width: 1100px) {
        .book-card {
            grid-template-columns: 350px 1fr;
        }
        
        .book-image-wrapper {
            width: 240px;
            height: 340px;
        }
        
        .book-isbn-badge-left {
            width: 240px;
        }
    }
    
    @media (max-width: 992px) {
        .book-card {
            grid-template-columns: 1fr;
        }
        
        .book-image-section {
            border-right: none;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            padding: 3rem 2rem;
        }
        
        .book-image-wrapper {
            width: 220px;
            height: 310px;
        }
        
        .book-isbn-badge-left {
            width: 220px;
        }
        
        .book-details-section {
            padding: 3rem 2.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .book-details-wrapper {
            padding: 2.5rem 0;
        }
        
        .book-details-container {
            padding: 0 1rem;
        }
        
        .book-title {
            font-size: 2.2rem;
        }
        
        .book-author {
            font-size: 1.3rem;
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
        
        .book-details-section {
            padding: 2.5rem 2rem;
        }
    }
    
    @media (max-width: 480px) {
        .book-meta-row {
            grid-template-columns: 1fr;
        }
        
        .book-title {
            font-size: 1.8rem;
        }
        
        .availability-banner {
            flex-direction: column;
            text-align: center;
        }
        
        .book-image-wrapper {
            width: 200px;
            height: 280px;
        }
        
        .book-isbn-badge-left {
            width: 200px;
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
                             class="book-image"
                             onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/images/no-book-cover.jpg'; if(this.complete && this.naturalHeight === 0) { this.style.display='none'; this.nextElementSibling.style.display='flex'; }">
                        <!-- Default SVG Fallback -->
                        <div class="default-book-cover" style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); align-items: center; justify-content: center; flex-direction: column; gap: 1rem; padding: 2rem;">
                            <i class="fas fa-book" style="font-size: 5rem; color: white; opacity: 0.9;"></i>
                            <div style="color: white; font-size: 1.2rem; font-weight: 700; text-align: center; opacity: 0.9;">No Cover Available</div>
                        </div>
                    <?php else: ?>
                        <img src="<?= BASE_URL ?>assets/images/no-book-cover.jpg" 
                             alt="Default book cover" 
                             class="book-image"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <!-- Default SVG Fallback -->
                        <div class="default-book-cover" style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); align-items: center; justify-content: center; flex-direction: column; gap: 1rem; padding: 2rem;">
                            <i class="fas fa-book" style="font-size: 5rem; color: white; opacity: 0.9;"></i>
                            <div style="color: white; font-size: 1.2rem; font-weight: 700; text-align: center; opacity: 0.9;">No Cover Available</div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- ISBN Badge - Below Book Image -->
                <div class="book-isbn-badge-left">
                    <div class="label">
                        <i class="fas fa-barcode"></i> ISBN
                    </div>
                    <div class="value"><?= htmlspecialchars($book['isbn']) ?></div>
                </div>
                
                <!-- Decorative Animated Elements -->
                <div class="decorative-elements">
                    <!-- Floating Book Icons -->
                    <div class="floating-icon floating-icon-1">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="floating-icon floating-icon-2">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <div class="floating-icon floating-icon-3">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    
                    <!-- Animated Reading Quote -->
                    <div class="reading-quote">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p class="quote-text">A room without books is like a body without a soul</p>
                        <span class="quote-author">— Cicero</span>
                    </div>
                    
                    <!-- Decorative Circles -->
                    <div class="deco-circle deco-circle-1"></div>
                    <div class="deco-circle deco-circle-2"></div>
                    <div class="deco-circle deco-circle-3"></div>
                </div>
            </div>
            
            <!-- Right Side - Book Details -->
            <div class="book-details-section">
                <div class="book-header-top">
                    <h1 class="book-title"><?= htmlspecialchars($book['bookName']) ?></h1>
                    <p class="book-author">
                        <?= htmlspecialchars($book['authorName']) ?>
                    </p>
                    <p class="book-publisher">
                        <i class="fas fa-building"></i>
                        <?= htmlspecialchars($book['publisherName']) ?>
                    </p>
                </div>
                
                <!-- Meta Information Row -->
                <div class="book-meta-row">
                    <?php if (!empty($book['category'])): ?>
                    <div class="meta-item-inline">
                        <i class="fas fa-folder-open meta-icon-inline"></i>
                        <div class="meta-label-inline">Category</div>
                        <div class="meta-value-inline"><?= htmlspecialchars($book['category']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['publicationYear'])): ?>
                    <div class="meta-item-inline">
                        <i class="fas fa-calendar-alt meta-icon-inline"></i>
                        <div class="meta-label-inline">Year</div>
                        <div class="meta-value-inline"><?= htmlspecialchars($book['publicationYear']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="meta-item-inline">
                        <i class="fas fa-books meta-icon-inline"></i>
                        <div class="meta-label-inline">Total Copies</div>
                        <div class="meta-value-inline"><?= $book['totalCopies'] ?></div>
                    </div>
                    
                    <div class="meta-item-inline">
                        <i class="fas fa-check-circle meta-icon-inline"></i>
                        <div class="meta-label-inline">Available</div>
                        <div class="meta-value-inline"><?= $book['available'] ?></div>
                    </div>
                </div>
                
                <!-- Availability Banner -->
                <div class="availability-banner <?= $book['available'] > 0 ? 'available' : 'unavailable' ?>">
                    <span class="availability-icon">
                        <?= $book['available'] > 0 ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?>
                    </span>
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
                    <h2 class="section-title">
                        <i class="fas fa-book-open"></i>
                        About This Book
                    </h2>
                    <p class="description-text"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="action-section">
                    <?php if ($book['available'] > 0): ?>
                        <form method="POST" action="<?= BASE_URL ?>faculty/reserve/<?= urlencode($book['isbn']) ?>" style="flex: 1; min-width: 200px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-bookmark btn-icon"></i>
                                <span class="btn-text">Reserve This Book</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="<?= BASE_URL ?>faculty/reserve/<?= urlencode($book['isbn']) ?>" style="flex: 1; min-width: 200px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-bell btn-icon"></i>
                                <span class="btn-text">Join Waiting List</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>faculty/books" class="btn btn-secondary">
                        <i class="fas fa-arrow-left btn-icon"></i>
                        <span class="btn-text">Back to Library</span>
                    </a>
                </div>
                
                <!-- Waiting List Info -->
                <?php if ($book['available'] == 0): ?>
                <div class="waiting-info">
                    <i class="fas fa-lightbulb waiting-info-icon"></i>
                    <div class="waiting-info-text">
                        <strong>Join the waiting list</strong> to be notified when this book becomes available. You'll receive an email notification as soon as a copy is returned.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Add Font Awesome if not already included
if (!document.querySelector('link[href*="font-awesome"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
    document.head.appendChild(link);
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>