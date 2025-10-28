<?php
// ...existing code...
?>

<div class="export-section">
    <h3>📥 Export Borrow History</h3>
    
    <form method="GET" action="/index.php" class="export-form">
        <input type="hidden" name="route" value="export-history">
        
        <div class="form-row">
            <div class="form-group">
                <label>Start Date:</label>
                <input type="date" name="start_date" value="<?php echo date('Y-m-01'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>End Date:</label>
                <input type="date" name="end_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Category (Optional):</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Fiction">Fiction</option>
                    <option value="Non-Fiction">Non-Fiction</option>
                    <option value="Science">Science</option>
                    <option value="Technology">Technology</option>
                    <option value="History">History</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Format:</label>
                <select name="format">
                    <option value="csv">CSV</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn-primary">📥 Download Export</button>
    </form>
</div>

<?php
// ...existing code...
?>