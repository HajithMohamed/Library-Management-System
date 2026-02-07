<?php
/**
 * Database Migration: Add Faculty Management Columns
 * 
 * Adds columns needed for admin-created faculty accounts:
 * - password_changed: tracks if user has changed their temp password
 * - first_login: flags if user needs to change password on first login
 * - department: faculty department
 * - designation: faculty designation/title
 * - employee_id: faculty employee ID
 * 
 * Run this file once to apply the migration.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dbConnection.php';

echo "<h2>Faculty Management Migration</h2>\n";
echo "<pre>\n";

$migrations = [
    [
        'description' => 'Add password_changed column',
        'check' => "SHOW COLUMNS FROM users LIKE 'password_changed'",
        'sql' => "ALTER TABLE users ADD COLUMN password_changed BOOLEAN DEFAULT 0"
    ],
    [
        'description' => 'Add first_login column',
        'check' => "SHOW COLUMNS FROM users LIKE 'first_login'",
        'sql' => "ALTER TABLE users ADD COLUMN first_login BOOLEAN DEFAULT 1"
    ],
    [
        'description' => 'Add department column',
        'check' => "SHOW COLUMNS FROM users LIKE 'department'",
        'sql' => "ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL"
    ],
    [
        'description' => 'Add designation column',
        'check' => "SHOW COLUMNS FROM users LIKE 'designation'",
        'sql' => "ALTER TABLE users ADD COLUMN designation VARCHAR(100) DEFAULT NULL"
    ],
    [
        'description' => 'Add employee_id column',
        'check' => "SHOW COLUMNS FROM users LIKE 'employee_id'",
        'sql' => "ALTER TABLE users ADD COLUMN employee_id VARCHAR(50) DEFAULT NULL"
    ],
];

$successCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($migrations as $migration) {
    echo "Checking: {$migration['description']}... ";
    
    $result = $conn->query($migration['check']);
    
    if ($result && $result->num_rows > 0) {
        echo "SKIPPED (already exists)\n";
        $skipCount++;
    } else {
        if ($conn->query($migration['sql'])) {
            echo "SUCCESS\n";
            $successCount++;
        } else {
            echo "ERROR: " . $conn->error . "\n";
            $errorCount++;
        }
    }
}

// Set existing users as already having changed their password (not first login)
echo "\nUpdating existing users (mark as password already changed)... ";
$updateResult = $conn->query("UPDATE users SET password_changed = 1, first_login = 0 WHERE createdAt IS NOT NULL");
if ($updateResult) {
    $affectedRows = $conn->affected_rows;
    echo "SUCCESS ({$affectedRows} rows updated)\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
    $errorCount++;
}

echo "\n";
echo "=================================\n";
echo "Migration Summary:\n";
echo "  Successful: {$successCount}\n";
echo "  Skipped:    {$skipCount}\n";
echo "  Errors:     {$errorCount}\n";
echo "=================================\n";
echo "</pre>\n";

if ($errorCount === 0) {
    echo "<p style='color: green; font-weight: bold;'>Migration completed successfully!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Migration completed with {$errorCount} error(s). Please review.</p>";
}
