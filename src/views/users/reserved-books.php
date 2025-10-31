<?php
// ...existing header...
$requests = $requests ?? [];
?>

<div class="container" style="max-width: 900px; margin: 40px auto;">
    <h2>My Reserved Books</h2>
    <table class="table" style="width:100%; background:#fff; border-radius:10px;">
        <thead>
            <tr>
                <th>Book</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Admin/Reason</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($requests)): ?>
            <tr><td colspan="7" style="text-align:center;">No reserved books found.</td></tr>
        <?php else: ?>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['bookName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($req['authorName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($req['isbn'] ?? '') ?></td>
                    <td><?= date('M d, Y H:i', strtotime($req['requestDate'])) ?></td>
                    <td>
                        <?php
                        $status = $req['status'];
                        if ($status == 'Pending') echo '<span style="color:orange;">Pending</span>';
                        elseif ($status == 'Approved') echo '<span style="color:green;">Approved</span>';
                        else echo '<span style="color:red;">Rejected</span>';
                        ?>
                    </td>
                    <td>
                        <?= $req['dueDate'] ? date('M d, Y', strtotime($req['dueDate'])) : '-' ?>
                    </td>
                    <td>
                        <?php
                        if ($status == 'Rejected') {
                            echo htmlspecialchars($req['rejectionReason'] ?? '-');
                        } else {
                            echo htmlspecialchars($req['approvedBy'] ?? '-');
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
