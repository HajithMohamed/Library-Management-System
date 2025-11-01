<?php

namespace App\Models;

class BorrowRecord extends BaseModel
{
    protected $table = 'transactions';

    public function getActiveBorrowCount($userId)
    {
        // FIXED: Updated to use books_borrowed table columns
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE userid = ? AND returnDate IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    public function getOverdueCount($userId)
    {
        // FIXED: Updated to use dueDate column from books_borrowed
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE userid = ? AND returnDate IS NULL 
                AND dueDate < CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    public function getTotalFines($userId)
    {
        // FIXED: books_borrowed doesn't have fines - calculate based on overdue days
        $sql = "SELECT 
                    SUM(GREATEST(0, DATEDIFF(CURDATE(), dueDate)) * 1) as total 
                FROM {$this->table} 
                WHERE userid = ? AND returnDate IS NULL AND dueDate < CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }

    public function getRecentActivity($userId, $limit = 5)
    {
        // FIXED: Updated to use books_borrowed table
        $sql = "SELECT t.*, b.bookName as title, b.authorName as author 
                FROM {$this->table} t
                LEFT JOIN books b ON t.isbn = b.isbn
                WHERE t.userid = ?
                ORDER BY t.borrowDate DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $userId, $limit);
        $stmt->execute();
        
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Map to expected format
        $activity = [];
        foreach ($results as $row) {
            $activity[] = [
                'borrow_date' => $row['borrowDate'],
                'return_date' => $row['returnDate'],
                'due_date' => $row['dueDate'],
                'title' => $row['title'],
                'author' => $row['author']
            ];
        }
        
        return $activity;
    }

    public function getUserFines($userId)
    {
        // FIXED: Calculate fines based on overdue books
        $sql = "SELECT 
                    t.id,
                    t.userid,
                    t.isbn,
                    t.borrowDate,
                    t.dueDate,
                    t.returnDate,
                    t.status,
                    b.bookName as title,
                    b.authorName as author,
                    GREATEST(0, DATEDIFF(CURDATE(), t.dueDate)) as overdue_days,
                    GREATEST(0, DATEDIFF(CURDATE(), t.dueDate)) * 1 as fineAmount
                FROM {$this->table} t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userid = ? 
                AND t.returnDate IS NULL 
                AND t.dueDate < CURDATE()
                ORDER BY t.borrowDate DESC";
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param('s', $userId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Get result failed: " . $stmt->error);
                return [];
            }
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getUserFines: " . $e->getMessage());
            return [];
        }
    }

    public function payFine($borrowId, $amount, $paymentMethod = 'online')
    {
        // FIXED: Since books_borrowed doesn't have payment fields, just update status
        $sql = "UPDATE {$this->table} 
                SET notes = CONCAT(COALESCE(notes, ''), ' | Fine paid: $', ?, ' on ', CURDATE())
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ds', $amount, $borrowId);
        return $stmt->execute();
    }

    public function createReservation($userId, $isbn)
    {
        $sql = "INSERT INTO book_reservations (userId, isbn, reservationStatus, expiryDate, createdAt) 
                VALUES (?, ?, 'Active', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $userId, $isbn);
        return $stmt->execute();
    }

    public function getActiveBorrows($userId)
    {
        // FIXED: Updated to use books_borrowed table
        $sql = "SELECT 
                    t.id,
                    t.userid,
                    t.isbn,
                    t.borrowDate,
                    t.dueDate,
                    t.returnDate,
                    t.status,
                    b.bookName as title,
                    b.authorName as author,
                    CASE 
                        WHEN CURDATE() > t.dueDate THEN 'Overdue'
                        ELSE 'Active'
                    END as borrow_status
                FROM {$this->table} t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userid = ? AND t.returnDate IS NULL
                ORDER BY t.borrowDate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function returnBook($borrowId)
    {
        // FIXED: Updated to use books_borrowed table
        $sql = "UPDATE {$this->table} 
                SET returnDate = CURDATE(),
                    status = 'Returned'
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $borrowId);
        return $stmt->execute();
    }

    public function getBorrowHistory($userId = null)
    {
        // FIXED: Updated to use books_borrowed table with correct columns
        $sql = "SELECT 
                    t.id as tid,
                    t.userid as userId,
                    t.isbn,
                    t.borrowDate,
                    t.dueDate,
                    t.returnDate,
                    t.status,
                    t.notes,
                    t.addedBy,
                    COALESCE(b.bookName, CONCAT('Book (ISBN: ', t.isbn, ')')) as bookName,
                    COALESCE(b.authorName, 'Unknown Author') as authorName,
                    CASE 
                        WHEN t.returnDate IS NOT NULL THEN 'Returned'
                        WHEN CURDATE() > t.dueDate THEN 'Overdue'
                        ELSE 'Active'
                    END as borrow_status,
                    GREATEST(0, DATEDIFF(CURDATE(), t.dueDate)) as overdue_days,
                    GREATEST(0, DATEDIFF(CURDATE(), t.dueDate)) * 1 as fineAmount,
                    CASE 
                        WHEN t.returnDate IS NOT NULL THEN 'paid'
                        WHEN CURDATE() > t.dueDate THEN 'pending'
                        ELSE NULL
                    END as fineStatus
                FROM {$this->table} t
                LEFT JOIN books b ON t.isbn = b.isbn
                WHERE t.userid = ?
                ORDER BY t.borrowDate DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param('s', $userId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Get result failed: " . $stmt->error);
                return [];
            }
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getBorrowHistory: " . $e->getMessage());
            return [];
        }
    }
}
