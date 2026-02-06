<?php
/**
 * Migration: Add privilege differentiation columns to users and books_borrowed tables
 * 
 * Run this file once to add the new columns for role-based borrowing limits.
 * Safe to run multiple times - uses IF NOT EXISTS / column existence checks.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dbConnection.php';

global $conn, $mysqli;
$db = $conn ?? $mysqli;

if (!$db) {
    die("Database connection failed.\n");
}

$results = [];

// ====================================================================
// 1. Add privilege columns to users table
// ====================================================================
$userColumns = [
    'max_borrow_limit' => "ALTER TABLE users ADD COLUMN max_borrow_limit INT NOT NULL DEFAULT 3",
    'borrow_period_days' => "ALTER TABLE users ADD COLUMN borrow_period_days INT NOT NULL DEFAULT 14",
    'max_renewals' => "ALTER TABLE users ADD COLUMN max_renewals INT NOT NULL DEFAULT 1"
];

foreach ($userColumns as $colName => $alterSql) {
    // Check if column already exists
    $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = '{$colName}'");
    $exists = $check->fetch_assoc()['cnt'] > 0;
    
    if (!$exists) {
        if ($db->query($alterSql)) {
            $results[] = "✓ Added column '{$colName}' to users table.";
        } else {
            $results[] = "✗ Failed to add column '{$colName}': " . $db->error;
        }
    } else {
        $results[] = "• Column '{$colName}' already exists in users table. Skipped.";
    }
}

// ====================================================================
// 2. Add renewal tracking columns to books_borrowed table
// ====================================================================
$borrowColumns = [
    'renewalCount' => "ALTER TABLE books_borrowed ADD COLUMN renewalCount INT NOT NULL DEFAULT 0",
    'lastRenewalDate' => "ALTER TABLE books_borrowed ADD COLUMN lastRenewalDate DATE DEFAULT NULL"
];

foreach ($borrowColumns as $colName => $alterSql) {
    $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'books_borrowed' AND COLUMN_NAME = '{$colName}'");
    $exists = $check->fetch_assoc()['cnt'] > 0;
    
    if (!$exists) {
        if ($db->query($alterSql)) {
            $results[] = "✓ Added column '{$colName}' to books_borrowed table.";
        } else {
            $results[] = "✗ Failed to add column '{$colName}': " . $db->error;
        }
    } else {
        $results[] = "• Column '{$colName}' already exists in books_borrowed table. Skipped.";
    }
}

// ====================================================================
// 3. Set default privilege values for existing users based on role
// ====================================================================
$updateQueries = [
    "UPDATE users SET max_borrow_limit = 3, borrow_period_days = 14, max_renewals = 1 WHERE userType = 'Student'" 
        => "Student privileges",
    "UPDATE users SET max_borrow_limit = 10, borrow_period_days = 60, max_renewals = 2 WHERE userType = 'Faculty'" 
        => "Faculty privileges",
    "UPDATE users SET max_borrow_limit = 999, borrow_period_days = 365, max_renewals = 999 WHERE userType = 'Admin'" 
        => "Admin privileges",
    "UPDATE users SET max_borrow_limit = 999, borrow_period_days = 365, max_renewals = 999 WHERE userType = 'Librarian'" 
        => "Librarian privileges"
];

foreach ($updateQueries as $sql => $label) {
    if ($db->query($sql)) {
        $affected = $db->affected_rows;
        $results[] = "✓ Set {$label} for {$affected} user(s).";
    } else {
        $results[] = "✗ Failed to set {$label}: " . $db->error;
    }
}

// ====================================================================
// Output results
// ====================================================================
echo "<h2>Privilege Differentiation Migration</h2>";
echo "<pre>";
foreach ($results as $r) {
    echo $r . "\n";
}
echo "\n✅ Migration complete!\n";
echo "</pre>";

echo "<h3>Current Privilege Settings</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>User Type</th><th>Max Borrow Limit</th><th>Borrow Period (Days)</th><th>Max Renewals</th><th>Count</th></tr>";

$verifyResult = $db->query("SELECT userType, max_borrow_limit, borrow_period_days, max_renewals, COUNT(*) as user_count FROM users GROUP BY userType, max_borrow_limit, borrow_period_days, max_renewals ORDER BY userType");
if ($verifyResult) {
    while ($row = $verifyResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['userType']}</td>";
        echo "<td>{$row['max_borrow_limit']}</td>";
        echo "<td>{$row['borrow_period_days']}</td>";
        echo "<td>{$row['max_renewals']}</td>";
        echo "<td>{$row['user_count']}</td>";
        echo "</tr>";
    }
}
echo "</table>";
?>
