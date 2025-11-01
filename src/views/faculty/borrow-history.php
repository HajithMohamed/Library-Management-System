<?php
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}
$pageTitle = 'Borrow History';
include APP_ROOT . '/views/layouts/header.php';
?>
<div class="container mt-4">
    <h2>📖 Borrow History</h2>
   
    <!-- Export Controls -->
    <div class="export-controls">
        <h3>📥 Export Borrow History</h3>
         
        <form method="GET" action="/index.php" class="export-form">
            <input type="hidden" name="route" value="export-history">
         
            <div class="form-row">
                <div class="form-group">
                    <label>From Date:</label>
                    <input type="date" name="start_date" value="<?php echo date('Y-m-01'); ?>"
                           max="<?php echo date('Y-m-d'); ?>" required class="form-control">
                </div>
             
                <div class="form-group">
                    <label>To Date:</label>
                    <input type="date" name="end_date" value="<?php echo date('Y-m-d'); ?>"
                           max="<?php echo date('Y-m-d'); ?>" required class="form-control">
                </div>
             
                <div class="form-group">
                    <label>Category:</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="Fiction">Fiction</option>
                        <option value="Non-Fiction">Non-Fiction</option>
                        <option value="Science">Science</option>
                        <option value="Technology">Technology</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="History">History</option>
                        <option value="Arts">Arts</option>
                        <option value="Business">Business</option>
                    </select>
                </div>
             
                <div class="form-group">
                    <label>Format:</label>
                    <select name="format" class="form-control">
                        <option value="csv">CSV (Excel)</option>
                    </select>
                </div>
            </div>
         
            <button type="submit" class="btn btn-primary">
                <i class="icon-download"></i> Download Export
            </button>
        </form>
    </div>
   
    <hr>
   
    <?php if (!empty($history)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['bookName']) ?></td>
                            <td><?= htmlspecialchars($item['authorName']) ?></td>
                            <td><?= date('M d, Y', strtotime($item['borrowDate'])) ?></td>
                            <td><?= $item['returnDate'] ? date('M d, Y', strtotime($item['returnDate'])) : '<span class="badge bg-warning">Borrowed</span>' ?></td>
                            <td>
                                <?php if ($item['returnDate']): ?>
                                    <span class="badge bg-success">Returned</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No borrow history found.</p>
    <?php endif; ?>
</div>
<?php include APP_ROOT . '/views/layouts/footer.php'; ?>