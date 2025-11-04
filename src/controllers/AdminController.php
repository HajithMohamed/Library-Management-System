<?php

namespace App\Controllers;

use App\Services\AdminService;
use App\Services\AuthService; // ADDED: Import AuthService
use App\Helpers\AuthHelper;
use App\Helpers\ValidationHelper; // FIXED: Add this

class AdminController
{
  private $adminService;
  private $authHelper;
  private $authService; // ADDED: AuthService instance

  public function __construct()
  {
    $this->adminService = new AdminService();
    $this->authHelper = new AuthHelper();
    $this->authService = new AuthService(); // ADDED: Initialize AuthService
  }

  /**
   * Admin Dashboard
   */
  public function dashboard()
  {
    $this->authHelper->requireAuth();
    $userType = $_SESSION['userType'] ?? '';
    if (strtolower($userType) !== 'admin') {
        http_response_code(403);
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    global $conn;

    // Fetch total books
    $totalBooksResult = $conn->query("SELECT COUNT(*) as count FROM books");
    $totalBooks = $totalBooksResult->fetch_assoc()['count'];

    // Fetch available books (sum of available column)
    $availableBooksResult = $conn->query("SELECT SUM(available) as count FROM books");
    $availableBooks = $availableBooksResult->fetch_assoc()['count'] ?? 0;

    // Fetch borrowed books (sum of borrowed column)
    $borrowedBooksResult = $conn->query("SELECT SUM(borrowed) as count FROM books");
    $borrowedBooks = $borrowedBooksResult->fetch_assoc()['count'] ?? 0;

    // Fetch total users
    $totalUsersResult = $conn->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $totalUsersResult->fetch_assoc()['count'];

    // Fetch active users (verified users)
    $activeUsersResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE isVerified = 1");
    $activeUsers = $activeUsersResult->fetch_assoc()['count'];

    // Fetch total transactions
    $totalTransactionsResult = $conn->query("SELECT COUNT(*) as count FROM transactions");
    $totalTransactions = $totalTransactionsResult->fetch_assoc()['count'];

    // Fetch active borrowings (not returned yet)
    $activeBorrowingsResult = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE returnDate IS NULL");
    $activeBorrowings = $activeBorrowingsResult->fetch_assoc()['count'];

    // Fetch overdue books (borrowed more than 14 days ago, not returned)
    $overdueBooksResult = $conn->query("
            SELECT COUNT(*) as count
            FROM transactions
            WHERE returnDate IS NULL
            AND DATEDIFF(CURDATE(), borrowDate) > 14
        ");
    $overdueBooks = $overdueBooksResult->fetch_assoc()['count'];

    // Fetch low stock books (available <= 2)
    $lowStockBooksResult = $conn->query("SELECT COUNT(*) as count FROM books WHERE available <= 2");
    $lowStockBooks = $lowStockBooksResult->fetch_assoc()['count'];

    // Prepare stats array for dashboard
    $stats = [
      'users' => [
        'total_users' => $totalUsers,
        'active_users' => $activeUsers
      ],
      'books' => [
        'total_books' => $totalBooks,
        'available_books' => $availableBooks,
        'borrowed_books' => $borrowedBooks
      ],
      'active_borrowings' => $activeBorrowings,
      'overdue_books' => $overdueBooks,
      'total_transactions' => $totalTransactions
    ];

    // Prepare system health array
    $systemHealth = [
      'database' => $conn->ping() ? 'healthy' : 'error',
      'disk_space' => 'healthy', // Simplified for now
      'overdue_books' => $overdueBooks,
      'low_stock_books' => $lowStockBooks,
      'overall' => ($overdueBooks > 50 || $lowStockBooks > 20) ? 'warning' : 'healthy'
    ];

    // Fetch recent transactions (last 5)
    $recentTransactionsQuery = "
            SELECT
                t.tid,
                t.userId,
                u.emailId,
                u.userType,
                t.isbn,
                b.bookName,
                b.authorName,
                t.borrowDate,
                t.returnDate
            FROM transactions t
            LEFT JOIN users u ON t.userId = u.userId
            LEFT JOIN books b ON t.isbn = b.isbn
            ORDER BY t.borrowDate DESC
            LIMIT 5
        ";
    $recentTransactionsResult = $conn->query($recentTransactionsQuery);
    $recentTransactions = [];
    if ($recentTransactionsResult) {
      while ($row = $recentTransactionsResult->fetch_assoc()) {
        $recentTransactions[] = $row;
      }
    }

    // Fetch popular books (top 5 most borrowed)
    $popularBooksQuery = "
            SELECT
                b.isbn,
                b.bookName,
                b.authorName,
                b.publisherName,
                COUNT(t.tid) as borrow_count
            FROM books b
            LEFT JOIN transactions t ON b.isbn = t.isbn
            GROUP BY b.isbn, b.bookName, b.authorName, b.publisherName
            ORDER BY borrow_count DESC
            LIMIT 5
        ";
    $popularBooksResult = $conn->query($popularBooksQuery);
    $popularBooks = [];
    if ($popularBooksResult) {
      while ($row = $popularBooksResult->fetch_assoc()) {
        $popularBooks[] = $row;
      }
    }

    // Fetch recent notifications (last 5 unread)
    $notificationsQuery = "
            SELECT id, userId, title, message, type, priority, isRead, createdAt
            FROM notifications
            WHERE isRead = 0
            ORDER BY createdAt DESC
            LIMIT 5
        ";
    $notificationsResult = $conn->query($notificationsQuery);
    $notifications = [];
    if ($notificationsResult) {
      while ($row = $notificationsResult->fetch_assoc()) {
        $notifications[] = $row;
      }
    }

    // Pass all data to view
    $pageTitle = 'Admin Dashboard';
    include APP_ROOT . '/views/admin/dashboard.php';
  }

  /**
   * Manage Users
   */
  public function users()
  {
    // Check if user is admin
    if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
      header('Location: ' . BASE_URL . '403');
      exit();
    }

    global $conn;

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = $_POST['action'] ?? 'add';

      if ($action === 'edit') {
        // Handle edit user
        $userId = trim($_POST['userId'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $emailId = trim($_POST['emailId'] ?? '');
        $phoneNumber = trim($_POST['phoneNumber'] ?? '');
        $userType = $_POST['userType'] ?? 'Student';
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $address = $_POST['address'] ?? '';
        $isVerified = isset($_POST['isVerified']) ? 1 : 0;
        $newPassword = $_POST['new_password'] ?? '';

        // Validate required fields
        if (empty($userId) || empty($username) || empty($emailId) || empty($phoneNumber)) {
          $_SESSION['error'] = 'Please fill all required fields.';
          header('Location: ' . BASE_URL . 'admin/users');
          exit();
        }

        // Check if username or email already exists for other users
        $checkStmt = $conn->prepare("SELECT userId FROM users WHERE (username = ? OR emailId = ?) AND userId != ?");
        $checkStmt->bind_param("sss", $username, $emailId, $userId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
          $_SESSION['error'] = 'Username or email already exists.';
          $checkStmt->close();
          header('Location: ' . BASE_URL . 'admin/users');
          exit();
        }
        $checkStmt->close();

        // Update user (without password if not provided)
        if (!empty($newPassword)) {
          $hashedPassword = $this->authHelper->hashPassword($newPassword);
          $updateStmt = $conn->prepare("
            UPDATE users 
            SET username = ?, emailId = ?, phoneNumber = ?, userType = ?, gender = ?, dob = ?, address = ?, isVerified = ?, password = ?, updatedAt = NOW()
            WHERE userId = ?
          ");
          $updateStmt->bind_param("ssssssssss", $username, $emailId, $phoneNumber, $userType, $gender, $dob, $address, $isVerified, $hashedPassword, $userId);
        } else {
          $updateStmt = $conn->prepare("
            UPDATE users 
            SET username = ?, emailId = ?, phoneNumber = ?, userType = ?, gender = ?, dob = ?, address = ?, isVerified = ?, updatedAt = NOW()
            WHERE userId = ?
          ");
          $updateStmt->bind_param("sssssssss", $username, $emailId, $phoneNumber, $userType, $gender, $dob, $address, $isVerified, $userId);
        }

        if ($updateStmt->execute()) {
          $_SESSION['success'] = "User '{$username}' updated successfully!";
        } else {
          $_SESSION['error'] = 'Failed to update user. Please try again.';
        }
        $updateStmt->close();

        header('Location: ' . BASE_URL . 'admin/users');
        exit();
      } else {
        // Handle add user (existing code)
        $username = trim($_POST['username'] ?? '');
        $emailId = trim($_POST['emailId'] ?? '');
        $phoneNumber = trim($_POST['phoneNumber'] ?? '');
        $plainPassword = $_POST['password'] ?? '';
        $userType = $_POST['userType'] ?? 'Student';
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $address = $_POST['address'] ?? '';
        $isVerified = isset($_POST['isVerified']) ? 1 : 0;

        // Validate required fields
        if (empty($username) || empty($emailId) || empty($phoneNumber) || empty($plainPassword)) {
          $_SESSION['error'] = 'Please fill all required fields.';
          header('Location: ' . BASE_URL . 'admin/users');
          exit();
        }

        // Check if username or email already exists
        $checkStmt = $conn->prepare("SELECT userId FROM users WHERE username = ? OR emailId = ?");
        $checkStmt->bind_param("ss", $username, $emailId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
          $_SESSION['error'] = 'Username or email already exists.';
          $checkStmt->close();
          header('Location: ' . BASE_URL . 'admin/users');
          exit();
        }
        $checkStmt->close();

        // Hash password
        $hashedPassword = $this->authHelper->hashPassword($plainPassword);

        // Generate user ID based on type
        $currentYear = date('Y');
        $prefix = ($userType === 'Faculty') ? 'FAC' : 'STU';
        
        $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE userId LIKE ?");
        $pattern = $prefix . $currentYear . '%';
        $countStmt->bind_param("s", $pattern);
        $countStmt->execute();
        $count = $countStmt->get_result()->fetch_assoc()['count'];
        $countStmt->close();
        
        $userId = $prefix . $currentYear . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Insert new user
        $insertStmt = $conn->prepare("
          INSERT INTO users (userId, username, emailId, phoneNumber, password, userType, gender, dob, address, isVerified, createdAt) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $insertStmt->bind_param("sssssssssi", $userId, $username, $emailId, $phoneNumber, $hashedPassword, $userType, $gender, $dob, $address, $isVerified);

        if ($insertStmt->execute()) {
          // ADDED: Send email to Faculty user with credentials
          if ($userType === 'Faculty') {
            $emailSubject = "Your Library Account - Faculty Access";
            $emailBody = "Dear {$username},\n\n";
            $emailBody .= "Your faculty account has been created in the Library Management System.\n\n";
            $emailBody .= "Login Credentials:\n";
            $emailBody .= "User ID: {$userId}\n";
            $emailBody .= "Username: {$username}\n";
            $emailBody .= "Password: {$plainPassword}\n\n";
            $emailBody .= "Please login at: " . BASE_URL . "\n\n";
            $emailBody .= "IMPORTANT: Please change your password after your first login for security purposes.\n\n";
            $emailBody .= "If you did not request this account, please contact the library administrator immediately.\n\n";
            $emailBody .= "Best regards,\n";
            $emailBody .= "Library Management System\n";
            $emailBody .= "Automated Email - Please Do Not Reply";

            // Send email using AuthService
            $emailSent = $this->authService->sendEmail($emailId, $emailSubject, $emailBody);

            if ($emailSent) {
              $_SESSION['success'] = "Faculty user '{$username}' added successfully! Login credentials sent to {$emailId}. User ID: {$userId}";
            } else {
              $_SESSION['success'] = "Faculty user '{$username}' added successfully! User ID: {$userId}. Note: Email notification failed - please share credentials manually.";
            }
          } else {
            $_SESSION['success'] = "User '{$username}' added successfully! User ID: {$userId}";
          }
        } else {
          $_SESSION['error'] = 'Failed to add user. Please try again.';
        }
        $insertStmt->close();

        header('Location: ' . BASE_URL . 'admin/users');
        exit();
      }
    }

    // Fetch all users with username
    $sql = "SELECT userId, username, emailId, phoneNumber, userType, gender, dob, address, isVerified, createdAt
                FROM users
                ORDER BY createdAt DESC";

    $result = $conn->query($sql);

    $users = [];
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $users[] = $row;
      }
    }

    // Get statistics
    $totalUsers = count($users);
    $verifiedUsers = count(array_filter($users, fn($u) => $u['isVerified'] == 1));
    $adminCount = count(array_filter($users, fn($u) => $u['userType'] === 'Admin'));
    $studentCount = count(array_filter($users, fn($u) => $u['userType'] === 'Student'));
    $teacherCount = count(array_filter($users, fn($u) => $u['userType'] === 'Teacher'));

    // Pass data to view
    $pageTitle = 'Users Management';
    include APP_ROOT . '/views/admin/users.php';
  }

  /**
   * Delete User
   */
  public function deleteUser()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $userId = $_POST['userId'] ?? '';

      if ($this->adminService->deleteUser($userId)) {
        $_SESSION['success'] = 'User deleted successfully.';
      } else {
        $_SESSION['error'] = 'Failed to delete user. User may have active transactions.';
      }
    }

    $this->redirect('/admin/users');
  }

  /**
   * Fine Management
   */
  public function fines()
  {
    $this->authHelper->requireAdmin();

    $status = $_GET['status'] ?? null;
    $userId = $_GET['userId'] ?? null;
    $fines = $this->adminService->getAllFines($status, $userId);
    
    // Get fine settings - now returns associative array with setting names as keys
    $fineSettings = $this->adminService->getFineSettings();

    $this->render('admin/fines', [
      'fines' => $fines,
      'fineSettings' => $fineSettings,
      'currentStatus' => $status,
      'currentUserId' => $userId
    ]);
  }

  /**
   * Update Fine Status
   */
  public function updateFines()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = $_POST['action'] ?? '';

      switch ($action) {
        case 'update_status':
          $transactionId = $_POST['transactionId'] ?? '';
          $status = $_POST['status'] ?? '';
          $paymentMethod = $_POST['paymentMethod'] ?? null;

          if ($this->adminService->updateFineStatus($transactionId, $status, $paymentMethod)) {
            $_SESSION['success'] = 'Fine status updated successfully.';
          } else {
            $_SESSION['error'] = 'Failed to update fine status.';
          }
          break;

        case 'update_settings':
          $settings = $_POST['settings'] ?? [];
          
          // Validate settings
          $errors = [];
          
          if (!isset($settings['fine_per_day']) || $settings['fine_per_day'] < 0) {
            $errors[] = 'Fine per day must be a positive number.';
          }
          
          if (!isset($settings['max_borrow_days']) || $settings['max_borrow_days'] < 1) {
            $errors[] = 'Max borrow days must be at least 1.';
          }
          
          if (!isset($settings['grace_period_days']) || $settings['grace_period_days'] < 0) {
            $errors[] = 'Grace period must be 0 or positive.';
          }
          
          if (!isset($settings['max_fine_amount']) || $settings['max_fine_amount'] < 0) {
            $errors[] = 'Max fine amount must be a positive number.';
          }
          
          if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            break;
          }
          
          // Log the settings being updated
          error_log("Fine settings being updated: " . print_r($settings, true));
          
          $updated = $this->adminService->updateFineSettings($settings);
          
          if ($updated > 0) {
            $_SESSION['success'] = "Successfully updated {$updated} fine setting(s). All pending fines have been recalculated and users have been notified.";
          } else {
            $_SESSION['error'] = 'No settings were updated. Please check if values were changed.';
          }
          break;

        case 'update_all_fines':
          $updatedCount = $this->adminService->updateAllFines();
          $_SESSION['success'] = "Updated {$updatedCount} fine(s) based on current settings.";
          break;
      }
    }

    $this->redirect('/admin/fines');
  }

  /**
   * Borrow Requests Management
   */
  public function borrowRequests()
  {
    $this->authHelper->requireAdmin();

    $status = $_GET['status'] ?? 'pending';

    // Fix: Fetch all requests if status is 'all'
    global $mysqli;
    if ($status === 'all') {
        $requests = [];
        $sql = "SELECT br.*, u.username as userName, b.bookName as bookTitle, b.authorName
                FROM borrow_requests br
                LEFT JOIN users u ON br.userId = u.userId
                LEFT JOIN books b ON br.isbn = b.isbn
                ORDER BY br.requestDate DESC";
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    } else {
        $requests = $this->adminService->getBorrowRequests($status);
    }

    // --- NEW: Fetch counts for all statuses ---
    $statusCounts = [
      'pending' => 0,
      'approved' => 0,
      'rejected' => 0,
      'all' => 0
    ];
    $result = $mysqli->query("SELECT status, COUNT(*) as cnt FROM borrow_requests GROUP BY status");
    while ($row = $result->fetch_assoc()) {
      $key = strtolower($row['status']);
      if (isset($statusCounts[$key])) {
        $statusCounts[$key] = (int)$row['cnt'];
      }
    }
    // Total count
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM borrow_requests");
    $statusCounts['all'] = (int)($result->fetch_assoc()['cnt'] ?? 0);

    $this->render('admin/borrow-requests', [
      'requests' => $requests,
      'currentStatus' => $status,
      'statusCounts' => $statusCounts // pass to view
    ]);
  }

  /**
   * Handle Borrow Request Actions
   */
  public function handleBorrowRequest()
  {
    $this->authHelper->requireAuth();
    
    // Check if user is admin
    $userType = $_SESSION['userType'] ?? '';
    if (strtolower($userType) !== 'admin') {
        http_response_code(403);
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        global $mysqli;

        $requestId = $_POST['requestId'] ?? '';
        $action = $_POST['action'] ?? '';
        $adminId = $_SESSION['userId'];

        switch ($action) {
            case 'approve':
                // Approve: only update status, do not create transaction or update book
                $stmt = $mysqli->prepare("SELECT userId, isbn FROM borrow_requests WHERE id = ?");
                $stmt->bind_param("i", $requestId);
                $stmt->execute();
                $result = $stmt->get_result();
                $request = $result->fetch_assoc();

                if (!$request) {
                    $_SESSION['error'] = 'Request not found.';
                    break;
                }

                $dueDate = $_POST['dueDate'] ?? date('Y-m-d', strtotime('+14 days'));

                $updateStmt = $mysqli->prepare("
                    UPDATE borrow_requests 
                    SET status = 'Approved', approvedBy = ?, dueDate = ? 
                    WHERE id = ?
                ");
                $updateStmt->bind_param("ssi", $adminId, $dueDate, $requestId);

                if ($updateStmt->execute()) {
                    // Send notification to user
                    $notifStmt = $mysqli->prepare("
                        INSERT INTO notifications (userId, title, message, type, priority, relatedId) 
                        VALUES (?, 'Borrow Request Approved', ?, 'approval', 'high', ?)
                    ");
                    $notifMessage = "Your request to borrow '{$request['isbn']}' has been approved! Please visit the library to collect your book.";
                    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
                    $notifStmt->execute();

                    $_SESSION['success'] = 'Borrow request approved. Awaiting user to collect the book.';
                } else {
                    $_SESSION['error'] = 'Failed to approve borrow request.';
                }
                break;

            case 'mark_borrowed':
                // Mark as borrowed: move to books_borrowed, update book, update request status
                $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE id = ?");
                $stmt->bind_param("i", $requestId);
                $stmt->execute();
                $request = $stmt->get_result()->fetch_assoc();

                if (!$request || $request['status'] !== 'Approved') {
                    $_SESSION['error'] = 'Request not found or not approved.';
                    break;
                }

                // Check book availability
                $bookStmt = $mysqli->prepare("SELECT available FROM books WHERE isbn = ?");
                $bookStmt->bind_param("s", $request['isbn']);
                $bookStmt->execute();
                $book = $bookStmt->get_result()->fetch_assoc();

                if (!$book || $book['available'] <= 0) {
                    $_SESSION['error'] = 'Book is not available for borrowing.';
                    break;
                }

                $borrowDate = $_POST['borrowDate'] ?? date('Y-m-d');
                $dueDate = $request['dueDate'];

                // Insert into books_borrowed
                $stmt = $mysqli->prepare("INSERT INTO books_borrowed (userId, isbn, borrowDate, dueDate, status, addedBy, createdAt, updatedAt) VALUES (?, ?, ?, ?, 'Active', ?, NOW(), NOW())");
                $stmt->bind_param("sssss", $request['userId'], $request['isbn'], $borrowDate, $dueDate, $adminId);
                $stmt->execute();

                // Update book availability
                $mysqli->query("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = '{$request['isbn']}'");

                // Do NOT set status to 'Borrowed' (not allowed by ENUM). Just update updatedAt.
                $updateStmt = $mysqli->prepare("UPDATE borrow_requests SET updatedAt = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $requestId);
                $updateStmt->execute();

                // Send notification to user
                $notifStmt = $mysqli->prepare("
                    INSERT INTO notifications (userId, title, message, type, priority, relatedId) 
                    VALUES (?, 'Book Borrowed', ?, 'system', 'high', ?)
                ");
                $notifMessage = "You have successfully borrowed the book (ISBN: {$request['isbn']}). Due date: {$dueDate}";
                $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
                $notifStmt->execute();

                $_SESSION['success'] = 'Book marked as borrowed and recorded in books_borrowed.';
                break;

            case 'reject':
                $reason = trim($_POST['reason'] ?? 'Request rejected by administrator');

                // Get request details for notification
                $stmt = $mysqli->prepare("SELECT userId, isbn FROM borrow_requests WHERE id = ?");
                $stmt->bind_param("i", $requestId);
                $stmt->execute();
                $result = $stmt->get_result();
                $request = $result->fetch_assoc();

                if (!$request) {
                    $_SESSION['error'] = 'Request not found.';
                    break;
                }

                // Get book name
                $bookStmt = $mysqli->prepare("SELECT bookName FROM books WHERE isbn = ?");
                $bookStmt->bind_param("s", $request['isbn']);
                $bookStmt->execute();
                $book = $bookStmt->get_result()->fetch_assoc();

                // Update request status
                $updateStmt = $mysqli->prepare("
                    UPDATE borrow_requests 
                    SET status = 'Rejected', rejectionReason = ?, approvedBy = ? 
                    WHERE id = ?
                ");
                $updateStmt->bind_param("ssi", $reason, $adminId, $requestId);
                $updateStmt->execute();

                // Insert into book_reservations for record-keeping
                $stmt = $mysqli->prepare("INSERT INTO book_reservations (userId, isbn, reservationStatus, notifiedDate, expiryDate, createdAt, updatedAt) VALUES (?, ?, 'Rejected', NOW(), NULL, NOW(), NOW())");
                $stmt->bind_param("ss", $request['userId'], $request['isbn']);
                $stmt->execute();

                // Send notification to user
                $notifStmt = $mysqli->prepare("
                    INSERT INTO notifications (userId, title, message, type, priority, relatedId) 
                    VALUES (?, 'Borrow Request Rejected', ?, 'approval', 'high', ?)
                ");
                $bookName = $book['bookName'] ?? 'the requested book';
                $notifMessage = "Your request to borrow '{$bookName}' has been rejected. Reason: {$reason}";
                $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
                $notifStmt->execute();

                $_SESSION['success'] = 'Borrow request rejected. User has been notified and reservation recorded.';
                break;
        }
    }

    $this->redirect('/admin/borrow-requests');
  }

  /**
   * Notifications Management
   */
  public function notifications()
  {
    $this->authHelper->requireAdmin();

    $type = $_GET['type'] ?? null;
    $unreadOnly = isset($_GET['unread']) ? true : false;
    $userTypeFilter = $_GET['userType'] ?? null;
    $priority = $_GET['priority'] ?? null;
    $viewMode = $_GET['viewMode'] ?? 'own'; // 'own' or 'all'
    
    global $mysqli;
    
    $adminUserId = $_SESSION['userId'];
    
    // Build dynamic query
    $sql = "SELECT 
                n.id,
                n.userId,
                n.title,
                n.message,
                n.type,
                n.priority,
                n.isRead,
                n.relatedId,
                n.createdAt,
                u.userType,
                u.username,
                u.emailId
            FROM notifications n
            LEFT JOIN users u ON n.userId = u.userId
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Filter by view mode
    if ($viewMode === 'own') {
        $sql .= " AND (n.userId = ? OR n.userId IS NULL)";
        $params[] = $adminUserId;
        $types .= 's';
    }
    // If viewMode is 'all', show all notifications (no userId filter)
    
    if ($type) {
        $sql .= " AND n.type = ?";
        $params[] = $type;
        $types .= 's';
    }
    
    if ($unreadOnly) {
        $sql .= " AND n.isRead = 0";
    }
    
    if ($userTypeFilter) {
        $sql .= " AND (u.userType = ? OR n.userId IS NULL)";
        $params[] = $userTypeFilter;
        $types .= 's';
    }
    
    if ($priority) {
        $sql .= " AND n.priority = ?";
        $params[] = $priority;
        $types .= 's';
    }
    
    $sql .= " ORDER BY n.isRead ASC, n.createdAt DESC";
    
    $stmt = $mysqli->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    $this->render('admin/notifications', [
      'notifications' => $notifications,
      'currentType' => $type,
      'unreadOnly' => $unreadOnly,
      'userTypeFilter' => $userTypeFilter,
      'viewMode' => $viewMode
    ]);
  }

  /**
   * Mark Notification as Read
   */
  public function markNotificationRead()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $notificationId = $_POST['notificationId'] ?? '';

      if ($this->adminService->markNotificationAsRead($notificationId)) {
        $_SESSION['success'] = 'Notification marked as read.';
      } else {
        $_SESSION['error'] = 'Failed to mark notification as read.';
      }
    }

    $this->redirect('/admin/notifications');
  }

  /**
   * System Maintenance
   */
  public function maintenance()
  {
    $this->authHelper->requireAdmin();

    $systemHealth = $this->adminService->getSystemHealth();
    $backupHistory = $this->adminService->getBackupHistory(10);
    $maintenanceLog = $this->adminService->getMaintenanceLog(20);

    $this->render('admin/maintenance', [
      'systemHealth' => $systemHealth,
      'backupHistory' => $backupHistory,
      'maintenanceLog' => $maintenanceLog
    ]);
  }

  /**
   * Create Database Backup
   */
  public function createBackup()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $backupType = $_POST['backupType'] ?? 'manual';

      $result = $this->adminService->createDatabaseBackup($backupType);

      if ($result['success']) {
        $_SESSION['success'] = $result['message'];
      } else {
        $_SESSION['error'] = $result['message'];
      }
    }

    $this->redirect('/admin/maintenance');
  }

  /**
   * Perform Maintenance Tasks
   */
  public function performMaintenance()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $tasks = $_POST['tasks'] ?? [];

      if (!empty($tasks)) {
        $results = $this->adminService->performMaintenance($tasks);

        $successCount = 0;
        $messages = [];
        
        foreach ($results as $task => $result) {
          if ($result > 0) {
            $successCount++;
            $taskName = ucwords(str_replace('_', ' ', $task));
            $messages[] = "{$taskName}: {$result} items processed";
          }
        }

        if ($successCount > 0) {
          $_SESSION['success'] = "Completed {$successCount} maintenance task(s). " . implode(', ', $messages);
        } else {
          $_SESSION['error'] = 'No maintenance tasks were completed.';
        }
      } else {
        $_SESSION['error'] = 'Please select at least one maintenance task.';
      }
    }

    $this->redirect('/admin/maintenance');
  }

  /**
   * Reports
   */
  public function reports()
  {
    $this->authHelper->requireAuth();
    
    // Check if user is admin
    $userType = $_SESSION['userType'] ?? '';
    if (strtolower($userType) !== 'admin') {
        http_response_code(403);
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    // Get filter parameters
    $reportType = $_GET['type'] ?? 'overview';
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    $report = [];
    
    // Initialize models
    $transactionModel = new \App\Models\Transaction();
    $userModel = new \App\Models\User();
    $bookModel = new \App\Models\Book();
    
    switch ($reportType) {
        case 'overview':
            $report = [
                'total_transactions' => $transactionModel->getTotalTransactionsCount($startDate, $endDate),
                'total_users' => $userModel->getTotalUsersCount(),
                'total_books' => $bookModel->getTotalBooksCount(),
                'total_fines' => $transactionModel->getTotalFinesAmount($startDate, $endDate),
                'active_borrowings' => $transactionModel->getActiveBorrowingCount(),
                'overdue_books' => $transactionModel->getOverdueBooksCount($startDate, $endDate)
            ];
            break;
            
        case 'borrowing':
            $report = [
                'total_borrowings' => $transactionModel->getTotalTransactionsCount($startDate, $endDate),
                'returned_books' => $transactionModel->getReturnedBooksCount($startDate, $endDate),
                'active_loans' => $transactionModel->getActiveBorrowingCount(),
                'overdue_books' => $transactionModel->getOverdueBooksCount($startDate, $endDate)
            ];
            break;
            
        case 'fines':
            $report = [
                'total_fines' => $transactionModel->getTotalFinesAmount($startDate, $endDate),
                'collected_fines' => $transactionModel->getCollectedFinesAmount($startDate, $endDate),
                'pending_fines' => $transactionModel->getPendingFinesAmount($startDate, $endDate),
                'overdue_books' => $transactionModel->getOverdueBooksCount($startDate, $endDate),
                'period' => date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate))
            ];
            break;
            
        case 'users':
            $newUsers = $userModel->getUsersByDateRange($startDate, $endDate);
            $report = [
                'total_users' => $userModel->getTotalUsersCount(),
                'active_users' => $userModel->getActiveUsersCount($startDate, $endDate),
                'new_users' => count($newUsers),
                'period' => date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate))
            ];
            break;
            
        case 'books':
            $report = [
                'total_books' => $bookModel->getTotalBooksCount(),
                'available_books' => $bookModel->getAvailableBooksCount(),
                'borrowed_books' => $transactionModel->getActiveBorrowingCount(),
                'popular_books' => $bookModel->getPopularBooks(10, $startDate, $endDate),
                'period' => date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate))
            ];
            break;
    }
    
    $this->render('admin/reports', [
        'reportType' => $reportType,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'report' => $report
    ]);
  }

  /**
   * Settings
   */
  public function settings()
  {
    $this->authHelper->requireAdmin();

    $fineSettings = $this->adminService->getFineSettings();

    $this->render('admin/settings', [
      'fineSettings' => $fineSettings
    ]);
  }

  /**
   * Update Settings
   */
  public function updateSettings()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $settings = $_POST['settings'] ?? [];
      $updated = $this->adminService->updateFineSettings($settings);
      $_SESSION['success'] = "Updated {$updated} settings.";
    }

    $this->redirect('/admin/settings');
  }

  public function analytics()
  {
    // Check if user is admin
    if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
      header('Location: ' . BASE_URL . '403');
      exit();
    }

    // Get analytics data
    $stats = $this->adminService->getOverallStats();
    $borrowTrends = $this->adminService->getBorrowTrends();
    $topBooks = $this->adminService->getTopBooks();
    $categoryDistribution = $this->adminService->getCategoryDistribution();
    $userActivity = $this->adminService->getUserActivity();
    $fineStats = $this->adminService->getFineStats();
    $monthlyStats = $this->adminService->getMonthlyStats();
    $recentActivities = $this->adminService->getRecentActivities();

    // Load the view
    $pageTitle = 'Analytics Dashboard';
    include APP_ROOT . '/views/admin/analytics.php';
  }



  /**
   * Render a view with data
   */
  private function render($view, $data = [])
  {
    $this->authHelper->requireAuth();
    $userType = $_SESSION['userType'] ?? '';
    if (strtolower($userType) !== 'admin') {
        http_response_code(403);
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    // Extract data to make variables available in view
    extract($data);
    
    // Build view file path
    $viewFile = APP_ROOT . '/views/' . $view . '.php';

    if (file_exists($viewFile)) {
      include $viewFile;
    } else {
      error_log("View file not found: {$viewFile}");
      http_response_code(404);
      echo "View not found: {$view}";
      exit;
    }
  }

  /**
   * Redirect to a URL
   */
  private function redirect($url)
  {
    header('Location: ' . BASE_URL . ltrim($url, '/'));
    exit;
  }

  /**
   * Books Borrowed Management - List all borrowed books
   */
  public function booksBorrowed()
  {
    $this->authHelper->requireAdmin();

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = $_POST['action'] ?? '';

      switch ($action) {
        case 'add':
          $userId = $_POST['userId'] ?? '';
          $isbn = $_POST['isbn'] ?? '';
          $borrowDate = $_POST['borrowDate'] ?? date('Y-m-d');
          $dueDate = $_POST['dueDate'] ?? date('Y-m-d', strtotime('+14 days'));
          $notes = $_POST['notes'] ?? '';
          $addedBy = $_SESSION['userId'];

          $result = $this->adminService->addBorrowedBook($userId, $isbn, $borrowDate, $dueDate, $notes, $addedBy);
          $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
          break;

        case 'edit':
          $id = $_POST['id'] ?? '';
          $userId = $_POST['userId'] ?? '';
          $isbn = $_POST['isbn'] ?? '';
          $borrowDate = $_POST['borrowDate'] ?? '';
          $dueDate = $_POST['dueDate'] ?? '';
          $returnDate = $_POST['returnDate'] ?: null;
          $status = $_POST['status'] ?? 'Active';
          $notes = $_POST['notes'] ?? '';

          $result = $this->adminService->updateBorrowedBook($id, $userId, $isbn, $borrowDate, $dueDate, $returnDate, $status, $notes);
          $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
          break;

        case 'mark_returned':
          $id = $_POST['id'] ?? '';
          $result = $this->adminService->markBookAsReturned($id);
          $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
          break;

        case 'delete':
          $id = $_POST['id'] ?? '';
          $result = $this->adminService->deleteBorrowedBook($id);
          $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
          break;
      }

      $this->redirect('/admin/borrowed-books');
      return;
    }

    // Get filters
    $status = $_GET['status'] ?? null;
    $userId = $_GET['userId'] ?? null;
    $isbn = $_GET['isbn'] ?? null;

    // Get data using AdminService methods
    $borrowedBooks = $this->adminService->getAllBorrowedBooks($status, $userId, $isbn);
    $stats = $this->adminService->getBorrowedBooksStats();

    // Get all users and books for the form dropdowns
    global $mysqli;
    
    $users = [];
    $usersResult = $mysqli->query("SELECT userId, username, emailId, userType FROM users WHERE isVerified = 1 ORDER BY username");
    while ($row = $usersResult->fetch_assoc()) {
      $users[] = $row;
    }

    $books = [];
    $booksResult = $mysqli->query("SELECT isbn, COALESCE(bookName, 'Unknown Book') as bookName, COALESCE(authorName, 'Unknown Author') as authorName, COALESCE(available, 0) as available FROM books ORDER BY bookName");
    while ($row = $booksResult->fetch_assoc()) {
      $books[] = $row;
    }

    $this->render('admin/borrowed-books', [
      'borrowedBooks' => $borrowedBooks,
      'stats' => $stats,
      'currentStatus' => $status,
      'currentUserId' => $userId,
      'currentIsbn' => $isbn,
      'users' => $users,
      'books' => $books
    ]);
  }

  /**
   * Add Borrowed Book - Show form and process
   */
  public function addBooksBorrowed()
  {
    // Redirect to main page - form is now a modal
    $this->redirect('/admin/borrowed-books');
  }

  /**
   * Edit Borrowed Book - Show form and process
   */
  public function editBooksBorrowed()
  {
    // Redirect to main page - form is now a modal
    $this->redirect('/admin/borrowed-books');
  }

  /**
   * Delete Borrowed Book
   */
  public function deleteBooksBorrowed()
  {
    // Redirect to main page - handled in POST
    $this->redirect('/admin/borrowed-books');
  }

  /**
   * Reserve a book (send to borrow_requests table)
   */
  public function reserve()
  {
    $this->authHelper->requireAdmin();

    $userId = $_SESSION['userId'];
    $isbn = $_GET['isbn'] ?? null;

    if (!$isbn) {
      $_SESSION['error'] = 'No book specified for reservation';
      header('Location: ' . BASE_URL . 'admin/books');
      exit;
    }

    global $mysqli;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Check if already has a pending/approved request for this book
      $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE userId = ? AND isbn = ? AND status IN ('Pending','Approved') AND dueDate >= CURDATE()");
      $stmt->bind_param("ss", $userId, $isbn);
      $stmt->execute();
      if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'You already have a pending or approved request for this book.';
        $stmt->close();
        header('Location: ' . BASE_URL . 'admin/books');
        exit;
      }
      $stmt->close();

      // Only allow reservation for 1 day
      $dueDate = date('Y-m-d', strtotime('+1 day'));

      // Insert into borrow_requests
      $stmt = $mysqli->prepare("INSERT INTO borrow_requests (userId, isbn, dueDate, status) VALUES (?, ?, ?, 'Pending')");
      $stmt->bind_param("sss", $userId, $isbn, $dueDate);
      if ($stmt->execute()) {
        $_SESSION['success'] = 'Reservation request sent! Awaiting admin approval.';
      } else {
        $_SESSION['error'] = 'Failed to send reservation request.';
      }
      $stmt->close();

      header('Location: ' . BASE_URL . 'admin/reserved-books');
      exit;
    }

    // GET: Show confirmation page
    $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$book) {
      $_SESSION['error'] = 'Book not found';
      header('Location: ' . BASE_URL . 'admin/books');
      exit;
    }

    $this->render('admin/reserve', ['book' => $book]);
  }

  /**
   * Show admin's reserved books (borrow requests)
   */
  public function reservedBooks()
  {
    $this->authHelper->requireAdmin();
    global $mysqli;
    $userId = $_SESSION['userId'];

    $sql = "SELECT br.*, b.bookName, b.authorName 
            FROM borrow_requests br
            LEFT JOIN books b ON br.isbn = b.isbn
            WHERE br.userId = ?
            ORDER BY br.requestDate DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();

    $this->render('admin/reserved-books', ['requests' => $requests]);
  }
  
    /**
   * Export Report as Excel/CSV
   */
  public function exportReport()
  {
    $this->authHelper->requireAdmin();

    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $reportType = $_GET['type'] ?? 'overview';
    $format = $_GET['format'] ?? 'csv'; // 'csv' or 'txt'

    // Initialize models
    $transactionModel = new \App\Models\Transaction();
    $userModel = new \App\Models\User();
    $bookModel = new \App\Models\Book();

    if ($format === 'csv') {
        // Generate CSV (Excel-compatible)
        $filename = "report_{$reportType}_{$startDate}_to_{$endDate}.csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, ['Library Report - ' . ucfirst($reportType)]);
        fputcsv($output, ['Period', $startDate . ' to ' . $endDate]);
        fputcsv($output, ['Generated', date('Y-m-d H:i:s')]);
        fputcsv($output, []); // Empty row
        
        switch ($reportType) {
            case 'overview':
                fputcsv($output, ['Metric', 'Value']);
                fputcsv($output, ['Total Transactions', $transactionModel->getTotalTransactionsCount($startDate, $endDate)]);
                fputcsv($output, ['Total Users', $userModel->getTotalUsersCount()]);
                fputcsv($output, ['Total Books', $bookModel->getTotalBooksCount()]);
                fputcsv($output, ['Total Fines', 'LKR ' . number_format($transactionModel->getTotalFinesAmount($startDate, $endDate), 2)]);
                fputcsv($output, ['Active Borrowings', $transactionModel->getActiveBorrowingCount()]);
                fputcsv($output, ['Overdue Books', $transactionModel->getOverdueBooksCount($startDate, $endDate)]);
                break;
                
            case 'borrowing':
                fputcsv($output, ['Metric', 'Count']);
                fputcsv($output, ['Total Borrowings', $transactionModel->getTotalTransactionsCount($startDate, $endDate)]);
                fputcsv($output, ['Returned Books', $transactionModel->getReturnedBooksCount($startDate, $endDate)]);
                fputcsv($output, ['Active Loans', $transactionModel->getActiveBorrowingCount()]);
                fputcsv($output, ['Overdue Books', $transactionModel->getOverdueBooksCount($startDate, $endDate)]);
                
                // Add detailed transaction list
                fputcsv($output, []); // Empty row
                fputcsv($output, ['Detailed Transactions']);
                fputcsv($output, ['Transaction ID', 'User ID', 'ISBN', 'Book Name', 'Borrow Date', 'Return Date', 'Status']);
                
                $transactions = $transactionModel->getTransactionsByDateRange($startDate, $endDate);
                foreach ($transactions as $txn) {
                    fputcsv($output, [
                        $txn['tid'] ?? '',
                        $txn['userId'] ?? '',
                        $txn['isbn'] ?? '',
                        $txn['bookName'] ?? '',
                        $txn['borrowDate'] ?? '',
                        $txn['returnDate'] ?? 'Not Returned',
                        $txn['returnDate'] ? 'Returned' : 'Active'
                    ]);
                }
                break;
                
            case 'fines':
                fputcsv($output, ['Metric', 'Amount (LKR)']);
                fputcsv($output, ['Total Fines', number_format($transactionModel->getTotalFinesAmount($startDate, $endDate), 2)]);
                fputcsv($output, ['Collected Fines', number_format($transactionModel->getCollectedFinesAmount($startDate, $endDate), 2)]);
                fputcsv($output, ['Pending Fines', number_format($transactionModel->getPendingFinesAmount($startDate, $endDate), 2)]);
                fputcsv($output, ['Overdue Books', $transactionModel->getOverdueBooksCount($startDate, $endDate)]);
                
                // Add detailed fines list
                fputcsv($output, []); // Empty row
                fputcsv($output, ['Detailed Fines']);
                fputcsv($output, ['Transaction ID', 'User ID', 'ISBN', 'Book Name', 'Fine Amount (LKR)', 'Status', 'Payment Date']);
                
                $fines = $this->adminService->getAllFines(null, null);
                foreach ($fines as $fine) {
                    if (strtotime($fine['borrowDate']) >= strtotime($startDate) && strtotime($fine['borrowDate']) <= strtotime($endDate)) {
                        fputcsv($output, [
                            $fine['tid'] ?? '',
                            $fine['userId'] ?? '',
                            $fine['isbn'] ?? '',
                            $fine['bookName'] ?? '',
                            number_format($fine['fineAmount'] ?? 0, 2),
                            $fine['fineStatus'] ?? 'pending',
                            $fine['finePaymentDate'] ?? 'N/A'
                        ]);
                    }
                }
                break;
                
            case 'users':
                $newUsers = $userModel->getUsersByDateRange($startDate, $endDate);
                fputcsv($output, ['Metric', 'Count']);
                fputcsv($output, ['Total Users', $userModel->getTotalUsersCount()]);
                fputcsv($output, ['Active Users', $userModel->getActiveUsersCount($startDate, $endDate)]);
                fputcsv($output, ['New Users', count($newUsers)]);
                
                // Add new users list
                if (!empty($newUsers)) {
                    fputcsv($output, []); // Empty row
                    fputcsv($output, ['New Users Details']);
                    fputcsv($output, ['User ID', 'Username', 'Email', 'User Type', 'Created Date']);
                    
                    foreach ($newUsers as $user) {
                        fputcsv($output, [
                            $user['userId'] ?? '',
                            $user['username'] ?? '',
                            $user['emailId'] ?? '',
                            $user['userType'] ?? '',
                            $user['createdAt'] ?? ''
                        ]);
                    }
                }
                break;
                
            case 'books':
                $popularBooks = $bookModel->getPopularBooks(10, $startDate, $endDate);
                fputcsv($output, ['Metric', 'Count']);
                fputcsv($output, ['Total Books', $bookModel->getTotalBooksCount()]);
                fputcsv($output, ['Available Books', $bookModel->getAvailableBooksCount()]);
                fputcsv($output, ['Borrowed Books', $transactionModel->getActiveBorrowingCount()]);
                
                // Add popular books list
                if (!empty($popularBooks)) {
                    fputcsv($output, []); // Empty row
                    fputcsv($output, ['Popular Books']);
                    fputcsv($output, ['ISBN', 'Book Name', 'Author', 'Borrow Count']);
                    
                    foreach ($popularBooks as $book) {
                        fputcsv($output, [
                            $book['isbn'] ?? '',
                            $book['bookName'] ?? '',
                            $book['authorName'] ?? '',
                            $book['borrow_count'] ?? 0
                        ]);
                    }
                }
                break;
        }
        
        fclose($output);
        exit;
        
    } else {
        // Original text format
        $report = $this->adminService->generateReport($reportType, $startDate, $endDate);
        $filename = "report_{$reportType}_{$startDate}_to_{$endDate}.txt";
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $content = "Library Report\n";
        $content .= "Type: " . ucfirst($reportType) . "\n";
        $content .= "Period: {$startDate} to {$endDate}\n";
        $content .= "=================================================\n\n";

        if (empty($report)) {
            $content .= "No data available for this report.";
        } else {
            foreach ($report as $key => $value) {
                if (is_array($value)) {
                    $content .= ucfirst(str_replace('_', ' ', $key)) . ":\n";
                    foreach($value as $sub_key => $sub_value) {
                         $content .= "  - " . ucfirst(str_replace('_', ' ', $sub_key)) . ": " . $sub_value . "\n";
                    }
                } else {
                    $content .= ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
                }
            }
        }
        
        echo $content;
        exit;
    }
  }

  /**
   * Admin Profile
   */
  public function profile()
  {
    $this->authHelper->requireAdmin();

    $adminId = $_SESSION['userId'] ?? null;
    global $mysqli;
    $admin = [];

    // Always fetch fresh data from database first
    if ($adminId) {
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE userId = ? AND userType = 'Admin' LIMIT 1");
        $stmt->bind_param("s", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        // Store in session for use in view
        $_SESSION['admin'] = $admin;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Validate using ValidationHelper
      $errors = ValidationHelper::validateProfileUpdate($_POST);
      
      if (!empty($errors)) {
        $_SESSION['validation_errors'] = $errors;
        ValidationHelper::setFormData($_POST);
        $_SESSION['error'] = 'Please fix the validation errors below';
        header('Location: ' . BASE_URL . 'admin/profile');
        exit;
      }
      
      ValidationHelper::clearValidation();
      
      // Validate only fields that exist in the users table
      $data = [
        'emailId' => trim($_POST['emailId'] ?? ''),
        'phoneNumber' => trim($_POST['phoneNumber'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'dob' => trim($_POST['dob'] ?? ''),
        'address' => trim($_POST['address'] ?? '')
      ];

      $errors = [];
      
      if (empty($data['emailId']) || !filter_var($data['emailId'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required.';
      }
      
      if (empty($data['phoneNumber']) || !preg_match('/^[0-9+\-\s()]{7,20}$/', $data['phoneNumber'])) {
        $errors[] = 'Valid phone number is required.';
      }
      
      if (empty($data['gender'])) {
        $errors[] = 'Gender is required.';
      }
      
      if (empty($data['dob'])) {
        $errors[] = 'Date of birth is required.';
      }
      
      if (empty($data['address'])) {
        $errors[] = 'Address is required.';
      }

      // Handle profile image upload if provided
      if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profileImage'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
          $errors[] = 'Profile image must be JPG or PNG.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
          $errors[] = 'Profile image must be less than 2MB.';
        } else {
          $targetDir = APP_ROOT . '/public/assets/images/admins/';
          if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
          }
          
          // Delete old profile images for this user
          foreach (glob($targetDir . $adminId . '.*') as $oldFile) {
            if (file_exists($oldFile)) {
              unlink($oldFile);
            }
          }
          
          $profileImagePath = $targetDir . $adminId . '.' . $ext;
          if (move_uploaded_file($file['tmp_name'], $profileImagePath)) {
            error_log("Profile image uploaded successfully: " . $profileImagePath);
          } else {
            $errors[] = 'Failed to upload profile image.';
            error_log("Failed to move uploaded file to: " . $profileImagePath);
          }
        }
      }

      if (empty($errors)) {
        $userModel = new \App\Models\User();
        $success = $userModel->updateProfile($adminId, $data);

        if ($success) {
          // Reload admin data from database
          $stmt = $mysqli->prepare("SELECT * FROM users WHERE userId = ? AND userType = 'Admin' LIMIT 1");
          $stmt->bind_param("s", $adminId);
          $stmt->execute();
          $result = $stmt->get_result();
          $admin = $result->fetch_assoc();
          $stmt->close();
          $_SESSION['admin'] = $admin;
          $_SESSION['success'] = 'Profile updated successfully.';
        } else {
          $_SESSION['error'] = 'Failed to update profile.';
        }
      } else {
        $_SESSION['error'] = implode('<br>', $errors);
      }

      // Redirect to avoid form resubmission
      header('Location: ' . BASE_URL . 'admin/profile');
      exit;
    }

    // Pass admin data to view
    $pageTitle = 'Admin Profile';
    include APP_ROOT . '/views/admin/admin-profile.php';
  }

  /**
   * Mark All Notifications as Read
   */
  public function markAllNotificationsRead()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      global $conn;
      
      $adminUserId = $_SESSION['userId'];
      
      // Mark all notifications for this admin as read
      $stmt = $conn->prepare("UPDATE notifications SET isRead = 1 WHERE (userId = ? OR userId IS NULL) AND isRead = 0");
      $stmt->bind_param("s", $adminUserId);
      
      if ($stmt->execute()) {
        $affectedRows = $stmt->affected_rows;
        $_SESSION['success'] = "Marked {$affectedRows} notification(s) as read.";
      } else {
        $_SESSION['error'] = 'Failed to mark notifications as read.';
      }
      $stmt->close();
    }

    $this->redirect('/admin/notifications');
  }

  /**
   * Check and Create Overdue Notifications
   */
  public function checkOverdueNotifications()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      global $conn;
      
      try {
        // Get overdue books (borrowed more than 14 days ago, not returned)
        $sql = "SELECT t.tid, t.userId, t.isbn, t.borrowDate, 
                       u.username, u.emailId, b.bookName,
                       DATEDIFF(CURDATE(), t.borrowDate) as daysOverdue
                FROM transactions t
                LEFT JOIN users u ON t.userId = u.userId
                LEFT JOIN books b ON t.isbn = b.isbn
                WHERE t.returnDate IS NULL 
                AND DATEDIFF(CURDATE(), t.borrowDate) > 14
                AND NOT EXISTS (
                  SELECT 1 FROM notifications n 
                  WHERE n.userId = t.userId 
                  AND n.type = 'overdue' 
                  AND n.relatedId = t.tid
                  AND DATE(n.createdAt) = CURDATE()
                )";
        
        $result = $conn->query($sql);
        $count = 0;
        
        if ($result) {
          while ($row = $result->fetch_assoc()) {
            $title = "Overdue Book Reminder";
            $message = "Your borrowed book '{$row['bookName']}' is overdue by {$row['daysOverdue']} days. Please return it immediately to avoid additional fines.";
            
            $stmt = $conn->prepare("INSERT INTO notifications (userId, title, message, type, priority, relatedId, createdAt) VALUES (?, ?, ?, 'overdue', 'high', ?, NOW())");
            $stmt->bind_param("sssi", $row['userId'], $title, $message, $row['tid']);
            
            if ($stmt->execute()) {
              $count++;
            }
            $stmt->close();
          }
        }
        
        if ($count > 0) {
          $_SESSION['success'] = "Created {$count} overdue notification(s).";
        } else {
          $_SESSION['info'] = 'No new overdue notifications needed.';
        }
        
      } catch (\Exception $e) {
        error_log("Error checking overdue notifications: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to check overdue notifications.';
      }
    }

    $this->redirect('/admin/notifications');
  }

  /**
   * Check and Create Out of Stock Notifications
   */
  public function checkOutOfStockNotifications()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      global $conn;
      
      try {
        // Get books that are out of stock or low stock
        $sql = "SELECT isbn, bookName, available, totalCopies
                FROM books
                WHERE available <= 2
                AND NOT EXISTS (
                  SELECT 1 FROM notifications n 
                  WHERE n.type = 'out_of_stock' 
                  AND n.message LIKE CONCAT('%', books.isbn, '%')
                  AND DATE(n.createdAt) = CURDATE()
                )";
        
        $result = $conn->query($sql);
        $count = 0;
        
        if ($result) {
          while ($row = $result->fetch_assoc()) {
            $status = $row['available'] <= 0 ? 'out of stock' : 'low stock';
            $title = "Book Stock Alert";
            $message = "The book '{$row['bookName']}' (ISBN: {$row['isbn']}) is {$status}. Available: {$row['available']}, Total: {$row['totalCopies']}. Please consider restocking.";
            
            // Send to all admins (userId = NULL for system-wide)
            $stmt = $conn->prepare("INSERT INTO notifications (userId, title, message, type, priority, createdAt) VALUES (NULL, ?, ?, 'out_of_stock', 'medium', NOW())");
            $stmt->bind_param("ss", $title, $message);
            
            if ($stmt->execute()) {
              $count++;
            }
            $stmt->close();
          }
        }
        
        if ($count > 0) {
          $_SESSION['success'] = "Created {$count} stock alert notification(s).";
        } else {
          $_SESSION['info'] = 'No stock alerts needed. All books are adequately stocked.';
        }
        
      } catch (\Exception $e) {
        error_log("Error checking stock notifications: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to check stock notifications.';
      }
    }

    $this->redirect('/admin/notifications');
  }

  /**
   * Clear Old Notifications (older than 30 days and read)
   */
  public function clearOldNotifications()
  {
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      global $conn;
      
      try {
        // Delete notifications older than 30 days that are read
        $stmt = $conn->prepare("DELETE FROM notifications WHERE isRead = 1 AND createdAt < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        if ($stmt->execute()) {
          $deletedCount = $stmt->affected_rows;
          
          if ($deletedCount > 0) {
            $_SESSION['success'] = "Deleted {$deletedCount} old notification(s).";
            
            // Log the maintenance activity
            $this->adminService->logMaintenanceActivity(
              'Clear Old Notifications',
              "Deleted {$deletedCount} read notifications older than 30 days",
              'success'
            );
          } else {
            $_SESSION['info'] = 'No old notifications to delete.';
          }
        } else {
          $_SESSION['error'] = 'Failed to delete old notifications.';
        }
        $stmt->close();
        
      } catch (\Exception $e) {
        error_log("Error clearing old notifications: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to clear old notifications.';
      }
    }

    $this->redirect('/admin/notifications');
  }
}
