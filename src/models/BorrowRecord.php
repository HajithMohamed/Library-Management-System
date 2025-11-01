<?php

namespace App\Models;

class BorrowRecord extends BaseModel
{
    protected $table = 'transactions';

    public function getActiveBorrowCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE userId = ? AND returnDate IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    public function getOverdueCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE userId = ? AND returnDate IS NULL 
                AND borrowDate < DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    public function getTotalFines($userId)
    {
        $sql = "SELECT SUM(fineAmount) as total FROM {$this->table} 
                WHERE userId = ? AND fineAmount > 0 AND fineStatus = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }

    public function getRecentActivity($userId, $limit = 5)
    {
        $sql = "SELECT t.*, b.bookName as title, b.authorName as author 
                FROM {$this->table} t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ?
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
                'due_date' => date('Y-m-d', strtotime($row['borrowDate'] . ' + 14 days')),
                'title' => $row['title'],
                'author' => $row['author']
            ];
        }
        
        return $activity;
    }

    public function getUserFines($userId)
    {
        $sql = "SELECT t.*, b.bookName as title, b.authorName as author 
                FROM {$this->table} t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? AND t.fineAmount > 0 AND t.fineStatus = 'pending'
                ORDER BY t.borrowDate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function payFine($borrowId, $amount)
    {
        $sql = "UPDATE {$this->table} 
                SET fineStatus = 'paid', finePaymentDate = CURDATE(), finePaymentMethod = 'online' 
                WHERE tid = ? AND fineAmount = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sd', $borrowId, $amount);
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
        $sql = "SELECT t.*, b.bookName as title, b.authorName as author, b.isbn 
                FROM {$this->table} t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ? AND t.returnDate IS NULL
                ORDER BY t.borrowDate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function returnBook($borrowId)
    {
        $sql = "UPDATE {$this->table} 
                SET returnDate = CURDATE() 
                WHERE tid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $borrowId);
        return $stmt->execute();
    }

    public function getBorrowHistory($userId = null)
    {
        // Use correct aliases for bookName and authorName
        $sql = "SELECT t.*, b.bookName, b.authorName, b.isbn 
                FROM {$this->table} t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.userId = ?
                ORDER BY t.borrowDate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($results as &$row) {
            if (empty($row['dueDate']) && !empty($row['borrowDate'])) {
                $row['dueDate'] = date('Y-m-d', strtotime($row['borrowDate'] . ' + 14 days'));
            }
        }
        return $results;
    }
}
