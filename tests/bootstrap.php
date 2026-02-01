<?php

use App\Models\BaseModel;

// Set app root
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/src/public');

// Load autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables if needed (testing values are in phpunit.xml)
// But we might need some constants from config.php without connecting to the real DB
// Let's define some constants that might be expected
define('ADMIN_CODE', 'test_admin_code');
define('OTP_EXPIRY_MINUTES', 15);
define('BASE_URL', 'http://localhost/');

// We will handle the DB connection in a base test class or here
// For Unit tests, we will mock the DB.
// For Integration tests, we will initialize a PDO SQLite connection.

// Setup error logging for tests
ini_set('error_log', APP_ROOT . '/logs/test_error.log');
error_reporting(E_ALL);
