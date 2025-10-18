<?php
/**
 * Database Schema Update Script
 * Run this script to update existing database with new columns and tables
 */

include_once('config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Updating Database Schema...</h2>";

// Add name column to users table
$addNameColumn = "ALTER TABLE users ADD COLUMN IF NOT EXISTS name VARCHAR(255)";
if ($conn->query($addNameColumn) === TRUE) {
    echo "<p style='color: green;'>✓ Added name column to users table</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
}

// Add new columns to books table
$alterations = [
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS bookImage VARCHAR(255)",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS description TEXT",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS category VARCHAR(100)",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS publicationYear YEAR",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS totalCopies int DEFAULT 1"
];

foreach ($alterations as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ " . $sql . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $sql . " - " . $conn->error . "</p>";
    }
}

// Create book statistics table
$createStatsTable = "CREATE TABLE IF NOT EXISTS book_statistics( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(13),
    date_added DATE,
    total_borrowed INT DEFAULT 0,
    total_returned INT DEFAULT 0,
    new_arrivals INT DEFAULT 0,
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE
)";

if ($conn->query($createStatsTable) === TRUE) {
    echo "<p style='color: green;'>✓ Created book_statistics table</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating book_statistics table: " . $conn->error . "</p>";
}

// Update existing books to have totalCopies = available + borrowed
$updateTotalCopies = "UPDATE books SET totalCopies = available + borrowed WHERE totalCopies = 1";
if ($conn->query($updateTotalCopies) === TRUE) {
    echo "<p style='color: green;'>✓ Updated totalCopies for existing books</p>";
} else {
    echo "<p style='color: red;'>✗ Error updating totalCopies: " . $conn->error . "</p>";
}

// Create uploads directory if it doesn't exist
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Integrated-Library-System/assets/images/books/';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Created uploads directory: " . $uploadDir . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating uploads directory</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Uploads directory already exists</p>";
}

// Create borrow_requests table for borrow request functionality
$createBorrowRequestsTable = "CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(13) NOT NULL,
    userId VARCHAR(255) NOT NULL,
    requestDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE
)";
if ($conn->query($createBorrowRequestsTable) === TRUE) {
    echo "<p style='color: green;'>✓ Created borrow_requests table</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating borrow_requests table: " . $conn->error . "</p>";
}

echo "<h3>Database schema update completed!</h3>";
echo "<p><a href='../src/admin/adminDashboard.php'>Go to Admin Dashboard</a></p>";

$conn->close();
?>



