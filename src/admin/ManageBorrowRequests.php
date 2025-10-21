<?php
include '../../config/config.php';
session_start();
include DIR_URL . 'src/global/middleware.php';
include DIR_URL . 'config/dbConnection.php';

// Restrict access
$userType = $_SESSION['userType'];
if ($userType != 'Admin' && $userType != 'Librarian') {
  http_response_code(403);
  echo '<h2 style="color:red;text-align:center;margin-top:40px;">403 Forbidden</h2>';
  exit();
}

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
  $id = intval($_POST['id']);
  $status = $_POST['action'] === 'approve' ? 'Approved' : 'Rejected';
  $conn->query("UPDATE borrow_requests SET status='$status' WHERE id=$id");
  echo json_encode(['success' => true]);
  exit;
}

// Fetch pending requests
$sql = "SELECT br.id, br.isbn, br.userId, br.requestDate, br.status,
               u.userId AS userCode, u.emailId, b.bookName, b.bookImage
        FROM borrow_requests br
        JOIN users u ON br.userId = u.userId
        JOIN books b ON br.isbn = b.isbn
        WHERE br.status = 'Pending'
        ORDER BY br.requestDate DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Borrow Requests</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
  <style>
    /* ==== General Layout ==== */
    body {
      font-family: 'Poppins', 'Segoe UI', sans-serif;
      margin: 0;
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 10px;
    }

    .container {
      width: 95%;
      max-width: 1200px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(15px);
      border-radius: 16px;
      padding: 25px 20px 35px;
      box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.8s ease;
    }

    h1 {
      text-align: center;
      color: #fff;
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      margin-bottom: 25px;
    }

    h1 i {
      margin-right: 8px;
      color: #ffd86f;
    }

    /* ==== Table ==== */
    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 10px;
      overflow: hidden;
    }

    thead {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
    }

    th {
      text-align: left;
      padding: 14px 16px;
      font-weight: 600;
      font-size: 15px;
    }

    tbody tr {
      background: rgba(255, 255, 255, 0.08);
      transition: all 0.3s ease;
    }

    tbody tr:hover {
      background: rgba(255, 255, 255, 0.18);
      transform: scale(1.005);
    }

    td {
      padding: 14px 16px;
      color: #fff;
      font-size: 14.5px;
      vertical-align: middle;
    }

    /* ==== Book Image ==== */
    .book-img {
      width: 52px;
      height: 70px;
      border-radius: 8px;
      object-fit: cover;
      background-color: #eee;
      box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    }

    /* ==== Buttons ==== */
    td[style*="text-align:center;"] {
      white-space: nowrap;
    }

    .action-btn {
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.25s ease;
      color: #fff;
      margin: 3px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .approve-btn {
      background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
      box-shadow: 0 3px 10px rgba(0,176,155,0.3);
    }

    .approve-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,176,155,0.45);
    }

    .reject-btn {
      background: linear-gradient(135deg, #ff512f 0%, #dd2476 100%);
      box-shadow: 0 3px 10px rgba(255,81,47,0.3);
    }

    .reject-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255,81,47,0.45);
    }

    /* ==== No Data ==== */
    .no-requests {
      text-align: center;
      color: #fff;
      font-size: 1.1rem;
      padding: 30px 0;
      opacity: 0.85;
    }

    /* ==== Animation ==== */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .fade-out {
      opacity: 0;
      transform: scale(0.97);
      transition: all 0.3s ease;
    }

    @media (max-width: 700px) {
      th, td { font-size: 12.5px; padding: 10px 8px; }
      .book-img { width: 40px; height: 56px; }
      h1 { font-size: 1.6rem; }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1><i class="fa-solid fa-envelope-open-text"></i> Manage Borrow Requests</h1>
    <table>
      <thead>
        <tr>
          <th>Book</th>
          <th>Title</th>
          <th>User</th>
          <th>Email</th>
          <th>Request Date</th>
          <th style="text-align:center;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr id="row-<?php echo $row['id']; ?>">
              <td><img src="<?php echo BASE_URL . (!empty($row['bookImage']) ? $row['bookImage'] : 'assets/images/default-book.png'); ?>" class="book-img" alt="Book"></td>
              <td><?php echo htmlspecialchars($row['bookName']); ?></td>
              <td><?php echo htmlspecialchars($row['userCode']); ?></td>
              <td><?php echo htmlspecialchars($row['emailId']); ?></td>
              <td><?php echo date('d M Y', strtotime($row['requestDate'])); ?></td>
              <td style="text-align:center;">
                <button class="action-btn approve-btn" onclick="handleRequest(<?php echo $row['id']; ?>, 'approve')">
                  <i class="fa-solid fa-check"></i> Approve
                </button>
                <button class="action-btn reject-btn" onclick="handleRequest(<?php echo $row['id']; ?>, 'reject')">
                  <i class="fa-solid fa-xmark"></i> Reject
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" class="no-requests">No pending borrow requests.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <script>
    function handleRequest(requestId, action) {
      if (!confirm(`Are you sure to ${action} this request?`)) return;
      fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=${encodeURIComponent(action)}&id=${encodeURIComponent(requestId)}`
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const row = document.getElementById('row-' + requestId);
          row.classList.add('fade-out');
          setTimeout(() => row.remove(), 300);
          alert(`Request ${action === 'approve' ? 'approved' : 'rejected'} successfully!`);
        } else {
          alert('Failed to update request.');
        }
      })
      .catch(() => alert('Network error.'));
    }
  </script>
</body>
</html>
