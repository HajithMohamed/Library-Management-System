<?php

namespace App\Models;

class BookReservation
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Get reservations by user
     */
    public function getReservationsByUser($userId)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT br.*, b.bookName, b.authorName, b.bookImage
                FROM book_reservations br
                JOIN books b ON br.isbn = b.isbn
                WHERE br.userId = ? AND br.reservationStatus = 'Active'
                ORDER BY br.createdAt DESC
            ");
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting reservations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new reservation
     */
    public function createReservation($userId, $isbn)
    {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO book_reservations (userId, isbn, reservationStatus, expiryDate, createdAt)
                VALUES (?, ?, 'Active', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW())
            ");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ss", $userId, $isbn);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error creating reservation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active reservation by user and book
     */
    public function getActiveReservationByUserAndBook($userId, $isbn)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM book_reservations 
                WHERE userId = ? AND isbn = ? AND reservationStatus = 'Active'
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
            error_log("Error checking reservation: " . $e->getMessage());
            return null;
        }
    }
}