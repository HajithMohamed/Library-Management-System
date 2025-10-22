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
        $newUsers = array_filter($users, function($user) use ($startDate) {
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
        } catch (Exception $e) {
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
        $unverified = array_filter($users, function($user) {
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
        $lowStock = array_filter($books, function($book) {
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
     * Get all fines with detailed information
     */
    public function getAllFines($status = null, $userId = null)
    {
        global $conn;
        
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
        
        $sql .= " ORDER BY t.fineAmount DESC, t.borrowDate DESC";
        
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
                    null, // Get userId from transaction
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
     * Get all notifications
     */
    public function getAllNotifications($userId = null, $type = null, $unreadOnly = false)
    {
        global $conn;
        
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
    }

    /**
     * Create a new notification
     */
    public function createNotification($userId, $type, $title, $message, $priority = 'medium', $relatedId = null)
    {
        global $conn;
        
        $sql = "INSERT INTO notifications (userId, type, title, message, priority, relatedId) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssss', $userId, $type, $title, $message, $priority, $relatedId);
        
        return $stmt->execute();
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId)
    {
        global $conn;
        
        $sql = "UPDATE notifications SET isRead = TRUE WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $notificationId);
        
        return $stmt->execute();
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
                    null, // System-wide notification
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
     * Get notification by type and related ID
     */
    private function getNotificationByTypeAndRelatedId($type, $relatedId)
    {
        global $conn;
        
        $sql = "SELECT * FROM notifications WHERE type = ? AND relatedId = ? AND isRead = FALSE LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $type, $relatedId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Create database backup using mysqldump
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
        $username = DB_USERNAME;
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
            
            // Log backup in database
            $this->logBackup($filename, $filepath, $filesize, $backupType, 'success');
            
            return $filename;
        } else {
            // Log failed backup
            $this->logBackup($filename, $filepath, 0, $backupType, 'failed');
            return false;
        }
    }

    /**
     * Log backup operation
     */
    private function logBackup($filename, $filepath, $filesize, $backupType, $status)
    {
        global $conn;
        
        $sql = "INSERT INTO backup_log (filename, filepath, filesize, backupType, status, createdBy) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $createdBy = $_SESSION['userId'] ?? 'system';
        $stmt->bind_param('ssisss', $filename, $filepath, $filesize, $backupType, $status, $createdBy);
        $stmt->execute();
    }

    /**
     * Get backup history
     */
    public function getBackupHistory($limit = 10)
    {
        global $conn;
        
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
        
        $tables = ['users', 'books', 'transactions', 'notifications', 'borrow_requests', 'book_statistics'];
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
     * Get fine settings
     */
    public function getFineSettings()
    {
        global $conn;
        
        $sql = "SELECT * FROM fine_settings ORDER BY setting_name";
        $result = $conn->query($sql);
        
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
        
        return $settings;
    }

    /**
     * Update fine settings
     */
    public function updateFineSettings($settings)
    {
        global $conn;
        
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
    }

    /**
     * Get maintenance log
     */
    public function getMaintenanceLog($limit = 20)
    {
        global $conn;
        
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
    }

    /**
     * Log maintenance action
     */
    public function logMaintenanceAction($action, $description, $status = 'success', $details = null)
    {
        global $conn;
        
        $sql = "INSERT INTO maintenance_log (action, description, performedBy, status, details) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $performedBy = $_SESSION['userId'] ?? 'system';
        $detailsJson = $details ? json_encode($details) : null;
        $stmt->bind_param('sssss', $action, $description, $performedBy, $status, $detailsJson);
        
        return $stmt->execute();
    }
}
?>
