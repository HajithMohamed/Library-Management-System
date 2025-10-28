<?php
// This file is now DEPRECATED
// All database connections are handled in config.php using $mysqli
// Keep this file for backwards compatibility, but it just sets aliases

if (!isset($mysqli)) {
    die("Database connection not initialized. Include config.php first.");
}

// Create alias for backwards compatibility
$conn = $mysqli;
$GLOBALS['conn'] = $mysqli;

// Ensure UTF-8 encoding
mysqli_set_charset($mysqli, 'utf8mb4');
?>
