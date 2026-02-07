<?php
/**
 * Database Migration: Add Password Security Features
 * 
 * Adds:
 * - password_changed_at column to users table
 * - force_password_change column to users table
 * - password_change_attempts table (rate limiting)
 * - password_history table (prevent reuse of last 3 passwords)
 * - password_change_log table (audit trail)
 * 
 * Run this script once to update the database schema.
 */

require_once __DIR__ . '/dbConnection.php';

echo "<h2>Password Security Migration</h2>";
echo "<pre>";

$migrations = [
    [
        'description' => 'Add password_changed_at column to users',
        'check' => "SHOW COLUMNS FROM users LIKE 'password_changed_at'",
        'sql' => "ALTER TABLE users ADD COLUMN password_changed_at DATETIME DEFAULT NULL"
    ],
    [
        'description' => 'Add force_password_change column to users',
        'check' => "SHOW COLUMNS FROM users LIKE 'force_password_change'",
        'sql' => "ALTER TABLE users ADD COLUMN force_password_change TINYINT(1) DEFAULT 0"
    ],
    [
        'description' => 'Create password_history table',
        'check' => "SHOW TABLES LIKE 'password_history'",
        'sql' => "CREATE TABLE password_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(50) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_userId (userId),
            FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'description' => 'Create password_change_log table',
        'check' => "SHOW TABLES LIKE 'password_change_log'",
        'sql' => "CREATE TABLE password_change_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(50) NOT NULL,
            change_type ENUM('voluntary', 'forced', 'admin_reset', 'forgot_password') NOT NULL DEFAULT 'voluntary',
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_userId (userId),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'description' => 'Create password_change_attempts table',
        'check' => "SHOW TABLES LIKE 'password_change_attempts'",
        'sql' => "CREATE TABLE password_change_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(50) NOT NULL,
            attempt_type ENUM('wrong_current_password', 'validation_failure') NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_userId (userId),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ]
];

$successCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($migrations as $migration) {
    echo "\n[Migration] {$migration['description']}... ";
    
    $checkResult = $conn->query($migration['check']);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        echo "SKIPPED (already exists)\n";
        $skipCount++;
        continue;
    }
    
    if ($conn->query($migration['sql'])) {
        echo "SUCCESS ✓\n";
        $successCount++;
    } else {
        echo "FAILED ✗ - {$conn->error}\n";
        $errorCount++;
    }
}

// Seed password_history with current passwords for existing users
echo "\n[Seed] Storing current passwords in password_history... ";
$seedResult = $conn->query("
    INSERT INTO password_history (userId, password_hash, created_at)
    SELECT userId, password, NOW()
    FROM users 
    WHERE userId NOT IN (SELECT DISTINCT userId FROM password_history)
    AND password IS NOT NULL AND password != ''
");

if ($seedResult) {
    $seeded = $conn->affected_rows;
    echo "SUCCESS ✓ ({$seeded} users seeded)\n";
} else {
    echo "FAILED ✗ - {$conn->error}\n";
}

// Update password_changed_at for existing users
echo "[Seed] Setting password_changed_at for existing users... ";
$updateResult = $conn->query("UPDATE users SET password_changed_at = NOW() WHERE password_changed_at IS NULL");
if ($updateResult) {
    $updated = $conn->affected_rows;
    echo "SUCCESS ✓ ({$updated} users updated)\n";
} else {
    echo "FAILED ✗ - {$conn->error}\n";
}

echo "\n========================================\n";
echo "Migration Complete!\n";
echo "  Success: {$successCount}\n";
echo "  Skipped: {$skipCount}\n";
echo "  Errors:  {$errorCount}\n";
echo "========================================\n";
echo "</pre>";
