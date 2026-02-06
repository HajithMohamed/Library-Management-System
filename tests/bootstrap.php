<?php

use App\Models\BaseModel;

// Set app root
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/src/public');

// Load autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Set test mode so models don't try to load dbConnection.php
$_ENV['TEST_MODE'] = true;
putenv('TEST_MODE=true');

// Load environment variables if needed (testing values are in phpunit.xml)
// But we might need some constants from config.php without connecting to the real DB
// Let's define some constants that might be expected
define('ADMIN_CODE', 'test_admin_code');
define('OTP_EXPIRY_MINUTES', 15);
define('BASE_URL', 'http://localhost/');

// We will handle the DB connection in a base test class or here
// For Unit tests, we will mock the DB.
// For Integration tests, we will initialize a PDO SQLite connection.

// Ensure logs directory exists for tests
$logsDir = APP_ROOT . '/logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0755, true);
}

// Setup error logging for tests
ini_set('error_log', $logsDir . '/test_error.log');
error_reporting(E_ALL);
