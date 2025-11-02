<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Your Fines';
include APP_ROOT . '/views/layouts/header.php';

// Calculate total unpaid fine
$totalFine = 0;
$totalPaidFine = 0;
if (!empty($fines)) {
    foreach ($fines as $fine) {
        $fineAmount = (float)($fine['fineAmount'] ?? 0);
        if ($fine['fineStatus'] === 'paid') {
            $totalPaidFine += $fineAmount;
        } else {
            $totalFine += $fineAmount;
        }
    }
}
?>

<style>
    /* Hide scrollbars globally */
    body {
        overflow-x: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    body::-webkit-scrollbar {
        display: none;
    }

    .fines-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 4rem 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    /* Animated background particles */
    .fines-wrapper::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 20s infinite ease-in-out;
    }
    
    .fines-wrapper::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 25s infinite ease-in-out reverse;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); }
        33% { transform: translateY(-30px) translateX(30px) rotate(120deg); }
        66% { transform: translateY(30px) translateX(-30px) rotate(240deg); }
    }
    
    .fines-container {
        max-width: 1600px;
        width: 95%;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    /* Header Section */
    .fines-header {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 32px 32px 0 0;
        padding: 2.5rem 3rem;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
        animation: slideInDown 0.6s ease-out;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-bottom: none;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fines-header-content {
        display: inline-block;
    }
    
    .fines-header-content h1 {
        font-size: 2.2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        letter-spacing: -0.5px;
    }
    
    .fines-header-content p {
        color: #6b7280;
        font-size: 1rem;
        margin: 0 0 1rem 0;
        font-weight: 500;
    }
    
    .total-badge {
        display: inline-flex;
        flex-direction: column;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4);
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .total-badge-label {
        font-size: 0.85rem;
        opacity: 0.95;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }
    
    .total-badge-amount {
        font-size: 2rem;
        font-weight: 900;
        margin: 0;
    }
    
    /* Body Section */
    .fines-body {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 0 0 32px 32px;
        padding: 2.5rem 3rem 3rem;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
        animation: slideInUp 0.6s ease-out 0.2s both;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-top: none;
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
    
    /* Info Alert */
    .info-alert {
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
        border-left: 5px solid #f59e0b;
        border-radius: 16px;
        margin-bottom: 2.5rem;
        display: flex;
        gap: 1rem;
        align-items: start;
        box-shadow: 0 5px 20px rgba(245, 158, 11, 0.2);
    }
    
    .info-alert-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f59e0b;
        color: white;
        border-radius: 12px;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    
    .info-alert-content h5 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #92400e;
        margin-bottom: 0.5rem;
    }
    
    .info-alert-content p {
        color: #b45309;
        margin: 0;
        font-size: 1rem;
        line-height: 1.6;
        font-weight: 600;
    }
    
    /* Modern Table */
    .table-wrapper {
        overflow-x: auto;
        border-radius: 20px;
        margin-bottom: 2rem;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .table-wrapper::-webkit-scrollbar {
        display: none;
    }
    
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .modern-table thead th {
        padding: 1.25rem 1rem;
        text-align: left;
        font-weight: 800;
        color: white;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        white-space: nowrap;
    }
    
    .modern-table thead th:first-child {
        border-radius: 20px 0 0 0;
    }
    
    .modern-table thead th:last-child {
        border-radius: 0 20px 0 0;
    }
    
    .modern-table tbody tr {
        background: white;
        transition: all 0.3s ease;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .modern-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.03), rgba(220, 38, 38, 0.03));
        transform: scale(1.01);
        box-shadow: 0 5px 20px rgba(239, 68, 68, 0.1);
    }
    
    .modern-table tbody tr:last-child {
        border-bottom: none;
    }
    
    .modern-table tbody tr:last-child td:first-child {
        border-radius: 0 0 0 20px;
    }
    
    .modern-table tbody tr:last-child td:last-child {
        border-radius: 0 0 20px 0;
    }
    
    .modern-table tbody td {
        padding: 1.5rem 1rem;
        color: #374151;
        border: none;
        font-weight: 600;
    }
    
    .book-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .book-title {
        font-weight: 800;
        color: #1f2937;
        font-size: 1.05rem;
    }
    
    .book-isbn {
        font-size: 0.9rem;
        color: #9ca3af;
        font-family: 'Courier New', monospace;
    }
    
    .date-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1rem;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
        color: #3b82f6;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 700;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    .fine-amount {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.2rem;
        font-weight: 900;
        color: #ef4444;
    }
    
    .btn-pay {
        padding: 0.75rem 1.25rem;
        border: none;
        border-radius: 12px;
        font-weight: 800;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        margin: 0.25rem;
        position: relative;
        overflow: hidden;
    }
    
    .btn-pay::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s ease, height 0.6s ease;
    }
    
    .btn-pay:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-pay i,
    .btn-pay span {
        position: relative;
        z-index: 1;
    }
    
    .btn-pay:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }
    
    .no-fine-badge {
        padding: 0.65rem 1.25rem;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        color: #10b981;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .paid-badge {
        padding: 0.65rem 1.25rem;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        color: #10b981;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .paid-row {
        opacity: 0.7;
        background: rgba(16, 185, 129, 0.02) !important;
    }
    
    .payment-info {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    
    /* Total Summary */
    .total-summary {
        padding: 2rem;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(220, 38, 38, 0.08));
        border-radius: 20px;
        border: 3px dashed rgba(239, 68, 68, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .total-summary-label {
        font-size: 1.4rem;
        font-weight: 800;
        color: #374151;
    }
    
    .total-summary-amount {
        font-size: 2.5rem;
        font-weight: 900;
        color: #ef4444;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .empty-state-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        border-radius: 50%;
        font-size: 4rem;
    }
    
    .empty-state-icon i {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .empty-state h4 {
        font-size: 2rem;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 450px;
        margin: 0 auto 2.5rem;
        line-height: 1.7;
        font-weight: 500;
    }
    
    .btn-browse {
        padding: 1.25rem 2.5rem;
        border: none;
        border-radius: 16px;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.3);
        font-size: 1.1rem;
        position: relative;
        overflow: hidden;
    }
    
    .btn-browse::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s ease, height 0.6s ease;
    }
    
    .btn-browse:hover::before {
        width: 400px;
        height: 400px;
    }
    
    .btn-browse i,
    .btn-browse span {
        position: relative;
        z-index: 1;
    }
    
    .btn-browse:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 18px 45px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    /* Payment Modal */
    #paymentModal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }
    
    .modal-content {
        background: white;
        border-radius: 24px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 2.5rem;
        position: relative;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 2rem;
        color: #9ca3af;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        z-index: 1;
    }
    
    .modal-close:hover {
        background: #f3f4f6;
        color: #ef4444;
    }
    
    .modal-content h3 {
        font-size: 1.8rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 2rem;
        padding-right: 3rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        font-weight: 600;
        box-sizing: border-box;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .form-row {
        display: flex;
        gap: 1rem;
    }
    
    .form-row .form-group {
        flex: 1;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .checkbox-group label {
        margin: 0;
        cursor: pointer;
        font-weight: 600;
    }
    
    #cardError {
        color: #ef4444;
        margin-top: 1rem;
        padding: 0.75rem;
        background: rgba(239, 68, 68, 0.1);
        border-radius: 8px;
        display: none;
        font-weight: 600;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .fines-container {
            width: 95%;
        }
    }
    
    @media (max-width: 768px) {
        .fines-wrapper {
            padding: 2rem 1rem;
        }
        
        .fines-header,
        .fines-body {
            padding: 2rem 1.5rem;
        }
        
        .fines-header-content h1 {
            font-size: 1.8rem;
        }
        
        .modern-table thead {
            display: none;
        }
        
        .modern-table tbody tr {
            display: block;
            margin-bottom: 1.5rem;
            border: 2px solid #f3f4f6;
            border-radius: 16px;
            padding: 1.5rem;
        }
        
        .modern-table tbody td {
            display: block;
            padding: 0.75rem 0;
            border: none;
        }
        
        .modern-table tbody td::before {
            content: attr(data-label);
            font-weight: 800;
            color: #6b7280;
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .total-summary {
            flex-direction: column;
            text-align: center;
        }
        
        .btn-pay {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .fines-header,
        .fines-body {
            padding: 1.5rem;
        }
        
        .fines-header-content h1 {
            font-size: 1.5rem;
        }
        
        .modal-content {
            padding: 2rem 1.5rem;
        }
    }
</style>

<div class="fines-wrapper">
    <div class="fines-container">
        <!-- Header -->
        <div class="fines-header">
            <div class="fines-header-content">
                <h1>
                    <i class="fas fa-receipt"></i>
                    Your Fines
                </h1>
                <p>Manage and pay your outstanding library fines</p>
                <?php if ($totalFine > 0) { ?>
                <div class="total-badge">
                    <div class="total-badge-label">Total Outstanding</div>
                    <div class="total-badge-amount">LKR <?= number_format($totalFine, 2) ?></div>
                </div>
                <?php } elseif ($totalPaidFine > 0) { ?>
                <div class="total-badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="total-badge-label">All Fines Paid</div>
                    <div class="total-badge-amount">LKR <?= number_format($totalPaidFine, 2) ?></div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Body -->
        <div class="fines-body">
            <?php if (!empty($fines)) { ?>
                <!-- Info Alert -->
                <div class="info-alert">
                    <div class="info-alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="info-alert-content">
                        <h5>Fine Calculation</h5>
                        <p>Fines are calculated at LKR 5 per day for overdue books (14-day borrowing period). Cash payments must be completed at the library counter.</p>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book"></i> Book Details</th>
                                <th><i class="fas fa-calendar-plus"></i> Borrowed Date</th>
                                <th><i class="fas fa-calendar-check"></i> Due Date</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Days Overdue</th>
                                <th><i class="fas fa-rupee-sign"></i> Fine Amount</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-credit-card"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $maxBorrowDays = 14;
                        
                        foreach ($fines as $fine) { 
                            $fineAmount = (float)($fine['fineAmount'] ?? 0);
                            $isPaid = ($fine['fineStatus'] ?? '') === 'paid';
                            
                            $borrowDate = new DateTime($fine['borrowDate']);
                            $maxBorrowDays = isset($fine['max_borrow_days']) ? (int)$fine['max_borrow_days'] : 14;
                            $dueDate = clone $borrowDate;
                            $dueDate->add(new DateInterval("P{$maxBorrowDays}D"));
                            
                            $currentDate = new DateTime();
                            $interval = $dueDate->diff($currentDate);
                            $daysOverdue = $currentDate > $dueDate ? $interval->days : 0;
                            
                            $rowClass = $isPaid ? 'paid-row' : '';
                        ?>
                            <tr class="<?= $rowClass ?>">
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
                                        <?= htmlspecialchars($borrowDate->format('M d, Y')) ?>
                                    </span>
                                </td>
                                <td data-label="Due Date">
                                    <span class="date-badge" style="background: <?= $daysOverdue > 0 ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)' ?>; color: <?= $daysOverdue > 0 ? '#ef4444' : '#10b981' ?>; border-color: <?= $daysOverdue > 0 ? 'rgba(239, 68, 68, 0.3)' : 'rgba(16, 185, 129, 0.3)' ?>">
                                        <i class="fas fa-calendar-check"></i>
                                        <?= $dueDate->format('M d, Y') ?>
                                    </span>
                                </td>
                                <td data-label="Days Overdue">
                                    <?php if ($daysOverdue > 0 && !$isPaid): ?>
                                        <span class="date-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <?= $daysOverdue ?> days
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Fine Amount">
                                    <span class="fine-amount" style="<?= $isPaid ? 'color: #10b981;' : '' ?>">
                                        <i class="fas fa-rupee-sign"></i>
                                        <?= number_format($fineAmount, 2) ?>
                                    </span>
                                    <?php if ($isPaid && !empty($fine['finePaymentDate'])): ?>
                                        <div class="payment-info">
                                            Paid on <?= date('M d, Y', strtotime($fine['finePaymentDate'])) ?>
                                            <?php if (!empty($fine['finePaymentMethod'])): ?>
                                                via <?= ucfirst($fine['finePaymentMethod']) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php if ($isPaid): ?>
                                        <span class="paid-badge">
                                            <i class="fas fa-check-circle"></i>
                                            Paid
                                        </span>
                                    <?php elseif ($fineAmount > 0): ?>
                                        <span class="date-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                                            <i class="fas fa-clock"></i>
                                            Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="no-fine-badge">
                                            <i class="fas fa-check"></i>
                                            No Fine
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action">
                                    <?php if (!$isPaid && $fineAmount > 0): ?>
                                        <form method="POST" action="<?= BASE_URL ?>faculty/fines" style="display:inline">
                                            <input type="hidden" name="borrow_id" value="<?= htmlspecialchars($fine['tid'] ?? $fine['id'] ?? '') ?>">
                                            <input type="hidden" name="amount" value="<?= htmlspecialchars($fineAmount) ?>">
                                            <button type="submit" class="btn-pay">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span>Pay Cash</span>
                                            </button>
                                        </form>
                                        <a href="<?= BASE_URL ?>faculty/payment-form?borrow_id=<?= urlencode($fine['tid'] ?? $fine['id'] ?? '') ?>&amount=<?= urlencode($fineAmount) ?>" class="btn-pay" style="text-decoration: none;">
                                            <i class="fas fa-credit-card"></i>
                                            <span>Pay Online</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="paid-badge">
                                            <i class="fas fa-check-double"></i>
                                            Settled
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Total Summary -->
                <?php if ($totalFine > 0): ?>
                <div class="total-summary">
                    <span class="total-summary-label">Total Outstanding Amount:</span>
                    <span class="total-summary-amount">
                        <i class="fas fa-rupee-sign"></i>
                        <?= number_format($totalFine, 2) ?>
                    </span>
                </div>
                <?php endif; ?>
            <?php } else { ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4>No Pending Fines!</h4>
                    <p>Great job! You don't have any outstanding fines at the moment. Keep up the good work!</p>
                    <a href="<?= BASE_URL ?>faculty/books" class="btn-browse">
                        <i class="fas fa-book"></i>
                        <span>Browse Books</span>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
// Auto-format card number
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/\D/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Auto-format expiry date
document.getElementById('card_expiry')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

// CVV numeric only
document.getElementById('card_cvv')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

// Card holder name uppercase
document.getElementById('card_holder')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
});

function validateCardForm() {
    var holder = document.getElementById('card_holder').value.trim();
    var number = document.getElementById('card_number').value.replace(/\s+/g, '');
    var expiry = document.getElementById('card_expiry').value.trim();
    var cvv = document.getElementById('card_cvv').value.trim();
    var error = '';

    if (holder.length < 2) {
        error = 'Card holder name required (minimum 2 characters).';
    } else if (!/^\d{13,19}$/.test(number) || !luhnCheck(number)) {
        error = 'Invalid card number.';
    } else if (!/^\d{2}\/\d{2}$/.test(expiry)) {
        error = 'Invalid expiry format (MM/YY).';
    } else {
        var parts = expiry.split('/');
        var mm = parseInt(parts[0], 10), yy = parseInt(parts[1], 10);
        var now = new Date();
        var expDate = new Date(2000 + yy, mm - 1, 1);
        if (mm < 1 || mm > 12) error = 'Invalid expiry month (01-12).';
        else if (expDate < new Date(now.getFullYear(), now.getMonth(), 1)) error = 'Card expired.';
    }
    
    if (!error && !/^\d{3,4}$/.test(cvv)) {
        error = 'Invalid CVV (3-4 digits required).';
    }
    
    if (error) {
        document.getElementById('cardError').innerText = error;
        document.getElementById('cardError').style.display = 'block';
        return false;
    }
    return true;
}

function luhnCheck(num) {
    var arr = (num + '').split('').reverse().map(x => parseInt(x));
    var sum = arr.reduce((acc, val, idx) => acc + (idx % 2 ? ((val *= 2) > 9 ? val - 9 : val) : val), 0);
    return sum % 10 === 0;
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('paymentModal');
    if (event.target == modal) {
        closePaymentModal();
    }
}

// Add Font Awesome if not already included
if (!document.querySelector('link[href*="font-awesome"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
    document.head.appendChild(link);
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
