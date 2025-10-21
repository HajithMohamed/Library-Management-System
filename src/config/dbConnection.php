<?php
include_once('config.php');

// Use port 3306 for Docker (DB_HOST = 'db'), else use DB_PORT (default 3307 for XAMPP)
$host = DB_HOST;
$port = (strtolower($host) === 'db') ? 3306 : (int)DB_PORT;

// Database Connection
$conn = mysqli_connect($host, DB_USER, DB_PASSWORD, DB_NAME, $port);

// Check Connection
if (!$conn) {
    die("Connection failed ({$host}:{$port}): " . mysqli_connect_error());
}

// Ensure UTF-8 encoding
mysqli_set_charset($conn, 'utf8mb4');
?>
