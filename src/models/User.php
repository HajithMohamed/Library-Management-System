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
            // Try emailId column first (your schema)
            $sql = "SELECT * FROM {$this->table} WHERE emailId = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return $row;
            }
            
            // Fallback: try 'email' column
            $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }
            
            return null;
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
    
    /**
     * Get user by username
     */
    public function getUserByUsername($username)
    {
        global $mysqli;
        
        if (!$mysqli) {
            return null;
        }
        
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $mysqli->error);
            return null;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        
        return null;
    }
    
    /**
     * Check if username already exists
     */
    public function usernameExists($username, $excludeUserId = null)
    {
        global $mysqli;
        
        if (!$mysqli) {
            return false;
        }
        
        if ($excludeUserId) {
            $stmt = $mysqli->prepare("SELECT userId FROM users WHERE username = ? AND userId != ?");
            $stmt->bind_param("ss", $username, $excludeUserId);
        } else {
            $stmt = $mysqli->prepare("SELECT userId FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
        }
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $mysqli->error);
            return false;
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
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
        global $mysqli;
        
        if (!$mysqli) {
            error_log("Database connection not available");
            return false;
        }
        
        // Generate a unique user ID (e.g., STU2024001, FAC2024001)
        $userId = $this->generateUserId($data['userType']);
        
        // Get privilege defaults based on user type
        $privileges = self::getPrivilegeDefaults($data['userType']);
        $maxBorrowLimit = $privileges['max_borrow_limit'];
        $borrowPeriodDays = $privileges['borrow_period_days'];
        $maxRenewals = $privileges['max_renewals'];
        
        $stmt = $mysqli->prepare("
            INSERT INTO users (
                userId, username, password, userType, gender, dob, 
                emailId, phoneNumber, address, isVerified, otp, otpExpiry,
                max_borrow_limit, borrow_period_days, max_renewals
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $mysqli->error);
            return false;
        }
        
        $stmt->bind_param(
            "ssssssssssssiis",
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
            $data['otpExpiry'],
            $maxBorrowLimit,
            $borrowPeriodDays,
            $maxRenewals
        );
        
        $result = $stmt->execute();
        
        if ($result) {
            // Store the generated user ID for retrieval
            $this->lastGeneratedUserId = $userId;
        } else {
            error_log("Failed to create user: " . $stmt->error);
        }
        
        return $result;
    }
    
    /**
     * Generate a unique user ID based on user type
     */
    private function generateUserId($userType)
    {
        global $mysqli;
        
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
        $stmt = $mysqli->prepare("SELECT userId FROM users WHERE userId LIKE ? ORDER BY userId DESC LIMIT 1");
        $pattern = $prefix . $year . '%';
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Extract the number and increment
            $lastId = $row['userId'];
            $number = (int)substr($lastId, -3);
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
        global $mysqli;
        
        if (!$mysqli) {
            error_log("Database connection not available");
            return false;
        }
        
        // Get user's OTP and expiry
        $stmt = $mysqli->prepare("SELECT otp, otpExpiry FROM users WHERE userId = ?");
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $mysqli->error);
            return false;
        }
        
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Check if OTP matches and is not expired
            if ($row['otp'] === $otp && strtotime($row['otpExpiry']) > time()) {
                // Update user as verified and clear OTP
                $updateStmt = $mysqli->prepare("UPDATE users SET isVerified = 1, otp = NULL, otpExpiry = NULL WHERE userId = ?");
                
                if (!$updateStmt) {
                    error_log("Failed to prepare update statement: " . $mysqli->error);
                    return false;
                }
                
                $updateStmt->bind_param("s", $userId);
                return $updateStmt->execute();
            } else {
                error_log("OTP mismatch or expired for user: " . $userId);
                return false;
            }
        }
        
        error_log("User not found: " . $userId);
        return false;
    }

    /**
     * Get all users
     */
    public function getAllUsers($userType = null, $limit = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if ($userType) {
                $sql .= " WHERE userType = ?";
            }
            
            $sql .= " ORDER BY userId DESC";
            
            if ($limit) {
                $sql .= " LIMIT ?";
            }
            
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            if ($userType && $limit) {
                $stmt->bind_param("si", $userType, $limit);
            } elseif ($userType) {
                $stmt->bind_param("s", $userType);
            } elseif ($limit) {
                $stmt->bind_param("i", $limit);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
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
            $result = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            if ($row = $result->fetch_assoc()) {
                $stats['total'] = (int)$row['total'];
            }
            
            // Users by type
            $result = $this->db->query("SELECT userType, COUNT(*) as count FROM {$this->table} GROUP BY userType");
            while ($row = $result->fetch_assoc()) {
                $stats['byType'][$row['userType']] = (int)$row['count'];
            }
            
            // Verified vs unverified
            $result = $this->db->query("SELECT COUNT(*) as verified FROM {$this->table} WHERE isVerified = 1");
            if ($row = $result->fetch_assoc()) {
                $stats['verified'] = (int)$row['verified'];
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
            $result = $this->db->query($sql);
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['count'];
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
                    FROM books_borrowed 
                    WHERE borrowDate BETWEEN ? AND ?
                ");
                $stmt->bind_param("ss", $startDate, $endDate);
            } else {
                $stmt = $this->db->prepare("
                    SELECT COUNT(DISTINCT userId) as count 
                    FROM books_borrowed 
                    WHERE status = 'Active'
                ");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['count'];
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
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
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
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("s", $userId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($userId, $data)
    {
        $fields = [];
        $values = [];
        $types = '';

        // Only update fields that exist in users table
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $values[] = $data['username'];
            $types .= 's';
        }

        if (isset($data['emailId'])) {
            $fields[] = "emailId = ?";
            $values[] = $data['emailId'];
            $types .= 's';
        }
        
        if (isset($data['gender'])) {
            $fields[] = "gender = ?";
            $values[] = $data['gender'];
            $types .= 's';
        }

        if (isset($data['dob'])) {
            $fields[] = "dob = ?";
            $values[] = $data['dob'];
            $types .= 's';
        }

        if (isset($data['phoneNumber'])) {
            $fields[] = "phoneNumber = ?";
            $values[] = $data['phoneNumber'];
            $types .= 's';
        }
        
        if (isset($data['address'])) {
            $fields[] = "address = ?";
            $values[] = $data['address'];
            $types .= 's';
        }

        // Handle profile image update
        if (isset($data['profileImage'])) {
            $fields[] = "profileImage = ?";
            $values[] = $data['profileImage'];
            $types .= 's';
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $userId;
        $types .= 's';

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare update statement: " . $this->db->error);
            return false;
        }
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Verify current password
        $sql = "SELECT password FROM {$this->table} WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result || !password_verify($currentPassword, $result['password'])) {
            $this->lastError = 'Incorrect current password.';
            return false;
        }
        
        // Update password (remove updatedAt reference)
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password = ? WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $hashedPassword, $userId);
        
        return $stmt->execute();
    }

    /**
     * Find user by ID (using userId column)
     */
    public function findById($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE userId = ?");
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update user OTP for password reset
     */
    public function updateUserOtp($userId, $otp, $otpExpiry)
    {
        try {
            $sql = "UPDATE {$this->table} SET otp = ?, otpExpiry = ? WHERE userId = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                error_log("Failed to prepare OTP update statement: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param('sss', $otp, $otpExpiry, $userId);
            return $stmt->execute();
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
            
            if (!$stmt) {
                error_log("Failed to prepare password update statement: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param('ss', $hashedPassword, $userId);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating user password: " . $e->getMessage());
            return false;
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    // ====================================================================
    // Privilege Differentiation Methods
    // ====================================================================

    /**
     * Get maximum borrow limit for a user
     */
    public function getMaxBorrowLimit($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT max_borrow_limit FROM users WHERE userId = ?");
            if (!$stmt) {
                return $this->getDefaultBorrowLimit($userId);
            }
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stmt->close();
                return (int)$row['max_borrow_limit'];
            }
            $stmt->close();
            return $this->getDefaultBorrowLimit($userId);
        } catch (\Exception $e) {
            error_log("Error getting max borrow limit: " . $e->getMessage());
            return $this->getDefaultBorrowLimit($userId);
        }
    }

    /**
     * Get borrow period in days for a user
     */
    public function getBorrowPeriodDays($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT borrow_period_days FROM users WHERE userId = ?");
            if (!$stmt) {
                return $this->getDefaultBorrowPeriod($userId);
            }
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stmt->close();
                return (int)$row['borrow_period_days'];
            }
            $stmt->close();
            return $this->getDefaultBorrowPeriod($userId);
        } catch (\Exception $e) {
            error_log("Error getting borrow period: " . $e->getMessage());
            return $this->getDefaultBorrowPeriod($userId);
        }
    }

    /**
     * Get maximum renewals allowed for a user
     */
    public function getMaxRenewals($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT max_renewals FROM users WHERE userId = ?");
            if (!$stmt) {
                return $this->getDefaultMaxRenewals($userId);
            }
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stmt->close();
                return (int)$row['max_renewals'];
            }
            $stmt->close();
            return $this->getDefaultMaxRenewals($userId);
        } catch (\Exception $e) {
            error_log("Error getting max renewals: " . $e->getMessage());
            return $this->getDefaultMaxRenewals($userId);
        }
    }

    /**
     * Get all privilege info for a user in one call
     */
    public function getUserPrivileges($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT userType, max_borrow_limit, borrow_period_days, max_renewals FROM users WHERE userId = ?");
            if (!$stmt) {
                return $this->getDefaultPrivileges($userId);
            }
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stmt->close();
                return [
                    'userType' => $row['userType'],
                    'max_borrow_limit' => (int)$row['max_borrow_limit'],
                    'borrow_period_days' => (int)$row['borrow_period_days'],
                    'max_renewals' => (int)$row['max_renewals']
                ];
            }
            $stmt->close();
            return $this->getDefaultPrivileges($userId);
        } catch (\Exception $e) {
            error_log("Error getting user privileges: " . $e->getMessage());
            return $this->getDefaultPrivileges($userId);
        }
    }

    /**
     * Fallback: get default borrow limit based on userType
     */
    private function getDefaultBorrowLimit($userId)
    {
        $user = $this->findById($userId);
        $type = strtolower($user['userType'] ?? 'student');
        switch ($type) {
            case 'admin': case 'librarian': return 999;
            case 'faculty': return 10;
            default: return 3;
        }
    }

    /**
     * Fallback: get default borrow period based on userType
     */
    private function getDefaultBorrowPeriod($userId)
    {
        $user = $this->findById($userId);
        $type = strtolower($user['userType'] ?? 'student');
        switch ($type) {
            case 'admin': case 'librarian': return 365;
            case 'faculty': return 60;
            default: return 14;
        }
    }

    /**
     * Fallback: get default max renewals based on userType
     */
    private function getDefaultMaxRenewals($userId)
    {
        $user = $this->findById($userId);
        $type = strtolower($user['userType'] ?? 'student');
        switch ($type) {
            case 'admin': case 'librarian': return 999;
            case 'faculty': return 2;
            default: return 1;
        }
    }

    /**
     * Fallback: get default privileges based on userType
     */
    private function getDefaultPrivileges($userId)
    {
        $user = $this->findById($userId);
        $type = strtolower($user['userType'] ?? 'student');
        switch ($type) {
            case 'admin': case 'librarian':
                return ['userType' => $user['userType'] ?? 'Admin', 'max_borrow_limit' => 999, 'borrow_period_days' => 365, 'max_renewals' => 999];
            case 'faculty':
                return ['userType' => 'Faculty', 'max_borrow_limit' => 10, 'borrow_period_days' => 60, 'max_renewals' => 2];
            default:
                return ['userType' => 'Student', 'max_borrow_limit' => 3, 'borrow_period_days' => 14, 'max_renewals' => 1];
        }
    }

    /**
     * Set privilege values for a user based on their role
     */
    public static function getPrivilegeDefaults($userType)
    {
        $type = strtolower($userType);
        switch ($type) {
            case 'admin': case 'librarian':
                return ['max_borrow_limit' => 999, 'borrow_period_days' => 365, 'max_renewals' => 999];
            case 'faculty':
                return ['max_borrow_limit' => 10, 'borrow_period_days' => 60, 'max_renewals' => 2];
            default:
                return ['max_borrow_limit' => 3, 'borrow_period_days' => 14, 'max_renewals' => 1];
        }
    }
}
?>
