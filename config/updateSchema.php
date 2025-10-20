<?php
/**
 * Idempotent DB schema updater without using ALTER ... IF NOT EXISTS
 */
include_once('config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
mysqli_set_charset($conn, 'utf8mb4');

function columnMissing($conn,$table,$col){
  $db = DB_NAME;
  $stmt = $conn->prepare("SELECT COUNT(*) c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?");
  $stmt->bind_param('sss',$db,$table,$col);
  $stmt->execute(); $res=$stmt->get_result()->fetch_assoc(); $stmt->close();
  return ($res['c']==0);
}
function tableCreate($conn,$sql,$ok,$fail){
  if($conn->query($sql)===TRUE) echo "<p style='color:green'>✓ $ok</p>";
  else echo "<p style='color:red'>✗ $fail: ".$conn->error."</p>";
}

echo "<h2>Updating Database Schema...</h2>";

// Users columns
if (columnMissing($conn,'users','name'))          $conn->query("ALTER TABLE users ADD COLUMN name VARCHAR(255)");
if (columnMissing($conn,'users','isVerified'))     $conn->query("ALTER TABLE users ADD COLUMN isVerified TINYINT(1) DEFAULT 0");
if (columnMissing($conn,'users','otp'))            $conn->query("ALTER TABLE users ADD COLUMN otp VARCHAR(10)");
if (columnMissing($conn,'users','otpExpiry'))      $conn->query("ALTER TABLE users ADD COLUMN otpExpiry VARCHAR(20)");

// Books columns
if (columnMissing($conn,'books','bookImage'))      $conn->query("ALTER TABLE books ADD COLUMN bookImage VARCHAR(255)");
if (columnMissing($conn,'books','description'))    $conn->query("ALTER TABLE books ADD COLUMN description TEXT");
if (columnMissing($conn,'books','category'))       $conn->query("ALTER TABLE books ADD COLUMN category VARCHAR(100)");
if (columnMissing($conn,'books','publicationYear'))$conn->query("ALTER TABLE books ADD COLUMN publicationYear YEAR");
if (columnMissing($conn,'books','totalCopies'))    $conn->query("ALTER TABLE books ADD COLUMN totalCopies INT DEFAULT 0");

// Borrow requests table and columns
tableCreate(
  $conn,
  "CREATE TABLE IF NOT EXISTS borrow_requests(
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(13) NOT NULL,
    userId VARCHAR(255) NOT NULL,
    requestDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    approvedBy VARCHAR(255) NULL,
    dueDate DATE NULL,
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE
  )",
  "borrow_requests table ready",
  "Error creating borrow_requests table"
);

// Transactions extra columns
if (columnMissing($conn,'transactions','dueDate'))       $conn->query("ALTER TABLE transactions ADD COLUMN dueDate DATE NULL");
if (columnMissing($conn,'transactions','status'))        $conn->query("ALTER TABLE transactions ADD COLUMN status ENUM('Borrowed','Returned','Overdue') DEFAULT 'Borrowed'");
if (columnMissing($conn,'transactions','extendedOnce'))  $conn->query("ALTER TABLE transactions ADD COLUMN extendedOnce TINYINT(1) DEFAULT 0");
if (columnMissing($conn,'transactions','paymentRef'))    $conn->query("ALTER TABLE transactions ADD COLUMN paymentRef VARCHAR(100) NULL");

// Book statistics
tableCreate(
  $conn,
  "CREATE TABLE IF NOT EXISTS book_statistics(
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(13),
    date_added DATE,
    total_borrowed INT DEFAULT 0,
    total_returned INT DEFAULT 0,
    new_arrivals INT DEFAULT 0,
    FOREIGN KEY(isbn) REFERENCES books(isbn) ON UPDATE CASCADE ON DELETE CASCADE
  )",
  "book_statistics table ready",
  "Error creating book_statistics table"
);

// Payments
tableCreate(
  $conn,
  "CREATE TABLE IF NOT EXISTS payments(
    paymentId INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('fine','membership') NOT NULL,
    method ENUM('cash','online') NOT NULL,
    reference VARCHAR(100),
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE
  )",
  "payments table ready",
  "Error creating payments table"
);

// Notifications
tableCreate(
  $conn,
  "CREATE TABLE IF NOT EXISTS notifications(
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(255) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('System','Reminder','Approval') NOT NULL DEFAULT 'System',
    isRead TINYINT(1) NOT NULL DEFAULT 0,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(userId) REFERENCES users(userId) ON UPDATE CASCADE ON DELETE CASCADE
  )",
  "notifications table ready",
  "Error creating notifications table"
);

// Audit logs
tableCreate(
  $conn,
  "CREATE TABLE IF NOT EXISTS audit_logs(
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(255) NULL,
    action VARCHAR(255) NOT NULL,
    ipAddress VARCHAR(64),
    userAgent VARCHAR(255),
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  )",
  "audit_logs table ready",
  "Error creating audit_logs table"
);

// Tweak totals for existing data
$conn->query("UPDATE books SET totalCopies = COALESCE(totalCopies,0)");
$conn->query("UPDATE books SET available = COALESCE(available,0), borrowed = COALESCE(borrowed,0)");
$conn->query("UPDATE books SET totalCopies = GREATEST(totalCopies, available + borrowed)");

echo "<h3>Schema update completed.</h3>";
echo "<p><a href='../src/admin/adminDashboard.php'>Go to Dashboard</a></p>";
$conn->close();
?>



