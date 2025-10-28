<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Request Book';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .request-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 60px 20px;
        position: relative;
        overflow: hidden;
    }
    
    .request-page::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        animation: drift 60s linear infinite;
    }
    
    @keyframes drift {
        0% { transform: translate(0, 0); }
        100% { transform: translate(50px, 50px); }
    }
    
    .request-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 30px;
        position: relative;
        z-index: 1;
    }
    
    .request-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
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
    
    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left-color: #10b981;
    }
    
    .alert-error {
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
        .request-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="request-page">
    <div class="request-container">
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
                <p>Track your borrow requests</p>
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

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>