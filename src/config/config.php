<?php
// =========================
// Application Paths
// =========================
if (!defined('APP_ROOT')) {
    $defaultAppRoot = dirname(__DIR__); // usually .../src
    $projectRootCandidate = dirname($defaultAppRoot);
    if (file_exists($projectRootCandidate . '/vendor/autoload.php')) {
        define('APP_ROOT', $projectRootCandidate);
    } else {
        define('APP_ROOT', $defaultAppRoot);
    }
}
define('PUBLIC_ROOT', APP_ROOT . '/public');
define('DIR_URL', APP_ROOT . '/');

// =========================
// Base URL
// =========================
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $port = $_SERVER['SERVER_PORT'] ?? '8080';

    if ($host === 'localhost' && $port === '8080') {
        define('BASE_URL', "http://localhost:8080/");
    } else {
        define('BASE_URL', $protocol . "://$host/");
    }
}

// =========================
// Load Composer Autoloader & Dotenv
// =========================
$autoloadPath = APP_ROOT . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Composer autoload not found! Run 'composer install' in " . APP_ROOT);
}
require_once $autoloadPath;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(APP_ROOT);
$dotenv->safeLoad();

// Helper function to get environment variable with fallback
function getEnvVar(string $key, $default = '') {
    return $_ENV[$key] ?? $default;
}

// =========================
// Admin & Timezone
// =========================
define('ADMIN_CODE', getEnvVar('ADMIN_CODE', 'hello_world'));
date_default_timezone_set(getEnvVar('TZ', 'Asia/Kolkata'));

// =========================
// Database Configuration
// =========================
define('DB_HOST', getEnvVar('DB_HOST', 'db'));
define('DB_PORT', getEnvVar('DB_PORT', '3306')); // MySQL port inside Docker
define('DB_USER', getEnvVar('DB_USER', 'library_user'));
define('DB_PASSWORD', getEnvVar('DB_PASSWORD', 'library_password'));
define('DB_NAME', getEnvVar('DB_NAME', 'integrated_library_system'));

// =========================
// MySQL Connection with Retry
// =========================
$retry = 10; // number of retries
$mysqli = null;

while ($retry > 0) {
    $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, (int)DB_PORT);
    if ($mysqli && $mysqli->connect_errno === 0) {
        break; // connected successfully
    }
    $retry--;
    sleep(2); // wait 2 seconds before retry
}

if (!$mysqli || $mysqli->connect_errno) {
    die("Database connection failed: (" . ($mysqli->connect_errno ?? 'N/A') . ") " . ($mysqli->connect_error ?? 'N/A'));
}

// Ensure UTF-8 encoding
$mysqli->set_charset('utf8mb4');

// =========================
// SMTP / Email Configuration
// =========================
define('SMTP_HOST', getEnvVar('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', getEnvVar('SMTP_PORT', '587'));
define('SMTP_USERNAME', getEnvVar('SMTP_USERNAME', 'hanoufaatif@gmail.com'));
define('SMTP_PASSWORD', getEnvVar('SMTP_PASSWORD', 'crvhjrwgktwozfhv'));
define('SMTP_ENCRYPTION', getEnvVar('SMTP_ENCRYPTION', 'tls'));
define('SMTP_FROM_EMAIL', getEnvVar('SMTP_FROM_EMAIL', SMTP_USERNAME));
define('SMTP_FROM_NAME', getEnvVar('SMTP_FROM_NAME', 'Library Management System University of Ruhuna'));

// =========================
// SMS Gateway (Optional)
// =========================
define('SMS_API_URL', getEnvVar('SMS_API_URL', ''));
define('SMS_API_KEY', getEnvVar('SMS_API_KEY', ''));
define('SMS_SENDER_ID', getEnvVar('SMS_SENDER_ID', ''));
