<?php

namespace App\Models;

class BookRequest
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function createRequest($userId, $bookTitle, $author, $isbn, $reason)
    {
        $sql = "INSERT INTO book_requests (user_id, book_title, author, isbn, reason) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('issss', $userId, $bookTitle, $author, $isbn, $reason);
        return $stmt->execute();
    }

    public function getRequestsByUserId($userId)
    {
        $sql = "SELECT * FROM book_requests WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}