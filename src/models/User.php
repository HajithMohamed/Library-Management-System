<?php

namespace App\Models;

class User
{
    private $db;      // Database connection property
    private $conn;    // Alternative property name
    
    public function __construct()
    {
        // Initialize database connection
        global $mysqli;
        global $conn;
        
        // Support both $mysqli and $conn naming conventions
        if (isset($mysqli)) {
            $this->db = $mysqli;
            $this->conn = $mysqli;
        } elseif (isset($conn)) {
            $this->db = $conn;
            $this->conn = $conn;
        } else {
            throw new \Exception("Database connection not available");
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE userId = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user
     */
    public function updateUser($userId, $data)
    {
        try {
            $fields = [];
            $values = [];
            $types = "";
            
            // Build dynamic UPDATE query based on provided data
            if (isset($data['name'])) {
                $fields[] = "name = ?";
                $values[] = $data['name'];
                $types .= "s";
            }
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $values[] = $data['email'];
                $types .= "s";
            }
            if (isset($data['password'])) {
                $fields[] = "password = ?";
                $values[] = $data['password'];
                $types .= "s";
            }
            if (isset($data['department'])) {
                $fields[] = "department = ?";
                $values[] = $data['department'];
                $types .= "s";
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE userId = ?";
            $values[] = $userId;
            $types .= "s";
            
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param($types, ...$values);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notifications for a user
     */
    public function getNotifications($userId)
    {
        try {
            // Check if table exists first
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'notifications'");
            if ($tableCheck->num_rows === 0) {
                return [];
            }
            
            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE userId = ? ORDER BY createdAt DESC LIMIT 10");
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = [
                    'id' => $row['id'] ?? $row['notificationId'] ?? null,
                    'message' => $row['message'] ?? $row['content'] ?? '',
                    'content' => $row['message'] ?? $row['content'] ?? '',
                    'createdAt' => $row['createdAt'] ?? $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'created_at' => $row['createdAt'] ?? $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'isRead' => $row['isRead'] ?? $row['is_read'] ?? 0
                ];
            }
            
            return $notifications;
        } catch (\Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user exists by email
     */
    public function emailExists($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['count'] > 0;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            return false;
        }
    }
}
?>
