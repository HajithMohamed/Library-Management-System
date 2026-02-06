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

if (!$requestId || !in_array($action, ['approve', 'reject', 'mark_borrowed'])) {
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

    // Use dueDate from form, or calculate based on user's role privileges
    $userId = $request['userId'];
    $borrowPeriodDays = 14; // default
    $userStmt = $conn->prepare("SELECT borrow_period_days FROM users WHERE userId = ?");
    if ($userStmt) {
        $userStmt->bind_param("s", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result()->fetch_assoc();
        if ($userResult && isset($userResult['borrow_period_days'])) {
            $borrowPeriodDays = (int)$userResult['borrow_period_days'];
        }
        $userStmt->close();
    }
    $dueDate = $_POST['dueDate'] ?? date('Y-m-d', strtotime("+{$borrowPeriodDays} days"));

    // Update request status to Approved (do not create transaction or update book yet)
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Approved', approvedBy = ?, dueDate = ? WHERE id = ?");
    $stmt->bind_param("ssi", $adminId, $dueDate, $requestId);
    $stmt->execute();
    $stmt->close();

    // Send notification to user
    $notifStmt = $conn->prepare("INSERT INTO notifications (userId, title, message, type, priority, relatedId) VALUES (?, 'Borrow Request Approved', ?, 'approval', 'high', ?)");
    $notifMessage = "Your request to borrow '{$request['isbn']}' has been approved! Please visit the library to collect your book.";
    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
    $notifStmt->execute();
    $notifStmt->close();

    $_SESSION['success'] = 'Borrow request approved. Awaiting user to collect the book.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

if ($action === 'mark_borrowed') {
    // Mark as borrowed: move to books_borrowed, update book, update request status
    $stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$request || $request['status'] !== 'Approved') {
        $_SESSION['error'] = 'Request not found or not approved.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Check book availability
    $stmt = $conn->prepare("SELECT available FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $request['isbn']);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$book || $book['available'] <= 0) {
        $_SESSION['error'] = 'Book is not available for borrowing. Available: ' . ($book['available'] ?? 0);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $borrowDate = $_POST['borrowDate'] ?? date('Y-m-d');
    $dueDate = $request['dueDate'];
    
    // If dueDate not set, calculate from user's role-based borrow period
    if (empty($dueDate)) {
        $borrowPeriodDays = 14;
        $userPrivStmt = $conn->prepare("SELECT borrow_period_days FROM users WHERE userId = ?");
        if ($userPrivStmt) {
            $userPrivStmt->bind_param("s", $request['userId']);
            $userPrivStmt->execute();
            $privResult = $userPrivStmt->get_result()->fetch_assoc();
            if ($privResult && isset($privResult['borrow_period_days'])) {
                $borrowPeriodDays = (int)$privResult['borrow_period_days'];
            }
            $userPrivStmt->close();
        }
        $dueDate = date('Y-m-d', strtotime($borrowDate . " +{$borrowPeriodDays} days"));
    }

    // Check user's borrow limit before allowing
    $limitStmt = $conn->prepare("SELECT max_borrow_limit FROM users WHERE userId = ?");
    $maxLimit = 3;
    if ($limitStmt) {
        $limitStmt->bind_param("s", $request['userId']);
        $limitStmt->execute();
        $limitResult = $limitStmt->get_result()->fetch_assoc();
        if ($limitResult && isset($limitResult['max_borrow_limit'])) {
            $maxLimit = (int)$limitResult['max_borrow_limit'];
        }
        $limitStmt->close();
    }

    // Count current active borrows
    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM books_borrowed WHERE userId = ? AND returnDate IS NULL");
    $currentBorrows = 0;
    if ($countStmt) {
        $countStmt->bind_param("s", $request['userId']);
        $countStmt->execute();
        $countResult = $countStmt->get_result()->fetch_assoc();
        $currentBorrows = (int)($countResult['count'] ?? 0);
        $countStmt->close();
    }

    if ($currentBorrows >= $maxLimit) {
        $_SESSION['error'] = "User has reached their borrowing limit ({$currentBorrows}/{$maxLimit} books). Cannot issue more books.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Insert into books_borrowed
    $stmt = $conn->prepare("INSERT INTO books_borrowed (userId, isbn, borrowDate, dueDate, status, addedBy, createdAt, updatedAt) VALUES (?, ?, ?, ?, 'Active', ?, NOW(), NOW())");
    $stmt->bind_param("sssss", $request['userId'], $request['isbn'], $borrowDate, $dueDate, $adminId);
    $stmt->execute();
    $stmt->close();

    // Update book availability
    $stmt = $conn->prepare("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ?");
    $stmt->bind_param("s", $request['isbn']);
    $stmt->execute();
    $stmt->close();

    // Do NOT set status to 'Borrowed' (not allowed by ENUM). Just update updatedAt.
    $stmt = $conn->prepare("UPDATE borrow_requests SET updatedAt = NOW() WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $stmt->close();

    // Send notification to user
    $notifStmt = $conn->prepare("INSERT INTO notifications (userId, title, message, type, priority, relatedId) VALUES (?, 'Book Borrowed', ?, 'system', 'high', ?)");
    $notifMessage = "You have successfully borrowed the book (ISBN: {$request['isbn']}). Due date: {$dueDate}";
    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
    $notifStmt->execute();
    $notifStmt->close();

    $_SESSION['success'] = 'Book marked as borrowed and recorded in books_borrowed.';
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

    // Insert into book_reservations for record-keeping
    $stmt = $conn->prepare("INSERT INTO book_reservations (userId, isbn, reservationStatus, notifiedDate, expiryDate, createdAt, updatedAt) VALUES (?, ?, 'Rejected', NOW(), NULL, NOW(), NOW())");
    $stmt->bind_param("ss", $request['userId'], $request['isbn']);
    $stmt->execute();
    $stmt->close();

    // Send notification to user
    $notifStmt = $conn->prepare("INSERT INTO notifications (userId, title, message, type, priority, relatedId) VALUES (?, 'Borrow Request Rejected', ?, 'approval', 'high', ?)");
    $bookName = $book['bookName'] ?? 'the requested book';
    $notifMessage = "Your request to borrow '{$bookName}' has been rejected. Reason: {$reason}";
    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
    $notifStmt->execute();
    $notifStmt->close();

    $_SESSION['success'] = 'Borrow request rejected. User has been notified and reservation recorded.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
