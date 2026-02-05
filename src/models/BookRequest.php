<?php

namespace App\Models;

class BookRequest extends BaseModel
{
    public function createRequest($userId, $bookTitle, $author, $isbn, $reason)
    {
        $sql = "INSERT INTO book_requests (user_id, book_title, author, isbn, reason) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $bookTitle, $author, $isbn, $reason]);
    }

    public function getRequestsByUserId($userId)
    {
        $sql = "SELECT * FROM book_requests WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}