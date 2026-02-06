<?php

namespace App\Models;

use PDO;
use PDOException;

class Transaction extends BaseModel
{
    protected $table = 'transactions'; // Add this line

    public function __construct(?\PDO $db = null)
    {
        parent::__construct($db);
    }

    /**
     * Create a new transaction
     */
    public function createTransaction($data)
    {
        $sql = "INSERT INTO transactions (tid, userId, isbn, fine, borrowDate, returnDate, lastFinePaymentDate) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $data['tid'],
            $data['userId'],
            $data['isbn'],
            $data['fine'],
            $data['borrowDate'],
            $data['returnDate'] ?? null,
            $data['lastFinePaymentDate'] ?? null
        ]);
    }

    /**
     * Get transaction by ID
     */
    public function getTransactionById($tid)
    {
        $sql = "SELECT * FROM transactions WHERE tid = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tid]);

        return $stmt->fetch();
    }

    /**
     * Get borrowed books for a user
     */
    public function getBorrowedBooks($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT t.*, b.bookName, b.authorName, b.bookImage, b.isbn
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? AND t.returnDate IS NULL
                ORDER BY t.borrowDate DESC
            ");

            $stmt->execute([$userId]);

            $books = [];
            while ($row = $stmt->fetch()) {
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
            $stmt = $this->pdo->prepare("
                SELECT t.*, b.bookName, b.authorName, b.bookImage
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? 
                AND t.returnDate IS NULL 
                AND DATE_ADD(t.borrowDate, INTERVAL 14 DAY) < CURDATE()
                ORDER BY t.borrowDate ASC
            ");

            $stmt->execute([$userId]);
            return $stmt->fetchAll();
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
            $stmt = $this->pdo->prepare("
                SELECT t.*, b.bookName, b.authorName
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ?
                ORDER BY t.borrowDate DESC
                LIMIT 50
            ");

            $stmt->execute([$userId]);

            $transactions = [];
            while ($row = $stmt->fetch()) {
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
            $stmt = $this->pdo->prepare("
                SELECT t.*, b.bookName, b.isbn, b.authorName
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? 
                AND (
                    t.fineAmount > 0 
                    OR (t.returnDate IS NULL AND DATEDIFF(CURDATE(), t.borrowDate) > 14)
                    OR t.fineStatus IS NOT NULL
                )
                ORDER BY 
                    CASE 
                        WHEN t.fineStatus = 'pending' OR t.fineStatus IS NULL THEN 0
                        ELSE 1
                    END,
                    t.borrowDate DESC
            ");

            $stmt->execute([$userId]);

            $fines = [];
            while ($row = $stmt->fetch()) {
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

                // Ensure fineStatus is set - if fine exists but no status, it's pending
                if (!isset($row['fineStatus']) || empty($row['fineStatus'])) {
                    $row['fineStatus'] = ($row['fineAmount'] > 0) ? 'pending' : null;
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
            $stmt = $this->pdo->prepare("
                UPDATE transactions 
                SET returnDate = CURDATE() 
                WHERE tid = ? AND returnDate IS NULL
            ");

            $result = $stmt->execute([$transactionId]);

            if ($result && $stmt->rowCount() > 0) {
                // Update book availability
                $getIsbn = $this->pdo->prepare("SELECT isbn FROM transactions WHERE tid = ?");
                $getIsbn->execute([$transactionId]);
                $isbnResult = $getIsbn->fetch();

                if ($isbnResult) {
                    $updateBook = $this->pdo->prepare("
                        UPDATE books 
                        SET available = available + 1, borrowed = borrowed - 1 
                        WHERE isbn = ?
                    ");
                    $updateBook->execute([$isbnResult['isbn']]);
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
            $stmt = $this->pdo->prepare("
                SELECT * FROM transactions 
                WHERE userId = ? AND isbn = ? AND returnDate IS NULL
                LIMIT 1
            ");

            $stmt->execute([$userId, $isbn]);

            return $stmt->fetch();
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
                $stmt = $this->pdo->prepare("
                    SELECT t.*, b.bookName, b.authorName, b.isbn
                    FROM transactions t
                    JOIN books b ON t.isbn = b.isbn
                    WHERE t.borrowDate BETWEEN ? AND ?
                    AND t.userId = ?
                    ORDER BY t.borrowDate DESC
                ");
                $stmt->execute([$startDate, $endDate, $userId]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT t.*, b.bookName, b.authorName, b.isbn
                    FROM transactions t
                    JOIN books b ON t.isbn = b.isbn
                    WHERE t.borrowDate BETWEEN ? AND ?
                    ORDER BY t.borrowDate DESC
                ");
                $stmt->execute([$startDate, $endDate]);
            }

            return $stmt->fetchAll();
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
            $stmt = $this->pdo->query($sql);

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
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

            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error getting overdue transactions: " . $e->getMessage());
            return [];
        }
    }

    public function updateFine($tid, $fineAmount)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE transactions SET fineAmount = ? WHERE tid = ?");
            return $stmt->execute([$fineAmount, $tid]);
        } catch (\Exception $e) {
            error_log("Error updating fine: " . $e->getMessage());
            return false;
        }
    }

    public function payFine($tid, $amount, $paymentMethod = 'cash', $cardDetails = null)
    {
        try {
            $this->pdo->beginTransaction();

            // Validate amount matches transaction fine
            $stmt = $this->pdo->prepare("SELECT fineAmount, fineStatus FROM transactions WHERE tid = ?");
            $stmt->execute([$tid]);
            $result = $stmt->fetch();

            if (!$result) {
                throw new \Exception("Transaction not found");
            }

            if ($result['fineStatus'] === 'paid') {
                throw new \Exception("Fine already paid");
            }

            if ((float) $result['fineAmount'] != (float) $amount) {
                throw new \Exception("Amount mismatch");
            }

            // Update transaction fine status
            $stmt = $this->pdo->prepare("
                UPDATE transactions 
                SET fineStatus = 'paid', 
                    finePaymentDate = CURDATE(), 
                    finePaymentMethod = ?,
                    lastFinePaymentDate = CURDATE() 
                WHERE tid = ?
            ");

            $success = $stmt->execute([$paymentMethod, $tid]);

            if (!$success) {
                throw new \Exception("Failed to update transaction");
            }

            // Record payment in payments table if it exists
            $tableCheck = $this->pdo->query("SHOW TABLES LIKE 'payments'");
            if ($tableCheck->rowCount() > 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO payments (transactionId, amount, paymentMethod, paymentDate, status)
                    VALUES (?, ?, ?, CURDATE(), 'completed')
                ");
                $stmt->execute([$tid, $amount, $paymentMethod]);
            }

            $this->pdo->commit();
            return true;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
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

            $stmt = $this->pdo->query($sql);
            return $stmt->fetch();
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

    /**
     * Get total transactions count
     */
    public function getTotalTransactionsCount($startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count 
                    FROM transactions 
                    WHERE borrowDate BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM transactions");
                $stmt->execute();
            }

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
            }

            return 0;
        } catch (\Exception $e) {
            error_log("Error getting total transactions count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total fines amount
     */
    public function getTotalFinesAmount($startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->pdo->prepare("
                    SELECT COALESCE(SUM(fineAmount), 0) as total
                    FROM transactions 
                    WHERE fineAmount > 0 
                    AND borrowDate BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT COALESCE(SUM(fineAmount), 0) as total
                    FROM transactions 
                    WHERE fineAmount > 0
                ");
                $stmt->execute();
            }

            if ($row = $stmt->fetch()) {
                return (float) $row['total'];
            }

            return 0.0;
        } catch (\Exception $e) {
            error_log("Error getting total fines amount: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get collected fines amount
     */
    public function getCollectedFinesAmount($startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->pdo->prepare("
                    SELECT COALESCE(SUM(fineAmount), 0) as total
                    FROM transactions 
                    WHERE fineStatus = 'paid' 
                    AND finePaymentDate BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT COALESCE(SUM(fineAmount), 0) as total
                    FROM transactions 
                    WHERE fineStatus = 'paid'
                ");
                $stmt->execute();
            }

            if ($row = $stmt->fetch()) {
                return (float) $row['total'];
            }

            return 0.0;
        } catch (\Exception $e) {
            error_log("Error getting collected fines amount: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get pending fines amount
     */
    public function getPendingFinesAmount($startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->pdo->prepare("
                    SELECT COALESCE(SUM(fineAmount), 0) as total
                    FROM transactions 
                    WHERE (fineStatus = 'pending' OR fineStatus IS NULL)
                    AND fineAmount > 0
                    AND borrowDate BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT COALESCE(SUM(fineAmount), 0) as total
                    FROM transactions 
                    WHERE (fineStatus = 'pending' OR fineStatus IS NULL)
                    AND fineAmount > 0
                ");
                $stmt->execute();
            }

            if ($row = $stmt->fetch()) {
                return (float) $row['total'];
            }

            return 0.0;
        } catch (\Exception $e) {
            error_log("Error getting pending fines amount: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get overdue books count
     */
    public function getOverdueBooksCount($startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM transactions 
                    WHERE returnDate IS NULL 
                    AND DATE_ADD(borrowDate, INTERVAL 14 DAY) < CURDATE()
                    AND borrowDate BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM transactions 
                    WHERE returnDate IS NULL 
                    AND DATE_ADD(borrowDate, INTERVAL 14 DAY) < CURDATE()
                ");
                $stmt->execute();
            }

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
            }

            return 0;
        } catch (\Exception $e) {
            error_log("Error getting overdue books count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get returned books count
     */
    public function getReturnedBooksCount($startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM transactions 
                    WHERE returnDate IS NOT NULL
                    AND returnDate BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM transactions 
                    WHERE returnDate IS NOT NULL
                ");
                $stmt->execute();
            }

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
            }

            return 0;
        } catch (\Exception $e) {
            error_log("Error getting returned books count: " . $e->getMessage());
            return 0;
        }
    }
}