<?php
$pageTitle = 'Returned Books';
include APP_ROOT . '/views/layouts/header.php';

$returnedBooks = $returnedBooks ?? [];
?>

<style>
    body {
        overflow-x: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        margin: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    body::-webkit-scrollbar {
        display: none;
    }
    .return-wrapper {
        min-height: 100vh;
        padding: 4rem 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .return-wrapper::before {
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
    .return-wrapper::after {
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
    .return-container {
        max-width: 1100px;
        width: 98%;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    .return-header {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 32px 32px 0 0;
        padding: 2.5rem 3rem;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
        animation: slideInDown 0.6s ease-out;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-bottom: none;
    }
    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-30px);}
        to { opacity: 1; transform: translateY(0);}
    }
    .header-content {
        display: inline-block;
    }
    .header-title h1 {
        font-size: 2.2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        letter-spacing: -0.5px;
    }
    .header-title h1 i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .header-title p {
        color: #6b7280;
        font-size: 1rem;
        margin: 0;
        font-weight: 500;
    }
    .books-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: #667eea;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.95rem;
        border: 2px solid rgba(102, 126, 234, 0.3);
        margin-top: 1rem;
    }
    .return-body {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 0 0 32px 32px;
        padding: 2.5rem 3rem 3rem;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
        animation: slideInUp 0.6s ease-out 0.2s both;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-top: none;
    }
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px);}
        to { opacity: 1; transform: translateY(0);}
    }
    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }
    .book-card {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-radius: 20px;
        padding: 2rem;
        transition: all 0.3s ease;
        border: 2px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
    }
    .book-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }
    .book-card:hover::before {
        transform: scaleX(1);
    }
    .book-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
        border-color: rgba(102, 126, 234, 0.3);
        background: white;
    }
    .book-info {
        margin-bottom: 1.5rem;
    }
    .book-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    .book-author {
        font-size: 1.05rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }
    .book-author::before {
        content: 'by ';
        font-weight: 500;
        color: #6b7280;
        background: none;
        -webkit-text-fill-color: #6b7280;
    }
    .book-isbn {
        font-size: 0.9rem;
        color: #9ca3af;
        font-family: 'Courier New', monospace;
        font-weight: 600;
    }
    .book-isbn::before {
        content: '📖 ISBN: ';
        font-weight: 700;
    }
    .book-meta {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1.25rem;
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(102, 126, 234, 0.1);
    }
    .meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.95rem;
    }
    .meta-label {
        color: #6b7280;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .meta-value {
        color: #1f2937;
        font-weight: 800;
    }
    .meta-value.ok {
        color: #10b981;
    }
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        animation: fadeIn 0.6s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9);}
        to { opacity: 1; transform: scale(1);}
    }
    .empty-state-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 50%;
        font-size: 4rem;
        animation: pulse 2s ease-in-out infinite;
    }
    .empty-state-icon i {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1);}
        50% { transform: scale(1.05);}
    }
    .empty-state h4 {
        font-size: 2rem;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 1rem;
    }
    .empty-state p {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 450px;
        margin: 0 auto 2.5rem;
        line-height: 1.7;
        font-weight: 500;
    }
    .browse-btn {
        padding: 1.25rem 2.5rem;
        border-radius: 16px;
        border: none;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.3);
        font-size: 1.1rem;
        position: relative;
        overflow: hidden;
    }
    .browse-btn::before {
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
    .browse-btn:hover::before {
        width: 400px;
        height: 400px;
    }
    .browse-btn i,
    .browse-btn span {
        position: relative;
        z-index: 1;
    }
    .browse-btn:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 18px 45px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .info-alert {
        padding: 1.5rem;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 5px solid #3b82f6;
        border-radius: 16px;
        margin-bottom: 2.5rem;
        display: flex;
        gap: 1rem;
        align-items: start;
        box-shadow: 0 5px 20px rgba(59, 130, 246, 0.2);
    }
    .info-alert-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #3b82f6;
        color: white;
        border-radius: 12px;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .info-alert-content h5 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: 0.5rem;
    }
    .info-alert-content p {
        color: #1e40af;
        margin: 0;
        font-size: 1rem;
        line-height: 1.6;
        font-weight: 600;
    }
    @media (max-width: 1200px) {
        .return-container {
            max-width: 95%;
        }
    }
    @media (max-width: 768px) {
        .return-wrapper {
            padding: 2rem 1rem;
        }
        .return-container {
            width: 100%;
        }
        .return-header {
            padding: 2rem 1.5rem;
        }
        .return-body {
            padding: 2rem 1.5rem;
        }
        .header-title h1 {
            font-size: 1.8rem;
        }
        .books-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 480px) {
        .return-header {
            padding: 1.5rem;
        }
        .return-body {
            padding: 1.5rem;
        }
        .header-title h1 {
            font-size: 1.5rem;
            flex-direction: column;
            gap: 0.5rem;
        }
        .book-card {
            padding: 1.5rem;
        }
    }
</style>

<div class="return-wrapper">
    <div class="return-container">
        <!-- Header -->
        <div class="return-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>
                        <i class="fas fa-undo"></i>
                        Returned Books
                    </h1>
                    <p>Books you have successfully returned to the library</p>
                </div>
                <?php if (!empty($returnedBooks)): ?>
                    <div class="books-count-badge">
                        <i class="fas fa-book"></i>
                        <span><?= count($returnedBooks) ?> <?= count($returnedBooks) === 1 ? 'Book' : 'Books' ?> Returned</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Body -->
        <div class="return-body">
            <?php if (!empty($returnedBooks)): ?>
                <div class="info-alert">
                    <div class="info-alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="info-alert-content">
                        <h5>Successfully Returned</h5>
                        <p>Thank you for returning your books on time! Below is the complete history of your returned books.</p>
                    </div>
                </div>
                <div class="books-grid">
                    <?php foreach ($returnedBooks as $book): ?>
                        <div class="book-card">
                            <div class="book-info">
                                <h3 class="book-title"><?= htmlspecialchars($book['bookName'] ?? $book['title'] ?? 'Unknown Book') ?></h3>
                                <p class="book-author"><?= htmlspecialchars($book['authorName'] ?? $book['author'] ?? 'Unknown Author') ?></p>
                                <p class="book-isbn"><?= htmlspecialchars($book['isbn'] ?? '') ?></p>
                            </div>
                            <div class="book-meta">
                                <div class="meta-row">
                                    <span class="meta-label"><i class="fas fa-calendar-check"></i> Returned</span>
                                    <span class="meta-value ok"><?= $book['returnDate'] ? date('M d, Y', strtotime($book['returnDate'])) : '' ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4>No Books Returned Yet</h4>
                    <p>You haven't returned any books yet. Once you return books, they will appear here with complete transaction details.</p>
                    <a href="<?= BASE_URL ?>user/books" class="browse-btn">
                        <i class="fas fa-book"></i>
                        <span>Browse Books</span>
                    </a>
                </div>
            <?php endif; ?>
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
