<?php
// Assuming a connection to the database is already established

// Fetch the borrow history of the user
$stmt = $pdo->prepare("SELECT b.name as bookName, a.name as authorName, bh.borrow_date as borrowDate, bh.return_date as returnDate, bh.fine_amount as fineAmount, bh.fine_status as fineStatus, b.isbn FROM borrow_history bh JOIN books b ON bh.book_id = b.id JOIN authors a ON b.author_id = a.id WHERE bh.user_id = ?");
$stmt->execute([$_SESSION['userId']]);
$borrowHistory = $stmt->fetchAll();
?>

<!-- ...existing header... -->

<div class="history-container">
    <h2>Borrow History</h2>
    
    <table class="history-table">
        <thead>
            <tr>
                <th>Book Name</th>
                <th>Author</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Fine</th>
                <th>Actions</th>
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
                            <span class="fine-amount">$<?php echo number_format($transaction['fineAmount'], 2); ?></span>
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
                            $stmt = $pdo->prepare("SELECT id FROM book_reviews WHERE userId = ? AND isbn = ?");
                            $stmt->execute([$_SESSION['userId'], $transaction['isbn']]);
                            $hasReview = $stmt->fetch();
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

<!-- Review Modal -->
<div id="reviewModal" class="modal" style="display:none;">
    <div class="modal-overlay" onclick="closeReviewModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Rate & Review</h3>
            <button class="close-btn" onclick="closeReviewModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <p class="book-title" id="review-book-title"></p>
            
            <form method="POST" action="/index.php?route=submit-review">
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
                    <small class="form-text">Maximum 500 characters</small>
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

<!-- ...existing footer... -->