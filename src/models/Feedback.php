<?php

namespace App\Models;

class Feedback
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function createFeedback($userId, $subject, $message)
    {
        $sql = "INSERT INTO feedback (userId, subject, message) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $userId, $subject, $message);
        return $stmt->execute();
    }

    public function getFeedbackByUserId($userId)
    {
        $sql = "SELECT * FROM feedback WHERE userId = ? ORDER BY createdAt DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}