<?php

namespace App\Services;

use PDO;
use PDOException;
use PHPGangsta_GoogleAuthenticator;

class TwoFactorService
{
    private $pdo;
    private $ga;
    
    public function __construct($pdo = null)
    {
        // Support test mode
        if (isset($_ENV['TEST_MODE']) && $_ENV['TEST_MODE'] && isset($GLOBALS['test_pdo'])) {
            $this->pdo = $GLOBALS['test_pdo'];
        } elseif ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            if (!isset($_ENV['TEST_MODE'])) {
                require_once __DIR__ . '/../config/dbConnection.php';
                $this->pdo = $GLOBALS['pdo'] ?? null;
            }
        }
        
        // Initialize Google Authenticator
        $this->ga = new PHPGangsta_GoogleAuthenticator();
    }

    /**
     * Generate a new secret
     */
    public function generateSecret()
    {
        return bin2hex(random_bytes(5)); // 10 chars hex
    }

    /**
     * Get QR Code URL
     */
    public function getQRCodeUrl($username, $secret, $issuer = 'LibrarySystem')
    {
        if (!$this->ga) {
            return null;
        }
        return $this->ga->getQRCodeGoogleUrl($issuer . ' (' . $username . ')', $secret);
    }

    /**
     * Verify OTP code
     */
    public function verifyCode($secret, $code)
    {
        if (!$this->ga) {
            return false;
        }
        // 2 = 2*30sec tolerance (1 minute before/after)
        return $this->ga->verifyCode($secret, $code, 2);
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes($userId, $count = 10)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(5)); // 10 chars hex
        }

        // Store hash of codes (never store plain text backup codes if possible, 
        // but often printed once. Here we store plain for display then hash later?
        // Actually usually we print them once and store hashed.
        // For simplicity in this implementation, we will store them encrypted or hashed.
        // Let's store them as JSON array of hashed values.

        $hashedCodes = array_map(function ($code) {
            return password_hash($code, PASSWORD_DEFAULT);
        }, $codes);

        $this->storeBackupCodes($userId, $hashedCodes);

        return $codes; // Return plain codes for display to user
    }

    /**
     * Verify and use a backup code
     */
    public function verifyBackupCode($userId, $code)
    {
        $storedCodes = $this->getStoredBackupCodes($userId); // Returns array of hashes
        if (empty($storedCodes))
            return false;

        foreach ($storedCodes as $index => $hash) {
            if (password_verify($code, $hash)) {
                // Code is valid. Remove it from list (used once)
                unset($storedCodes[$index]);
                $this->storeBackupCodes($userId, $storedCodes);
                return true;
            }
        }
        return false;
    }

    private function storeBackupCodes($userId, $codes)
    {
        $json = json_encode(array_values($codes));
        $stmt = $this->pdo->prepare("UPDATE users SET backup_codes = ? WHERE userId = ?");
        return $stmt->execute([$json, $userId]);
    }

    private function getStoredBackupCodes($userId)
    {
        $stmt = $this->pdo->prepare("SELECT backup_codes FROM users WHERE userId = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row && !empty($row['backup_codes']) ? json_decode($row['backup_codes'], true) : [];
    }

    public function enable2FA($userId, $secret)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET two_factor_secret = ?, is_2fa_enabled = 1 WHERE userId = ?");
            return $stmt->execute([$secret, $userId]);
        } catch (\Exception $e) {
            error_log("Error enabling 2FA: " . $e->getMessage());
            return false;
        }
    }

    public function disable2FA($userId)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET two_factor_secret = NULL, is_2fa_enabled = 0, backup_codes = NULL WHERE userId = ?");
            return $stmt->execute([$userId]);
        } catch (\Exception $e) {
            error_log("Error disabling 2FA: " . $e->getMessage());
            return false;
        }
    }
}
