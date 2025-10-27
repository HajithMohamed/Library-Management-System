<?php

namespace App\Models;

use mysqli;

class BookReservation
{
    private $db;

    public function __construct()
    {
        global $mysqli;
        $this->db = $mysqli;
    }

    /**
     * Get reserved books for a user
     */
    public function getReservedBooks($userId)
    {
        $query = "SELECT * FROM book_reservations WHERE userId = ? AND reservationStatus = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}