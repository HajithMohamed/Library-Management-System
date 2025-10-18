<?php
include_once('config.php');
$conn=mysqli_connect( DB_HOST, DB_USER , DB_PASSWORD , "", DB_PORT );//Create Connection
if($conn->connect_error)//Check Connection
{
     die("Connection failed: ".$conn->connect_error);
}

$messages = []; // Store all messages
$sql="CREATE DATABASE IF NOT EXISTS ".DB_NAME; // Creating the database if not exists

if($conn->query($sql)===TRUE)
{
     // Select the database before creating tables
     $conn->select_db(DB_NAME);

    // Check if we should drop and recreate (only if explicitly requested)
    $dropTables = isset($_GET['reset']) && $_GET['reset'] === 'true';
    
    if ($dropTables) {
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $conn->query("DROP TABLE IF EXISTS borrow_requests");
        $conn->query("DROP TABLE IF EXISTS book_statistics");
        $conn->query("DROP TABLE IF EXISTS transactions");
        $conn->query("DROP TABLE IF EXISTS books");
        $conn->query("DROP TABLE IF EXISTS users");
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $messages[] = "✓ Dropped existing tables";
    }

    // Create tables with all columns
    $sql1="CREATE TABLE IF NOT EXISTS users( 
        userId VARCHAR(255) PRIMARY KEY, 
        password VARCHAR(255), 
        userType VARCHAR(25), 
        name VARCHAR(255), 
        gender VARCHAR(6), 
        dob VARCHAR(10), 
        emailId VARCHAR(255), 
        phoneNumber VARCHAR(10), 
        address VARCHAR(255), 
        isVerified TINYINT(1) DEFAULT 0, 
        otp VARCHAR(10), 
        otpExpiry VARCHAR(20)
    )";
     
    $sql2="CREATE TABLE IF NOT EXISTS books( 
        isbn VARCHAR(13) PRIMARY KEY, 
        bookName VARCHAR(255), 
        authorName VARCHAR(255), 
        publisherName VARCHAR(255), 
        available INT, 
        borrowed INT, 
        bookImage VARCHAR(255), 
        description TEXT, 
        category VARCHAR(100), 
        publicationYear YEAR, 
        totalCopies INT DEFAULT 1
    )";

    // Create parent tables
    if($conn->query($sql1)===TRUE) { 
        $messages[] = "✓ Users table ready"; 
    } else { 
        $messages[] = "⚠ Users: ".$conn->error; 
    }
    
    if($conn->query($sql2)===TRUE) { 
        $messages[] = "✓ Books table ready"; 
    } else { 
        $messages[] = "⚠ Books: ".$conn->error; 
    }

    // Child tables with foreign keys
    $sql3="CREATE TABLE IF NOT EXISTS transactions( 
        tid VARCHAR(25) PRIMARY KEY, 
        userId VARCHAR(255), 
        isbn VARCHAR(13), 
        fine INT, 
        borrowDate VARCHAR(10), 
        returnDate VARCHAR(10), 
        lastFinePaymentDate VARCHAR(10), 
        FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE, 
        FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE
    )";

    $sql4="CREATE TABLE IF NOT EXISTS book_statistics( 
        id INT AUTO_INCREMENT PRIMARY KEY,
        isbn VARCHAR(13),
        date_added DATE,
        total_borrowed INT DEFAULT 0,
        total_returned INT DEFAULT 0,
        new_arrivals INT DEFAULT 0,
        FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE
    )";

    $sql5="CREATE TABLE IF NOT EXISTS borrow_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        isbn VARCHAR(13) NOT NULL,
        userId VARCHAR(255) NOT NULL,
        requestDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
        FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
        FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE
    )";
    
    if($conn->query($sql3)===TRUE) { 
        $messages[] = "✓ Transactions table ready"; 
    } else { 
        $messages[] = "⚠ Transactions: ".$conn->error; 
    }
    
    if($conn->query($sql4)===TRUE) { 
        $messages[] = "✓ Book statistics table ready"; 
    } else { 
        $messages[] = "⚠ Book statistics: ".$conn->error; 
    }

    if($conn->query($sql5) === TRUE) {
        $messages[] = "✓ Borrow requests table ready";
    } else {
        $messages[] = "⚠ Borrow requests: ".$conn->error;
    }
}
else {
     $messages[] = "❌ Error creating database: ".$conn->error;
}
$conn->close();

// Output
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Setup</title></head><body>";
echo "<h2>Database Setup Complete</h2><ul>";
foreach ($messages as $msg) { echo "<li>$msg</li>"; }
echo "</ul>";
echo "<p><a href='createDB.php?reset=true'>→ Reset & Drop All Tables</a></p>";
echo "<p><a href='insertSampleData.php'>→ Insert Sample Data</a></p>";
echo "<p><a href='../index.php'>→ Go to Login</a></p>";
echo "</body></html>";
?>
