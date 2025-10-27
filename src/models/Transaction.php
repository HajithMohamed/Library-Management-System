<?php

namespace App\Models;

class Transaction
{
    private $db;
    
    public function __construct()
    {
        global $mysqli;
        $this->db = $mysqli;
    }
    
    /**
     * Create a new transaction
     */
    public function createTransaction($data)
    {
        $sql = "INSERT INTO transactions (tid, userId, isbn, fine, borrowDate, returnDate, lastFinePaymentDate) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssisss', 
            $data['tid'], 
            $data['userId'], 
            $data['isbn'], 
            $data['fine'], 
            $data['borrowDate'], 
            $data['returnDate'] ?? null,
            $data['lastFinePaymentDate'] ?? null
        );
        
        return $stmt->execute();
    }

    /**
     * Get transaction by ID
     */
    public function getTransactionById($tid)
    {
        $sql = "SELECT * FROM transactions WHERE tid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $tid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get borrowed books for a user
     */
    public function getBorrowedBooks($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, b.bookName, b.authorName 
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? AND t.returnDate IS NULL
                ORDER BY t.borrowDate DESC
            ");
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error getting borrowed books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get overdue books for a user
     */
    public function getOverdueBooks($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, b.bookName, b.authorName 
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? 
                AND t.returnDate IS NULL 
                AND DATE_ADD(t.borrowDate, INTERVAL 14 DAY) < CURDATE()
                ORDER BY t.borrowDate ASC
            ");
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error getting overdue books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all transactions for a user
     */
    public function getTransactionsByUser($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, b.bookName, b.authorName 
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ?
                ORDER BY t.borrowDate DESC
                LIMIT 50
            ");
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
            
            return $transactions;
        } catch (\Exception $e) {
            error_log("Error getting transactions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get transactions by user ID (alias)
     */
    public function getTransactionsByUserId($userId)
    {
        return $this->getTransactionsByUser($userId);
    }
    
    /**
     * Check if user has active borrow for a book
     */
    public function getActiveBorrowByUserAndBook($userId, $isbn)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM transactions 
                WHERE userId = ? AND isbn = ? AND returnDate IS NULL
                LIMIT 1
            ");
            
            $stmt->bind_param("ss", $userId, $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error checking active borrow: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get count of active borrowings for a user
     */
    public function getActiveBorrowingCount($userId = null)
    {
        if ($userId) {
            $sql = "SELECT COUNT(*) as count FROM transactions WHERE userId = ? AND returnDate IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $userId);
        } else {
            $sql = "SELECT COUNT(*) as count FROM transactions WHERE returnDate IS NULL";
            $stmt = $this->db->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }

    /**
     * Return a book (update transaction)
     */
    public function returnBook($tid, $returnDate, $fine)
    {
        $sql = "UPDATE transactions SET returnDate = ?, fine = ? WHERE tid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sis', $returnDate, $fine, $tid);
        
        return $stmt->execute();
    }

    /**
     * Update fine for a transaction
     */
    public function updateFine($tid, $fine)
    {
        $sql = "UPDATE transactions SET fine = ? WHERE tid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('is', $fine, $tid);
        
        return $stmt->execute();
    }

    /**
     * Get overdue transactions
     */
    public function getOverdueTransactions()
    {
        $maxDays = 14; // 2 weeks
        $cutoffDate = date('Y-m-d', strtotime("-{$maxDays} days"));
        
        $sql = "SELECT * FROM transactions WHERE returnDate IS NULL AND borrowDate < ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $cutoffDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Duplicate method removed: use getUserTransactionHistory defined earlier to avoid duplicate declaration.
     */

    /**
     * Get all transactions with book and user details
     */
    public function getAllTransactions($limit = 100)
    {
        $sql = "SELECT t.*, b.bookName, b.authorName, u.emailId, u.userType 
                FROM transactions t 
                INNER JOIN books b ON t.isbn = b.isbn 
                INNER JOIN users u ON t.userId = u.userId 
                ORDER BY t.borrowDate DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get fine statistics
     */
    public function getFineStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(fine) as total_fines,
                    COUNT(CASE WHEN fine > 0 THEN 1 END) as transactions_with_fines,
                    COUNT(CASE WHEN returnDate IS NULL AND fine > 0 THEN 1 END) as active_fines
                FROM transactions";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Pay fine for a transaction
     */
    public function payFine($tid, $amount)
    {
        $transaction = $this->getTransactionById($tid);
        if (!$transaction) {
            return false;
        }

        $newFine = max(0, $transaction['fine'] - $amount);
        $lastPaymentDate = date('Y-m-d');
        
        $sql = "UPDATE transactions SET fine = ?, lastFinePaymentDate = ? WHERE tid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iss', $newFine, $lastPaymentDate, $tid);
        
        return $stmt->execute();
    }

    /**
     * Get transactions by date range
     */
    public function getTransactionsByDateRange($startDate, $endDate)
    {
        $sql = "SELECT t.*, b.bookName, b.authorName, u.emailId, u.userType 
                FROM transactions t 
                INNER JOIN books b ON t.isbn = b.isbn 
                INNER JOIN users u ON t.userId = u.userId 
                WHERE t.borrowDate BETWEEN ? AND ? 
                ORDER BY t.borrowDate DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
