<?php

namespace App\Models;

class BookReservation
{
    private $db;
    
    public function __construct()
    {
        global $mysqli;
        $this->db = $mysqli;
    }
    
    /**
     * Get reservations for a user
     */
    public function getReservationsByUser($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, b.bookName, b.authorName 
                FROM book_reservations r
                JOIN books b ON r.isbn = b.isbn
                WHERE r.userId = ? AND r.reservationStatus = 'Active'
                ORDER BY r.createdAt DESC
            ");
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reservations = [];
            while ($row = $result->fetch_assoc()) {
                $reservations[] = $row;
            }
            
            return $reservations;
        } catch (\Exception $e) {
            error_log("Error getting reservations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user has active reservation for a book
     */
    public function getActiveReservationByUserAndBook($userId, $isbn)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM book_reservations 
                WHERE userId = ? AND isbn = ? AND reservationStatus = 'Active'
                LIMIT 1
            ");
            
            $stmt->bind_param("ss", $userId, $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error checking active reservation: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new reservation
     */
    public function createReservation($userId, $isbn)
    {
        try {
            $expiryDate = date('Y-m-d', strtotime('+7 days'));
            
            $stmt = $this->db->prepare("
                INSERT INTO book_reservations (userId, isbn, reservationStatus, expiryDate, createdAt)
                VALUES (?, ?, 'Active', ?, NOW())
            ");
            
            $stmt->bind_param("sss", $userId, $isbn, $expiryDate);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error creating reservation: " . $e->getMessage());
            return false;
        }
    }
}