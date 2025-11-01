<?php
session_start();
require_once __DIR__ . '/../config/dbConnection.php';

if (!isset($_SESSION['userId']) || strtolower($_SESSION['userType']) !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$action = $_POST['action'] ?? '';
$requestId = $_POST['requestId'] ?? '';
$adminId = $_SESSION['userId'];

if (!$requestId || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

global $conn;

if ($action === 'approve') {
    // Get request details
    $stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$request) {
        $_SESSION['error'] = 'Request not found.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Check book availability
    $stmt = $conn->prepare("SELECT available, bookName FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $request['isbn']);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$book || $book['available'] <= 0) {
        $_SESSION['error'] = 'Book is not available for borrowing.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Use dueDate from form or default to 14 days from now
    $dueDate = $_POST['dueDate'] ?? date('Y-m-d', strtotime('+14 days'));

    // Update request status
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Approved', approvedBy = ?, dueDate = ? WHERE id = ?");
    $stmt->bind_param("ssi", $adminId, $dueDate, $requestId);
    $stmt->execute();
    $stmt->close();

    // Create transaction
    $tid = 'TXN' . time() . rand(100, 999);
    $borrowDate = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO transactions (tid, userId, isbn, borrowDate, dueDate, fineAmount, fineStatus) VALUES (?, ?, ?, ?, ?, 0.00, 'pending')");
    $stmt->bind_param("sssss", $tid, $request['userId'], $request['isbn'], $borrowDate, $dueDate);
    $stmt->execute();
    $stmt->close();

    // Update book availability
    $stmt = $conn->prepare("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ?");
    $stmt->bind_param("s", $request['isbn']);
    $stmt->execute();
    $stmt->close();

    // Send notification to user
    $notifStmt = $conn->prepare("INSERT INTO notifications (userId, title, message, type, priority, relatedId) VALUES (?, 'Borrow Request Approved', ?, 'approval', 'high', ?)");
    $notifMessage = "Your request to borrow '{$book['bookName']}' has been approved! Due date: {$dueDate}";
    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
    $notifStmt->execute();
    $notifStmt->close();

    $_SESSION['success'] = 'Borrow request approved successfully. User has been notified.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

if ($action === 'reject') {
    $reason = trim($_POST['reason'] ?? 'Request rejected by administrator');

    // Get request details for notification
    $stmt = $conn->prepare("SELECT userId, isbn FROM borrow_requests WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$request) {
        $_SESSION['error'] = 'Request not found.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Get book name
    $stmt = $conn->prepare("SELECT bookName FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $request['isbn']);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Update request status
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Rejected', rejectionReason = ?, approvedBy = ? WHERE id = ?");
    $stmt->bind_param("ssi", $reason, $adminId, $requestId);
    $stmt->execute();
    $stmt->close();

    // Send notification to user
    $notifStmt = $conn->prepare("INSERT INTO notifications (userId, title, message, type, priority, relatedId) VALUES (?, 'Borrow Request Rejected', ?, 'approval', 'high', ?)");
    $bookName = $book['bookName'] ?? 'the requested book';
    $notifMessage = "Your request to borrow '{$bookName}' has been rejected. Reason: {$reason}";
    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
    $notifStmt->execute();
    $notifStmt->close();

    $_SESSION['success'] = 'Borrow request rejected. User has been notified.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
