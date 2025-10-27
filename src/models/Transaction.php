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
     * Get fines for a user
     */
    public function getFinesByUserId($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, b.bookName, b.isbn
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? AND t.fineAmount > 0
                ORDER BY t.borrowDate DESC
            ");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
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
}
?>
