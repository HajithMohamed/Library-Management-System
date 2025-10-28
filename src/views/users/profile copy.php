<?php
// Assuming session and other necessary initializations are already done

// Fetch user favorites from the database
$favorites = [];
if (in_array($_SESSION['userType'], ['Student', 'Faculty'])) {
    require_once __DIR__ . '/../../src/config/dbConnection.php';
    $stmt = $pdo->prepare("
        SELECT f.*, b.bookName, b.authorName, b.category, b.available, b.bookImage 
        FROM favorites f 
        JOIN books b ON f.isbn = b.isbn 
        WHERE f.userId = ? 
        ORDER BY f.createdAt DESC
    ");
    $stmt->execute([$_SESSION['userId']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- ...existing profile information... -->

<!-- Favorites Section -->
<?php if ($_SESSION['userType'] === 'Student' || $_SESSION['userType'] === 'Faculty'): ?>
<div class="profile-section">
    <div class="section-header">
        <h3>📚 My Reading List</h3>
        <?php if (!empty($favorites)): ?>
            <a href="/index.php?route=export-favorites" class="btn btn-outline">
                <i class="icon-download"></i> Export CSV
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <p>Your reading list is empty. Start adding books you love!</p>
            <a href="/index.php?route=books" class="btn btn-primary">Browse Books</a>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $fav): ?>
                <div class="favorite-card">
                    <div class="book-cover">
                        <img src="/uploads/books/<?php echo htmlspecialchars($fav['bookImage'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($fav['bookName']); ?>">
                        
                        <?php if ($fav['available'] > 0): ?>
                            <span class="availability-badge available">Available</span>
                        <?php else: ?>
                            <span class="availability-badge unavailable">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-details">
                        <h4><?php echo htmlspecialchars($fav['bookName']); ?></h4>
                        <p class="author"><?php echo htmlspecialchars($fav['authorName']); ?></p>
                        <span class="category-badge"><?php echo htmlspecialchars($fav['category']); ?></span>
                        
                        <div class="notes-section">
                            <form method="POST" action="/index.php?route=favorites&action=update">
                                <input type="hidden" name="isbn" value="<?php echo $fav['isbn']; ?>">
                                <textarea name="notes" rows="2" class="form-control" 
                                          placeholder="Add personal notes..."><?php echo htmlspecialchars($fav['notes'] ?? ''); ?></textarea>
                                <button type="submit" class="btn-small btn-primary">Save Notes</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <a href="/index.php?route=view-book&isbn=<?php echo $fav['isbn']; ?>" 
                           class="btn-small btn-outline">View Details</a>
                        <a href="/index.php?route=favorites&action=remove&isbn=<?php echo $fav['isbn']; ?>" 
                           class="btn-small btn-danger" 
                           onclick="return confirm('Remove from favorites?')">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ...existing code... -->