<?php

namespace App\Models;

class BorrowRecord extends BaseModel
{
    protected $table = 'books_borrowed';

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
                    AND dueDate < CURDATE()";
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
            $sql = "SELECT bb.*, b.bookName as title, b.authorName as author 
                    FROM {$this->table} bb
                    JOIN books b ON bb.isbn = b.isbn
                    WHERE bb.userId = ?
                    ORDER BY bb.borrowDate DESC
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
                    'due_date' => $row['dueDate'] ?? null,
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
        // Note: Assuming fines are tracked in books_borrowed or separate table
        // Adjust if needed based on your schema
        $sql = "SELECT bb.*, b.bookName as title, b.authorName as author 
                FROM {$this->table} bb
                JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userId = ? AND bb.status = 'Overdue'
                ORDER BY bb.borrowDate DESC";
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
        $sql = "SELECT bb.*, b.bookName as title, b.authorName as author, b.isbn 
                FROM {$this->table} bb
                JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userId = ? AND bb.returnDate IS NULL
                ORDER BY bb.borrowDate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function returnBook($borrowId)
    {
        $sql = "UPDATE {$this->table} 
                SET returnDate = CURDATE(), status = 'Returned' 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $borrowId);
        return $stmt->execute();
    }

    public function getBorrowHistory($userId = null)
    {
        $sql = "SELECT bb.*, b.bookName, b.authorName, b.isbn 
                FROM {$this->table} bb
                JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userId = ?
                ORDER BY bb.borrowDate DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    public function getAllBorrowedBooks($userId = null)
    {
        if ($userId) {
            $sql = "SELECT bb.*, b.bookName, b.authorName, b.isbn, b.category,
                    u.username, u.emailId, u.userType
                    FROM {$this->table} bb
                    JOIN books b ON bb.isbn = b.isbn
                    JOIN users u ON bb.userId = u.userId
                    WHERE bb.userId = ? AND bb.returnDate IS NULL
                    ORDER BY bb.borrowDate DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $userId);
        } else {
            $sql = "SELECT bb.*, b.bookName, b.authorName, b.isbn, b.category,
                    u.username, u.emailId, u.userType
                    FROM {$this->table} bb
                    JOIN books b ON bb.isbn = b.isbn
                    JOIN users u ON bb.userId = u.userId
                    WHERE bb.returnDate IS NULL
                    ORDER BY bb.borrowDate DESC";
            $stmt = $this->db->prepare($sql);
        }
        
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Calculate if overdue
        foreach ($results as &$row) {
            $row['isOverdue'] = !empty($row['dueDate']) && strtotime($row['dueDate']) < time();
        }
        
        return $results;
    }

    public function getAllBorrowRecords($filters = [])
    {
        $sql = "SELECT bb.*, b.bookName, b.authorName, b.isbn, b.category,
                u.username, u.emailId, u.userType
                FROM {$this->table} bb
                JOIN books b ON bb.isbn = b.isbn
                JOIN users u ON bb.userId = u.userId
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if (!empty($filters['userId'])) {
            $sql .= " AND bb.userId = ?";
            $params[] = $filters['userId'];
            $types .= 's';
        }
        
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql .= " AND bb.returnDate IS NULL";
            } elseif ($filters['status'] === 'returned') {
                $sql .= " AND bb.returnDate IS NOT NULL";
            } elseif ($filters['status'] === 'overdue') {
                $sql .= " AND bb.returnDate IS NULL AND bb.dueDate < CURDATE()";
            }
        }
        
        $sql .= " ORDER BY bb.borrowDate DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}