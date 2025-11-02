<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Borrow History';
include APP_ROOT . '/views/layouts/header.php';

$borrowHistory = $borrowHistory ?? [];
?>

<style>
/* Hide scrollbars globally while maintaining scroll functionality */
body {
    overflow-x: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

body::-webkit-scrollbar {
    display: none;
}

/* Modern borrow history page */
.history-modern-bg {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 4rem 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.history-modern-bg::before {
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

.history-modern-bg::after {
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

.history-modern-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    border-radius: 32px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
    max-width: 1600px;
    width: 95%;
    margin: 0 auto;
    overflow: hidden;
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

.history-modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 2.5rem 3rem;
    border-radius: 32px 32px 0 0;
    position: relative;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
    text-align: center;
}

.history-modern-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.history-header-content {
    position: relative;
    z-index: 1;
}

.history-modern-header h1 {
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0 0 0.5rem 0;
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.history-modern-header p {
    font-size: 1rem;
    opacity: 0.95;
    margin: 0;
    font-weight: 500;
}

.history-modern-body {
    padding: 2.5rem 3rem;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.table-wrapper::-webkit-scrollbar {
    display: none;
}

.history-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.history-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: sticky;
    top: 0;
    z-index: 10;
}

.history-table th {
    padding: 1.25rem 1rem;
    text-align: left;
    color: white;
    font-weight: 800;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    white-space: nowrap;
}

.history-table th:first-child {
    border-radius: 20px 0 0 0;
}

.history-table th:last-child {
    border-radius: 0 20px 0 0;
}

.history-table tbody tr {
    background: white;
    transition: all 0.3s ease;
    border-bottom: 1px solid #f3f4f6;
}

.history-table tbody tr:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
    transform: scale(1.01);
}

.history-table td {
    padding: 1.25rem 1rem;
    font-weight: 600;
    color: #374151;
    border: none;
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
    display: inline-block;
}

.badge-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.badge-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.badge-danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
}

.btn-small {
    padding: 0.5rem 1rem;
    border-radius: 10px;
    border: none;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 5rem;
    margin-bottom: 2rem;
    opacity: 0.4;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.empty-state h4 {
    font-size: 2rem;
    font-weight: 900;
    margin-bottom: 1rem;
    color: #1f2937;
}

.empty-state p {
    font-size: 1.2rem;
    margin-bottom: 2.5rem;
    color: #6b7280;
}

/* Review Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 2rem;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 800;
}

.close-btn {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: #6b7280;
    transition: color 0.3s;
}

.close-btn:hover {
    color: #ef4444;
}

.modal-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 700;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.required {
    color: #ef4444;
}

.modal-footer {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn-secondary:hover {
    background: #d1d5db;
}

@media (max-width: 768px) {
    .history-modern-header,
    .history-modern-body {
        padding: 2rem 1.5rem;
    }
    
    .history-table th,
    .history-table td {
        padding: 1rem 0.75rem;
        font-size: 0.9rem;
    }
}
</style>

<div class="history-modern-bg">
    <div class="history-modern-card">
        <div class="history-modern-header">
            <div class="history-header-content">
                <h1>
                    <i class="fas fa-history"></i>
                    Borrow History
                </h1>
                <p>Track all your past and current book borrowings</p>
            </div>
        </div>
        
        <div class="history-modern-body">
            <?php if (!empty($borrowHistory)): ?>
                <div class="table-wrapper">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book"></i> Book Name</th>
                                <th><i class="fas fa-user-edit"></i> Author</th>
                                <th><i class="fas fa-calendar-plus"></i> Borrow Date</th>
                                <th><i class="fas fa-calendar-check"></i> Return Date</th>
                                <th><i class="fas fa-money-bill-wave"></i> Fine</th>
                                <th><i class="fas fa-star"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrowHistory as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['bookName']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['authorName'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($transaction['borrowDate'])); ?></td>
                                    <td>
                                        <?php if ($transaction['returnDate']): ?>
                                            <?php echo date('M d, Y', strtotime($transaction['returnDate'])); ?>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Not Returned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($transaction['fineAmount'] > 0): ?>
                                            <span class="fine-amount">₹<?php echo number_format($transaction['fineAmount'], 2); ?></span>
                                            <span class="badge <?php echo $transaction['fineStatus'] === 'Paid' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $transaction['fineStatus']; ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($transaction['returnDate'] && $_SESSION['userType'] === 'Student'): ?>
                                            <?php
                                            global $mysqli;
                                            $stmt = $mysqli->prepare("SELECT id FROM book_reviews WHERE userId = ? AND isbn = ?");
                                            $stmt->bind_param("ss", $_SESSION['userId'], $transaction['isbn']);
                                            $stmt->execute();
                                            $hasReview = $stmt->fetch();
                                            $stmt->close();
                                            ?>
                                            
                                            <?php if (!$hasReview): ?>
                                                <button class="btn-small btn-primary" 
                                                        onclick="showReviewModal('<?php echo $transaction['isbn']; ?>', '<?php echo htmlspecialchars(addslashes($transaction['bookName'])); ?>')">
                                                    ⭐ Rate & Review
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-success">✓ Reviewed</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>No Borrow History</h4>
                    <p>You haven't borrowed any books yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="modal" style="display:none;">
    <div class="modal-overlay" onclick="closeReviewModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Rate & Review</h3>
            <button class="close-btn" onclick="closeReviewModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <p class="book-title" id="review-book-title" style="font-weight: 700; color: #667eea; margin-bottom: 1.5rem;"></p>
            
            <form method="POST" action="<?= BASE_URL ?>user/submit-review">
                <input type="hidden" name="isbn" id="review-isbn">
                
                <div class="form-group">
                    <label>Your Rating <span class="required">*</span></label>
                    <select name="rating" required class="form-control">
                        <option value="">Select rating...</option>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                        <option value="4">⭐⭐⭐⭐ Good</option>
                        <option value="3">⭐⭐⭐ Average</option>
                        <option value="2">⭐⭐ Poor</option>
                        <option value="1">⭐ Very Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Your Review (Optional)</label>
                    <textarea name="reviewText" rows="4" class="form-control" 
                              placeholder="Share your thoughts about this book..." 
                              maxlength="500"></textarea>
                    <small class="form-text" style="color: #6b7280;">Maximum 500 characters</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showReviewModal(isbn, bookName) {
    document.getElementById('review-isbn').value = isbn;
    document.getElementById('review-book-title').textContent = bookName;
    document.getElementById('reviewModal').style.display = 'block';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
