<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Return Books';
include APP_ROOT . '/views/layouts/header.php';

$borrowedBooks = $borrowedBooks ?? [];
?>

<style>
    .return-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
    }
    
    .return-wrapper::before {
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
    
    .return-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }
    
    /* Header Section */
    .return-header {
        background: white;
        border-radius: 30px 30px 0 0;
        padding: 2.5rem 3rem;
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.1);
        animation: slideInDown 0.6s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    
    .header-title h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .header-title h1 i {
        color: #667eea;
    }
    
    .header-title p {
        color: #6b7280;
        font-size: 1.1rem;
        margin: 0;
    }
    
    .header-actions {
        display: flex;
        gap: 1rem;
    }
    
    .back-button {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    
    .back-button:hover {
        background: #667eea;
        color: white;
        transform: translateX(-5px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .back-button i {
        transition: transform 0.3s ease;
    }
    
    .back-button:hover i {
        transform: translateX(-3px);
    }
    
    .books-count {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: #667eea;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        border: 2px solid #667eea;
    }
    
    /* Books Body */
    .return-body {
        background: white;
        border-radius: 0 0 30px 30px;
        padding: 2rem 3rem 3rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideInUp 0.6s ease-out 0.2s both;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Book Cards Grid */
    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }
    
    .book-card {
        background: #f9fafb;
        border-radius: 20px;
        padding: 1.75rem;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .book-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }
    
    .book-card:hover::before {
        transform: scaleX(1);
    }
    
    .book-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        border-color: rgba(102, 126, 234, 0.3);
        background: white;
    }
    
    .book-info {
        margin-bottom: 1.5rem;
    }
    
    .book-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .book-author {
        font-size: 1rem;
        color: #667eea;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    .book-author::before {
        content: 'by ';
        font-weight: 400;
        color: #6b7280;
    }
    
    .book-isbn {
        font-size: 0.85rem;
        color: #9ca3af;
        font-family: 'Courier New', monospace;
    }
    
    .book-isbn::before {
        content: 'ISBN: ';
        font-weight: 600;
    }
    
    /* Book Meta */
    .book-meta {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: white;
        border-radius: 12px;
    }
    
    .meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }
    
    .meta-label {
        color: #6b7280;
        font-weight: 600;
    }
    
    .meta-value {
        color: #1f2937;
        font-weight: 700;
    }
    
    .meta-value.overdue {
        color: #ef4444;
    }
    
    .meta-value.due-soon {
        color: #f59e0b;
    }
    
    .meta-value.ok {
        color: #10b981;
    }
    
    /* Return Button */
    .return-btn {
        width: 100%;
        padding: 0.875rem 1.5rem;
        border-radius: 12px;
        border: none;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }
    
    .return-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.4);
    }
    
    .return-btn:active {
        transform: translateY(0);
    }
    
    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    .status-badge.overdue {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
        color: #ef4444;
        border: 2px solid rgba(239, 68, 68, 0.3);
    }
    
    .status-badge.due-soon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        color: #f59e0b;
        border: 2px solid rgba(245, 158, 11, 0.3);
    }
    
    .status-badge.ok {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        color: #10b981;
        border: 2px solid rgba(16, 185, 129, 0.3);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
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
        font-size: 3.5rem;
        color: #667eea;
    }
    
    .empty-state h4 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.75rem;
    }
    
    .empty-state p {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 400px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }
    
    .browse-btn {
        padding: 1rem 2rem;
        border-radius: 12px;
        border: none;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }
    
    .browse-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    /* Info Alert */
    .info-alert {
        padding: 1.25rem;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(6, 182, 212, 0.05));
        border-left: 4px solid #3b82f6;
        border-radius: 12px;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        align-items: start;
    }
    
    .info-alert-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border-radius: 10px;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .info-alert-content h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    
    .info-alert-content p {
        color: #6b7280;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .return-wrapper {
            padding: 2rem 0;
        }
        
        .return-container {
            padding: 0 1rem;
        }
        
        .return-header {
            padding: 2rem 1.5rem;
        }
        
        .return-body {
            padding: 1.5rem;
        }
        
        .header-content {
            flex-direction: column;
            align-items: start;
        }
        
        .header-title h1 {
            font-size: 2rem;
        }
        
        .header-actions {
            width: 100%;
            flex-direction: column;
        }
        
        .back-button,
        .books-count {
            width: 100%;
            justify-content: center;
        }
        
        .books-grid {
            grid-template-columns: 1fr;
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
                        Return Books
                    </h1>
                    <p>Select books to return to the library</p>
                </div>
                <div class="header-actions">
                    <a href="<?= BASE_URL ?>faculty/dashboard" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
                    <?php if (!empty($borrowedBooks)): ?>
                        <span class="books-count">
                            <i class="fas fa-book"></i>
                            <?= count($borrowedBooks) ?> <?= count($borrowedBooks) === 1 ? 'Book' : 'Books' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="return-body">
            <?php if (!empty($borrowedBooks)): ?>
                <!-- Info Alert -->
                <div class="info-alert">
                    <div class="info-alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="info-alert-content">
                        <h5>Return Policy</h5>
                        <p>Please ensure books are returned in good condition. Late returns may incur fines. Check the due date for each book below.</p>
                    </div>
                </div>

                <!-- Books Grid -->
                <div class="books-grid">
                    <?php foreach ($borrowedBooks as $book): 
                        $borrowDate = strtotime($book['borrowDate']);
                        $dueDate = strtotime('+14 days', $borrowDate);
                        $today = time();
                        $daysUntilDue = ceil(($dueDate - $today) / (60 * 60 * 24));
                        
                        if ($daysUntilDue < 0) {
                            $statusClass = 'overdue';
                            $statusText = abs($daysUntilDue) . ' days overdue';
                            $statusIcon = '⚠️';
                        } elseif ($daysUntilDue <= 3) {
                            $statusClass = 'due-soon';
                            $statusText = 'Due in ' . $daysUntilDue . ' ' . ($daysUntilDue == 1 ? 'day' : 'days');
                            $statusIcon = '⏰';
                        } else {
                            $statusClass = 'ok';
                            $statusText = 'Due in ' . $daysUntilDue . ' days';
                            $statusIcon = '✅';
                        }
                    ?>
                        <div class="book-card">
                            <span class="status-badge <?= $statusClass ?>">
                                <?= $statusIcon ?> <?= $statusText ?>
                            </span>
                            
                            <div class="book-info">
                                <h3 class="book-title"><?= htmlspecialchars($book['title'] ?? $book['bookName'] ?? 'Unknown Book') ?></h3>
                                <p class="book-author"><?= htmlspecialchars($book['author'] ?? $book['authorName'] ?? 'Unknown Author') ?></p>
                                <p class="book-isbn"><?= htmlspecialchars($book['isbn']) ?></p>
                            </div>
                            
                            <div class="book-meta">
                                <div class="meta-row">
                                    <span class="meta-label">Borrowed</span>
                                    <span class="meta-value"><?= date('M d, Y', $borrowDate) ?></span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">Due Date</span>
                                    <span class="meta-value <?= $statusClass ?>"><?= date('M d, Y', $dueDate) ?></span>
                                </div>
                                <?php if ($daysUntilDue < 0): ?>
                                <div class="meta-row">
                                    <span class="meta-label">Fine</span>
                                    <span class="meta-value overdue">₹<?= abs($daysUntilDue) * 5 ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" action="<?= BASE_URL ?>faculty/return">
                                <input type="hidden" name="borrow_id" value="<?= htmlspecialchars($book['tid'] ?? $book['id'] ?? '') ?>">
                                <button type="submit" class="return-btn">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Return Book</span>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4>No Books to Return</h4>
                    <p>You don't have any borrowed books at the moment. Browse the library to find your next read!</p>
                    <a href="<?= BASE_URL ?>faculty/books" class="browse-btn">
                        <i class="fas fa-book"></i>
                        <span>Browse Books</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
