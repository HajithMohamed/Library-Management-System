<?php

namespace App\Models;

class User
{
    private $conn;
    private $lastGeneratedUserId;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId)
    {
        $sql = "SELECT * FROM users WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE emailId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Generate a unique user ID
     */
    public function generateUserId()
    {
        $prefix = 'STU';
        $year = date('Y');
        
        // Get the next available ID number for this year
        $sql = "SELECT COUNT(*) as count FROM users WHERE userId LIKE ?";
        $pattern = $prefix . $year . '%';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $nextNumber = $row['count'] + 1;
        
        // Format as STU2024001, STU2024002, etc.
        return $prefix . $year . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new user with auto-generated user ID
     */
    public function createUser($data)
    {
        // Generate unique user ID
        $data['userId'] = $this->generateUserId();
        
        $sql = "INSERT INTO users (userId, username, password, userType, gender, dob, emailId, phoneNumber, address, isVerified, otp, otpExpiry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssssssssiss', 
            $data['userId'], 
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
        );
        
        $result = $stmt->execute();
        
        // Store the generated user ID for later retrieval
        if ($result) {
            $this->lastGeneratedUserId = $data['userId'];
        }
        
        return $result;
    }

    /**
     * Get the last generated user ID
     */
    public function getLastGeneratedUserId()
    {
        return $this->lastGeneratedUserId ?? null;
    }

    /**
     * Update user information
     */
    public function updateUser($userId, $data)
    {
        $sql = "UPDATE users SET gender = ?, dob = ?, emailId = ?, phoneNumber = ?, address = ? WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssssss', 
            $data['gender'], 
            $data['dob'], 
            $data['emailId'], 
            $data['phoneNumber'], 
            $data['address'], 
            $userId
        );
        
        return $stmt->execute();
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword)
    {
        $sql = "UPDATE users SET password = ? WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $newPassword, $userId);
        
        return $stmt->execute();
    }

    /**
     * Verify user account
     */
    public function verifyUser($userId, $otp)
    {
        $sql = "UPDATE users SET isVerified = 1, otp = NULL, otpExpiry = NULL WHERE userId = ? AND otp = ? AND otpExpiry > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $userId, $otp);
        $stmt->execute();
        
        return $stmt->affected_rows > 0;
    }

    /**
     * Set OTP for user
     */
    public function setOTP($userId, $otp, $expiry)
    {
        $sql = "UPDATE users SET otp = ?, otpExpiry = ? WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $otp, $expiry, $userId);
        
        return $stmt->execute();
    }

    /**
     * Delete user
     */
    public function deleteUser($userId)
    {
        // Check if user has active transactions
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE userId = ? AND returnDate IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return false; // Cannot delete user with active transactions
        }
        
        $sql = "DELETE FROM users WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $userId);
        
        return $stmt->execute();
    }

    /**
     * Get all users
     */
    public function getAllUsers($limit = 100)
    {
        $sql = "SELECT userId, userType, gender, dob, emailId, phoneNumber, address, isVerified FROM users ORDER BY userId ASC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Search users
     */
    public function searchUsers($search)
    {
        $sql = "SELECT userId, userType, gender, dob, emailId, phoneNumber, address, isVerified 
                FROM users 
                WHERE userId LIKE ? OR emailId LIKE ? OR phoneNumber LIKE ? 
                ORDER BY userId ASC";
        
        $searchTerm = "%{$search}%";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN userType = 'Student' THEN 1 END) as students,
                    COUNT(CASE WHEN userType = 'Faculty' THEN 1 END) as faculty,
                    COUNT(CASE WHEN userType = 'Admin' THEN 1 END) as admins,
                    COUNT(CASE WHEN isVerified = 1 THEN 1 END) as verified_users
                FROM users";
        
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Get user by username
     */
    public function getUserByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Authenticate user by username
     */
    public function authenticateByUsername($username, $password)
    {
        $user = $this->getUserByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        // Check if user is verified
        if (!$user['isVerified']) {
            return false;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Authenticate user by user ID (kept for backward compatibility)
     */
    public function authenticate($userId, $password)
    {
        $user = $this->getUserById($userId);
        
        if (!$user) {
            return false;
        }
        
        // Check if user is verified
        if (!$user['isVerified']) {
            return false;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Validate user data for signup
     */
    public function validateUserData($data, $isUpdate = false)
    {
        $errors = [];
        
        if (!$isUpdate) {
            if (empty($data['username'])) {
                $errors[] = 'Username is required';
            } elseif (strlen($data['username']) < 3) {
                $errors[] = 'Username must be at least 3 characters';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                $errors[] = 'Username can only contain letters, numbers, and underscores';
            }
            
            if (empty($data['password'])) {
                $errors[] = 'Password is required';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
        }
        
        // User type will be automatically set to 'Student' for signup
        // No need to validate userType for signup
        
        if (empty($data['emailId'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['emailId'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($data['phoneNumber'])) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[\+]?[\d\s\-\(\)]{10,15}$/', $data['phoneNumber'])) {
            $errors[] = 'Phone number must be 10-15 digits and may include +, spaces, hyphens, or parentheses';
        }
        
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required';
        } elseif (!in_array($data['gender'], ['Male', 'Female', 'Other'])) {
            $errors[] = 'Invalid gender';
        }
        
        if (empty($data['dob'])) {
            $errors[] = 'Date of birth is required';
        }
        
        if (empty($data['address'])) {
            $errors[] = 'Address is required';
        }
        
        return $errors;
    }

    /**
     * Check if user ID exists
     */
    public function userIdExists($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeUserId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeUserId) {
            $sql .= " AND userId != ?";
            $params[] = $excludeUserId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE emailId = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND userId != ?";
            $params[] = $excludeUserId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
}
?>
