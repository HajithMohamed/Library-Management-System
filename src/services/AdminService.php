<?php

namespace App\Services;

use App\Models\User;
use App\Models\Book;
use App\Models\Transaction;

class AdminService
{
  private $userModel;
  private $bookModel;
  private $transactionModel;

  public function __construct()
  {
    $this->userModel = new User();
    $this->bookModel = new Book();
    $this->transactionModel = new Transaction();
  }

  /**
   * Check if a specific table exists
   */
  private function tableExists($tableName)
  {
    global $conn;

    try {
      $result = $conn->query("SHOW TABLES LIKE '$tableName'");
      return $result && $result->num_rows > 0;
    } catch (\Exception $e) {
      return false;
    }
  }

  /**
   * Check if notifications table exists
   */
  private function notificationsTableExists()
  {
    return $this->tableExists('notifications');
  }

  /**
   * Check if borrow_requests table exists
   */
  private function borrowRequestsTableExists()
  {
    return $this->tableExists('borrow_requests');
  }

  /**
   * Get dashboard statistics
   */
  public function getDashboardStats()
  {
    $userStats = $this->userModel->getUserStats();
    $bookStats = $this->bookModel->getBookStats();
    $transactionStats = $this->transactionModel->getFineStats();

    return [
      'users' => $userStats,
      'books' => $bookStats,
      'transactions' => $transactionStats,
      'active_borrowings' => $this->transactionModel->getActiveBorrowingCount(),
      'overdue_books' => count($this->transactionModel->getOverdueTransactions())
    ];
  }

  /**
   * Update all fines for overdue books
   */
  public function updateAllFines()
  {
    $overdueTransactions = $this->transactionModel->getOverdueTransactions();
    $updatedCount = 0;

    foreach ($overdueTransactions as $transaction) {
      $fine = $this->calculateFine($transaction['borrowDate']);
      if ($fine > $transaction['fine']) {
        $this->transactionModel->updateFine($transaction['tid'], $fine);
        $updatedCount++;
      }
    }

    return $updatedCount;
  }

  /**
   * Calculate fine for overdue books
   */
  private function calculateFine($borrowDate, $returnDate = null)
  {
    $returnDate = $returnDate ?: date('Y-m-d');
    $borrowTimestamp = strtotime($borrowDate);
    $returnTimestamp = strtotime($returnDate);

    $daysDiff = ($returnTimestamp - $borrowTimestamp) / (60 * 60 * 24);
    $maxDays = 14; // 2 weeks borrowing period

    if ($daysDiff > $maxDays) {
      $overdueDays = $daysDiff - $maxDays;
      return $overdueDays * 5; // 5 rupees per day fine
    }

    return 0;
  }

  /**
   * Generate various reports
   */
  public function generateReport($type, $startDate, $endDate)
  {
    switch ($type) {
      case 'borrowing':
        return $this->generateBorrowingReport($startDate, $endDate);
      case 'fines':
        return $this->generateFinesReport($startDate, $endDate);
      case 'users':
        return $this->generateUsersReport($startDate, $endDate);
      case 'books':
        return $this->generateBooksReport($startDate, $endDate);
      default:
        return $this->generateOverviewReport($startDate, $endDate);
    }
  }

  /**
   * Generate overview report
   */
  private function generateOverviewReport($startDate, $endDate)
  {
    $transactions = $this->transactionModel->getTransactionsByDateRange($startDate, $endDate);
    $users = $this->userModel->getAllUsers();
    $books = $this->bookModel->getAllBooks();

    return [
      'period' => "{$startDate} to {$endDate}",
      'total_transactions' => count($transactions),
      'total_users' => count($users),
      'total_books' => count($books),
      'active_borrowings' => $this->transactionModel->getActiveBorrowingCount(),
      'overdue_books' => count($this->transactionModel->getOverdueTransactions()),
      'total_fines' => array_sum(array_column($transactions, 'fine'))
    ];
  }

  /**
   * Generate borrowing report
   */
  private function generateBorrowingReport($startDate, $endDate)
  {
    $transactions = $this->transactionModel->getTransactionsByDateRange($startDate, $endDate);

    $report = [
      'period' => "{$startDate} to {$endDate}",
      'total_borrowings' => count($transactions),
      'borrowings_by_day' => [],
      'borrowings_by_user_type' => [],
      'most_borrowed_books' => []
    ];

    // Group by day
    foreach ($transactions as $transaction) {
      $date = $transaction['borrowDate'];
      $report['borrowings_by_day'][$date] = ($report['borrowings_by_day'][$date] ?? 0) + 1;
    }

    // Group by user type
    foreach ($transactions as $transaction) {
      $userType = $transaction['userType'];
      $report['borrowings_by_user_type'][$userType] = ($report['borrowings_by_user_type'][$userType] ?? 0) + 1;
    }

    // Most borrowed books
    $bookCounts = [];
    foreach ($transactions as $transaction) {
      $bookName = $transaction['bookName'];
      $bookCounts[$bookName] = ($bookCounts[$bookName] ?? 0) + 1;
    }
    arsort($bookCounts);
    $report['most_borrowed_books'] = array_slice($bookCounts, 0, 10, true);

    return $report;
  }

