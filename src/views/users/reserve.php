<?php
$pageTitle = 'Reserve Book';
include APP_ROOT . '/views/layouts/header.php';

// Check if user is logged in - REMOVED STRICT USER TYPE CHECK
if (!isset($_SESSION['userId'])) {
    header('Location: ' . BASE_URL . 'login');
    exit();
}

// Allow both Student and Faculty to access this page
$userType = $_SESSION['userType'] ?? '';
$allowedTypes = ['student', 'faculty'];
if (!in_array(strtolower($userType), $allowedTypes)) {
    $_SESSION['error'] = 'Access denied. Only students and faculty can reserve books.';
    header('Location: ' . BASE_URL . 'user/dashboard');
    exit();
}
?>

<style>
  .reserve-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    animation: fadeIn 0.6s ease-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .reserve-card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  }

  .reserve-header {
    text-align: center;
    margin-bottom: 30px;
  }

  .reserve-header h2 {
    color: #667eea;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 10px;
  }

  .book-details {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
  }

  .book-details h3 {
    font-size: 1.5rem;
    color: #1f2937;
    margin: 0 0 20px 0;
  }

  .detail-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 20px;
  }

  .detail-item {
    margin: 0;
  }

  .detail-item strong {
    color: #374151;
    font-weight: 600;
  }

  .alert {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 4px solid;
  }

  .alert-warning {
    background: #fff3cd;
    border-color: #ffc107;
    color: #856404;
  }

  .alert-info {
    background: #d4edda;
    border-left-color: #28a745;
    color: #155724;
  }

  .alert strong {
    display: block;
    margin-bottom: 10px;
    font-size: 1.1rem;
  }

  .alert ul {
    margin: 10px 0 0 20px;
    padding: 0;
  }

  .alert ul li {
    margin-bottom: 8px;
  }

  .btn-group {
    display: flex;
    gap: 15px;
    margin-top: 30px;
  }

  .btn {
    flex: 1;
    padding: 15px 40px;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
  }

  .btn-secondary {
    background: #6c757d;
    color: white;
  }

  .btn-secondary:hover {
    background: #5a6268;
  }

  @media (max-width: 768px) {
    .reserve-container {
      padding: 15px;
    }

    .reserve-card {
      padding: 25px;
    }

    .detail-row {
      grid-template-columns: 1fr;
    }

    .btn-group {
      flex-direction: column;
    }
  }
</style>

<div class="reserve-container">
  <div class="reserve-card">
    <div class="reserve-header">
      <h2>📚 Reserve Book</h2>
    </div>

    <div class="book-details">
      <h3><?= htmlspecialchars($book['bookName']) ?></h3>
      <div class="detail-row">
        <p class="detail-item"><strong>Author:</strong> <?= htmlspecialchars($book['authorName']) ?></p>
        <p class="detail-item"><strong>Publisher:</strong> <?= htmlspecialchars($book['publisherName']) ?></p>
        <p class="detail-item"><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn']) ?></p>
        <p class="detail-item"><strong>Available:</strong> <?= $book['available'] ?> / <?= $book['totalCopies'] ?></p>
      </div>
    </div>

    <?php if (isset($existingReservation) && $existingReservation): ?>
      <div class="alert alert-warning">
        <strong>⚠️ You already have an active reservation for this book</strong>
        <p style="margin: 10px 0 0 0;">Expires on: <?= date('F j, Y', strtotime($existingReservation['expiryDate'])) ?></p>
      </div>
      <div class="btn-group">
        <a href="<?= BASE_URL ?>user/books" class="btn btn-secondary">
          ← Back to Books
        </a>
      </div>
    <?php else: ?>
      <form method="POST" onsubmit="return confirm('Confirm reservation for this book?');">
        <div class="alert alert-info">
          <strong>ℹ️ Reservation Details:</strong>
          <ul>
            <li>Your reservation will be active for 7 days</li>
            <li>You'll be notified when the book becomes available</li>
            <li>You can cancel the reservation anytime from your dashboard</li>
            <li>Priority is given based on reservation date</li>
          </ul>
        </div>

        <div class="btn-group">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-check"></i>
            Confirm Reservation
          </button>
          <a href="<?= BASE_URL ?>user/books" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
          </a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>