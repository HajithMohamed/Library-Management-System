<?php
include_once('config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Adding Barcode Column to Books Table...</h2>";

// Check if barcode column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM books LIKE 'barcode'");

if ($checkColumn->num_rows == 0) {
    // Add barcode column
    $sql = "ALTER TABLE books ADD COLUMN barcode VARCHAR(255) NULL AFTER isbn";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ Barcode column added successfully</p>";
        
        // Generate barcodes for existing books
        $result = $conn->query("SELECT isbn, bookName FROM books WHERE barcode IS NULL OR barcode = ''");
        $updated = 0;
        
        while ($row = $result->fetch_assoc()) {
            $barcode = 'BC' . str_replace('-', '', $row['isbn']);
            $updateSql = "UPDATE books SET barcode = '$barcode' WHERE isbn = '{$row['isbn']}'";
            if ($conn->query($updateSql)) {
                $updated++;
            }
        }
        
        echo "<p style='color:green'>✓ Generated barcodes for $updated existing books</p>";
    } else {
        echo "<p style='color:red'>✗ Error adding barcode column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:orange'>⚠ Barcode column already exists</p>";
}

// Create barcodes directory if it doesn't exist
$barcodeDir = APP_ROOT . '/public/uploads/barcodes';
if (!file_exists($barcodeDir)) {
    if (mkdir($barcodeDir, 0777, true)) {
        echo "<p style='color:green'>✓ Barcodes directory created</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create barcodes directory</p>";
    }
} else {
    echo "<p style='color:blue'>ℹ Barcodes directory already exists</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p><a href='../admin/books'>Go to Books Management</a></p>";

$conn->close();
?>