  /**
   * Generate fines report
   */
  private function generateFinesReport($startDate, $endDate)
  {
    $transactions = $this->transactionModel->getTransactionsByDateRange($startDate, $endDate);
    $overdueTransactions = $this->transactionModel->getOverdueTransactions();

    $report = [
      'period' => "{$startDate} to {$endDate}",
      'total_fines' => array_sum(array_column($transactions, 'fine')),
      'overdue_books' => count($overdueTransactions),
      'fines_by_user_type' => [],
      'top_fine_payers' => []
    ];

    // Group fines by user type
    foreach ($transactions as $transaction) {
      if ($transaction['fine'] > 0) {
        $userType = $transaction['userType'];
        $report['fines_by_user_type'][$userType] = ($report['fines_by_user_type'][$userType] ?? 0) + $transaction['fine'];
      }
    }

    // Top fine payers
    $userFines = [];
    foreach ($transactions as $transaction) {
      if ($transaction['fine'] > 0) {
        $userId = $transaction['userId'];
        $userFines[$userId] = ($userFines[$userId] ?? 0) + $transaction['fine'];
      }
    }
    arsort($userFines);
    $report['top_fine_payers'] = array_slice($userFines, 0, 10, true);

    return $report;
  }

  /**
   * Generate users report
   */
  private function generateUsersReport($startDate, $endDate)
  {
    $users = $this->userModel->getAllUsers();
    $transactions = $this->transactionModel->getTransactionsByDateRange($startDate, $endDate);

    $report = [
      'period' => "{$startDate} to {$endDate}",
      'total_users' => count($users),
      'users_by_type' => [],
      'active_users' => [],
      'new_users' => []
    ];

    // Group users by type
    foreach ($users as $user) {
      $userType = $user['userType'];
      $report['users_by_type'][$userType] = ($report['users_by_type'][$userType] ?? 0) + 1;
    }

    // Active users (users who borrowed books in the period)
    $activeUserIds = array_unique(array_column($transactions, 'userId'));
    $report['active_users'] = count($activeUserIds);

    // New users (users created in the period)
    $newUsers = array_filter($users, function ($user) use ($startDate) {
      // This would require a created_at field in the users table
      // For now, we'll return 0
      return false;
    });
    $report['new_users'] = count($newUsers);

    return $report;
  }

  /**
   * Generate books report
   */
  private function generateBooksReport($startDate, $endDate)
  {
    $books = $this->bookModel->getAllBooks();
    $transactions = $this->transactionModel->getTransactionsByDateRange($startDate, $endDate);
    $popularBooks = $this->bookModel->getPopularBooks(10);

    $report = [
      'period' => "{$startDate} to {$endDate}",
      'total_books' => count($books),
      'available_books' => array_sum(array_column($books, 'available')),
      'borrowed_books' => array_sum(array_column($books, 'borrowed')),
      'popular_books' => $popularBooks,
      'books_by_publisher' => [],
      'books_by_author' => []
    ];

    // Group books by publisher
    foreach ($books as $book) {
      $publisher = $book['publisherName'];
      $report['books_by_publisher'][$publisher] = ($report['books_by_publisher'][$publisher] ?? 0) + 1;
    }

    // Group books by author
    foreach ($books as $book) {
      $author = $book['authorName'];
      $report['books_by_author'][$author] = ($report['books_by_author'][$author] ?? 0) + 1;
    }

    return $report;
  }


  /**
   * Get system health status
   */
  public function getSystemHealth()
  {
    $health = [
      'database' => $this->checkDatabaseHealth(),
      'disk_space' => $this->checkDiskSpace(),
      'overdue_books' => count($this->transactionModel->getOverdueTransactions()),
      'unverified_users' => $this->getUnverifiedUsersCount(),
      'low_stock_books' => $this->getLowStockBooksCount()
    ];

    $health['overall'] = $this->calculateOverallHealth($health);

    return $health;
  }

  /**
   * Check database health
   */
  private function checkDatabaseHealth()
  {
    try {
      global $conn;
      $result = $conn->query("SELECT 1");
      return $result ? 'healthy' : 'unhealthy';
    } catch (\Exception $e) {
      return 'unhealthy';
    }
  }

  /**
   * Check disk space
   */
  private function checkDiskSpace()
  {
    $freeBytes = disk_free_space(APP_ROOT);
    $totalBytes = disk_total_space(APP_ROOT);
    $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;

    if ($usedPercent < 80) {
      return 'healthy';
    } elseif ($usedPercent < 90) {
      return 'warning';
    } else {
      return 'critical';
    }
  }

  /**
   * Get count of unverified users
   */
  private function getUnverifiedUsersCount()
  {
    $users = $this->userModel->getAllUsers();
    $unverified = array_filter($users, function ($user) {
      return !$user['isVerified'];
    });

    return count($unverified);
  }

  /**
   * Get count of low stock books
   */
  private function getLowStockBooksCount()
  {
    $books = $this->bookModel->getAllBooks();
    $lowStock = array_filter($books, function ($book) {
      return $book['available'] < 2;
    });

    return count($lowStock);
  }

