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
                SELECT t.*, b.bookName, b.authorName, b.bookImage, b.isbn
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? AND t.returnDate IS NULL
                ORDER BY t.borrowDate DESC
            ");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $row['title'] = $row['bookName'];
                $row['author'] = $row['authorName'];
                $row['image'] = $row['bookImage'];
                $row['dueDate'] = $row['returnDate'] ?? date('Y-m-d', strtotime($row['borrowDate'] . ' + 14 days'));
                $row['transactionId'] = $row['tid'];
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
                SELECT t.*, b.bookName, b.authorName, b.bookImage
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? 
                AND t.returnDate IS NULL 
                AND DATE_ADD(t.borrowDate, INTERVAL 14 DAY) < CURDATE()
                ORDER BY t.borrowDate ASC
            ");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
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
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $row['title'] = $row['bookName'];
                $row['transactionId'] = $row['tid'];
                $row['issueDate'] = $row['borrowDate'];
                $transactions[] = $row;
            }
            
            return $transactions;
        } catch (\Exception $e) {
            error_log("Error getting transactions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get fines for a user (ALL fines - paid and unpaid)
     */
    public function getFinesByUserId($userId)
    {
        try {
            // Query to get ALL transactions with fines (paid or unpaid)
            $stmt = $this->db->prepare("
                SELECT t.*, b.bookName, b.isbn, b.authorName
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? 
                AND (
                    t.fineAmount > 0 
                    OR (t.returnDate IS NULL AND DATEDIFF(CURDATE(), t.borrowDate) > 14)
                    OR t.fineStatus IS NOT NULL
                )
                ORDER BY t.fineStatus ASC, t.borrowDate ASC
            ");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $fines = [];
            while ($row = $result->fetch_assoc()) {
                // Calculate fine for unreturned books
                if ($row['returnDate'] === null) {
                    $borrowDate = new \DateTime($row['borrowDate']);
                    $currentDate = new \DateTime();
                    $interval = $borrowDate->diff($currentDate);
                    $totalDays = $interval->days;
                    $daysOverdue = max(0, $totalDays - 14); // 14 day borrow period
                    
                    if ($daysOverdue > 0) {
                        $calculatedFine = $daysOverdue * 5; // ₹5 per day
                        // Use the higher of database fine or calculated fine
                        $row['fineAmount'] = max($row['fineAmount'] ?? 0, $calculatedFine);
                    }
                }
                
                // Ensure fineAmount is set
                if (!isset($row['fineAmount'])) {
                    $row['fineAmount'] = 0;
                }
                
                // Ensure fineStatus is set
                if (!isset($row['fineStatus']) || empty($row['fineStatus'])) {
                    $row['fineStatus'] = ($row['fineAmount'] > 0) ? 'pending' : 'paid';
                }
                
                $fines[] = $row;
            }
            
            return $fines;
        } catch (\Exception $e) {
            error_log("Error getting fines: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Return a book
     */
    public function returnBook($transactionId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE transactions 
                SET returnDate = CURDATE() 
                WHERE tid = ? AND returnDate IS NULL
            ");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $transactionId);
            $result = $stmt->execute();
            
            if ($result && $stmt->affected_rows > 0) {
                // Update book availability
                $getIsbn = $this->db->prepare("SELECT isbn FROM transactions WHERE tid = ?");
                $getIsbn->bind_param("s", $transactionId);
                $getIsbn->execute();
                $isbnResult = $getIsbn->get_result()->fetch_assoc();
                
                if ($isbnResult) {
                    $updateBook = $this->db->prepare("
                        UPDATE books 
                        SET available = available + 1, borrowed = borrowed - 1 
                        WHERE isbn = ?
                    ");
                    $updateBook->bind_param("s", $isbnResult['isbn']);
                    $updateBook->execute();
                }
                
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error returning book: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get transactions by user ID
     */
    public function getTransactionsByUserId($userId)
    {
        return $this->getTransactionsByUser($userId);
    }

    /**
     * Get active borrow by user and book
     */
    public function getActiveBorrowByUserAndBook($userId, $isbn)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM transactions 
                WHERE userId = ? AND isbn = ? AND returnDate IS NULL
                LIMIT 1
            ");
            
            if (!$stmt) {
                return null;
            }
            
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
     * Get transactions by date range
     */
    public function getTransactionsByDateRange($startDate, $endDate, $userId = null)
    {
        try {
            if ($userId) {
                $stmt = $this->db->prepare("
                    SELECT t.*, b.bookName, b.authorName, b.isbn
                    FROM transactions t
                    JOIN books b ON t.isbn = b.isbn
                    WHERE t.borrowDate BETWEEN ? AND ?
                    AND t.userId = ?
                    ORDER BY t.borrowDate DESC
                ");
                $stmt->bind_param("sss", $startDate, $endDate, $userId);
            } else {
                $stmt = $this->db->prepare("
                    SELECT t.*, b.bookName, b.authorName, b.isbn
                    FROM transactions t
                    JOIN books b ON t.isbn = b.isbn
                    WHERE t.borrowDate BETWEEN ? AND ?
                    ORDER BY t.borrowDate DESC
                ");
                $stmt->bind_param("ss", $startDate, $endDate);
            }
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting transactions by date range: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active borrowing count
     */
    public function getActiveBorrowingCount()
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM transactions WHERE returnDate IS NULL";
            $result = $this->db->query($sql);
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['count'];
            }
            
            return 0;
        } catch (\Exception $e) {
            error_log("Error getting active borrowing count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get overdue transactions
     */
    public function getOverdueTransactions()
    {
        try {
            $sql = "SELECT t.*, b.bookName, b.authorName, u.emailId, u.userType
                    FROM transactions t
                    JOIN books b ON t.isbn = b.isbn
                    JOIN users u ON t.userId = u.userId
                    WHERE t.returnDate IS NULL 
                    AND DATEDIFF(CURDATE(), t.borrowDate) > 14
                    ORDER BY t.borrowDate ASC";
            
            $result = $this->db->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting overdue transactions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update fine for a transaction
     */
    public function updateFine($tid, $fineAmount)
    {
        try {
            $stmt = $this->db->prepare("UPDATE transactions SET fineAmount = ? WHERE tid = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("ds", $fineAmount, $tid);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating fine: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pay fine for a transaction
     */
    public function payFine($tid, $paymentMethod = 'card', $cardLastFour = null)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE transactions 
                SET fineStatus = 'paid', 
                    finePaymentDate = CURDATE(), 
                    finePaymentMethod = ?,
                    lastFinePaymentDate = CURDATE() 
                WHERE tid = ?
            ");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("ss", $paymentMethod, $tid);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error paying fine: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get fine statistics
     */
    public function getFineStats()
    {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_fines,
                    SUM(CASE WHEN fineStatus = 'paid' THEN fineAmount ELSE 0 END) as paid_fines,
                    SUM(CASE WHEN fineStatus = 'pending' THEN fineAmount ELSE 0 END) as pending_fines,
                    SUM(fineAmount) as total_amount
                    FROM transactions 
                    WHERE fineAmount > 0";
            
            $result = $this->db->query($sql);
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error getting fine stats: " . $e->getMessage());
            return [
                'total_fines' => 0,
                'paid_fines' => 0,
                'pending_fines' => 0,
                'total_amount' => 0
            ];
        }
    }
}
?>
