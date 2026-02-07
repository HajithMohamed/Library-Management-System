<style>
.my-recommend-container {
    max-width: 900px;
    margin: 40px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(102,126,234,0.15);
    padding: 32px 24px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.my-recommend-container h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 24px;
    color: #667eea;
    text-align: center;
}
.my-recommend-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
}
.my-recommend-table th, .my-recommend-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    font-size: 1rem;
}
.my-recommend-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.my-recommend-table tr:last-child td {
    border-bottom: none;
}
.status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.95rem;
    font-weight: 700;
    text-transform: capitalize;
    background: #fa709a;
    color: #fff;
}
.status-badge.approved { background: #43e97b; }
.status-badge.rejected { background: #f093fb; }
.status-badge.ordered { background: #fee140; color: #333; }
.status-badge.received { background: #38f9d7; }
.status-badge.pending { background: #fa709a; }
.reviewed-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 0.9rem;
    font-weight: 600;
    background: #e5e7eb;
    color: #374151;
    margin-left: 8px;
}
</style>
<div class="my-recommend-container">
    <h2>My Book Recommendations</h2>
    <table class="my-recommend-table">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Status</th>
            <th>Admin Notes</th>
            <th>Rejection Reason</th>
            <th>Reviewed</th>
        </tr>
        <?php foreach ($recommendations as $rec): ?>
            <tr>
                <td><?= htmlspecialchars($rec['title'] ?? '') ?></td>
                <td><?= htmlspecialchars($rec['author'] ?? '') ?></td>
                <td><span class="status-badge <?= htmlspecialchars($rec['status']) ?>"><?= htmlspecialchars($rec['status']) ?></span></td>
                <td><?= htmlspecialchars($rec['admin_notes'] ?? '') ?></td>
                <td><?= htmlspecialchars($rec['rejection_reason'] ?? '') ?></td>
                <td>
                    <?php if (!empty($rec['reviewed_by'])): ?>
                        <span class="reviewed-badge">Reviewed</span>
                    <?php else: ?>
                        <span class="reviewed-badge" style="background:#fa709a;color:#fff;">Not yet</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>