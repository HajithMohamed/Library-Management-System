<?php
// Fetch transactions for the user
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE userId = ? ORDER BY borrowDate DESC");
$stmt->execute([$_SESSION['userId']]);
$transactions = $stmt->fetchAll();
?>

<h2>Your Book History</h2>

<table>
    <tr>
        <th>Book Name</th>
        <th>Borrow Date</th>
        <th>Return Date</th>
        <th>Action</th>
    </tr>
    <?php foreach ($transactions as $transaction): ?>
        <tr>
            <td><?php echo htmlspecialchars($transaction['bookName']); ?></td>
            <td><?php echo htmlspecialchars($transaction['borrowDate']); ?></td>
            <td><?php echo htmlspecialchars($transaction['returnDate'] ?? 'Not Returned'); ?></td>
            <td>
                <?php if ($transaction['returnDate']): ?>
                    <?php
                    // Check if already reviewed
                    $stmt = $pdo->prepare("SELECT id FROM book_reviews WHERE userId = ? AND isbn = ?");
                    $stmt->execute([$_SESSION['userId'], $transaction['isbn']]);
                    $hasReview = $stmt->fetch();
                    ?>
                    
                    <?php if (!$hasReview): ?>
                        <button class="btn-small" onclick="showReviewModal('<?php echo $transaction['isbn']; ?>')">
                            ⭐ Rate & Review
                        </button>
                    <?php else: ?>
                        <span class="badge">✓ Reviewed</span>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Review Modal -->
<div id="reviewModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Rate & Review Book</h3>
        <form method="POST" action="/index.php?route=submit-review">
            <input type="hidden" name="isbn" id="review-isbn">
            
            <div class="rating-input">
                <label>Rating:</label>
                <select name="rating" required>
                    <option value="">Select...</option>
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Good</option>
                    <option value="3">⭐⭐⭐ Average</option>
                    <option value="2">⭐⭐ Poor</option>
                    <option value="1">⭐ Very Poor</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Review (optional):</label>
                <textarea name="reviewText" rows="4" maxlength="500"></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Submit Review</button>
            <button type="button" class="btn-secondary" onclick="closeReviewModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function showReviewModal(isbn) {
    document.getElementById('review-isbn').value = isbn;
    document.getElementById('reviewModal').style.display = 'block';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}
</script>