<?php

namespace App\Services;

use App\Core\Database;
use PHPGangsta_GoogleAuthenticator;

class TwoFactorService
{
    private $ga;
    private $db;

    public function __construct()
    {
        // Check if class exists to avoid fatal error if dependency missing
        if (class_exists('PHPGangsta_GoogleAuthenticator')) {
            $this->ga = new PHPGangsta_GoogleAuthenticator();
        }
        $this->db = new Database();
    }

    /**
     * Generate a new secret
     */
    public function generateSecret()
    {
        if (!$this->ga)
            return null;
        return $this->ga->createSecret();
    }

    /**
     * Get QR Code URL
     */
    public function getQRCodeUrl($username, $secret, $issuer = 'LibrarySystem')
    {
        if (!$this->ga)
            return null;
        return $this->ga->getQRCodeGoogleUrl($issuer . ' (' . $username . ')', $secret);
    }

    /**
     * Verify OTP code
     */
    public function verifyCode($secret, $code)
    {
        if (!$this->ga)
            return false;
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
        $stmt = $this->db->prepare("UPDATE users SET backup_codes = ? WHERE userId = ?");
        return $stmt->execute([$json, $userId]);
    }

    private function getStoredBackupCodes($userId)
    {
        $stmt = $this->db->prepare("SELECT backup_codes FROM users WHERE userId = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if ($row && !empty($row['backup_codes'])) {
            return json_decode($row['backup_codes'], true) ?: [];
        }
        return [];
    }

    public function enable2FA($userId, $secret)
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET two_factor_secret = ?, is_2fa_enabled = 1 WHERE userId = ?");
            return $stmt->execute([$secret, $userId]);
        } catch (\Exception $e) {
            error_log("Error enabling 2FA: " . $e->getMessage());
            return false;
        }
    }

    public function disable2FA($userId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET two_factor_secret = NULL, is_2fa_enabled = 0, backup_codes = NULL WHERE userId = ?");
            return $stmt->execute([$userId]);
        } catch (\Exception $e) {
            error_log("Error disabling 2FA: " . $e->getMessage());
            return false;
        }
    }
}
