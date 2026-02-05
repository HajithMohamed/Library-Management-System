<?php
/**
 * Database Connection Alias
 * Creates $conn as an alias to $mysqli for backward compatibility
 * Also ensures $pdo is available
 */

// Skip database connection in test mode
if (isset($_ENV['TEST_MODE']) && $_ENV['TEST_MODE']) {
    if (isset($GLOBALS['test_pdo'])) {
        $pdo = $GLOBALS['test_pdo'];
    }
    return;
}

// Ensure connections are available from config.php
if (!isset($mysqli) || !($mysqli instanceof \mysqli)) {
    // If mysqli failed but PDO succeeded, some things might still work if we don't die
    // But for now, we expect both in a healthy environment
    if (!isset($pdo) || !($pdo instanceof \PDO)) {
        error_log("ERROR: dbConnection.php - No database connection available");
        die("Database connection not available");
    }
}

// Create alias for backward compatibility
$conn = $mysqli ?? null;
$GLOBALS['conn'] = $conn;

// Ensure pdo is globally accessible if not already
if (isset($pdo)) {
    $GLOBALS['pdo'] = $pdo;
}

error_log("Database connection aliases created successfully");
?>