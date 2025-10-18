<?php

namespace App\Models;

class User
{
    private $conn;

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
     * Create a new user
     */
    public function createUser($data)
    {
        $sql = "INSERT INTO users (userId, password, userType, gender, dob, emailId, phoneNumber, address, isVerified, otp, otpExpiry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssssssssiss', 
            $data['userId'], 
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
        
        return $stmt->execute();
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
     * Authenticate user
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
     * Validate user data
     */
    public function validateUserData($data, $isUpdate = false)
    {
        $errors = [];
        
        if (!$isUpdate) {
            if (empty($data['userId'])) {
                $errors[] = 'User ID is required';
            } elseif (strlen($data['userId']) < 3) {
                $errors[] = 'User ID must be at least 3 characters';
            }
            
            if (empty($data['password'])) {
                $errors[] = 'Password is required';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
        }
        
        if (empty($data['userType'])) {
            $errors[] = 'User type is required';
        } elseif (!in_array($data['userType'], ['Student', 'Faculty', 'Admin'])) {
            $errors[] = 'Invalid user type';
        }
        
        if (empty($data['emailId'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['emailId'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($data['phoneNumber'])) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^\d{10}$/', $data['phoneNumber'])) {
            $errors[] = 'Phone number must be 10 digits';
        }
        
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required';
        } elseif (!in_array($data['gender'], ['Male', 'Female', 'Other'])) {
            $errors[] = 'Invalid gender';
        }
        
        if (empty($data['dob'])) {
            $errors[] = 'Date of birth is required';
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
