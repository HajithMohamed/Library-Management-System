<?php

namespace App\Models;

class BorrowRecord extends BaseModel
{
    protected $table = 'transactions';

    public function getActiveBorrowCount($userId)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE userId = ? AND returnDate IS NULL";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare statement in getActiveBorrowCount: " . $this->db->error);
                return 0;
            }
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Failed to get result in getActiveBorrowCount: " . $this->db->error);
                $stmt->close();
                return 0;
            }
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data['count'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error in getActiveBorrowCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getOverdueCount($userId)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE userId = ? AND returnDate IS NULL 
                    AND borrowDate < DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare statement in getOverdueCount: " . $this->db->error);
                return 0;
            }
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Failed to get result in getOverdueCount: " . $this->db->error);
                $stmt->close();
                return 0;
            }
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data['count'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error in getOverdueCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalFines($userId)
    {
        try {
            $sql = "SELECT SUM(fineAmount) as total FROM {$this->table} 
                    WHERE userId = ? AND fineAmount > 0 AND fineStatus = 'pending'";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare statement in getTotalFines: " . $this->db->error);
                return 0;
            }
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Failed to get result in getTotalFines: " . $this->db->error);
                $stmt->close();
                return 0;
            }
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data['total'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error in getTotalFines: " . $e->getMessage());
            return 0;
        }
    }

    public function getRecentActivity($userId, $limit = 5)
    {
        try {
            $sql = "SELECT t.*, b.bookName as title, b.authorName as author 
                    FROM {$this->table} t
                    JOIN books b ON t.isbn = b.isbn
                    WHERE t.userId = ?
                    ORDER BY t.borrowDate DESC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare statement in getRecentActivity: " . $this->db->error);
                return [];
            }
            $stmt->bind_param('si', $userId, $limit);
            $stmt->execute();
            
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Failed to get result in getRecentActivity: " . $this->db->error);
                $stmt->close();
                return [];
            }
            
            $results = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Map to expected format
            $activity = [];
            foreach ($results as $row) {
                $activity[] = [
                    'borrow_date' => $row['borrowDate'] ?? null,
                    'return_date' => $row['returnDate'] ?? null,
                    'due_date' => !empty($row['borrowDate']) ? date('Y-m-d', strtotime($row['borrowDate'] . ' + 14 days')) : null,
                    'title' => $row['title'] ?? 'Unknown',
                    'author' => $row['author'] ?? 'Unknown'
                ];
            }
            
            return $activity;
        } catch (\Exception $e) {
            error_log("Error in getRecentActivity: " . $e->getMessage());
            return [];
        }
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