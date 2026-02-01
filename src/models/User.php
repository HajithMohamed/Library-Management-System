<?php

namespace App\Models;

class User extends BaseModel
{
    protected $table = 'users';
    private $lastError = null;

    public function __construct()
    {
        // Call parent constructor to initialize $db from BaseModel
        parent::__construct();
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE userId = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch();
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
            // Try emailId column first (your schema)
            $sql = "SELECT * FROM {$this->table} WHERE emailId = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);

            if ($row = $stmt->fetch()) {
                return $row;
            }

            // Fallback: try 'email' column
            $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch();
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

            // Build dynamic UPDATE query based on provided data
            if (isset($data['name'])) {
                $fields[] = "name = ?";
                $values[] = $data['name'];
            }
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $values[] = $data['email'];
            }
            if (isset($data['password'])) {
                $fields[] = "password = ?";
                $values[] = $data['password'];
            }
            if (isset($data['department'])) {
                $fields[] = "department = ?";
                $values[] = $data['department'];
            }

            if (empty($fields)) {
                return false;
            }

            $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE userId = ?";
            $values[] = $userId;

            $stmt = $this->db->prepare($query);
            return $stmt->execute($values);
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
            // In SQLite 'SHOW TABLES' doesn't exist, we should use a more portable check or just try-catch
            // For now, let's keep it simple for MySQL but aware of SQLite
            if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'notifications'");
                if ($tableCheck->rowCount() === 0) {
                    return [];
                }
            }

            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE userId = ? ORDER BY createdAt DESC LIMIT 10");
            $stmt->execute([$userId]);

            $notifications = [];
            while ($row = $stmt->fetch()) {
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
            $stmt->execute([$email]);
            $row = $stmt->fetch();

            if ($row) {
                return (int) $row['count'] > 0;
            }

            return false;
        } catch (\Exception $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by username
     */
    public function getUserByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /**
     * Check if username already exists
     */
    public function usernameExists($username, $excludeUserId = null)
    {
        if ($excludeUserId) {
            $stmt = $this->db->prepare("SELECT userId FROM users WHERE username = ? AND userId != ?");
            $stmt->execute([$username, $excludeUserId]);
        } else {
            $stmt = $this->db->prepare("SELECT userId FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }

        return $stmt->rowCount() > 0;
    }

    /**
     * Validate user data for signup
     */
    public function validateUserData($data)
    {
        $errors = [];

        // Validate username
        if (empty($data['username'])) {
            $errors[] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }

        // Validate password
        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        // Validate email
        if (empty($data['emailId'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['emailId'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        // Validate phone number
        if (empty($data['phoneNumber'])) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[0-9]{10}$/', $data['phoneNumber'])) {
            $errors[] = 'Phone number must be 10 digits';
        }

        // Validate gender
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required';
        } elseif (!in_array($data['gender'], ['Male', 'Female', 'Other'])) {
            $errors[] = 'Invalid gender selection';
        }

        // Validate date of birth
        if (empty($data['dob'])) {
            $errors[] = 'Date of birth is required';
        } elseif (strtotime($data['dob']) > strtotime('-13 years')) {
            $errors[] = 'You must be at least 13 years old';
        }

        // Validate address
        if (empty($data['address'])) {
            $errors[] = 'Address is required';
        } elseif (strlen($data['address']) < 10) {
            $errors[] = 'Address must be at least 10 characters';
        }

        return $errors;
    }

    /**
     * Create a new user
     */
    public function createUser($data)
    {
        // Generate a unique user ID (e.g., STU2024001, FAC2024001)
        $userId = $this->generateUserId($data['userType']);

        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    userId, username, password, userType, gender, dob, 
                    emailId, phoneNumber, address, isVerified, otp, otpExpiry
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $userId,
                $data['username'],
                $data['password'],
                $data['userType'],
                $data['gender'],
                $data['dob'],
                $data['emailId'],
                $data['phoneNumber'],
                $data['address'],
                $data['isVerified'],
                $data['otp'],
                $data['otpExpiry']
            ]);

            if ($result) {
                // Store the generated user ID for retrieval
                $this->lastGeneratedUserId = $userId;
            }

            return $result;
        } catch (\Exception $e) {
            error_log("Failed to create user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique user ID based on user type
     */
    private function generateUserId($userType)
    {
        $prefix = 'USR';
        switch (strtolower($userType)) {
            case 'student':
                $prefix = 'STU';
                break;
            case 'faculty':
            case 'teacher':
                $prefix = 'FAC';
                break;
            case 'admin':
                $prefix = 'ADM';
                break;
        }

        $year = date('Y');

        // Get the last user ID with this prefix
        $pattern = $prefix . $year . '%';
        $stmt = $this->db->prepare("SELECT userId FROM users WHERE userId LIKE ? ORDER BY userId DESC LIMIT 1");
        $stmt->execute([$pattern]);

        if ($row = $stmt->fetch()) {
            // Extract the number and increment
            $lastId = $row['userId'];
            $number = (int) substr($lastId, -3);
            $newNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // First user of this type this year
            $newNumber = '001';
        }

        return $prefix . $year . $newNumber;
    }

    /**
     * Get the last generated user ID
     */
    private $lastGeneratedUserId = null;

    public function getLastGeneratedUserId()
    {
        return $this->lastGeneratedUserId;
    }

    /**
     * Verify user with OTP
     */
    public function verifyUser($userId, $otp)
    {
        try {
            // Get user's OTP and expiry
            $stmt = $this->db->prepare("SELECT otp, otpExpiry FROM users WHERE userId = ?");
            $stmt->execute([$userId]);

            if ($row = $stmt->fetch()) {
                // Check if OTP matches and is not expired
                if ($row['otp'] === $otp && strtotime($row['otpExpiry']) > time()) {
                    // Update user as verified and clear OTP
                    $updateStmt = $this->db->prepare("UPDATE users SET isVerified = 1, otp = NULL, otpExpiry = NULL WHERE userId = ?");
                    return $updateStmt->execute([$userId]);
                } else {
                    error_log("OTP mismatch or expired for user: " . $userId);
                    return false;
                }
            }

            error_log("User not found: " . $userId);
            return false;
        } catch (\Exception $e) {
            error_log("Error verifying user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers($userType = null, $limit = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];

            if ($userType) {
                $sql .= " WHERE userType = ?";
                $params[] = $userType;
            }

            $sql .= " ORDER BY userId DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = (int) $limit;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        try {
            $stats = [
                'total' => 0,
                'byType' => [],
                'verified' => 0,
                'unverified' => 0
            ];

            // Total users
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            if ($row = $stmt->fetch()) {
                $stats['total'] = (int) $row['total'];
            }

            // Users by type
            $stmt = $this->db->query("SELECT userType, COUNT(*) as count FROM {$this->table} GROUP BY userType");
            while ($row = $stmt->fetch()) {
                $stats['byType'][$row['userType']] = (int) $row['count'];
            }

            // Verified vs unverified
            $stmt = $this->db->query("SELECT COUNT(*) as verified FROM {$this->table} WHERE isVerified = 1");
            if ($row = $stmt->fetch()) {
                $stats['verified'] = (int) $row['verified'];
            }

            $stats['unverified'] = $stats['total'] - $stats['verified'];

            return $stats;
        } catch (\Exception $e) {
            error_log("Error getting user stats: " . $e->getMessage());
            return [
                'total' => 0,
                'byType' => [],
                'verified' => 0,
                'unverified' => 0
            ];
        }
    }

    /**
     * Get total users count
     */
    public function getTotalUsersCount()
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users";
            $stmt = $this->db->query($sql);

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
            }

            return 0;
        } catch (\Exception $e) {
            error_log("Error getting total users count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active users count (users who borrowed books in date range)
     */
    public function getActiveUsersCount($startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(DISTINCT userId) as count 
                    FROM transactions 
                    WHERE borrowDate BETWEEN ? AND ?
                ");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->db->query("
                    SELECT COUNT(DISTINCT userId) as count 
                    FROM transactions 
                    WHERE returnDate IS NULL
                ");
            }

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
            }

            return 0;
        } catch (\Exception $e) {
            error_log("Error getting active users count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get users by date range
     */
    public function getUsersByDateRange($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE createdAt BETWEEN ? AND ?
                ORDER BY createdAt DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error getting users by date range: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete user by ID
     */
    public function deleteUser($userId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE userId = ?");
            return $stmt->execute([$userId]);
        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($userId, $data)
    {
        $fields = [];
        $values = [];

        // Only update fields that exist in users table
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $values[] = $data['username'];
        }

        if (isset($data['emailId'])) {
            $fields[] = "emailId = ?";
            $values[] = $data['emailId'];
        }

        if (isset($data['gender'])) {
            $fields[] = "gender = ?";
            $values[] = $data['gender'];
        }

        if (isset($data['dob'])) {
            $fields[] = "dob = ?";
            $values[] = $data['dob'];
        }

        if (isset($data['phoneNumber'])) {
            $fields[] = "phoneNumber = ?";
            $values[] = $data['phoneNumber'];
        }

        if (isset($data['address'])) {
            $fields[] = "address = ?";
            $values[] = $data['address'];
        }

        // Handle profile image update
        if (isset($data['profileImage'])) {
            $fields[] = "profileImage = ?";
            $values[] = $data['profileImage'];
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $userId;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Verify current password
        $sql = "SELECT password FROM {$this->table} WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        if (!$result || !password_verify($currentPassword, $result['password'])) {
            $this->lastError = 'Incorrect current password.';
            return false;
        }

        // Update password (remove updatedAt reference)
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password = ? WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$hashedPassword, $userId]);
    }

    /**
     * Find user by ID (using userId column)
     */
    public function findById($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE userId = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Update user OTP for password reset
     */
    public function updateUserOtp($userId, $otp, $otpExpiry)
    {
        try {
            $sql = "UPDATE {$this->table} SET otp = ?, otpExpiry = ? WHERE userId = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$otp, $otpExpiry, $userId]);
        } catch (\Exception $e) {
            error_log("Error updating user OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     */
    public function updateUserPassword($userId, $hashedPassword)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET password = ?, otp = NULL, otpExpiry = NULL 
                    WHERE userId = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (\Exception $e) {
            error_log("Error updating user password: " . $e->getMessage());
            return false;
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}
?>