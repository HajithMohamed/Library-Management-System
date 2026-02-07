<?php
namespace App\Services;

class NotificationService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($userId, $title, $message, $type = 'info') {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO notifications (userId, title, message, type, isRead, createdAt) VALUES (?, ?, ?, ?, 0, NOW())");
            return $stmt->execute([$userId, $title, $message, $type]);
        } catch (\PDOException $e) {
            error_log("Notification creation error: " . $e->getMessage());
            return false;
        }
    }

    public function markAsRead($notificationId, $userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE notifications SET isRead = 1 WHERE id = ? AND userId = ?");
            return $stmt->execute([$notificationId, $userId]);
        } catch (\PDOException $e) {
            error_log("Notification update error: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notifications WHERE userId = ? AND isRead = 0");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Notification count error: " . $e->getMessage());
            return 0;
        }
    }

    // Static email notification methods for book recommendations
    public static function notifyAdmin($message) {
        // Send email to admin(s) when faculty recommends a book
        // Example: Use PHPMailer or mail()
        // mail('admin@example.com', 'New Book Recommendation', $message);
    }

    public static function notifyFaculty($recommendationId, $message) {
        // Lookup faculty email by recommendationId and send email
        // Example: Use PHPMailer or mail()
        // mail($facultyEmail, 'Book Recommendation Update', $message);
    }
}
