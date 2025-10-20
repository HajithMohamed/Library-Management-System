<?php
include_once('config.php');
$conn=mysqli_connect( DB_HOST, DB_USER , DB_PASSWORD , "", DB_PORT );//Create Connection
if($conn->connect_error)//Check Connection
{
     die("Connection failed: ".$conn->connect_error);
}
mysqli_set_charset($conn, 'utf8mb4');

$messages = [];
$sql="CREATE DATABASE IF NOT EXISTS ".DB_NAME;

if($conn->query($sql)===TRUE)
{
  $conn->select_db(DB_NAME);

  // Optional: drop all for a clean reset
  $dropTables = isset($_GET['reset']) && $_GET['reset'] === 'true';
  if ($dropTables) {
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("DROP TABLE IF EXISTS payments");
    $conn->query("DROP TABLE IF EXISTS notifications");
    $conn->query("DROP TABLE IF EXISTS borrow_requests");
    $conn->query("DROP TABLE IF EXISTS book_statistics");
    $conn->query("DROP TABLE IF EXISTS transactions");
    $conn->query("DROP TABLE IF EXISTS books");
    $conn->query("DROP TABLE IF EXISTS users");
    $conn->query("DROP TABLE IF EXISTS audit_logs");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    $messages[] = "✓ Dropped existing tables";
  }

  // Parents
  $sqlUsers="CREATE TABLE IF NOT EXISTS users(
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
    otpExpiry VARCHAR(20),
    INDEX(emailId)
  )";

  $sqlBooks="CREATE TABLE IF NOT EXISTS books(
    isbn VARCHAR(13) PRIMARY KEY,
    bookName VARCHAR(255),
    authorName VARCHAR(255),
    publisherName VARCHAR(255),
    available INT DEFAULT 0,
    borrowed INT DEFAULT 0,
    bookImage VARCHAR(255),
    description TEXT,
    category VARCHAR(100),
    publicationYear YEAR,
    totalCopies INT DEFAULT 0
  )";

  $messages[] = ($conn->query($sqlUsers)===TRUE) ? "✓ Users table ready" : "⚠ Users: ".$conn->error;
  $messages[] = ($conn->query($sqlBooks)===TRUE) ? "✓ Books table ready" : "⚠ Books: ".$conn->error;

  // Children
  $sqlTransactions="CREATE TABLE IF NOT EXISTS transactions(
    tid VARCHAR(25) PRIMARY KEY,
    userId VARCHAR(255),
    isbn VARCHAR(13),
    fine INT DEFAULT 0,
    borrowDate VARCHAR(10),
    returnDate VARCHAR(10),
    lastFinePaymentDate VARCHAR(10),
    dueDate DATE NULL,
    status ENUM('Borrowed','Returned','Overdue') DEFAULT 'Borrowed',
    extendedOnce TINYINT(1) DEFAULT 0,
    paymentRef VARCHAR(100) NULL,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX(userId),
    INDEX(isbn)
  )";

  $sqlBookStats="CREATE TABLE IF NOT EXISTS book_statistics(
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(13),
    date_added DATE,
    total_borrowed INT DEFAULT 0,
    total_returned INT DEFAULT 0,
    new_arrivals INT DEFAULT 0,
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX(isbn),
    INDEX(date_added)
  )";

  $sqlBorrowReq="CREATE TABLE IF NOT EXISTS borrow_requests(
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(13) NOT NULL,
    userId VARCHAR(255) NOT NULL,
    requestDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    approvedBy VARCHAR(255) NULL,
    dueDate DATE NULL,
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX(userId),
    INDEX(isbn),
    INDEX(status)
  )";

  $sqlNotifications="CREATE TABLE IF NOT EXISTS notifications(
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(255) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('System','Reminder','Approval') NOT NULL DEFAULT 'System',
    isRead TINYINT(1) NOT NULL DEFAULT 0,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX(userId),
    INDEX(isRead),
    INDEX(createdAt)
  )";

  $sqlPayments="CREATE TABLE IF NOT EXISTS payments(
    paymentId INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('fine','membership') NOT NULL,
    method ENUM('cash','online') NOT NULL,
    reference VARCHAR(100),
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX(userId),
    INDEX(createdAt)
  )";

  $sqlAudit="CREATE TABLE IF NOT EXISTS audit_logs(
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(255) NULL,
    action VARCHAR(255) NOT NULL,
    ipAddress VARCHAR(64),
    userAgent VARCHAR(255),
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(userId),
    INDEX(createdAt)
  )";

  $messages[] = ($conn->query($sqlTransactions)===TRUE) ? "✓ Transactions table ready" : "⚠ Transactions: ".$conn->error;
  $messages[] = ($conn->query($sqlBookStats)===TRUE) ? "✓ Book statistics table ready" : "⚠ Book statistics: ".$conn->error;
  $messages[] = ($conn->query($sqlBorrowReq)===TRUE) ? "✓ Borrow requests table ready" : "⚠ Borrow requests: ".$conn->error;
  $messages[] = ($conn->query($sqlNotifications)===TRUE) ? "✓ Notifications table ready" : "⚠ Notifications: ".$conn->error;
  $messages[] = ($conn->query($sqlPayments)===TRUE) ? "✓ Payments table ready" : "⚠ Payments: ".$conn->error;
  $messages[] = ($conn->query($sqlAudit)===TRUE) ? "✓ Audit logs table ready" : "⚠ Audit logs: ".$conn->error;

  // Optional seed (?seed=true)
  if (isset($_GET['seed']) && $_GET['seed']==='true') {
    // Seed librarian/admin
    $pw = password_hash('librarian123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO users(userId,password,userType,name,isVerified,emailId) VALUES
      ('LIB001','$pw','Librarian','Librarian One',1,'lib001@example.com')");

    // Seed books
    $conn->query("INSERT IGNORE INTO books(isbn,bookName,authorName,publisherName,available,borrowed,category,publicationYear,totalCopies) VALUES
      ('9780134685991','Effective Java','Joshua Bloch','Addison-Wesley',5,0,'Technology',2018,5),
      ('9780735619678','Code Complete','Steve McConnell','Microsoft Press',3,0,'Technology',2004,3)");

    // Seed sample borrow requests
    $conn->query("INSERT IGNORE INTO borrow_requests(isbn,userId,status) VALUES
      ('9780134685991','LIB001','Pending')");
    $messages[] = "✓ Seed data inserted";
  }
}
else {
  $messages[] = "❌ Error creating database: ".$conn->error;
}
$conn->close();

// Output
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Setup</title></head><body>";
echo "<h2>Database Setup Complete</h2><ul>";
foreach ($messages as $msg) echo "<li>$msg</li>";
echo "</ul>";
echo "<p><a href='createDB.php?reset=true'>→ Reset & Drop All Tables</a> | <a href='createDB.php?seed=true'>→ Seed Sample Data</a></p>";
echo "<p><a href='../index.php'>→ Go to Login</a></p>";
echo "</body></html>";
?>
