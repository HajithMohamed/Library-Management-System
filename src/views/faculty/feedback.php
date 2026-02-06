<?php
require_once __DIR__ . '/../partials/header.php';

/**
 * @var array $user
 * @var array $feedbacks
 * @var array $errors
 * @var string $success
 */

// Assume $pdo is available from includes
// Query for borrowed books for the form
$stmt = $pdo->prepare("
    SELECT DISTINCT b.isbn, b.bookName 
    FROM books_borrowed bb
    JOIN books b ON bb.isbn = b.isbn
    WHERE bb.userId = ? AND bb.returnDate IS NOT NULL
    ORDER BY b.bookName
");
$stmt->execute([$_SESSION['userId']]);
$borrowedBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query for user's previous feedbacks with book name
$stmt = $pdo->prepare("
    SELECT f.*, b.bookName 
    FROM feedback f 
    LEFT JOIN books b ON f.isbn = b.isbn 
    WHERE f.userId = ? 
    ORDER BY f.createdAt DESC
");
$stmt->execute([$_SESSION['userId']]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query for department feedback summary
$stmt = $pdo->prepare("
    SELECT department, COUNT(*) as suggestion_count, AVG(rating) as avg_rating 
    FROM feedback 
    WHERE is_suggestion = 1 
    GROUP BY department
");
$stmt->execute();
$deptFeedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container">
    <h1>📝 Submit Book Feedback & Suggestions</h1>
    <hr>

    <?php if (isset($success)) : ?>
        <div class="alert alert-success">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors) && !empty($errors)) : ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="/faculty/feedback" class="faculty-feedback-form">
                <div class="form-group">
                    <label>Select Book <span class="required">*</span></label>
                    <select name="isbn" required class="form-control">
                        <option value="">Choose a book...</option>
                        <?php foreach ($borrowedBooks as $book): ?>
                            <option value="<?php echo $book['isbn']; ?>">
                                <?php echo htmlspecialchars($book['bookName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Rating <span class="required">*</span></label>
                    <select name="rating" required class="form-control">
                        <option value="">Select rating...</option>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent - Highly Recommended</option>
                        <option value="4">⭐⭐⭐⭐ Good - Recommended</option>
                        <option value="3">⭐⭐⭐ Average - Suitable</option>
                        <option value="2">⭐⭐ Poor - Not Recommended</option>
                        <option value="1">⭐ Very Poor - Not Suitable</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="is_suggestion" id="is_suggestion" 
                               onchange="toggleDepartmentField()">
                        <span>This is a curriculum suggestion for my department</span>
                    </label>
                </div>
                
                <div class="form-group" id="dept-field" style="display:none;">
                    <label>Department <span class="required">*</span></label>
                    <select name="department" class="form-control">
                        <option value="Computer Science">Computer Science</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Business Administration">Business Administration</option>
                        <option value="Arts & Humanities">Arts & Humanities</option>
                        <option value="Natural Sciences">Natural Sciences</option>
                        <option value="Social Sciences">Social Sciences</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Education">Education</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Your Feedback/Suggestion <span class="required">*</span></label>
                    <textarea name="reviewText" rows="6" required class="form-control" 
                              placeholder="Share your detailed thoughts, how this book could benefit students, curriculum relevance, etc."
                              maxlength="1000"></textarea>
                    <small class="form-text">Maximum 1000 characters</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        </div>
        <div class="col-md-6">
            <h3>📈 Department Feedback Summary</h3>
            
            <?php if (!empty($deptFeedback)): ?>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Suggestions</th>
                            <th>Avg Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deptFeedback as $dept): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['department']); ?></td>
                                <td><span class="badge badge-info"><?php echo $dept['suggestion_count']; ?></span></td>
                                <td><span class="rating-display"><?php echo number_format($dept['avg_rating'], 1); ?>⭐</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No department suggestions yet</p>
            <?php endif; ?>

            <h3>Your Previous Feedback</h3>
            <?php if (empty($feedbacks)) : ?>
                <p>No feedback submitted yet.</p>
            <?php else : ?>
                <ul class="list-group">
                    <?php foreach ($feedbacks as $feedback) : ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($feedback['bookName'] ?? 'N/A') ?> - <?= $feedback['rating'] ?>/5</strong>
                            <?php if (!empty($feedback['is_suggestion']) && $feedback['is_suggestion']): ?>
                                <small class="text-muted d-block">Suggestion for: <?= htmlspecialchars($feedback['department']) ?></small>
                            <?php endif; ?>
                            <p><?= htmlspecialchars($feedback['reviewText'] ?? $feedback['message'] ?? '') ?></p>
                            <small class="text-muted">Submitted on: <?= date('Y-m-d H:i', strtotime($feedback['createdAt'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleDepartmentField() {
    const checkbox = document.getElementById('is_suggestion');
    const deptField = document.getElementById('dept-field');
    const deptSelect = deptField.querySelector('select');
    
    if (checkbox.checked) {
        deptField.style.display = 'block';
        deptSelect.required = true;
    } else {
        deptField.style.display = 'none';
        deptSelect.required = false;
    }
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>