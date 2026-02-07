<?php
use App\Helpers\ImageHelper;
if (!isset($_SESSION['userId'])) { header("Location: /"); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites - ILS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="favorites-container">
        <div class="header-actions">
            <h1>📚 My Reading List</h1>
            <a href="/index.php?route=export-favorites" class="btn-primary">📥 Export CSV</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($favorites)): ?>
            <p class="empty-state">No favorites yet. Start adding books you love!</p>
        <?php else: ?>
            <table class="favorites-table">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Book Details</th>
                        <th>My Notes</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($favorites as $fav): ?>
                        <tr>
                            <td>
                                <?= ImageHelper::renderBookCover($fav['bookImage'] ?? null, 'Cover', 'book-thumb') ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($fav['bookName']); ?></strong><br>
                                <small><?php echo htmlspecialchars($fav['authorName']); ?></small><br>
                                <span class="badge"><?php echo htmlspecialchars($fav['category']); ?></span>
                                <?php if ($fav['available'] > 0): ?>
                                    <span class="badge success">Available</span>
                                <?php else: ?>
                                    <span class="badge danger">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="/index.php?route=favorites&action=update" class="inline-form">
                                    <input type="hidden" name="isbn" value="<?php echo $fav['isbn']; ?>">
                                    <textarea name="notes" rows="2" placeholder="Add notes..."><?php echo htmlspecialchars($fav['notes'] ?? ''); ?></textarea>
                                    <button type="submit" class="btn-small">Save</button>
                                </form>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($fav['createdAt'])); ?></td>
                            <td>
                                <a href="/index.php?route=book-details&isbn=<?php echo $fav['isbn']; ?>" class="btn-small">View</a>
                                <a href="/index.php?route=favorites&action=remove&isbn=<?php echo $fav['isbn']; ?>" 
                                   class="btn-small btn-danger" 
                                   onclick="return confirm('Remove from favorites?')">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