  /**
   * Calculate overall system health
   */
  private function calculateOverallHealth($health)
  {
    $criticalIssues = 0;
    $warnings = 0;

    if ($health['database'] === 'unhealthy') $criticalIssues++;
    if ($health['disk_space'] === 'critical') $criticalIssues++;
    if ($health['disk_space'] === 'warning') $warnings++;
    if ($health['overdue_books'] > 50) $warnings++;
    if ($health['unverified_users'] > 10) $warnings++;
    if ($health['low_stock_books'] > 20) $warnings++;

    if ($criticalIssues > 0) {
      return 'critical';
    } elseif ($warnings > 2) {
      return 'warning';
    } else {
      return 'healthy';
    }
  }

  /**
   * Check if transaction table has required columns for fines
   */
  private function transactionFineColumnsExist()
  {
    global $conn;

    try {
      // Check if fineAmount column exists
      $result = $conn->query("SHOW COLUMNS FROM transactions LIKE 'fineAmount'");
      return $result && $result->num_rows > 0;
    } catch (\Exception $e) {
      error_log("Error checking transaction fine columns: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Get all fines with detailed information with error handling
   */
  public function getAllFines($status = null, $userId = null)
  {
    global $conn;

    // Check if table has the required columns
    if (!$this->transactionFineColumnsExist()) {
      error_log("transactions table is missing fine columns - returning empty results");
      return [];
    }

    try {
      $sql = "SELECT t.*, u.userId, u.emailId, u.userType, b.bookName, b.authorName
                    FROM transactions t
                    JOIN users u ON t.userId = u.userId
                    JOIN books b ON t.isbn = b.isbn
                    WHERE t.fineAmount > 0";

      $params = [];

      if ($status) {
        $sql .= " AND t.fineStatus = ?";
        $params[] = $status;
      }

      if ($userId) {
        $sql .= " AND t.userId = ?";
        $params[] = $userId;
      }

      $sql .= " ORDER BY t.borrowDate DESC";

      $stmt = $conn->prepare($sql);
      if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
      }

      $stmt->execute();
      $result = $stmt->get_result();

      $fines = [];
      while ($row = $result->fetch_assoc()) {
        $fines[] = $row;
      }

      return $fines;
    } catch (\Exception $e) {
      error_log("Error getting fines: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Update fine status
   */
  public function updateFineStatus($transactionId, $status, $paymentMethod = null)
  {
    global $conn;

    $sql = "UPDATE transactions SET fineStatus = ?, finePaymentDate = ?, finePaymentMethod = ? WHERE tid = ?";
    $stmt = $conn->prepare($sql);
    $paymentDate = ($status === 'paid') ? date('Y-m-d') : null;
    $stmt->bind_param('ssss', $status, $paymentDate, $paymentMethod, $transactionId);

    if ($stmt->execute()) {
      // Create notification for fine payment
      if ($status === 'paid') {
        $this->createNotification(
          null,
          'fine_paid',
          'Fine Payment Confirmed',
          "Fine payment of ₹{$this->getFineAmount($transactionId)} has been processed.",
          'medium',
          $transactionId
        );
      }
      return true;
    }

    return false;
  }

  /**
   * Get fine amount for a transaction
   */
  private function getFineAmount($transactionId)
  {
    global $conn;
    $sql = "SELECT fineAmount FROM transactions WHERE tid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['fineAmount'] : 0;
  }

  /**
   * Get all notifications - WITH ERROR HANDLING
   */
  public function getAllNotifications($userId = null, $type = null, $unreadOnly = false)
  {
    global $conn;

    // Check if table exists first
    if (!$this->notificationsTableExists()) {
      error_log("Notifications table does not exist");
      return [];
    }

    try {
      $sql = "SELECT * FROM notifications WHERE 1=1";
      $params = [];

      if ($userId) {
        $sql .= " AND (userId = ? OR userId IS NULL)";
        $params[] = $userId;
      }

      if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
      }

      if ($unreadOnly) {
        $sql .= " AND isRead = FALSE";
      }

      $sql .= " ORDER BY priority DESC, createdAt DESC";

      $stmt = $conn->prepare($sql);
      if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
      }

      $stmt->execute();
      $result = $stmt->get_result();

      $notifications = [];
      while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
      }

      return $notifications;
    } catch (\Exception $e) {
      error_log("Error getting notifications: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Create a new notification - WITH ERROR HANDLING
   */
  public function createNotification($userId, $type, $title, $message, $priority = 'medium', $relatedId = null)
  {
    global $conn;

    // Check if table exists first
    if (!$this->notificationsTableExists()) {
      error_log("Cannot create notification - table does not exist");
      return false;
    }

    try {
      $sql = "INSERT INTO notifications (userId, type, title, message, priority, relatedId) VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('ssssss', $userId, $type, $title, $message, $priority, $relatedId);

      return $stmt->execute();
    } catch (\Exception $e) {
      error_log("Error creating notification: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Mark notification as read - WITH ERROR HANDLING
   */
  public function markNotificationAsRead($notificationId)
  {
    global $conn;

    if (!$this->notificationsTableExists()) {
      return false;
    }

    try {
      $sql = "UPDATE notifications SET isRead = TRUE WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $notificationId);

      return $stmt->execute();
    } catch (\Exception $e) {
      error_log("Error marking notification as read: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Get out of stock books
   */
  public function getOutOfStockBooks()
  {
    global $conn;

    $sql = "SELECT * FROM books WHERE available = 0 ORDER BY bookName";
    $result = $conn->query($sql);

    $books = [];
    while ($row = $result->fetch_assoc()) {
      $books[] = $row;
    }

    return $books;
  }

  /**
   * Check and create out of stock notifications
   */
  public function checkOutOfStockNotifications()
  {
    $outOfStockBooks = $this->getOutOfStockBooks();
    $notificationsCreated = 0;

    foreach ($outOfStockBooks as $book) {
      // Check if notification already exists for this book
      $existingNotification = $this->getNotificationByTypeAndRelatedId('out_of_stock', $book['isbn']);

      if (!$existingNotification) {
        $this->createNotification(
          null,
          'out_of_stock',
          'Book Out of Stock',
          "Book '{$book['bookName']}' by {$book['authorName']} is currently out of stock.",
          'medium',
          $book['isbn']
        );
        $notificationsCreated++;
      }
    }

    return $notificationsCreated;
  }

  /**
   * Check if backup_log table exists
   */
  private function backupLogTableExists()
  {
    return $this->tableExists('backup_log');
  }

  /**
   * Create database backup using mysqldump with error handling
   */
  public function createDatabaseBackup($backupType = 'manual')
  {
    global $conn;

    $backupDir = APP_ROOT . '/backups';
    if (!is_dir($backupDir)) {
      mkdir($backupDir, 0755, true);
    }

    $timestamp = date('Y-m-d_H-i-s');
    $filename = "backup_{$timestamp}.sql";
    $filepath = $backupDir . '/' . $filename;

    // Get database connection details
    $host = DB_HOST;
    $username = DB_USER;
    $password = DB_PASSWORD;
    $database = DB_NAME;

    // Create mysqldump command
    $command = "mysqldump --host={$host} --user={$username} --password={$password} --single-transaction --routines --triggers {$database} > {$filepath}";

    // Execute backup command
    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);

    if ($returnCode === 0 && file_exists($filepath)) {
      $filesize = filesize($filepath);

      // Log backup in database if table exists
      $this->logBackup($filename, $filepath, $filesize, $backupType, 'success');

      return $filename;
    } else {
      // Log failed backup if table exists
      $this->logBackup($filename, $filepath, 0, $backupType, 'failed');
      return false;
    }
  }

  /**
   * Log backup operation with error handling
   */
  private function logBackup($filename, $filepath, $filesize, $backupType, $status)
  {
    global $conn;

    // Skip logging if backup_log table doesn't exist
    if (!$this->backupLogTableExists()) {
      error_log("backup_log table doesn't exist - skipping backup logging");
      return false;
    }

    try {
      $sql = "INSERT INTO backup_log (filename, filepath, filesize, backupType, status, createdBy) VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $createdBy = $_SESSION['userId'] ?? 'system';
      $stmt->bind_param('ssisss', $filename, $filepath, $filesize, $backupType, $status, $createdBy);
      return $stmt->execute();
    } catch (\Exception $e) {
      error_log("Error logging backup: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Get backup history with error handling
   */
  public function getBackupHistory($limit = 10)
  {
    global $conn;

    // Return empty array if backup_log table doesn't exist
    if (!$this->backupLogTableExists()) {
      error_log("backup_log table doesn't exist - returning empty backup history");
      return [];
    }

    try {
      $sql = "SELECT * FROM backup_log ORDER BY createdAt DESC LIMIT ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $limit);
      $stmt->execute();
      $result = $stmt->get_result();

      $backups = [];
      while ($row = $result->fetch_assoc()) {
        $backups[] = $row;
      }

      return $backups;
    } catch (\Exception $e) {
      error_log("Error getting backup history: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Perform system maintenance tasks
   */
  public function performMaintenance($tasks = [])
  {
    $results = [];

    foreach ($tasks as $task) {
      switch ($task) {
        case 'update_fines':
          $results[$task] = $this->updateAllFines();
          break;
        case 'clean_notifications':
          $results[$task] = $this->cleanOldNotifications();
          break;
        case 'optimize_database':
          $results[$task] = $this->optimizeDatabase();
          break;
        case 'check_out_of_stock':
          $results[$task] = $this->checkOutOfStockNotifications();
          break;
      }
    }

    return $results;
  }

  /**
   * Clean old notifications
   */
  private function cleanOldNotifications()
  {
    global $conn;

    if (!$this->notificationsTableExists()) {
      return 0;
    }

    // Delete notifications older than 30 days
    $sql = "DELETE FROM notifications WHERE createdAt < DATE_SUB(NOW(), INTERVAL 30 DAY) AND isRead = TRUE";
    $result = $conn->query($sql);

    return $conn->affected_rows;
  }

  /**
   * Optimize database tables
   */
  private function optimizeDatabase()
  {
    global $conn;

    $tables = ['users', 'books', 'transactions', 'borrow_requests', 'book_statistics'];

    // Only add notifications if it exists
    if ($this->notificationsTableExists()) {
      $tables[] = 'notifications';
    }

    $optimized = 0;

    foreach ($tables as $table) {
      $sql = "OPTIMIZE TABLE {$table}";
      if ($conn->query($sql)) {
        $optimized++;
      }
    }

    return $optimized;
  }

  /**
   * Check if fine_settings table exists
   */
  private function fineSettingsTableExists()
  {
    return $this->tableExists('fine_settings');
  }

  /**
   * Get fine settings with error handling
   */
  public function getFineSettings()
  {
    global $conn;

    // Check if table exists first
    if (!$this->fineSettingsTableExists()) {
      error_log("fine_settings table does not exist");

      // Return default settings
      return [
        'fine_per_day' => '5',
        'max_borrow_days' => '14',
        'grace_period_days' => '0',
        'max_fine_amount' => '500',
        'fine_calculation_method' => 'daily'
      ];
    }

    try {
      $sql = "SELECT * FROM fine_settings ORDER BY setting_name";
      $result = $conn->query($sql);

      $settings = [];
      while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
      }

      return $settings;
    } catch (\Exception $e) {
      error_log("Error getting fine settings: " . $e->getMessage());

      // Return default settings on error
      return [
        'fine_per_day' => '5',
        'max_borrow_days' => '14',
        'grace_period_days' => '0',
        'max_fine_amount' => '500',
        'fine_calculation_method' => 'daily'
      ];
    }
  }

  /**
   * Update fine settings with error handling
   */
  public function updateFineSettings($settings)
  {
    global $conn;

    if (!$this->fineSettingsTableExists()) {
      error_log("fine_settings table does not exist");
      return 0;
    }

    try {
      $updated = 0;
      $updatedBy = $_SESSION['userId'] ?? 'admin';

      foreach ($settings as $name => $value) {
        $sql = "UPDATE fine_settings SET setting_value = ?, updatedBy = ? WHERE setting_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $value, $updatedBy, $name);

        if ($stmt->execute()) {
          $updated++;
        }
      }

      return $updated;
    } catch (\Exception $e) {
      error_log("Error updating fine settings: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Check if maintenance_log table exists
   */
  private function maintenanceLogTableExists()
  {
    return $this->tableExists('maintenance_log');
  }

  /**
   * Get maintenance log with error handling
   */
  public function getMaintenanceLog($limit = 20)
  {
    global $conn;

    // Return empty array if maintenance_log table doesn't exist
    if (!$this->maintenanceLogTableExists()) {
      error_log("maintenance_log table doesn't exist - returning empty maintenance log");
      return [];
    }

    try {
      $sql = "SELECT ml.*, u.userId FROM maintenance_log ml
                    JOIN users u ON ml.performedBy = u.userId
                    ORDER BY ml.createdAt DESC LIMIT ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $limit);
      $stmt->execute();
      $result = $stmt->get_result();

      $logs = [];
      while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
      }

      return $logs;
    } catch (\Exception $e) {
      error_log("Error getting maintenance log: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Log maintenance action with error handling
   */
  public function logMaintenanceAction($action, $description, $status = 'success', $details = null)
  {
    global $conn;

    // Skip logging if maintenance_log table doesn't exist
    if (!$this->maintenanceLogTableExists()) {
      error_log("maintenance_log table doesn't exist - skipping maintenance logging");
      return false;
    }

    try {
      $sql = "INSERT INTO maintenance_log (action, description, performedBy, status, details) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $performedBy = $_SESSION['userId'] ?? 'system';
      $detailsJson = $details ? json_encode($details) : null;
      $stmt->bind_param('sssss', $action, $description, $performedBy, $status, $detailsJson);

      return $stmt->execute();
    } catch (\Exception $e) {
      error_log("Error logging maintenance action: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Get recent transactions
   */
  public function getRecentTransactions($limit = 10)
  {
    return $this->transactionModel->getAllTransactions($limit);
  }

  /**
   * Get popular books
   */
  public function getPopularBooks($limit = 5)
  {
    return $this->bookModel->getPopularBooks($limit);
  }

  /**
   * Get all users with search
   */
  public function getAllUsers($search = '')
  {
    if (!empty($search)) {
      return $this->userModel->searchUsers($search);
    }
    return $this->userModel->getAllUsers();
  }

  /**
   * Delete user
   */
  public function deleteUser($userId)
  {
    return $this->userModel->deleteUser($userId);
  }

  /**
   * Get borrow requests with error handling
   */
  public function getBorrowRequests($status = 'pending')
  {
    global $conn;

    // Check if table exists first
    if (!$this->borrowRequestsTableExists()) {
      error_log("borrow_requests table does not exist");
      return [];
    }

    try {
      $sql = "SELECT br.*, u.emailId, u.userType, b.bookName, b.authorName
                    FROM borrow_requests br
                    JOIN users u ON br.userId = u.userId
                    JOIN books b ON br.isbn = b.isbn
                    WHERE br.status = ?
                    ORDER BY br.requestDate DESC";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param('s', $status);
      $stmt->execute();
      $result = $stmt->get_result();

      $requests = [];
      while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
      }

      return $requests;
    } catch (\Exception $e) {
      error_log("Error getting borrow requests: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Approve borrow request with error handling
   */
  public function approveBorrowRequest($requestId)
  {
    global $conn;

    // Check if table exists first
    if (!$this->borrowRequestsTableExists()) {
      error_log("borrow_requests table does not exist");
      return false;
    }

    try {
      // Get request details
      $sql = "SELECT * FROM borrow_requests WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $requestId);
      $stmt->execute();
      $result = $stmt->get_result();
      $request = $result->fetch_assoc();

      if (!$request) {
        return false;
      }

      // Check if book is available
      if (!$this->bookModel->isBookAvailable($request['isbn'])) {
        return false;
      }

      // Start transaction
      $conn->begin_transaction();

      try {
        // Update borrow request status
        $sql = "UPDATE borrow_requests SET status = 'Approved', approvedBy = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $approvedBy = $_SESSION['userId'] ?? 'admin';
        $stmt->bind_param('si', $approvedBy, $requestId);
        $stmt->execute();

        // Create transaction record
        $tid = 'TXN' . time() . rand(1000, 9999);
        $transactionData = [
          'tid' => $tid,
          'userId' => $request['userId'],
          'isbn' => $request['isbn'],
          'fine' => 0,
          'borrowDate' => date('Y-m-d'),
          'returnDate' => null
        ];

        if (!$this->transactionModel->createTransaction($transactionData)) {
          throw new \Exception('Failed to create transaction');
        }

        // Decrease available count
        if (!$this->bookModel->decreaseAvailable($request['isbn'])) {
          throw new \Exception('Failed to update book availability');
        }

        // Create notification
        $this->createNotification(
          $request['userId'],
          'approval',
          'Borrow Request Approved',
          "Your request for '{$request['bookName']}' has been approved.",
          'medium',
          $requestId
        );

        $conn->commit();
        return true;
      } catch (\Exception $e) {
        $conn->rollback();
        error_log("Error approving borrow request: " . $e->getMessage());
        return false;
      }
    } catch (\Exception $e) {
      error_log("Error getting borrow request details: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Reject borrow request with error handling
   */
  public function rejectBorrowRequest($requestId, $reason = '')
  {
    global $conn;

    // Check if table exists first
    if (!$this->borrowRequestsTableExists()) {
      error_log("borrow_requests table does not exist");
      return false;
    }

    try {
      $sql = "UPDATE borrow_requests SET status = 'Rejected', approvedBy = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $rejectedBy = $_SESSION['userId'] ?? 'admin';
      $stmt->bind_param('si', $rejectedBy, $requestId);

      if ($stmt->execute()) {
        // Get request details for notification
        $sql = "SELECT br.*, b.bookName FROM borrow_requests br
                        JOIN books b ON br.isbn = b.isbn
                        WHERE br.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $requestId);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();

        if ($request) {
          $message = "Your request for '{$request['bookName']}' has been rejected.";
          if (!empty($reason)) {
            $message .= " Reason: {$reason}";
          }

          $this->createNotification(
            $request['userId'],
            'approval',
            'Borrow Request Rejected',
            $message,
            'medium',
            $requestId
          );
        }

        return true;
      }

      return false;
    } catch (\Exception $e) {
      error_log("Error rejecting borrow request: " . $e->getMessage());
      return false;
    }
  }





  /**
   * Check and create overdue notifications
   */
  public function checkOverdueNotifications()
  {
    $overdueTransactions = $this->transactionModel->getOverdueTransactions();
    $notificationsCreated = 0;

    foreach ($overdueTransactions as $transaction) {
      // Check if notification already exists for this transaction
      $existingNotification = $this->getNotificationByTypeAndRelatedId('overdue', $transaction['tid']);

      if (!$existingNotification) {
        $this->createNotification(
          $transaction['userId'],
          'overdue',
          'Book Overdue',
          "The book you borrowed is now overdue. Please return it as soon as possible.",
          'high',
          $transaction['tid']
        );
        $notificationsCreated++;
      }
    }

    return $notificationsCreated;
  }

  /**
   * Get notification by type and related ID
   *
   * @param string $type Notification type
   * @param int $relatedId Related entity ID
   * @return array|null Notification or null if not found
   */
  public function getNotificationByTypeAndRelatedId($type, $relatedId)
  {
    global $conn;

    if (!$this->notificationsTableExists()) {
      return null;
    }

    try {
      $sql = "SELECT * FROM notifications WHERE type = ? AND relatedId = ? AND isRead = FALSE LIMIT 1";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('ss', $type, $relatedId);
      $stmt->execute();
      $result = $stmt->get_result();

      return $result->fetch_assoc();
    } catch (\Exception $e) {
      error_log("Error getting notification by type and related ID: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Get notification by type and user ID
   *
   * @param string $type Notification type
   * @param string $userId User ID
   * @return array|null Notification or null if not found
   */
  public function getNotificationByTypeAndUserId($type, $userId)
  {
    global $conn;

    if (!$this->notificationsTableExists()) {
      return null;
    }

    try {
      $sql = "SELECT * FROM notifications WHERE type = ? AND userId = ? ORDER BY createdAt DESC LIMIT 1";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('ss', $type, $userId);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        return $result->fetch_assoc();
      }

      return null;
    } catch (\Exception $e) {
      error_log("Error getting notification by type and user ID: " . $e->getMessage());
      return null;
    }
  }





  // ========================================
  // FIXED ANALYTICS METHODS FOR AdminService.php
  // ADD THESE TO YOUR AdminService CLASS
  // ========================================

  /**
   * Get overall statistics for analytics dashboard
   * Fixed to handle null values and ensure proper structure
   */
  public function getOverallStats()
  {
    global $conn;

    // Initialize default structure
    $stats = [
      'books' => [
        'total' => 0,
        'copies' => 0,
        'available' => 0,
        'borrowed' => 0
      ],
      'users' => [
        'total' => 0,
        'students' => 0,
        'faculty' => 0
      ],
      'activeTransactions' => 0,
      'pendingRequests' => 0,
      'fines' => [
        'total' => 0.00,
        'pending' => 0.00,
        'paid' => 0.00
      ],
      'overdue' => 0
    ];

    try {
      // Total books - with null handling
      $result = $conn->query("SELECT
            COUNT(*) as total,
            COALESCE(SUM(totalCopies), 0) as copies,
            COALESCE(SUM(available), 0) as available,
            COALESCE(SUM(borrowed), 0) as borrowed
            FROM books");

      if ($result && $row = $result->fetch_assoc()) {
        $stats['books'] = [
          'total' => (int)($row['total'] ?? 0),
          'copies' => (int)($row['copies'] ?? 0),
          'available' => (int)($row['available'] ?? 0),
          'borrowed' => (int)($row['borrowed'] ?? 0)
        ];
      }

      // Total users - with null handling
      $result = $conn->query("SELECT
            COUNT(*) as total,
            SUM(CASE WHEN userType='Student' THEN 1 ELSE 0 END) as students,
            SUM(CASE WHEN userType='Faculty' THEN 1 ELSE 0 END) as faculty
            FROM users
            WHERE userType != 'Admin'");

      if ($result && $row = $result->fetch_assoc()) {
        $stats['users'] = [
          'total' => (int)($row['total'] ?? 0),
          'students' => (int)($row['students'] ?? 0),
          'faculty' => (int)($row['faculty'] ?? 0)
        ];
      }

      // Active transactions
      $result = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE returnDate IS NULL");
      if ($result && $row = $result->fetch_assoc()) {
        $stats['activeTransactions'] = (int)($row['total'] ?? 0);
      }

      // Pending requests - check if table exists first
      if ($this->borrowRequestsTableExists()) {
        $result = $conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE status='Pending'");
        if ($result && $row = $result->fetch_assoc()) {
          $stats['pendingRequests'] = (int)($row['total'] ?? 0);
        }
      }

      // Total fines - with null handling
      $result = $conn->query("SELECT
            COALESCE(SUM(fineAmount), 0) as total,
            COALESCE(SUM(CASE WHEN fineStatus='pending' THEN fineAmount ELSE 0 END), 0) as pending,
            COALESCE(SUM(CASE WHEN fineStatus='paid' THEN fineAmount ELSE 0 END), 0) as paid
            FROM transactions
            WHERE fineAmount > 0");

      if ($result && $row = $result->fetch_assoc()) {
        $stats['fines'] = [
          'total' => (float)($row['total'] ?? 0),
          'pending' => (float)($row['pending'] ?? 0),
          'paid' => (float)($row['paid'] ?? 0)
        ];
      }

      // Overdue books - using returnDate IS NULL and borrowDate
      $result = $conn->query("SELECT COUNT(*) as total
            FROM transactions
            WHERE returnDate IS NULL
            AND DATEDIFF(CURDATE(), borrowDate) > 14");

      if ($result && $row = $result->fetch_assoc()) {
        $stats['overdue'] = (int)($row['total'] ?? 0);
      }

      return $stats;
    } catch (\Exception $e) {
      error_log("Error getting overall stats: " . $e->getMessage());
      return $stats; // Return default structure on error
    }
  }

  /**
   * Get borrowing trends for the last 30 days
   */
  public function getBorrowTrends()
  {
    global $conn;

    try {
      $sql = "SELECT DATE(borrowDate) as date, COUNT(*) as count
                FROM transactions
                WHERE borrowDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(borrowDate)
                ORDER BY date ASC";

      $result = $conn->query($sql);
      $trends = [];

      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $trends[] = [
            'date' => $row['date'] ?? '',
            'count' => (int)($row['count'] ?? 0)
          ];
        }
      }

      return $trends;
    } catch (\Exception $e) {
      error_log("Error getting borrow trends: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get top 10 most borrowed books in the last 90 days
   */
  public function getTopBooks()
  {
    global $conn;

    try {
      $sql = "SELECT b.bookName, b.authorName, b.isbn, COUNT(t.tid) as borrowCount
                FROM books b
                LEFT JOIN transactions t ON b.isbn = t.isbn
                WHERE t.borrowDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                GROUP BY b.isbn, b.bookName, b.authorName
                ORDER BY borrowCount DESC
                LIMIT 10";

      $result = $conn->query($sql);
      $books = [];

      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $books[] = [
            'bookName' => $row['bookName'] ?? 'Unknown',
            'authorName' => $row['authorName'] ?? 'Unknown',
            'isbn' => $row['isbn'] ?? '',
            'borrowCount' => (int)($row['borrowCount'] ?? 0)
          ];
        }
      }

      return $books;
    } catch (\Exception $e) {
      error_log("Error getting top books: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get category distribution of books
   */
  public function getCategoryDistribution()
  {
    global $conn;

    try {
      $sql = "SELECT
                COALESCE(category, 'Uncategorized') as category,
                COUNT(*) as count,
                COALESCE(SUM(totalCopies), 0) as copies
                FROM books
                GROUP BY category
                ORDER BY count DESC";

      $result = $conn->query($sql);
      $distribution = [];

      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $distribution[] = [
            'category' => $row['category'] ?? 'Uncategorized',
            'count' => (int)($row['count'] ?? 0),
            'copies' => (int)($row['copies'] ?? 0)
          ];
        }
      }

      return $distribution;
    } catch (\Exception $e) {
      error_log("Error getting category distribution: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get user activity statistics for the last 30 days
   */
  public function getUserActivity()
  {
    global $conn;

    try {
      $sql = "SELECT u.userType,
                COUNT(DISTINCT t.userId) as activeUsers,
                COUNT(t.tid) as transactions
                FROM transactions t
                JOIN users u ON t.userId = u.userId
                WHERE t.borrowDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY u.userType";

      $result = $conn->query($sql);
      $activity = [];

      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $activity[] = [
            'userType' => $row['userType'] ?? 'Unknown',
            'activeUsers' => (int)($row['activeUsers'] ?? 0),
            'transactions' => (int)($row['transactions'] ?? 0)
          ];
        }
      }

      return $activity;
    } catch (\Exception $e) {
      error_log("Error getting user activity: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get fine statistics for the last 6 months
   */
  public function getFineStats()
  {
    global $conn;

    try {
      $sql = "SELECT
                    DATE_FORMAT(borrowDate, '%Y-%m') as month,
                    COALESCE(SUM(fineAmount), 0) as totalFines,
                    COALESCE(SUM(CASE WHEN fineStatus='paid' THEN fineAmount ELSE 0 END), 0) as paidFines,
                    COALESCE(SUM(CASE WHEN fineStatus='pending' THEN fineAmount ELSE 0 END), 0) as pendingFines
                FROM transactions
                WHERE fineAmount > 0 AND borrowDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month ASC";

      $result = $conn->query($sql);
      $stats = [];

      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $stats[] = [
            'month' => $row['month'] ?? '',
            'totalFines' => (float)($row['totalFines'] ?? 0),
            'paidFines' => (float)($row['paidFines'] ?? 0),
            'pendingFines' => (float)($row['pendingFines'] ?? 0)
          ];
        }
      }

      return $stats;
    } catch (\Exception $e) {
      error_log("Error getting fine stats: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get monthly statistics for the last 12 months
   */
  public function getMonthlyStats()
  {
    global $conn;

    try {
      $sql = "SELECT
                    DATE_FORMAT(borrowDate, '%Y-%m') as month,
                    COUNT(*) as issues,
                    SUM(CASE WHEN returnDate IS NOT NULL THEN 1 ELSE 0 END) as returns
                FROM transactions
                WHERE borrowDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month ASC";

      $result = $conn->query($sql);
      $stats = [];

      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $stats[] = [
            'month' => $row['month'] ?? '',
            'issues' => (int)($row['issues'] ?? 0),
            'returns' => (int)($row['returns'] ?? 0)
          ];
        }
      }

      return $stats;
    } catch (\Exception $e) {
      error_log("Error getting monthly stats: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get recent activities (transactions and requests) from the last 7 days
   */
  public function getRecentActivities()
  {
    global $conn;

    try {
      // Check if borrow_requests table exists
      $hasBorrowRequests = $this->borrowRequestsTableExists();

      if ($hasBorrowRequests) {
        $sql = "SELECT
                        'Transaction' as type,
                        CONCAT(COALESCE(u.emailId, 'Unknown User'), ' borrowed ', COALESCE(b.bookName, 'Unknown Book')) as description,
                        t.borrowDate as timestamp
                    FROM transactions t
                    LEFT JOIN users u ON t.userId = u.userId
                    LEFT JOIN books b ON t.isbn = b.isbn
                    WHERE t.borrowDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)

                    UNION ALL

                    SELECT
                        'Request' as type,
                        CONCAT(COALESCE(u.emailId, 'Unknown User'), ' requested ', COALESCE(b.bookName, 'Unknown Book')) as description,
                        br.requestDate as timestamp
                    FROM borrow_requests br
                    LEFT JOIN users u ON br.userId = u.userId
                    LEFT JOIN books b ON br.isbn = b.isbn
                    WHERE br.requestDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)

                    ORDER BY timestamp DESC
                    LIMIT 20";
      } else {
        // If borrow_requests table doesn't exist, only get transactions
        $sql = "SELECT
                        'Transaction' as type,
                        CONCAT(COALESCE(u.emailId, 'Unknown User'), ' borrowed ', COALESCE(b.bookName, 'Unknown Book')) as description,
                        t.borrowDate as timestamp
                    FROM transactions t
                    LEFT JOIN users u ON t.userId = u.userId
                    LEFT JOIN books b ON t.isbn = b.isbn
                    WHERE t.borrowDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ORDER BY timestamp DESC
                    LIMIT 20";
      }

      $result = $conn->query($sql);
      $activities = [];

      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $activities[] = [
            'type' => $row['type'] ?? 'Transaction',
            'description' => $row['description'] ?? 'No description',
            'timestamp' => $row['timestamp'] ?? date('Y-m-d H:i:s')
          ];
        }
      }

      return $activities;
    } catch (\Exception $e) {
      error_log("Error getting recent activities: " . $e->getMessage());
      return [];
    }
  }
}
