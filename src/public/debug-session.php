<?php
session_start();

// Define APP_ROOT if not defined
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Include config if needed
if (!defined('BASE_URL')) {
    require_once APP_ROOT . '/config/config.php';
}

echo "<!DOCTYPE html><html><head><title>Debug Session</title></head><body>";
echo "<h1>Session Debug Information</h1>";
echo "<h2>Full Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Individual Session Variables:</h2>";
echo "<ul>";
echo "<li><strong>userId:</strong> " . (isset($_SESSION['userId']) ? $_SESSION['userId'] : 'NOT SET') . "</li>";
echo "<li><strong>user_id:</strong> " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "</li>";
echo "<li><strong>userType:</strong> " . (isset($_SESSION['userType']) ? $_SESSION['userType'] : 'NOT SET') . "</li>";
echo "<li><strong>user_type:</strong> " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'NOT SET') . "</li>";
echo "<li><strong>username:</strong> " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET') . "</li>";
echo "</ul>";

echo "<h2>All Session Keys:</h2>";
echo "<pre>";
print_r(array_keys($_SESSION));
echo "</pre>";

echo "<h2>Request Information:</h2>";
echo "<ul>";
echo "<li><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
echo "<li><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</li>";
echo "<li><strong>Query String:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'NONE') . "</li>";
echo "</ul>";

echo "<h2>GET Parameters:</h2>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "</body></html>";
?>
