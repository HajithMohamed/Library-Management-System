<?php
/**
 * Database Connection Alias
 * Creates $conn as an alias to $mysqli for backward compatibility
 */

// Ensure mysqli is available from config.php
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    error_log("ERROR: dbConnection.php - mysqli not found from config.php");
    die("Database connection not available");
}

// Create alias
$conn = $mysqli;

// Store in GLOBALS for easy access
$GLOBALS['conn'] = $conn;

error_log("Database connection alias \$conn created successfully");
?>
