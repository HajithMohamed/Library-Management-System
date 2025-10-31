<?php
include APP_ROOT . '/views/layouts/header.php';
$book = $book ?? [];
?>

<style>
.reserve-bg {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 0;
}
.reserve-card {
    background: #fff;
    border-radius: 28px;
    box-shadow: 0 16px 48px rgba(102, 126, 234, 0.18);
    max-width: 480px;
    width: 100%;
    padding: 2.5rem 2.5rem 2rem 2.5rem;
    margin: 0 auto;
    animation: fadeInReserve 0.7s cubic-bezier(.4,0,.2,1);
}
@keyframes fadeInReserve {
    from { opacity: 0; transform: translateY(40px);}
    to { opacity: 1; transform: translateY(0);}
}
.reserve-title {
    font-size: 2rem;
    font-weight: 900;
    color: #667eea;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}
.reserve-book-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
}
.reserve-book-meta {
    color: #6b7280;
    font-size: 1.05rem;
    margin-bottom: 0.2rem;
}
.reserve-book-meta strong {
    color: #667eea;
    font-weight: 700;
}
.reserve-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-left: 4px solid #667eea;
    border-radius: 12px;
    padding: 1.1rem 1.2rem;
    margin: 1.5rem 0 2rem 0;
    color: #374151;
    font-size: 1.05rem;
    line-height: 1.7;
}
.reserve-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}
.reserve-btn {
    flex: 1;
    padding: 1rem 0;
    border-radius: 12px;
    border: none;
    font-weight: 800;
    font-size: 1.08rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.13);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
    text-decoration: none;
}
.reserve-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.reserve-btn-primary:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px) scale(1.03);
}
.reserve-btn-secondary {
    background: #f3f4f6;
    color: #667eea;
    border: 2px solid #667eea;
}
.reserve-btn-secondary:hover {
    background: #e5e7eb;
    color: #764ba2;
    transform: translateY(-2px) scale(1.03);
}
@media (max-width: 600px) {
    .reserve-card { padding: 1.2rem; }
    .reserve-title { font-size: 1.2rem; }
    .reserve-book-title { font-size: 1.1rem; }
    .reserve-actions { flex-direction: column; gap: 0.7rem; }
}
</style>

<div class="reserve-bg">
    <div class="reserve-card">
        <div class="reserve-title">
            <i class="fas fa-bookmark"></i> Reserve Book
        </div>
        <div class="reserve-book-title"><?= htmlspecialchars($book['bookName'] ?? '') ?></div>
        <div class="reserve-book-meta"><strong>Author:</strong> <?= htmlspecialchars($book['authorName'] ?? '') ?></div>
        <div class="reserve-book-meta"><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn'] ?? '') ?></div>
        <form method="POST">
            <div class="reserve-info">
                <i class="fas fa-info-circle" style="color:#667eea; margin-right:0.5rem;"></i>
                Are you sure you want to reserve this book? Reservation is valid for <b>1 day</b> and requires admin approval.
            </div>
            <div class="reserve-actions">
                <button type="submit" class="reserve-btn reserve-btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Reservation Request
                </button>
                <a href="<?= BASE_URL ?>faculty/books" class="reserve-btn reserve-btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
