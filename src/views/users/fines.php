<?php
$pageTitle = 'Your Fines';
include APP_ROOT . '/views/layouts/header.php';

// Calculate total fine
$totalFine = 0;
if (!empty($fines)) {
    foreach ($fines as $fine) {
        $totalFine += (float)($fine['fineAmount'] ?? 0);
    }
}
?>

<style>
    .fines-container {
        padding: 2rem 0;
        animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    /* Page Header */
    .fines-header {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
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
    
    .fines-header-content h1 {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .fines-header-content p {
        color: #6b7280;
        font-size: 1.05rem;
        margin: 0;
    }
    
    .total-badge {
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
    }
    
    .total-badge-label {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }
    
    .total-badge-amount {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
    }
    
    /* Fines Card */
    .fines-card {
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
    
    .fines-card-header {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
        padding: 2rem;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .fines-card-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .fines-card-header i {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 14px;
        font-size: 1.5rem;
    }
    
    .fines-card-header h3 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
    }
    
    .fines-count {
        padding: 0.5rem 1rem;
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.9rem;
    }
    
    .fines-card-body {
        padding: 2rem;
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
        background: rgba(239, 68, 68, 0.02);
    }
    
    .modern-table tbody tr:hover td:first-child::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
    }
    
    /* Date Badge */
    .date-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    /* Fine Amount */
    .fine-amount {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.1rem;
        font-weight: 800;
        color: #ef4444;
    }
    
    .fine-amount.no-fine {
        color: #10b981;
    }
    
    /* Action Buttons */
    .btn-pay {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }
    
    .btn-pay:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
    }
    
    .btn-pay:active {
        transform: translateY(0);
    }
    
    .no-fine-badge {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        color: #10b981;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
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
    
    /* Info Alert */
    .info-alert {
        padding: 1.25rem;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(249, 115, 22, 0.05));
        border-left: 4px solid #f59e0b;
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
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
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
    
    /* Total Summary */
    .total-summary {
        margin-top: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
        border-radius: 16px;
        border: 2px dashed rgba(239, 68, 68, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .total-summary-label {
        font-size: 1.25rem;
        font-weight: 700;
        color: #374151;
    }
    
    .total-summary-amount {
        font-size: 2rem;
        font-weight: 900;
        color: #ef4444;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .fines-header {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
        }
        
        .fines-card-header {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
        }
        
        .fines-card-header-left {
            flex-direction: column;
        }
        
        .fines-card-body {
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
        
        .total-summary {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div class="fines-container">
    <div class="container">
        <!-- Fines Header -->
        <div class="fines-header">
            <div class="fines-header-content">
                <h1>Your Fines</h1>
                <p>Manage and pay your outstanding library fines</p>
            </div>
            <?php if (!empty($fines) && $totalFine > 0) { ?>
            <div class="total-badge">
                <div class="total-badge-label">Total Outstanding</div>
                <div class="total-badge-amount">₹<?= number_format($totalFine, 2) ?></div>
            </div>
            <?php } ?>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="fines-card">
                    <div class="fines-card-header">
                        <div class="fines-card-header-left">
                            <i class="fas fa-receipt"></i>
                            <h3>Pending Fines</h3>
                        </div>
                        <?php if (!empty($fines)) { ?>
                        <span class="fines-count">
                            <?= count($fines) ?> <?= count($fines) === 1 ? 'Item' : 'Items' ?>
                        </span>
                        <?php } ?>
                    </div>
                    
                    <div class="fines-card-body">
                        <?php if (!empty($fines)) { ?>
                            <!-- Info Alert -->
                            <div class="info-alert">
                                <div class="info-alert-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="info-alert-content">
                                    <h5>Fine Calculation</h5>
                                    <p>Fines are calculated based on the number of days overdue. Please pay your fines promptly to avoid account suspension.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="modern-table">
                                    <thead>
                                        <tr>
                                            <th>Book Details</th>
                                            <th>Borrowed Date</th>
                                            <th>Fine Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($fines as $fine) { 
                                        $fineAmount = (float)($fine['fineAmount'] ?? 0);
                                    ?>
                                        <tr>
                                            <td data-label="Book Details">
                                                <div class="book-info">
                                                    <span class="book-title">
                                                        <?= htmlspecialchars($fine['title'] ?? $fine['bookName'] ?? 'Book') ?>
                                                    </span>
                                                    <span class="book-isbn">
                                                        ISBN: <?= htmlspecialchars($fine['isbn'] ?? '') ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td data-label="Borrowed Date">
                                                <span class="date-badge">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <?= htmlspecialchars(date('M d, Y', strtotime($fine['borrowDate']))) ?>
                                                </span>
                                            </td>
                                            <td data-label="Fine Amount">
                                                <span class="fine-amount">
                                                    <i class="fas fa-rupee-sign"></i>
                                                    <?= number_format($fineAmount, 2) ?>
                                                </span>
                                            </td>
                                            <td data-label="Action">
                                                <form method="POST" action="<?= BASE_URL ?>user/fines" style="display:inline">
                                                    <input type="hidden" name="borrow_id" value="<?= htmlspecialchars($fine['tid'] ?? $fine['id'] ?? '') ?>">
                                                    <input type="hidden" name="amount" value="<?= htmlspecialchars($fineAmount) ?>">
                                                    <button type="submit" class="btn-pay">
                                                        <i class="fas fa-credit-card"></i>
                                                        <span>Pay Now</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Total Summary -->
                            <div class="total-summary">
                                <span class="total-summary-label">Total Amount Due:</span>
                                <span class="total-summary-amount">
                                    <i class="fas fa-rupee-sign"></i>
                                    <?= number_format($totalFine, 2) ?>
                                </span>
                            </div>
                        <?php } else { ?>
                            <!-- Empty State -->
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h4>No Pending Fines!</h4>
                                <p>Great job! You don't have any outstanding fines at the moment.</p>
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