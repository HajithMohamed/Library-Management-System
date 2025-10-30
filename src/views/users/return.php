<?php
$pageTitle = 'Return Books';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
  .return-container {
    padding: 2rem 0;
    animation: fadeIn 0.6s ease-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }

  /* Page Header */
  .return-header {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    animation: slideInDown 0.6s ease-out;
  }

  @keyframes slideInDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .return-header-content h1 {
    font-size: clamp(1.75rem, 3vw, 2.5rem);
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .return-header-content p {
    color: #6b7280;
    font-size: 1.05rem;
    margin: 0;
  }

  .books-count-badge {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  /* Return Card */
  .return-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    overflow: hidden;
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

  .return-card-header {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05));
    padding: 2rem;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .return-card-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .return-card-header i {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 14px;
    font-size: 1.5rem;
  }

  .return-card-header h3 {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1f2937;
    margin: 0;
  }

  .items-count {
    padding: 0.5rem 1rem;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.9rem;
  }

  .return-card-body {
    padding: 2rem;
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

  /* Modern Table */
  .modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .modern-table thead th {
    padding: 1rem;
    text-align: left;
    font-weight: 700;
    color: #6b7280;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8fafc;
    border-bottom: 2px solid #e5e7eb;
  }

  .modern-table thead th:first-child {
    border-radius: 12px 0 0 0;
  }

  .modern-table thead th:last-child {
    border-radius: 0 12px 0 0;
  }

  .modern-table tbody td {
    padding: 1.25rem 1rem;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
  }

  .modern-table tbody tr {
    transition: all 0.3s ease;
    position: relative;
  }

  .modern-table tbody tr:hover {
    background: rgba(16, 185, 129, 0.02);
  }

  .modern-table tbody tr:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  }

  /* Book Info Cell */
  .book-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  .book-title {
    font-weight: 700;
    color: #1f2937;
    font-size: 1rem;
  }

  .book-isbn {
    font-size: 0.85rem;
    color: #9ca3af;
    font-family: 'Courier New', monospace;
  }

  .author-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
  }

  .author-cell i {
    color: #10b981;
  }

  /* Date Badge */
  .date-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
  }

  /* Return Button */
  .btn-return {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
  }

  .btn-return:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
  }

  .btn-return:active {
    transform: translateY(0);
  }

  /* Back Button */
  .back-button {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    border: 2px solid #10b981;
    background: white;
    color: #10b981;
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
    background: #10b981;
    color: white;
    transform: translateX(-5px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
  }

  .back-button i {
    transition: transform 0.3s ease;
  }

  .back-button:hover i {
    transform: translateX(-3px);
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 4rem 2rem;
  }

  .empty-state-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    border-radius: 50%;
    font-size: 3rem;
    color: #10b981;
  }

  .empty-state h4 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
  }

  .empty-state p {
    color: #6b7280;
    font-size: 1.05rem;
    margin-bottom: 2rem;
  }

  .btn-browse {
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
  }

  .btn-browse:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    color: white;
  }

  /* Due Date Warning */
  .due-warning {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.75rem;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 0.25rem;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .return-header {
      padding: 1.5rem;
      flex-direction: column;
      text-align: center;
    }

    .return-card-header {
      padding: 1.5rem;
      flex-direction: column;
      text-align: center;
    }

    .return-card-header-left {
      flex-direction: column;
    }

    .return-card-body {
      padding: 1rem;
    }

    .modern-table {
      font-size: 0.875rem;
    }

    .modern-table thead {
      display: none;
    }

    .modern-table tbody tr {
      display: block;
      margin-bottom: 1rem;
      border: 2px solid #f3f4f6;
      border-radius: 12px;
      padding: 1rem;
    }

    .modern-table tbody td {
      display: block;
      padding: 0.5rem 0;
      border: none;
    }

    .modern-table tbody td::before {
      content: attr(data-label);
      font-weight: 700;
      color: #6b7280;
      display: block;
      margin-bottom: 0.25rem;
      font-size: 0.85rem;
      text-transform: uppercase;
    }

    .btn-return {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<div class="return-container">
  <div class="container">
    <!-- Return Header -->
    <div class="return-header">
      <div class="return-header-content">
        <h1>Return Books</h1>
        <p>Manage and return your borrowed books</p>
      </div>
      <div style="display: flex; gap: 1rem; align-items: center;">
        <a href="<?= BASE_URL ?>user/dashboard" class="back-button">
          <i class="fas fa-arrow-left"></i>
          <span>Back to Dashboard</span>
        </a>
        <?php if (!empty($books)) { ?>
        <div class="books-count-badge">
          <i class="fas fa-book"></i>
          <span><?= count($books) ?> <?= count($books) === 1 ? 'Book' : 'Books' ?> Borrowed</span>
        </div>
        <?php } ?>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-11">
        <div class="return-card">
          <div class="return-card-header">
            <div class="return-card-header-left">
              <i class="fas fa-undo-alt"></i>
              <h3>Borrowed Books</h3>
            </div>
            <?php if (!empty($books)) { ?>
              <span class="items-count">
                <?= count($books) ?> <?= count($books) === 1 ? 'Item' : 'Items' ?>
              </span>
            <?php } ?>
          </div>

          <div class="return-card-body">
            <?php if (!empty($books)) { ?>
              <!-- Info Alert -->
              <div class="info-alert">
                <div class="info-alert-icon">
                  <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-alert-content">
                  <h5>Return Policy</h5>
                  <p>Please return books on time to avoid fines. Late returns are subject to daily charges. Check the borrowed date and plan your return accordingly.</p>
                </div>
              </div>

              <div class="table-responsive">
                <table class="modern-table">
                  <thead>
                    <tr>
                      <th>Book Details</th>
                      <th>Author</th>
                      <th>Borrowed Date</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($books as $book) {
                      $borrowDate = strtotime($book['borrowDate']);
                      $currentDate = time();
                      $daysBorrowed = floor(($currentDate - $borrowDate) / (60 * 60 * 24));
                    ?>
                      <tr>
                        <td data-label="Book Details">
                          <div class="book-info">
                            <span class="book-title">
                              <?= htmlspecialchars($book['bookName']) ?>
                            </span>
                            <span class="book-isbn">
                              ISBN: <?= htmlspecialchars($book['isbn']) ?>
                            </span>
                          </div>
                        </td>
                        <td data-label="Author">
                          <div class="author-cell">
                            <i class="fas fa-user"></i>
                            <span><?= htmlspecialchars($book['authorName']) ?></span>
                          </div>
                        </td>
                        <td data-label="Borrowed Date">
                          <div>
                            <span class="date-badge">
                              <i class="fas fa-calendar-alt"></i>
                              <?= htmlspecialchars(date('M d, Y', strtotime($book['borrowDate']))) ?>
                            </span>
                            <?php if ($daysBorrowed > 14) { ?>
                              <div class="due-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span><?= $daysBorrowed ?> days overdue</span>
                              </div>
                            <?php } ?>
                          </div>
                        </td>
                        <td data-label="Action">
                          <form method="POST" action="<?= BASE_URL ?>user/return" style="display:inline">
                            <input type="hidden" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
                            <button type="submit" class="btn-return">
                              <i class="fas fa-undo"></i>
                              <span>Return Book</span>
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            <?php } else { ?>
              <!-- Empty State -->
              <div class="empty-state">
                <div class="empty-state-icon">
                  <i class="fas fa-check-circle"></i>
                </div>
                <h4>No Books to Return</h4>
                <p>You don't have any borrowed books at the moment. Browse our collection to find your next read!</p>
                <a href="<?= BASE_URL ?>user/books" class="btn-browse">
                  <i class="fas fa-book"></i>
                  <span>Browse Books</span>
                </a>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
