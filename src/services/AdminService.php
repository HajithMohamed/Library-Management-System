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
   * Update all fines for overdue books - FIXED
   * Changed 'fine' to 'fineAmount'
   */
  public function updateAllFines()
  {
    $overdueTransactions = $this->transactionModel->getOverdueTransactions();
    $updatedCount = 0;

    foreach ($overdueTransactions as $transaction) {
      $fine = $this->calculateFine($transaction['borrowDate']);
      // FIXED: Changed 'fine' to 'fineAmount'
      if ($fine > ($transaction['fineAmount'] ?? 0)) {
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
   * Generate overview report - FIXED
   * Changed 'fine' to 'fineAmount'
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
      // FIXED: Changed 'fine' to 'fineAmount'
      'total_fines' => array_sum(array_column($transactions, 'fineAmount'))
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
   * Generate fines report - FIXED
   * Changed 'fine' to 'fineAmount'
   */
  private function generateFinesReport($startDate, $endDate)
  {
    $transactions = $this->transactionModel->getTransactionsByDateRange($startDate, $endDate);
    $overdueTransactions = $this->transactionModel->getOverdueTransactions();

    $report = [
      'period' => "{$startDate} to {$endDate}",
      // FIXED: Changed 'fine' to 'fineAmount'
      'total_fines' => array_sum(array_column($transactions, 'fineAmount')),
      'overdue_books' => count($overdueTransactions),
      'fines_by_user_type' => [],
      'top_fine_payers' => []
    ];

    // Group fines by user type - FIXED
    foreach ($transactions as $transaction) {
      // FIXED: Changed 'fine' to 'fineAmount'
      if (($transaction['fineAmount'] ?? 0) > 0) {
        $userType = $transaction['userType'];
        $report['fines_by_user_type'][$userType] = ($report['fines_by_user_type'][$userType] ?? 0) + $transaction['fineAmount'];
      }
    }

    // Top fine payers - FIXED
    $userFines = [];
    foreach ($transactions as $transaction) {
      // FIXED: Changed 'fine' to 'fineAmount'
      if (($transaction['fineAmount'] ?? 0) > 0) {
        $userId = $transaction['userId'];
        $userFines[$userId] = ($userFines[$userId] ?? 0) + $transaction['fineAmount'];
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
      'borrowed_books' => count($transactions),
      'popular_books' => $popularBooks,
      'categories' => []
    ];

    // Group books by category
    foreach ($books as $book) {
      $category = $book['category'] ?? 'Uncategorized';
      $report['categories'][$category] = ($report['categories'][$category] ?? 0) + 1;
    }

    return $report;
  }

  /**
   * Delete user
   */
  public function deleteUser($userId)
  {
    return $this->userModel->deleteUser($userId);
  }

  /**
   * Get all fines - FIXED
   * Added proper NULL handling for fineAmount
   */
  public function getAllFines($status = null, $userId = null)
  {
    global $conn;

    try {
      // FIXED: Changed 'fine' to 'fineAmount'
      $sql = "SELECT t.*, u.emailId, u.userType, b.bookName, b.authorName
              FROM transactions t
              LEFT JOIN users u ON t.userId = u.userId
              LEFT JOIN books b ON t.isbn = b.isbn
              WHERE fineAmount > 0";

      if ($status) {
        $sql .= " AND t.fineStatus = ?";
      }

      if ($userId) {
        $sql .= " AND t.userId = ?";
      }

      $sql .= " ORDER BY t.borrowDate DESC";

      $stmt = $conn->prepare($sql);

      $params = [];
      $types = "";

      if ($status) {
        $params[] = $status;
        $types .= "s";
      }

      if ($userId) {
        $params[] = $userId;
        $types .= "s";
      }

      if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
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
   * Get fine settings
   */
  public function getFineSettings()
  {
    global $conn;

    try {
      $result = $conn->query("SELECT * FROM fine_settings");

      $settings = [];
      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $settings[] = $row;
        }
      }

      return $settings;
    } catch (\Exception $e) {
      error_log("Error getting fine settings: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Update fine status
   */
  public function updateFineStatus($transactionId, $status, $paymentMethod = null)
  {
    global $conn;

    try {
      $sql = "UPDATE transactions SET fineStatus = ?";

      if ($status === 'paid' && $paymentMethod) {
        $sql .= ", finePaymentMethod = ?, finePaymentDate = CURDATE()";
      }

      $sql .= " WHERE tid = ?";

      $stmt = $conn->prepare($sql);

      if ($status === 'paid' && $paymentMethod) {
        $stmt->bind_param("sss", $status, $paymentMethod, $transactionId);
      } else {
        $stmt->bind_param("ss", $status, $transactionId);
      }

      return $stmt->execute();
    } catch (\Exception $e) {
      error_log("Error updating fine status: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Update fine settings
   */
  public function updateFineSettings($settings)
  {
    global $conn;

    try {
      $updatedCount = 0;

      foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("UPDATE fine_settings SET settingValue = ? WHERE settingKey = ?");
        $stmt->bind_param("ss", $value, $key);

        if ($stmt->execute()) {
          $updatedCount++;
        }
      }

      return $updatedCount;
    } catch (\Exception $e) {
      error_log("Error updating fine settings: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Get borrow requests
   */
  public function getBorrowRequests($status = 'pending')
  {
    global $conn;

    try {
      $sql = "SELECT br.*, u.emailId, u.userType, b.bookName, b.authorName, b.available
              FROM borrow_requests br
              LEFT JOIN users u ON br.userId = u.userId
              LEFT JOIN books b ON br.isbn = b.isbn
              WHERE br.status = ?
              ORDER BY br.requestDate DESC";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $status);
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
   * Approve borrow request
   */
  public function approveBorrowRequest($requestId)
  {
    global $conn;

    try {
      // Start transaction
      $conn->begin_transaction();

      // Get request details
      $stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE id = ?");
      $stmt->bind_param("i", $requestId);
      $stmt->execute();
      $request = $stmt->get_result()->fetch_assoc();

      if (!$request) {
        $conn->rollback();
        return false;
      }

      // Check if book is available
      $stmt = $conn->prepare("SELECT available FROM books WHERE isbn = ?");
      $stmt->bind_param("s", $request['isbn']);
      $stmt->execute();
      $book = $stmt->get_result()->fetch_assoc();

      if (!$book || $book['available'] <= 0) {
        $conn->rollback();
        return false;
      }

      // Update borrow request status
      $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Approved', approvedBy = ? WHERE id = ?");
      $adminId = $_SESSION['userId'] ?? 'ADM001';
      $stmt->bind_param("si", $adminId, $requestId);
      $stmt->execute();

      // Create transaction
      $tid = 'TXN' . time() . rand(100, 999);
      $borrowDate = date('Y-m-d');
      $stmt = $conn->prepare("INSERT INTO transactions (tid, userId, isbn, borrowDate) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $tid, $request['userId'], $request['isbn'], $borrowDate);
      $stmt->execute();

      // Update book availability
      $stmt = $conn->prepare("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ?");
      $stmt->bind_param("s", $request['isbn']);
      $stmt->execute();

      $conn->commit();
      return true;
    } catch (\Exception $e) {
      $conn->rollback();
      error_log("Error approving borrow request: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Reject borrow request
   */
  public function rejectBorrowRequest($requestId, $reason)
  {
    global $conn;

    try {
      $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'Rejected', rejectionReason = ? WHERE id = ?");
      $stmt->bind_param("si", $reason, $requestId);

      return $stmt->execute();
    } catch (\Exception $e) {
      error_log("Error rejecting borrow request: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Get all notifications
   */
  public function getAllNotifications($userId = null, $type = null, $unreadOnly = false)
  {
    global $conn;

    try {
      $sql = "SELECT * FROM notifications WHERE 1=1";

      if ($userId) {
        $sql .= " AND userId = ?";
      }

      if ($type) {
        $sql .= " AND type = ?";
      }

      if ($unreadOnly) {
        $sql .= " AND isRead = 0";
      }

      $sql .= " ORDER BY createdAt DESC";

      $stmt = $conn->prepare($sql);

      $params = [];
      $types = "";

      if ($userId) {
        $params[] = $userId;
        $types .= "s";
      }

      if ($type) {
        $params[] = $type;
        $types .= "s";
      }

      if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
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
   * Mark notification as read
   */
  public function markNotificationAsRead($notificationId)
  {
    global $conn;

    try {
      $stmt = $conn->prepare("UPDATE notifications SET isRead = 1 WHERE id = ?");
      $stmt->bind_param("i", $notificationId);

      return $stmt->execute();
    } catch (\Exception $e) {
      error_log("Error marking notification as read: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Get system health
   */
  public function getSystemHealth()
  {
    global $conn;

    try {
      $health = [
        'database' => $conn->ping() ? 'healthy' : 'error',
        'disk_space' => 'healthy',
        'overdue_books' => 0,
        'low_stock_books' => 0,
        'overall' => 'healthy'
      ];

      // Get overdue books count
      $result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE returnDate IS NULL AND DATEDIFF(CURDATE(), borrowDate) > 14");
      $health['overdue_books'] = $result->fetch_assoc()['count'] ?? 0;

      // Get low stock books count
      $result = $conn->query("SELECT COUNT(*) as count FROM books WHERE available <= 2");
      $health['low_stock_books'] = $result->fetch_assoc()['count'] ?? 0;

      // Determine overall health
      if ($health['overdue_books'] > 50 || $health['low_stock_books'] > 20) {
        $health['overall'] = 'warning';
      }

      return $health;
    } catch (\Exception $e) {
      error_log("Error getting system health: " . $e->getMessage());
      return [
        'database' => 'error',
        'overall' => 'error'
      ];
    }
  }

  /**
   * Get backup history
   */
  public function getBackupHistory($limit = 10)
  {
    // This would require a backups table
    // For now, return empty array
    return [];
  }

  /**
   * Get maintenance log
   */
  public function getMaintenanceLog($limit = 20)
  {
    global $conn;

    try {
      $sql = "SELECT * FROM audit_logs WHERE action LIKE '%MAINTENANCE%' ORDER BY createdAt DESC LIMIT ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $limit);
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
   * Create database backup
   */
  public function createDatabaseBackup($backupType = 'manual')
  {
    // This would require mysqldump or similar
    // Implementation depends on server environment
    return false;
  }

  /**
   * Perform maintenance tasks
   */
  public function performMaintenance($tasks)
  {
    $results = [];

    foreach ($tasks as $task) {
      switch ($task) {
        case 'update_fines':
          $results[$task] = $this->updateAllFines();
          break;

        case 'clean_expired_tokens':
          $results[$task] = $this->cleanExpiredTokens();
          break;

        case 'optimize_tables':
          $results[$task] = $this->optimizeTables();
          break;

        default:
          $results[$task] = 0;
      }
    }

    return $results;
  }

  /**
   * Clean expired tokens
   */
  private function cleanExpiredTokens()
  {
    global $conn;

    try {
      $stmt = $conn->prepare("UPDATE users SET verificationToken = NULL, otp = NULL WHERE otpExpiry < NOW()");
      $stmt->execute();

      return $stmt->affected_rows;
    } catch (\Exception $e) {
      error_log("Error cleaning expired tokens: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Optimize database tables
   */
  private function optimizeTables()
  {
    global $conn;

    try {
      $tables = ['users', 'books', 'transactions', 'borrow_requests', 'notifications'];
      $optimized = 0;

      foreach ($tables as $table) {
        $conn->query("OPTIMIZE TABLE $table");
        $optimized++;
      }

      return $optimized;
    } catch (\Exception $e) {
      error_log("Error optimizing tables: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Get overall statistics
   */
  public function getOverallStats()
  {
    global $conn;

    try {
      $stats = [];

      // Total books
      $result = $conn->query("SELECT COUNT(*) as total FROM books");
      $stats['total_books'] = $result->fetch_assoc()['total'] ?? 0;

      // Total users
      $result = $conn->query("SELECT COUNT(*) as total FROM users");
      $stats['total_users'] = $result->fetch_assoc()['total'] ?? 0;

      // Total transactions
      $result = $conn->query("SELECT COUNT(*) as total FROM transactions");
      $stats['total_transactions'] = $result->fetch_assoc()['total'] ?? 0;

      // Active borrowings
      $result = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE returnDate IS NULL");
      $stats['active_borrowings'] = $result->fetch_assoc()['total'] ?? 0;

      // Total fines - FIXED: Changed 'fine' to 'fineAmount'
      $result = $conn->query("SELECT SUM(fineAmount) as total FROM transactions WHERE fineAmount > 0");
      $stats['total_fines'] = $result->fetch_assoc()['total'] ?? 0;

      return $stats;
    } catch (\Exception $e) {
      error_log("Error getting overall stats: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get borrow trends for the last 30 days
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
   * Get fine statistics for the last 6 months - FIXED
   * Changed 'fine' to 'fineAmount'
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
