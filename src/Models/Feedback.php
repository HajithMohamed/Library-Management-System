<?php

namespace App\Models;

class Feedback extends BaseModel
{
    public function createFeedback($userId, $subject, $message)
    {
        $sql = "INSERT INTO feedback (userId, subject, message) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $subject, $message]);
    }

    public function getFeedbackByUserId($userId)
    {
        $sql = "SELECT * FROM feedback WHERE userId = ? ORDER BY createdAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}