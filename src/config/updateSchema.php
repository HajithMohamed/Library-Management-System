<?php
/**
 * Fixed Notifications Table Creation
 * Run this separately if the main schema update fails
 */
include_once('config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}
mysqli_set_charset($conn, 'utf8mb4');

echo "<h2>Creating Notifications Table...</h2>";

// Check if table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;

if ($tableExists) {
    echo "<p style='color:orange'>⚠ notifications table already exists</p>";
} else {
    // Try creating without foreign key first
    $sql = "CREATE TABLE notifications(
        id INT AUTO_INCREMENT PRIMARY KEY,
        userId VARCHAR(255) NULL,
        title VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('overdue','fine_paid','out_of_stock','system','reminder','approval') NOT NULL DEFAULT 'system',
        priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
        isRead TINYINT(1) NOT NULL DEFAULT 0,
        relatedId VARCHAR(255) NULL,
        createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_userId (userId),
        INDEX idx_type_relatedId (type, relatedId),
        INDEX idx_isRead (isRead)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ notifications table created successfully</p>";
        
        // Now try to add foreign key constraint
        $fkSql = "ALTER TABLE notifications 
                  ADD CONSTRAINT fk_notifications_userId 
                  FOREIGN KEY(userId) REFERENCES users(userId) 
                  ON UPDATE CASCADE ON DELETE CASCADE";
        
        if ($conn->query($fkSql) === TRUE) {
            echo "<p style='color:green'>✓ Foreign key constraint added</p>";
        } else {
            echo "<p style='color:orange'>⚠ Table created but foreign key constraint failed: " . $conn->error . "</p>";
            echo "<p style='color:info'>ℹ This is OK - the table will work without the constraint</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Error creating notifications table: " . $conn->error . "</p>";
    }
}

// Verify table structure
$result = $conn->query("DESCRIBE notifications");
if ($result) {
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse:collapse; margin:20px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Insert some test notifications
$testInsert = "INSERT INTO notifications (userId, title, message, type, priority) VALUES 
    (NULL, 'System Test', 'This is a test notification', 'system', 'low')";

if ($conn->query($testInsert) === TRUE) {
    echo "<p style='color:green'>✓ Test notification inserted successfully</p>";
} else {
    echo "<p style='color:red'>✗ Failed to insert test notification: " . $conn->error . "</p>";
}

// Borrow requests table creation
if ($conn->query("SHOW TABLES LIKE 'borrow_requests'")->num_rows == 0) {
    $sql = "CREATE TABLE borrow_requests(
        id INT AUTO_INCREMENT PRIMARY KEY,
        userId VARCHAR(255) NOT NULL,
        isbn VARCHAR(13) NOT NULL,
        requestDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
        approvedBy VARCHAR(255) NULL,
        dueDate DATE NULL,
        INDEX idx_userId (userId),
        INDEX idx_isbn (isbn),
        INDEX idx_status (status)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ borrow_requests table created successfully</p>";
        
        // Add foreign keys if users and books tables exist
        try {
            $conn->query("ALTER TABLE borrow_requests 
                          ADD CONSTRAINT fk_borrow_requests_userId
                          FOREIGN KEY (userId) REFERENCES users(userId) 
                          ON UPDATE CASCADE ON DELETE CASCADE");
                          
            $conn->query("ALTER TABLE borrow_requests 
                          ADD CONSTRAINT fk_borrow_requests_isbn
                          FOREIGN KEY (isbn) REFERENCES books(isbn) 
                          ON UPDATE CASCADE ON DELETE CASCADE");
                          
            echo "<p style='color:green'>✓ Foreign key constraints added to borrow_requests</p>";
        } catch (Exception $e) {
            echo "<p style='color:orange'>⚠ Tables created but foreign keys could not be added: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Error creating borrow_requests table: " . $conn->error . "</p>";
    }
}

// Fine settings table creation
if ($conn->query("SHOW TABLES LIKE 'fine_settings'")->num_rows == 0) {
    // Create fine_settings table
    $sql = "CREATE TABLE IF NOT EXISTS fine_settings(
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(100) NOT NULL UNIQUE,
        setting_value VARCHAR(255) NOT NULL,
        description TEXT,
        updatedBy VARCHAR(255),
        updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ fine_settings table created successfully</p>";
        
        // Insert default values
        $conn->query("INSERT INTO fine_settings (setting_name, setting_value, description, updatedBy) VALUES 
            ('fine_per_day', '5', 'Fine amount per day for overdue books', 'system'),
            ('max_borrow_days', '14', 'Maximum days a book can be borrowed', 'system'),
            ('grace_period_days', '0', 'Grace period before fines start', 'system'),
            ('max_fine_amount', '500', 'Maximum fine amount per book', 'system'),
            ('fine_calculation_method', 'daily', 'Method for calculating fines: daily or fixed', 'system')");
    } else {
        echo "<p style='color:red'>✗ Error creating fine_settings table: " . $conn->error . "</p>";
    }
}

// Backup log table creation
if ($conn->query("SHOW TABLES LIKE 'backup_log'")->num_rows == 0) {
    $sql = "CREATE TABLE backup_log(
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        filepath VARCHAR(500) NOT NULL,
        filesize BIGINT NOT NULL,
        backupType ENUM('manual','scheduled','system') NOT NULL DEFAULT 'manual',
        status ENUM('success','failed','in_progress') NOT NULL DEFAULT 'success',
        createdBy VARCHAR(255) NOT NULL,
        createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ backup_log table created successfully</p>";
    } else {
        echo "<p style='color:red'>✗ Error creating backup_log table: " . $conn->error . "</p>";
    }
}

// Maintenance log table creation
if ($conn->query("SHOW TABLES LIKE 'maintenance_log'")->num_rows == 0) {
    $sql = "CREATE TABLE maintenance_log(
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(255) NOT NULL,
        description TEXT,
        performedBy VARCHAR(255) NOT NULL,
        status ENUM('success','failed','warning') NOT NULL DEFAULT 'success',
        details TEXT NULL,
        createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ maintenance_log table created successfully</p>";
    } else {
        echo "<p style='color:red'>✗ Error creating maintenance_log table: " . $conn->error . "</p>";
    }
}

// Transactions fine columns
echo "<h3>Checking fine columns in transactions table...</h3>";

$fineColumns = [
    'fineAmount' => 'ALTER TABLE transactions ADD COLUMN fineAmount DECIMAL(10,2) DEFAULT 0',
    'fineStatus' => 'ALTER TABLE transactions ADD COLUMN fineStatus ENUM("pending","paid","waived") DEFAULT "pending"',
    'finePaymentDate' => 'ALTER TABLE transactions ADD COLUMN finePaymentDate DATE NULL',
    'finePaymentMethod' => 'ALTER TABLE transactions ADD COLUMN finePaymentMethod ENUM("cash","online","card") NULL'
];

foreach ($fineColumns as $column => $sql) {
    if ($conn->query("SHOW COLUMNS FROM transactions LIKE '$column'")->num_rows == 0) {
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>✓ Added '$column' column to transactions table</p>";
        } else {
            echo "<p style='color:red'>✗ Error adding '$column' column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ '$column' column already exists</p>";
    }
}

echo "<h3>Setup Complete!</h3>";
echo "<p><a href='../admin/adminDashboard.php'>Go to Dashboard</a></p>";

$conn->close();
?>