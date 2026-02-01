<?php
require_once __DIR__ . '/src/config/config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully\n";

$tables = ['e_resources', 'user_eresources', 'users', 'books'];

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "Table '$table' exists.\n";
    } else {
        echo "Table '$table' does NOT exist.\n";
    }
}

mysqli_close($conn);
