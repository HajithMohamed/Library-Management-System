<?php
include_once('config.php');
$conn=mysqli_connect( DB_HOST, DB_USER , DB_PASSWORD , "", DB_PORT );
if($conn->connect_error) {
     die("Connection failed: ".$conn->connect_error);
}

// Drop the database if it exists
$sql = "DROP DATABASE IF EXISTS ".DB_NAME;
if($conn->query($sql) === TRUE) {
    echo "<p>✓ Database dropped successfully</p>";
} else {
    echo "<p>✗ Error dropping database: " . $conn->error . "</p>";
}

$conn->close();

// Now run createDB.php
echo "<p>Redirecting to create database...</p>";
echo "<script>setTimeout(function(){ window.location.href='createDB.php'; }, 2000);</script>";
?>
