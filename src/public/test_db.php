<?php
// Set a long timeout to see if the connection is timing out
ini_set('default_socket_timeout', 15);
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

// We can't include the full config because it calls `die()`
// So we manually load the environment variables here.
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$envPath = dirname(__DIR__, 2); // Project root
if (file_exists($envPath . '/.env')) {
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->safeLoad();
}

function getEnvVar(string $key, $default = '') {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    $value = getenv($key);
    if ($value !== false && $value !== '') return $value;
    return $default;
}

// --- Manually get database credentials from environment ---
$host = getEnvVar('DB_HOST', 'db');
$user = getEnvVar('DB_USER', 'library_user');
$pass = getEnvVar('DB_PASSWORD', 'library_password');
$db   = getEnvVar('DB_NAME', 'integrated_library_system');
$port = (int)getEnvVar('DB_PORT', '3306');

echo "<p><strong>Attempting to connect with the following details:</strong></p>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . htmlspecialchars($host) . "</li>";
echo "<li><strong>Port:</strong> " . htmlspecialchars($port) . "</li>";
echo "<li><strong>Database:</strong> " . htmlspecialchars($db) . "</li>";
echo "<li><strong>User:</strong> " . htmlspecialchars($user) . "</li>";
echo "</ul><hr>";

try {
    // Use mysqli to connect, with error reporting enabled
    $mysqli = @new mysqli($host, $user, $pass, $db, $port);

    if ($mysqli->connect_error) {
        echo "<h2><font color='red'>Connection Failed!</font></h2>";
        echo "<p><strong>Error Number:</strong> " . $mysqli->connect_errno . "</p>";
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($mysqli->connect_error) . "</p>";
    } else {
        echo "<h2><font color='green'>Connection Successful!</font></h2>";
        echo "<p>PHP connected to the MySQL database successfully.</p>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<h2><font color='red'>A critical error occurred!</font></h2>";
    echo "<p><strong>Exception Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

?>
