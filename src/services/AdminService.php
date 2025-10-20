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
     * Create database backup
     */
    public function createDatabaseBackup()
    {
        $backupDir = APP_ROOT . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupFile = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // This would typically use mysqldump command
        // For now, we'll just create an empty file as a placeholder
        file_put_contents($backupFile, "-- Database backup created on " . date('Y-m-d H:i:s') . "\n");
        
        return basename($backupFile);
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
}
?>
