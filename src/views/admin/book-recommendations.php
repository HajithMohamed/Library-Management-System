<style>
.recommend-management-container {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(102,126,234,0.15);
    padding: 32px 24px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.recommend-management-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}
.recommend-management-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
}
.recommend-management-header .back-btn {
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    padding: 10px 18px;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(102,126,234,0.08);
    transition: background 0.2s;
}
.recommend-management-header .back-btn:hover {
    background: #e5e7eb;
}
.recommend-management-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
}
.recommend-management-table th, .recommend-management-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    font-size: 1rem;
}
.recommend-management-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.recommend-management-table tr:last-child td {
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
.action-btn {
    padding: 8px 18px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: 1rem;
    color: #fff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    cursor: pointer;
    margin-right: 8px;
    transition: background 0.3s, transform 0.2s;
}
.action-btn.reject {
    background: linear-gradient(135deg, #f093fb 0%, #fa709a 100%);
}
.action-btn:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
}
.action-btn.reject:hover {
    background: linear-gradient(135deg, #fa709a 0%, #f093fb 100%);
}
.icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f3f4f6;
    border: none;
    cursor: pointer;
    margin-right: 8px;
    transition: background 0.2s;
}
.icon-btn:hover {
    background: #e5e7eb;
}
</style>
<div class="recommend-management-container">
    <div class="recommend-management-header">
        <h2>Book Recommendations Management</h2>
        <button class="back-btn" onclick="window.location.href='/admin/dashboard'">&larr; Back to Dashboard</button>
    </div>
    <table class="recommend-management-table">
        <tr>
            <th>TITLE</th>
            <th>AUTHOR</th>
            <th>FACULTY</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
            <th>ADMIN NOTES</th>
            <th>REJECTION REASON</th>
        </tr>
        <?php foreach ($recommendations as $rec): ?>
            <tr>
                <td><?= htmlspecialchars($rec['title'] ?? '') ?></td>
                <td><?= htmlspecialchars($rec['author'] ?? '') ?></td>
                <td><?= htmlspecialchars($rec['recommended_by'] ?? '') ?></td>
                <td><span class="status-badge <?= htmlspecialchars($rec['status']) ?>"><?= htmlspecialchars($rec['status']) ?></span></td>
                <td>
                    <?php if ($rec['status'] == 'pending'): ?>
                        <form method="POST" action="/admin/recommendations/approve/<?= $rec['id'] ?>" style="display:inline">
                            <button type="submit" class="action-btn">Approve</button>
                        </form>
                        <button class="action-btn reject" onclick="showRejectPopup(<?= $rec['id'] ?>)">Reject</button>
                    <?php endif; ?>
                    <?php if ($rec['status'] == 'approved'): ?>
                        <form method="POST" action="/admin/recommendations/mark-ordered/<?= $rec['id'] ?>" style="display:inline">
                            <button type="submit" class="action-btn">Mark Ordered</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($rec['status'] == 'ordered'): ?>
                        <form method="POST" action="/admin/recommendations/mark-received/<?= $rec['id'] ?>" style="display:inline">
                            <button type="submit" class="action-btn">Mark Received</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($rec['admin_notes'] ?? '') ?></td>
                <td><?= htmlspecialchars($rec['rejection_reason'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<!-- Popup for rejection reason -->
<div class="popup-bg" id="rejectPopup">
    <form class="popup-form" method="POST" id="rejectForm">
        <h3>Reject Recommendation</h3>
        <textarea name="rejection_reason" required placeholder="Enter reason for rejection..."></textarea>
        <button type="submit">Submit Rejection</button>
    </form>
</div>
<script>
let rejectId = null;
function showRejectPopup(id) {
    rejectId = id;
    document.getElementById('rejectPopup').classList.add('active');
}
document.getElementById('rejectForm').onsubmit = function(e) {
    e.preventDefault();
    if (!rejectId) return;
    const form = e.target;
    const reason = form.rejection_reason.value;
    // Create a hidden form and submit
    const realForm = document.createElement('form');
    realForm.method = 'POST';
    realForm.action = `/admin/recommendations/reject/${rejectId}`;
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'rejection_reason';
    input.value = reason;
    realForm.appendChild(input);
    document.body.appendChild(realForm);
    realForm.submit();
    document.getElementById('rejectPopup').classList.remove('active');
    rejectId = null;
};
// Close popup on outside click
window.onclick = function(e) {
    const popup = document.getElementById('rejectPopup');
    if (popup.classList.contains('active') && !popup.contains(e.target) && e.target.className !== 'action-btn reject') {
        popup.classList.remove('active');
        rejectId = null;
    }
};
</script>