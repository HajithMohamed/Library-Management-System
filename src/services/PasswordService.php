<?php

namespace App\Services;

use PDO;
use PDOException;

class PasswordService
{
    private $pdo;
    
    public function __construct($pdo = null)
    {
        // Support test mode
        if (isset($_ENV['TEST_MODE']) && $_ENV['TEST_MODE'] && isset($GLOBALS['test_pdo'])) {
            $this->pdo = $GLOBALS['test_pdo'];
        } elseif ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            // Only load dbConnection if not in test mode
            if (!isset($_ENV['TEST_MODE'])) {
                require_once __DIR__ . '/../config/dbConnection.php';
                $this->pdo = $GLOBALS['pdo'] ?? null;
            }
        }
    }

    /**
     * Hash password using Argon2id
     */
    public function hashPassword($password)
    {
        // Use PHP 7.3+ constant if available, otherwise default
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        return password_hash($password, $algo);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Check current password needs rehash (e.g. if algorithm changed)
     */
    public function needsRehash($hash)
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        return password_needs_rehash($hash, $algo);
    }

    /**
     * Check password strength
     * Returns true if valid, or array of errors
     */
    public function checkStrength($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }
        if (!preg_match('/[\W]/', $password)) { // Non-word character (special char)
            $errors[] = "Password must contain at least one special character.";
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Check if password was used previously (Password History)
     */
    public function isPasswordReused($userId, $password)
    {
        // Check last 5 passwords
        $stmt = $this->pdo->prepare("
            SELECT password_hash FROM password_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            if ($this->verifyPassword($password, $row['password_hash'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Record password history
     */
    public function recordPasswordHistory($userId, $passwordHash)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO password_history (user_id, password_hash, created_at)
                VALUES (?, ?, NOW())
            ");
            return $stmt->execute([$userId, $passwordHash]);
        } catch (\Exception $e) {
            error_log("Error recording password history: " . $e->getMessage());
            return false;
        }
    }
}
