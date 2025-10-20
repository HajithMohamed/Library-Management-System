<?php
include_once('config.php');

// Database Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure UTF-8 encoding
mysqli_set_charset($conn, 'utf8mb4');
?>
