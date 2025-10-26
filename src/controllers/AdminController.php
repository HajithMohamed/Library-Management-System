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
    if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
      header('Location: ' . BASE_URL . '403');
      exit();
    }

    global $mysqli;

    // Fetch total books
    $totalBooksResult = $mysqli->query("SELECT COUNT(*) as count FROM books");
    $totalBooks = $totalBooksResult->fetch_assoc()['count'];

    // Fetch available books (sum of available column)
    $availableBooksResult = $mysqli->query("SELECT SUM(available) as count FROM books");
    $availableBooks = $availableBooksResult->fetch_assoc()['count'] ?? 0;

    // Fetch borrowed books (sum of borrowed column)
    $borrowedBooksResult = $mysqli->query("SELECT SUM(borrowed) as count FROM books");
    $borrowedBooks = $borrowedBooksResult->fetch_assoc()['count'] ?? 0;

    // Fetch total users
    $totalUsersResult = $mysqli->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $totalUsersResult->fetch_assoc()['count'];

    // Fetch active users (verified users)
    $activeUsersResult = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE isVerified = 1");
    $activeUsers = $activeUsersResult->fetch_assoc()['count'];

    // Fetch total transactions
    $totalTransactionsResult = $mysqli->query("SELECT COUNT(*) as count FROM transactions");
    $totalTransactions = $totalTransactionsResult->fetch_assoc()['count'];

    // Fetch active borrowings (not returned yet)
    $activeBorrowingsResult = $mysqli->query("SELECT COUNT(*) as count FROM transactions WHERE returnDate IS NULL");
    $activeBorrowings = $activeBorrowingsResult->fetch_assoc()['count'];

    // Fetch overdue books (borrowed more than 14 days ago, not returned)
    $overdueBooksResult = $mysqli->query("
            SELECT COUNT(*) as count
            FROM transactions
            WHERE returnDate IS NULL
            AND DATEDIFF(CURDATE(), borrowDate) > 14
        ");
    $overdueBooks = $overdueBooksResult->fetch_assoc()['count'];

    // Fetch low stock books (available <= 2)
    $lowStockBooksResult = $mysqli->query("SELECT COUNT(*) as count FROM books WHERE available <= 2");
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
      'database' => $mysqli->ping() ? 'healthy' : 'error',
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
    $recentTransactionsResult = $mysqli->query($recentTransactionsQuery);
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
    $popularBooksResult = $mysqli->query($popularBooksQuery);
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
    $notificationsResult = $mysqli->query($notificationsQuery);
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

    global $mysqli;

    // Fetch all users with username
    $sql = "SELECT userId, username, emailId, phoneNumber, userType, gender, dob, address, isVerified, createdAt
                FROM users
                ORDER BY createdAt DESC";

    $result = $mysqli->query($sql);

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
    $this->authHelper->requireAdmin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $requestId = $_POST['requestId'] ?? '';
      $action = $_POST['action'] ?? '';

      switch ($action) {
        case 'approve':
          if ($this->adminService->approveBorrowRequest($requestId)) {
            $_SESSION['success'] = 'Borrow request approved successfully.';
          } else {
            $_SESSION['error'] = 'Failed to approve borrow request.';
          }
          break;

        case 'reject':
          $reason = $_POST['reason'] ?? '';
          if ($this->adminService->rejectBorrowRequest($requestId, $reason)) {
            $_SESSION['success'] = 'Borrow request rejected.';
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
    $notifications = $this->adminService->getAllNotifications(null, $type, $unreadOnly);

    $this->render('admin/notifications', [
      'notifications' => $notifications,
      'currentType' => $type,
      'unreadOnly' => $unreadOnly
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
    extract($data);
    $viewFile = APP_ROOT . '/views/' . $view . '.php';

    if (file_exists($viewFile)) {
      include $viewFile;
    } else {
      http_response_code(404);
      include APP_ROOT . '/views/errors/404.php';
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
}
