<?php

namespace App\Models;

class Notification
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function createNotification($userId, $message)
    {
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $userId, $message);
        return $stmt->execute();
    }

    public function getNotificationsByUserId($userId)
    {
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function markAsRead($notificationId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $notificationId);
        return $stmt->execute();
    }
}