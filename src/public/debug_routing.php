<?php
// Debug routing issues
echo "Debug Information:\n";
echo "==================\n";

echo "1. Current URL: " . $_SERVER['REQUEST_URI'] . "\n";
echo "2. Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "3. Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "4. Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "5. Path Info: " . ($_SERVER['PATH_INFO'] ?? 'Not set') . "\n";

echo "\n6. BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'Not defined') . "\n";
echo "7. APP_ROOT: " . (defined('APP_ROOT') ? APP_ROOT : 'Not defined') . "\n";

echo "\n8. Session Data:\n";
session_start();
if (isset($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        echo "   {$key}: {$value}\n";
    }
} else {
    echo "   No session data\n";
}

echo "\n9. POST Data:\n";
if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        echo "   {$key}: {$value}\n";
    }
} else {
    echo "   No POST data\n";
}

echo "\n10. GET Data:\n";
if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        echo "   {$key}: {$value}\n";
    }
} else {
    echo "   No GET data\n";
}
?>
