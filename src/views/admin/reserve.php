<?php
// ...existing header...
$book = $book ?? [];
?>

<div class="container" style="max-width: 600px; margin: 40px auto;">
    <h2>Reserve Book</h2>
    <div class="card" style="padding: 24px; border-radius: 12px; background: #fff;">
        <h3><?= htmlspecialchars($book['bookName'] ?? '') ?></h3>
        <p>Author: <?= htmlspecialchars($book['authorName'] ?? '') ?></p>
        <p>ISBN: <?= htmlspecialchars($book['isbn'] ?? '') ?></p>
        <form method="POST">
            <p>
                Are you sure you want to reserve this book? Reservation is valid for <b>1 day</b> and requires admin approval.
            </p>
            <button type="submit" class="btn btn-primary">Send Reservation Request</button>
            <a href="<?= BASE_URL ?>admin/books" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
