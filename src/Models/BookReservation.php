<?php

namespace App\Models;

class BookReservation extends BaseModel
{
    /**
     * Get reservations by user
     */
    public function getReservationsByUser($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT br.*, b.bookName, b.authorName, b.bookImage
                FROM book_reservations br
                JOIN books b ON br.isbn = b.isbn
                WHERE br.userId = ? AND br.reservationStatus = 'Active'
                ORDER BY br.createdAt DESC
            ");

            $stmt->execute([$userId]);
            return $stmt->fetchAll();
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
            $stmt = $this->pdo->prepare("
                INSERT INTO book_reservations (userId, isbn, reservationStatus, expiryDate, createdAt)
                VALUES (?, ?, 'Active', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW())
            ");

            return $stmt->execute([$userId, $isbn]);
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
            $stmt = $this->pdo->prepare("
                SELECT * FROM book_reservations 
                WHERE userId = ? AND isbn = ? AND reservationStatus = 'Active'
                LIMIT 1
            ");

            $stmt->execute([$userId, $isbn]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Error checking reservation: " . $e->getMessage());
            return null;
        }
    }
}