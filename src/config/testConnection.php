<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';

echo "<h1>Database Connection Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";

// Test 1: Check if $mysqli exists
if (isset($mysqli) && $mysqli instanceof mysqli) {
    echo "<p class='success'>✓ \$mysqli connection exists</p>";
} else {
    echo "<p class='error'>✗ \$mysqli connection missing</p>";
    die();
}

// Test 2: Check connection
if ($mysqli->ping()) {
    echo "<p class='success'>✓ Database connection is alive</p>";
} else {
    echo "<p class='error'>✗ Database connection failed</p>";
    echo "<p>Error: " . $mysqli->connect_error . "</p>";
    die();
}

// Test 3: Check database
$result = $mysqli->query("SELECT DATABASE() as db");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p class='success'>✓ Connected to database: <strong>" . $row['db'] . "</strong></p>";
} else {
    echo "<p class='error'>✗ Cannot determine database</p>";
}

// Test 4: Check books table
$result = $mysqli->query("SELECT COUNT(*) as count FROM books");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p class='success'>✓ Books table exists with <strong>" . $row['count'] . "</strong> records</p>";
} else {
    echo "<p class='error'>✗ Cannot access books table: " . $mysqli->error . "</p>";
}

// Test 5: Check all tables
echo "<h2>Available Tables:</h2>";
$result = $mysqli->query("SHOW TABLES");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='error'>Cannot list tables</p>";
}

echo "<hr>";
echo "<h2>Configuration:</h2>";
echo "<ul>";
echo "<li>DB_HOST: " . DB_HOST . "</li>";
echo "<li>DB_PORT: " . DB_PORT . "</li>";
echo "<li>DB_NAME: " . DB_NAME . "</li>";
echo "<li>DB_USER: " . DB_USER . "</li>";
echo "<li>BASE_URL: " . BASE_URL . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='" . BASE_URL . "admin/books'>Go to Books Page</a></p>";
?>
