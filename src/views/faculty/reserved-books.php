<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'Reserved Books';
include APP_ROOT . '/views/layouts/header.php';

$requests = $requests ?? [];
?>

<style>
/* Hide scrollbars globally while maintaining scroll functionality */
body {
    overflow-x: hidden;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

body::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

/* Modern reserved books page with enhanced design */
.reserved-modern-bg {
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
.reserved-modern-bg::before {
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

.reserved-modern-bg::after {
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

.reserved-modern-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    border-radius: 32px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
    max-width: 1600px;
    width: 95%;
    margin: 0 auto;
    overflow: hidden;
    animation: slideUpFade 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    z-index: 1;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

@keyframes slideUpFade {
    from { 
        opacity: 0; 
        transform: translateY(50px) scale(0.95);
    }
    to { 
        opacity: 1; 
        transform: translateY(0) scale(1);
    }
}

.reserved-modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 2.5rem 3rem;
    border-radius: 32px 32px 0 0;
    position: relative;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
    text-align: center;
}

.reserved-modern-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.reserved-header-content {
    position: relative;
    z-index: 1;
    display: inline-block;
}

.reserved-modern-header h1 {
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0 0 0.5rem 0;
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.reserved-modern-header h1 i {
    font-size: 2rem;
}

.reserved-modern-header p {
    font-size: 1rem;
    opacity: 0.95;
    margin: 0;
    font-weight: 500;
}

.reserved-modern-header .back-button {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: #fff;
    border-radius: 16px;
    padding: 1rem 2rem;
    font-weight: 800;
    font-size: 1.05rem;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
    z-index: 1;
    overflow: hidden;
}

.reserved-modern-header .back-button::before {
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

.reserved-modern-header .back-button:hover::before {
    width: 300px;
    height: 300px;
}

.reserved-modern-header .back-button:hover {
    background: rgba(255, 255, 255, 0.95);
    color: #667eea;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.reserved-modern-header .back-button i,
.reserved-modern-header .back-button span {
    position: relative;
    z-index: 1;
}

.reserved-modern-body {
    padding: 2.5rem 3rem;
}

/* Stats Summary Cards */
.stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}

.stat-card-mini {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border: 1px solid rgba(102, 126, 234, 0.1);
    border-radius: 20px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card-mini:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
    border-color: rgba(102, 126, 234, 0.3);
}

.stat-icon-mini {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}

.stat-content-mini {
    flex: 1;
}

.stat-label-mini {
    font-size: 0.85rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-value-mini {
    font-size: 1.5rem;
    font-weight: 900;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Modern Table */
.table-wrapper {
    overflow-x: auto;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    /* Hide scrollbar but keep functionality */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.table-wrapper::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

.reserved-modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 0;
}

.reserved-modern-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: sticky;
    top: 0;
    z-index: 10;
}

.reserved-modern-table th {
    padding: 1.25rem 1rem;
    text-align: left;
    color: white;
    font-weight: 800;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    white-space: nowrap;
}

.reserved-modern-table th:first-child {
    border-radius: 20px 0 0 0;
}

.reserved-modern-table th:last-child {
    border-radius: 0 20px 0 0;
}

.reserved-modern-table tbody tr {
    background: white;
    transition: all 0.3s ease;
    border-bottom: 1px solid #f3f4f6;
}

.reserved-modern-table tbody tr:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
    transform: scale(1.01);
}

.reserved-modern-table tbody tr:last-child {
    border-bottom: none;
}

.reserved-modern-table tbody tr:last-child td:first-child {
    border-radius: 0 0 0 20px;
}

.reserved-modern-table tbody tr:last-child td:last-child {
    border-radius: 0 0 20px 0;
}

.reserved-modern-table td {
    padding: 1.25rem 1rem;
    font-weight: 600;
    color: #374151;
    border: none;
}

.book-title-cell {
    font-weight: 800;
    color: #1f2937;
    font-size: 1.05rem;
}

.author-cell {
    color: #667eea;
    font-weight: 700;
}

.isbn-cell {
    font-family: 'Courier New', monospace;
    font-size: 0.95rem;
    color: #6b7280;
}

.date-cell {
    color: #6b7280;
    font-size: 0.95rem;
}

/* Status Badges */
.status-badge-modern {
    padding: 0.65rem 1.5rem;
    border-radius: 25px;
    font-weight: 800;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    letter-spacing: 0.5px;
    border: 2px solid transparent;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
    text-transform: uppercase;
    transition: all 0.3s ease;
}

.status-badge-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.status-badge-modern::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

.status-badge-modern.Pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border-color: #fbbf24;
}

.status-badge-modern.Pending::before {
    background: #f59e0b;
}

.status-badge-modern.Approved {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-color: #34d399;
}

.status-badge-modern.Approved::before {
    background: #10b981;
}

.status-badge-modern.Rejected {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border-color: #f87171;
}

.status-badge-modern.Rejected::before {
    background: #ef4444;
}

/* Empty State */
.empty-modern-state {
    text-align: center;
    padding: 5rem 2rem;
    color: #9ca3af;
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.empty-modern-state i {
    font-size: 5rem;
    margin-bottom: 2rem;
    opacity: 0.4;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.empty-modern-state h4 {
    font-size: 2rem;
    font-weight: 900;
    margin-bottom: 1rem;
    color: #1f2937;
}

.empty-modern-state p {
    font-size: 1.2rem;
    margin-bottom: 2.5rem;
    color: #6b7280;
}

.browse-modern-btn {
    padding: 1.25rem 2.5rem;
    border-radius: 16px;
    border: none;
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

.browse-modern-btn::before {
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

.browse-modern-btn:hover::before {
    width: 400px;
    height: 400px;
}

.browse-modern-btn i {
    position: relative;
    z-index: 1;
    font-size: 1.2rem;
}

.browse-modern-btn span {
    position: relative;
    z-index: 1;
}

.browse-modern-btn:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 18px 45px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .reserved-modern-card {
        max-width: 95%;
    }
    
    .reserved-modern-header,
    .reserved-modern-body {
        padding: 2.5rem 2rem;
    }
}

@media (max-width: 992px) {
    .reserved-modern-header {
        flex-direction: column;
        gap: 1.5rem;
        align-items: flex-start;
    }
    
    .reserved-modern-header .back-button {
        width: 100%;
        justify-content: center;
    }
    
    .stats-summary {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .reserved-modern-bg {
        padding: 2rem 1rem;
    }
    
    .reserved-modern-header h1 {
        font-size: 2rem;
    }
    
    .reserved-modern-header,
    .reserved-modern-body {
        padding: 2rem 1.5rem;
    }
    
    .reserved-modern-table th,
    .reserved-modern-table td {
        padding: 1rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .stats-summary {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .reserved-modern-header h1 {
        font-size: 1.5rem;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .reserved-modern-table {
        font-size: 0.85rem;
    }
    
    .status-badge-modern {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
}
</style>

<div class="reserved-modern-bg">
    <div class="reserved-modern-card">
        <div class="reserved-modern-header">
            <div class="reserved-header-content">
                <h1>
                    <i class="fas fa-bookmark"></i> 
                    Reserved Books
                </h1>
                <p>Track and manage all your book reservations in one place</p>
            </div>
        </div>
        
        <div class="reserved-modern-body">
            <?php if (!empty($requests)): ?>
                <!-- Stats Summary -->
                <div class="stats-summary">
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini">
                            <i class="fas fa-bookmark"></i>
                        </div>
                        <div class="stat-content-mini">
                            <div class="stat-label-mini">Total Reserved</div>
                            <div class="stat-value-mini"><?= count($requests) ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content-mini">
                            <div class="stat-label-mini">Pending</div>
                            <div class="stat-value-mini">
                                <?= count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'Pending')) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content-mini">
                            <div class="stat-label-mini">Approved</div>
                            <div class="stat-value-mini">
                                <?= count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'Approved')) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content-mini">
                            <div class="stat-label-mini">Rejected</div>
                            <div class="stat-value-mini">
                                <?= count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'Rejected')) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Table -->
                <div class="table-wrapper">
                    <table class="reserved-modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book"></i> Book</th>
                                <th><i class="fas fa-user-edit"></i> Author</th>
                                <th><i class="fas fa-barcode"></i> ISBN</th>
                                <th><i class="fas fa-calendar-plus"></i> Requested</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-calendar-check"></i> Due Date</th>
                                <th><i class="fas fa-exclamation-triangle"></i> Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td class="book-title-cell">
                                    <?= htmlspecialchars($req['bookName'] ?? 'N/A') ?>
                                </td>
                                <td class="author-cell">
                                    <?= htmlspecialchars($req['authorName'] ?? 'N/A') ?>
                                </td>
                                <td class="isbn-cell">
                                    <?= htmlspecialchars($req['isbn'] ?? 'N/A') ?>
                                </td>
                                <td class="date-cell">
                                    <?= !empty($req['requestDate']) ? date('M d, Y', strtotime($req['requestDate'])) : '-' ?>
                                </td>
                                <td>
                                    <span class="status-badge-modern <?= htmlspecialchars($req['status'] ?? 'Pending') ?>">
                                        <?= htmlspecialchars($req['status'] ?? 'Pending') ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <?= !empty($req['dueDate']) ? date('M d, Y', strtotime($req['dueDate'])) : '-' ?>
                                </td>
                                <td>
                                    <?= !empty($req['rejectionReason']) ? htmlspecialchars($req['rejectionReason']) : '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-modern-state">
                    <i class="fas fa-bookmark"></i>
                    <h4>No Reserved Books</h4>
                    <p>You haven't reserved any books yet. Start exploring our collection!</p>
                    <a href="<?= BASE_URL ?>faculty/books" class="browse-modern-btn">
                        <i class="fas fa-book"></i>
                        <span>Browse Books</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Add Font Awesome if not already included
if (!document.querySelector('link[href*="font-awesome"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
    document.head.appendChild(link);
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>