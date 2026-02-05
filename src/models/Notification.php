<?php

namespace App\Models;

class Notification extends BaseModel
{
    public function createNotification($userId, $message)
    {
        $sql = "INSERT INTO notifications (userId, message) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $message]);
    }

    public function getNotificationsByUserId($userId)
    {
        $sql = "SELECT * FROM notifications WHERE userId = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markAsRead($notificationId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$notificationId]);
    }

    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE userId = ? AND isRead = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row['count'] ?? 0;
    }
}