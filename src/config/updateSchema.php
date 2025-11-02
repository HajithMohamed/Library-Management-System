<?php
/**
 * Comprehensive Database Schema Update
 * Creates all missing tables and columns for the library system
 */
include_once('config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

mysqli_set_charset($conn, 'utf8mb4');

// Enable better error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Schema Update</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        h2 { color: #764ba2; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #764ba2; }
        .progress { background: #e9ecef; border-radius: 5px; height: 30px; margin: 20px 0; }
        .progress-bar { background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; border-radius: 5px; line-height: 30px; text-align: center; color: white; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i>🔧</i> Database Schema Update - Library System</h1>
        <p>This script will create all necessary tables and columns for the library system.</p>

<?php

$totalSteps = 10;
$currentStep = 0;

function updateProgress($current, $total) {
    $percentage = ($current / $total) * 100;
    echo "<div class='progress'><div class='progress-bar' style='width: {$percentage}%'>{$percentage}% Complete</div></div>";
}

// ==========================================
// STEP 1: Create notifications table
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Creating notifications table...</h2>";

try {
    $tableExists = $conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;
    
    if (!$tableExists) {
        $sql = "CREATE TABLE notifications (
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
            INDEX idx_type (type),
            INDEX idx_isRead (isRead),
            INDEX idx_createdAt (createdAt)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($sql);
        echo "<div class='success'>✓ notifications table created successfully</div>";
    } else {
        echo "<div class='info'>ℹ notifications table already exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 2: Create borrow_requests table
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Creating borrow_requests table...</h2>";

try {
    $tableExists = $conn->query("SHOW TABLES LIKE 'borrow_requests'")->num_rows > 0;
    
    if (!$tableExists) {
        $sql = "CREATE TABLE borrow_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            userId VARCHAR(255) NOT NULL,
            isbn VARCHAR(13) NOT NULL,
            requestDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
            approvedBy VARCHAR(255) NULL,
            dueDate DATE NULL,
            rejectionReason TEXT NULL,
            INDEX idx_userId (userId),
            INDEX idx_isbn (isbn),
            INDEX idx_status (status),
            INDEX idx_requestDate (requestDate)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($sql);
        echo "<div class='success'>✓ borrow_requests table created successfully</div>";
    } else {
        echo "<div class='info'>ℹ borrow_requests table already exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 3: Create fine_settings table
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Creating fine_settings table...</h2>";

try {
    $tableExists = $conn->query("SHOW TABLES LIKE 'fine_settings'")->num_rows > 0;
    
    if (!$tableExists) {
        $sql = "CREATE TABLE fine_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_name VARCHAR(100) NOT NULL UNIQUE,
            setting_value VARCHAR(255) NOT NULL,
            description TEXT,
            updatedBy VARCHAR(255),
            updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($sql);
        echo "<div class='success'>✓ fine_settings table created successfully</div>";
        
        // Insert default settings
        $defaults = [
            ['fine_per_day', '5', 'Fine amount per day for overdue books'],
            ['max_borrow_days', '14', 'Maximum days a book can be borrowed'],
            ['grace_period_days', '0', 'Grace period before fines start'],
            ['max_fine_amount', '500', 'Maximum fine amount per book'],
            ['fine_calculation_method', 'daily', 'Method for calculating fines: daily or fixed']
        ];
        
        foreach ($defaults as $setting) {
            $conn->query("INSERT INTO fine_settings (setting_name, setting_value, description, updatedBy) 
                         VALUES ('{$setting[0]}', '{$setting[1]}', '{$setting[2]}', 'system')");
        }
        echo "<div class='success'>✓ Default fine settings inserted</div>";
    } else {
        echo "<div class='info'>ℹ fine_settings table already exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 4: Create backup_log table
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Creating backup_log table...</h2>";

try {
    $tableExists = $conn->query("SHOW TABLES LIKE 'backup_log'")->num_rows > 0;
    
    if (!$tableExists) {
        $sql = "CREATE TABLE backup_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            filepath VARCHAR(500) NOT NULL,
            filesize BIGINT NOT NULL,
            backupType ENUM('manual','scheduled','system') NOT NULL DEFAULT 'manual',
            status ENUM('success','failed','in_progress') NOT NULL DEFAULT 'success',
            createdBy VARCHAR(255) NOT NULL,
            createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_createdAt (createdAt),
            INDEX idx_backupType (backupType)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($sql);
        echo "<div class='success'>✓ backup_log table created successfully</div>";
    } else {
        echo "<div class='info'>ℹ backup_log table already exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 5: Create maintenance_log table
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Creating maintenance_log table...</h2>";

try {
    $tableExists = $conn->query("SHOW TABLES LIKE 'maintenance_log'")->num_rows > 0;
    
    if (!$tableExists) {
        $sql = "CREATE TABLE maintenance_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(255) NOT NULL,
            description TEXT,
            performedBy VARCHAR(255) NOT NULL,
            status ENUM('success','failed','warning') NOT NULL DEFAULT 'success',
            details TEXT NULL,
            createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_createdAt (createdAt),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($sql);
        echo "<div class='success'>✓ maintenance_log table created successfully</div>";
    } else {
        echo "<div class='info'>ℹ maintenance_log table already exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 6: Update transactions table with fine columns
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Updating transactions table...</h2>";

$fineColumns = [
    'fineAmount' => "ALTER TABLE transactions ADD COLUMN fineAmount DECIMAL(10,2) DEFAULT 0.00",
    'fineStatus' => "ALTER TABLE transactions ADD COLUMN fineStatus ENUM('pending','paid','waived') DEFAULT 'pending'",
    'finePaymentDate' => "ALTER TABLE transactions ADD COLUMN finePaymentDate DATE NULL",
    'finePaymentMethod' => "ALTER TABLE transactions ADD COLUMN finePaymentMethod ENUM('cash','online','card') NULL"
];

try {
    foreach ($fineColumns as $column => $sql) {
        $result = $conn->query("SHOW COLUMNS FROM transactions LIKE '$column'");
        if ($result->num_rows == 0) {
            $conn->query($sql);
            echo "<div class='success'>✓ Added column: $column</div>";
        } else {
            echo "<div class='info'>ℹ Column '$column' already exists</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 6.5: Add username column to users table if missing
// ==========================================
echo "<h2>Adding username column to users table...</h2>";

try {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
    if ($result->num_rows == 0) {
        // Add username column after userId
        $conn->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER userId");
        echo "<div class='success'>✓ Added username column to users table</div>";
        
        // Generate usernames for existing users based on emailId
        $users = $conn->query("SELECT userId, emailId FROM users WHERE username IS NULL OR username = ''");
        $updated = 0;
        while ($row = $users->fetch_assoc()) {
            // Extract username from email (part before @)
            $emailParts = explode('@', $row['emailId']);
            $baseUsername = $emailParts[0];
            
            // If username already exists, append userId
            $checkUsername = $conn->query("SELECT userId FROM users WHERE username = '$baseUsername'");
            if ($checkUsername->num_rows > 0) {
                $username = $baseUsername . '_' . substr($row['userId'], -3);
            } else {
                $username = $baseUsername;
            }
            
            $conn->query("UPDATE users SET username = '$username' WHERE userId = '{$row['userId']}'");
            $updated++;
        }
        echo "<div class='success'>✓ Generated usernames for $updated existing users</div>";
        
        // Make username unique
        $conn->query("ALTER TABLE users ADD UNIQUE KEY unique_username (username)");
        echo "<div class='success'>✓ Added unique constraint to username column</div>";
    } else {
        echo "<div class='info'>ℹ username column already exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

// ==========================================
// STEP 7: Update books table with barcode column
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Updating books table...</h2>";

try {
    $result = $conn->query("SHOW COLUMNS FROM books LIKE 'barcode'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE books ADD COLUMN barcode VARCHAR(255) NULL AFTER isbn");
        echo "<div class='success'>✓ Added barcode column to books table</div>";
        
        // Generate barcodes for existing books
        $books = $conn->query("SELECT isbn FROM books WHERE barcode IS NULL OR barcode = ''");
        $updated = 0;
        while ($row = $books->fetch_assoc()) {
            $cleanIsbn = str_replace('-', '', $row['isbn']);
            $barcode = 'BC' . $cleanIsbn;
            $conn->query("UPDATE books SET barcode = '$barcode' WHERE isbn = '{$row['isbn']}'");
            $updated++;
        }
        echo "<div class='success'>✓ Generated barcodes for $updated existing books</div>";
    } else {
        echo "<div class='info'>ℹ barcode column already exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 8: Create barcodes directory
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Creating upload directories...</h2>";

$directories = [
    APP_ROOT . '/public/uploads/books',
    APP_ROOT . '/public/uploads/barcodes',
    APP_ROOT . '/public/uploads/profiles',
    APP_ROOT . '/public/backups'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "<div class='success'>✓ Created directory: $dir</div>";
        } else {
            echo "<div class='error'>✗ Failed to create directory: $dir</div>";
        }
    } else {
        echo "<div class='info'>ℹ Directory exists: $dir</div>";
    }
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 9: Insert sample data (if tables are empty)
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Inserting sample data...</h2>";

try {
    // Check if notifications table is empty
    $count = $conn->query("SELECT COUNT(*) as count FROM notifications")->fetch_assoc()['count'];
    
    if ($count == 0) {
        $sampleNotifications = [
            [NULL, 'System Maintenance', 'System will undergo maintenance on Sunday', 'system', 'high'],
            ['STU001', 'Overdue Book', 'Your borrowed book is overdue', 'overdue', 'high'],
            ['STU002', 'Fine Paid', 'Your fine of LKR50 has been successfully paid', 'fine_paid', 'medium'],
            [NULL, 'Out of Stock', 'Book Harry Potter is out of stock', 'out_of_stock', 'medium'],
            ['FAC001', 'Borrow Approved', 'Your borrow request has been approved', 'approval', 'high']
        ];
        
        foreach ($sampleNotifications as $notif) {
            $userId = $notif[0] === NULL ? "NULL" : "'{$notif[0]}'";
            $conn->query("INSERT INTO notifications (userId, title, message, type, priority) 
                         VALUES ($userId, '{$notif[1]}', '{$notif[2]}', '{$notif[3]}', '{$notif[4]}')");
        }
        echo "<div class='success'>✓ Inserted sample notifications</div>";
    } else {
        echo "<div class='info'>ℹ Sample notifications already exist</div>";
    }
    
    // Insert sample maintenance logs
    $count = $conn->query("SELECT COUNT(*) as count FROM maintenance_log")->fetch_assoc()['count'];
    if ($count == 0) {
        $logs = [
            ['update_fines', 'Updated all overdue fines', 'ADMIN001', 'success'],
            ['optimize_database', 'Optimized all database tables', 'ADMIN001', 'success'],
            ['clear_cache', 'Cleared system cache files', 'ADMIN001', 'success'],
            ['check_overdue', 'Generated overdue notifications', 'system', 'success']
        ];
        
        foreach ($logs as $log) {
            $conn->query("INSERT INTO maintenance_log (action, description, performedBy, status) 
                         VALUES ('{$log[0]}', '{$log[1]}', '{$log[2]}', '{$log[3]}')");
        }
        echo "<div class='success'>✓ Inserted sample maintenance logs</div>";
    }
    
    // Insert sample backup logs
    $count = $conn->query("SELECT COUNT(*) as count FROM backup_log")->fetch_assoc()['count'];
    if ($count == 0) {
        $backups = [
            ['backup_2024_01_15.sql', '/backups/backup_2024_01_15.sql', 1024000, 'manual', 'ADMIN001'],
            ['backup_2024_01_20.sql', '/backups/backup_2024_01_20.sql', 1048576, 'scheduled', 'system'],
            ['backup_2024_01_25.sql', '/backups/backup_2024_01_25.sql', 2097152, 'manual', 'ADMIN001']
        ];
        
        foreach ($backups as $backup) {
            $conn->query("INSERT INTO backup_log (filename, filepath, filesize, backupType, createdBy) 
                         VALUES ('{$backup[0]}', '{$backup[1]}', {$backup[2]}, '{$backup[3]}', '{$backup[4]}')");
        }
        echo "<div class='success'>✓ Inserted sample backup logs</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: {$e->getMessage()}</div>";
}

updateProgress($currentStep, $totalSteps);

// ==========================================
// STEP 10: Verify all tables
// ==========================================
$currentStep++;
echo "<h2>Step {$currentStep}/{$totalSteps}: Verifying database structure...</h2>";

$requiredTables = [
    'users', 'books', 'transactions', 'borrow_requests', 
    'notifications', 'fine_settings', 'backup_log', 'maintenance_log'
];

echo "<table>";
echo "<tr><th>Table Name</th><th>Status</th><th>Row Count</th></tr>";

foreach ($requiredTables as $table) {
    $exists = $conn->query("SHOW TABLES LIKE '$table'")->num_rows > 0;
    if ($exists) {
        $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        echo "<tr><td>$table</td><td style='color:green'>✓ Exists</td><td>$count rows</td></tr>";
    } else {
        echo "<tr><td>$table</td><td style='color:red'>✗ Missing</td><td>-</td></tr>";
    }
}

echo "</table>";

updateProgress($totalSteps, $totalSteps);

// ==========================================
// Summary
// ==========================================
echo "<h2>✅ Database Schema Update Complete!</h2>";
echo "<div class='success'>";
echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li>✓ All required tables have been created/verified</li>";
echo "<li>✓ Fine management columns added to transactions table</li>";
echo "<li>✓ Barcode column added to books table</li>";
echo "<li>✓ Upload directories created</li>";
echo "<li>✓ Sample data inserted</li>";
echo "</ul>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Access phpMyAdmin to verify tables: <a href='http://localhost:8081' target='_blank'>http://localhost:8081</a></li>";
echo "<li>Insert more sample data: <a href='insertSampleData.php'>Insert Sample Data</a></li>";
echo "<li>Access admin dashboard: <a href='" . BASE_URL . "admin/dashboard'>Admin Dashboard</a></li>";
echo "</ol>";
echo "</div>";

$conn->close();
?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="insertSampleData.php" class="btn">📊 Insert More Sample Data</a>
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn">🎯 Go to Dashboard</a>
            <a href="http://localhost:8081" class="btn" target="_blank">🗄️ Open phpMyAdmin</a>
        </div>
    </div>
</body>
</html>
