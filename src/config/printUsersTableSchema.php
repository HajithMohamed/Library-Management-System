<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dbConnection.php';
$conn = $GLOBALS['conn'];
$result = $conn->query('DESCRIBE users');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' ' . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
