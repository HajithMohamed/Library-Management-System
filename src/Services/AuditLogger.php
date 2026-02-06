<?php

namespace App\Services;

use App\Core\Database;

class AuditLogger
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Log a security event
     *
     * @param string $userId User ID (if authenticated)
     * @param string $action Action name (e.g., 'login_success', 'password_change')
     * @param array $details Additional details
     * @return bool
     */
    public function log($userId, $action, $details = [])
    {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $detailsJson = json_encode($details);

            $stmt = $this->pdo->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            return $stmt->execute([
                $userId,
                $action,
                $detailsJson,
                $ipAddress,
                $userAgent
            ]);
        } catch (\Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log a failed login attempt
     */
    public function logLoginAttempt($username, $isSuccessful = false)
    {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $stmt = $this->pdo->prepare("
                INSERT INTO login_attempts (ip_address, username, attempt_time, is_successful)
                VALUES (?, ?, NOW(), ?)
            ");

            return $stmt->execute([
                $ipAddress,
                $username,
                $isSuccessful ? 1 : 0
            ]);
        } catch (\Exception $e) {
            error_log("Login attempt log error: " . $e->getMessage());
            return false;
        }
    }
}
