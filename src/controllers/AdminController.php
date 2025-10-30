<?php

namespace App\Controllers;

use App\Services\AdminService;
use App\Helpers\AuthHelper;

class AdminController
{
  private $adminService;
  private $authHelper;

  public function __construct()
  {
    $this->adminService = new AdminService();
    $this->authHelper = new AuthHelper();
  }

  /**
   * Admin Dashboard
   */
  public function dashboard()
  {
    // Use existing requireAuth method
    $this->authHelper->requireAuth();
    
    // Check if user is admin
    $userType = $_SESSION['userType'] ?? '';
    if (strtolower($userType) !== 'admin') {
        http_response_code(403);
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL);
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
          $updated = $this->adminService->updateFineSettings($settings);
          $_SESSION['success'] = "Updated {$updated} fine settings.";
          break;

        case 'update_all_fines':
          $updatedCount = $this->adminService->updateAllFines();
          $_SESSION['success'] = "Updated {$updatedCount} fines.";
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
    $requests = $this->adminService->getBorrowRequests($status);

    $this->render('admin/borrow-requests', [
      'requests' => $requests,
      'currentStatus' => $status
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
                // Get request details
                $stmt = $mysqli->prepare("SELECT userId, isbn FROM borrow_requests WHERE id = ?");
                $stmt->bind_param("i", $requestId);
                $stmt->execute();
                $result = $stmt->get_result();
                $request = $result->fetch_assoc();
                
                if (!$request) {
                    $_SESSION['error'] = 'Request not found.';
                    break;
                }
                
                // Check book availability
                $bookStmt = $mysqli->prepare("SELECT available, bookName FROM books WHERE isbn = ?");
                $bookStmt->bind_param("s", $request['isbn']);
                $bookStmt->execute();
                $bookResult = $bookStmt->get_result();
                $book = $bookResult->fetch_assoc();
                
                if (!$book || $book['available'] <= 0) {
                    $_SESSION['error'] = 'Book is not available for borrowing.';
                    break;
                }
                
                // Calculate due date (14 days from now)
                $dueDate = date('Y-m-d', strtotime('+14 days'));
                
                // Update request status
                $updateStmt = $mysqli->prepare("
                    UPDATE borrow_requests 
                    SET status = 'Approved', approvedBy = ?, dueDate = ? 
                    WHERE id = ?
                ");
                $updateStmt->bind_param("ssi", $adminId, $dueDate, $requestId);
                
                if ($updateStmt->execute()) {
                    // Create transaction
                    $tid = 'TXN' . time() . rand(100, 999);
                    $borrowDate = date('Y-m-d');
                    
                    $txnStmt = $mysqli->prepare("
                        INSERT INTO transactions (tid, userId, isbn, borrowDate, fineAmount, fineStatus) 
                        VALUES (?, ?, ?, ?, 0.00, 'pending')
                    ");
                    $txnStmt->bind_param("ssss", $tid, $request['userId'], $request['isbn'], $borrowDate);
                    $txnStmt->execute();
                    
                    // Update book availability
                    $mysqli->query("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = '{$request['isbn']}'");
                    
                    // Send notification to user
                    $notifStmt = $mysqli->prepare("
                        INSERT INTO notifications (userId, title, message, type, priority, relatedId) 
                        VALUES (?, 'Borrow Request Approved', ?, 'approval', 'high', ?)
                    ");
                    $notifMessage = "Your request to borrow '{$book['bookName']}' has been approved! Due date: {$dueDate}";
                    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
                    $notifStmt->execute();
                    
                    $_SESSION['success'] = 'Borrow request approved successfully. User has been notified.';
                } else {
                    $_SESSION['error'] = 'Failed to approve borrow request.';
                }
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
                $bookResult = $bookStmt->get_result();
                $book = $bookResult->fetch_assoc();
                
                // Update request status
                $updateStmt = $mysqli->prepare("
                    UPDATE borrow_requests 
                    SET status = 'Rejected', rejectionReason = ?, approvedBy = ? 
                    WHERE id = ?
                ");
                $updateStmt->bind_param("ssi", $reason, $adminId, $requestId);
                
                if ($updateStmt->execute()) {
                    // Send notification to user
                    $notifStmt = $mysqli->prepare("
                        INSERT INTO notifications (userId, title, message, type, priority, relatedId) 
                        VALUES (?, 'Borrow Request Rejected', ?, 'approval', 'high', ?)
                    ");
                    $bookName = $book['bookName'] ?? 'the requested book';
                    $notifMessage = "Your request to borrow '{$bookName}' has been rejected. Reason: {$reason}";
                    $notifStmt->bind_param("ssi", $request['userId'], $notifMessage, $requestId);
                    $notifStmt->execute();
                    
                    $_SESSION['success'] = 'Borrow request rejected. User has been notified.';
                } else {
                    $_SESSION['error'] = 'Failed to reject borrow request.';
                }
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

      $filename = $this->adminService->createDatabaseBackup($backupType);

      if ($filename) {
        $_SESSION['success'] = "Backup created successfully: {$filename}";
      } else {
        $_SESSION['error'] = 'Failed to create backup.';
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
        foreach ($results as $task => $result) {
          if ($result > 0) {
            $successCount++;
          }
        }

        $_SESSION['success'] = "Completed {$successCount} maintenance tasks.";
      }
    }

    $this->redirect('/admin/maintenance');
  }

  /**
   * Reports
   */
  public function reports()
  {
    $this->authHelper->requireAdmin();

    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $reportType = $_GET['type'] ?? 'overview';

    $report = $this->adminService->generateReport($reportType, $startDate, $endDate);

    $this->render('admin/reports', [
      'report' => $report,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'reportType' => $reportType
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
    // Ensure user is authenticated for admin views
    $this->authHelper->requireAuth();
    
    // Check if user is admin
    $userType = $_SESSION['userType'] ?? '';
    if (strtolower($userType) !== 'admin') {
        http_response_code(403);
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . BASE_URL);
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

    // Get data
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
    $booksResult = $mysqli->query("SELECT isbn, bookName, authorName, available FROM books ORDER BY bookName");
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
}
