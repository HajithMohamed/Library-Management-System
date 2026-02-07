<?php
use App\Helpers\ImageHelper;

// Get active reservations
require_once __DIR__ . '/../../config/dbConnection.php';
$userId = $_SESSION['userId'];

$stmt = $pdo->prepare("
    SELECT br.*, b.bookName, b.authorName, b.available, b.bookImage
    FROM book_reservations br
    JOIN books b ON br.isbn = b.isbn
    WHERE br.userId = ? AND br.reservationStatus IN ('Active', 'Notified')
    ORDER BY br.createdAt DESC
");
$stmt->execute([$userId]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get book requests (if the feature exists)
try {
    $requestStmt = $pdo->prepare("
        SELECT * FROM book_requests 
        WHERE userId = ? 
        ORDER BY requestDate DESC
    ");
    $requestStmt->execute([$userId]);
    $requests = $requestStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $requests = [];
}
?>

<style>
    /* Original styles */
    .book-request-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .reservations-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .reservations-section h3 {
        color: #1f2937;
        font-size: 1.8rem;
        margin-bottom: 25px;
        border-bottom: 3px solid #667eea;
        padding-bottom: 15px;
    }
    
    .reservations-list {
        display: grid;
        gap: 20px;
    }
    
    .reservation-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 20px;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .reservation-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border-color: #667eea;
    }
    
    .reservation-card.notified {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-color: #10b981;
    }
    
    .book-info {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .book-thumbnail {
        width: 80px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .details h4 {
        margin: 0 0 8px 0;
        color: #1f2937;
        font-size: 1.2rem;
    }
    
    .details .author {
        color: #6b7280;
        margin: 5px 0;
    }
    
    .details .isbn {
        font-size: 0.85rem;
        color: #9ca3af;
    }
    
    .reservation-status {
        min-width: 250px;
    }
    
    .alert {
        padding: 15px;
        border-radius: 10px;
        text-align: center;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 2px solid #10b981;
        color: #065f46;
    }
    
    .alert-success strong {
        display: block;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    
    .status-info {
        text-align: center;
    }
    
    .badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-info p {
        margin: 5px 0;
        color: #4b5563;
    }
    
    .queue-count {
        font-size: 0.9rem;
        color: #6b7280;
    }
    
    .status-info small {
        color: #9ca3af;
    }
    
    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
        transform: translateY(-2px);
    }
    
    .btn-small {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
    }
    
    /* New styles from copy file */
    .request-form-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 30px;
        padding: 40px;
        margin-bottom: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
    }
    
    .request-form-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        animation: drift 60s linear infinite;
        pointer-events: none;
    }
    
    @keyframes drift {
        0% { transform: translate(0, 0); }
        100% { transform: translate(50px, 50px); }
    }
    
    .request-form-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 30px;
        position: relative;
        z-index: 1;
    }
    
    .request-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 35px;
    }
    
    .card-header-modern {
        margin-bottom: 30px;
    }
    
    .card-header-modern h2 {
        font-size: 2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 10px;
    }
    
    .card-header-modern p {
        color: #6b7280;
        font-size: 1.1rem;
    }
    
    .form-group-modern {
        margin-bottom: 25px;
    }
    
    .form-label-modern {
        display: block;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 10px;
        font-size: 1rem;
    }
    
    .form-input-modern {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        box-sizing: border-box;
    }
    
    .form-input-modern:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    textarea.form-input-modern {
        resize: vertical;
        min-height: 120px;
    }
    
    .submit-btn-modern {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 15px;
        font-size: 1.2rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    
    .submit-btn-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
    }
    
    .requests-list {
        max-height: 600px;
        overflow-y: auto;
    }
    
    .request-item {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 5px solid;
        transition: all 0.3s ease;
    }
    
    .request-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .request-item.pending {
        border-left-color: #f59e0b;
    }
    
    .request-item.approved {
        border-left-color: #10b981;
    }
    
    .request-item.rejected {
        border-left-color: #ef4444;
    }
    
    .request-title {
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 8px;
        font-size: 1.1rem;
    }
    
    .request-date {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 10px;
    }
    
    .status-badge-modern {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge-modern.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }
    
    .status-badge-modern.approved {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }
    
    .status-badge-modern.rejected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }
    
    .alert-modern {
        padding: 18px 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        font-weight: 700;
        border-left: 5px solid;
    }
    
    .alert-modern.alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left-color: #10b981;
    }
    
    .alert-modern.alert-error {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left-color: #ef4444;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    @media (max-width: 992px) {
        .reservation-card {
            grid-template-columns: 1fr;
        }
        
        .request-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ...existing header... -->

<div class="book-request-container">
    <!-- Active Reservations Section -->
    <?php if (!empty($reservations)): ?>
    <div class="reservations-section">
        <h3>🔔 My Active Reservations</h3>
        
        <div class="reservations-list">
            <?php foreach ($reservations as $res): ?>
                <div class="reservation-card <?php echo $res['reservationStatus'] === 'Notified' ? 'notified' : ''; ?>">
                    <div class="book-info">
                        <?= ImageHelper::renderBookCover($res['bookImage'] ?? null, 'Cover', 'book-thumbnail') ?>
                        <div class="details">
                            <h4><?php echo htmlspecialchars($res['bookName']); ?></h4>
                            <p class="author"><?php echo htmlspecialchars($res['authorName']); ?></p>
                            <span class="isbn">ISBN: <?php echo htmlspecialchars($res['isbn']); ?></span>
                        </div>
                    </div>
                    
                    <div class="reservation-status">
                        <?php if ($res['reservationStatus'] === 'Notified'): ?>
                            <div class="alert alert-success">
                                <strong>✓ Book Available!</strong>
                                <p>Please borrow within 48 hours or reservation will expire</p>
                                <a href="/index.php?route=borrow&isbn=<?php echo $res['isbn']; ?>" 
                                   class="btn btn-success">Borrow Now</a>
                            </div>
                        <?php else: ?>
                            <?php
                            // Fix: Use the full namespace path
                            require_once __DIR__ . '/../../Controllers/BookController.php';
                            $bookController = new \App\Controllers\BookController();
                            $queue = $bookController->getReservationQueue($res['isbn']);
                            
                            $position = 0;
                            foreach ($queue as $idx => $q) {
                                if ($q['userId'] == $userId) {
                                    $position = $idx + 1;
                                    break;
                                }
                            }
                            ?>
                            
                            <div class="status-info">
                                <span class="badge badge-warning">Waiting</span>
                                <p>Queue Position: <strong>#<?php echo $position; ?></strong></p>
                                <p class="queue-count"><?php echo count($queue); ?> person(s) in queue</p>
                                <small>Reserved on: <?php echo date('M d, Y', strtotime($res['createdAt'])); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="actions">
                        <form method="POST" action="/index.php?route=manage-reservation">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="isbn" value="<?php echo $res['isbn']; ?>">
                            <button type="submit" class="btn-small btn-danger" 
                                    onclick="return confirm('Cancel this reservation?')">
                                Cancel Reservation
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Book Request Form Section (New Feature from Copy) -->
    <div class="request-form-section">
        <div class="request-form-grid">
            <!-- Request Form -->
            <div class="request-card">
                <div class="card-header-modern">
                    <h2>📚 Request a Book</h2>
                    <p>Can't find a book? Request it and we'll notify you!</p>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert-modern alert-success">
                        ✓ <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert-modern alert-error">
                        ✗ <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <form action="/faculty/book-request" method="POST">
                    <div class="form-group-modern">
                        <label for="isbn" class="form-label-modern">📖 Book ISBN</label>
                        <input type="text" 
                               name="isbn" 
                               id="isbn"
                               class="form-input-modern" 
                               placeholder="Enter ISBN number"
                               required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="book_title" class="form-label-modern">📕 Book Title</label>
                        <input type="text" 
                               name="book_title" 
                               id="book_title"
                               class="form-input-modern" 
                               placeholder="Enter book title"
                               required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="author" class="form-label-modern">✍️ Author Name</label>
                        <input type="text" 
                               name="author" 
                               id="author"
                               class="form-input-modern" 
                               placeholder="Enter author name"
                               required>
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="reason" class="form-label-modern">💬 Reason for Request</label>
                        <textarea name="reason" 
                                  id="reason"
                                  class="form-input-modern" 
                                  placeholder="Tell us why you need this book..."
                                  rows="4"></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn-modern">
                        🚀 Submit Request
                    </button>
                </form>
            </div>
            
            <!-- Your Requests -->
            <div class="request-card">
                <div class="card-header-modern">
                    <h2>📋 Your Requests</h2>
                    <p>Track your book requests</p>
                </div>
                
                <div class="requests-list">
                    <?php if (!empty($requests) && is_array($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                            <div class="request-item <?= strtolower($request['status'] ?? 'pending') ?>">
                                <div class="request-title">
                                    <?= htmlspecialchars($request['bookName'] ?? $request['book_title'] ?? 'Unknown Book') ?>
                                </div>
                                <div class="request-date">
                                    📅 Requested: <?= date('M d, Y', strtotime($request['requestDate'] ?? 'now')) ?>
                                </div>
                                <?php if (!empty($request['author'])): ?>
                                    <div class="request-date">
                                        ✍️ Author: <?= htmlspecialchars($request['author']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($request['dueDate']) && strtolower($request['status']) === 'approved'): ?>
                                    <div class="request-date">
                                        ⏰ Due: <?= date('M d, Y', strtotime($request['dueDate'])) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($request['rejectionReason']) && strtolower($request['status']) === 'rejected'): ?>
                                    <div class="request-date">
                                        ❌ Reason: <?= htmlspecialchars($request['rejectionReason']) ?>
                                    </div>
                                <?php endif; ?>
                                <div style="margin-top: 12px;">
                                    <span class="status-badge-modern <?= strtolower($request['status'] ?? 'pending') ?>">
                                        <?php
                                        $status = strtolower($request['status'] ?? 'pending');
                                        echo $status === 'pending' ? '⏳ Pending' : 
                                             ($status === 'approved' ? '✓ Approved' : '✗ Rejected');
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <p>No requests yet. Submit your first book request above!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ...existing footer... -->