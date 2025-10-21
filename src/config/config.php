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
    $port = $_SERVER['SERVER_PORT'] ?? '80';

    if ($host === 'localhost' && in_array($port, ['8080', '80'])) {
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

// Load .env file from project root
$envPath = APP_ROOT;
if (!file_exists($envPath . '/.env')) {
    // Try parent directory if not found
    $envPath = dirname(APP_ROOT);
}

if (file_exists($envPath . '/.env')) {
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->safeLoad();
}

// Helper function to get environment variable with fallback
function getEnvVar(string $key, $default = '') {
    // Check $_ENV first (Docker environment variables)
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    
    // Check getenv() (alternative method)
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    
    // Return default
    return $default;
}

// =========================
// Admin & Timezone
// =========================
define('ADMIN_CODE', getEnvVar('ADMIN_CODE', 'hello_world'));
define('OTP_EXPIRY_MINUTES', getEnvVar('OTP_EXPIRY_MINUTES', '15'));
date_default_timezone_set(getEnvVar('TZ', 'Asia/Kolkata'));

// =========================
// Database Configuration
// =========================
define('DB_HOST', getEnvVar('DB_HOST', 'db'));
define('DB_PORT', getEnvVar('DB_PORT', '3306'));
define('DB_USER', getEnvVar('DB_USER', 'library_user'));
define('DB_PASSWORD', getEnvVar('DB_PASSWORD', 'library_password'));
define('DB_NAME', getEnvVar('DB_NAME', 'integrated_library_system'));

// =========================
// MySQL Connection with Retry Logic
// =========================
function createDatabaseConnection($maxRetries = 10, $retryDelay = 2) {
    $retry = $maxRetries;
    $mysqli = null;
    $lastError = '';

    while ($retry > 0) {
        try {
            $mysqli = @new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASSWORD,
                DB_NAME,
                (int)DB_PORT
            );

            if ($mysqli->connect_errno === 0) {
                // Connection successful
                $mysqli->set_charset('utf8mb4');
                return $mysqli;
            }

            $lastError = "(" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            
        } catch (Exception $e) {
            $lastError = $e->getMessage();
        }

        $retry--;
        if ($retry > 0) {
            sleep($retryDelay);
        }
    }

    // Connection failed after all retries
    $errorMsg = "Database connection failed after $maxRetries attempts.\n";
    $errorMsg .= "Last error: $lastError\n";
    $errorMsg .= "Connection details:\n";
    $errorMsg .= "- Host: " . DB_HOST . "\n";
    $errorMsg .= "- Port: " . DB_PORT . "\n";
    $errorMsg .= "- Database: " . DB_NAME . "\n";
    $errorMsg .= "- User: " . DB_USER . "\n";
    
    die($errorMsg);
}

// Create database connection
$mysqli = createDatabaseConnection();

// =========================
// SMTP / Email Configuration
// =========================
define('SMTP_HOST', getEnvVar('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', getEnvVar('SMTP_PORT', '587'));
define('SMTP_USERNAME', getEnvVar('SMTP_USERNAME', 'youremail@gmail.com'));
define('SMTP_PASSWORD', getEnvVar('SMTP_PASSWORD', 'yourapppassword'));
define('SMTP_ENCRYPTION', getEnvVar('SMTP_ENCRYPTION', 'tls'));
define('SMTP_FROM_EMAIL', getEnvVar('SMTP_FROM_EMAIL', getEnvVar('SMTP_USERNAME', 'youremail@gmail.com')));
define('SMTP_FROM_NAME', getEnvVar('SMTP_FROM_NAME', 'Library Management System'));

// =========================
// SMS Gateway (Optional)
// =========================
define('SMS_API_URL', getEnvVar('SMS_API_URL', ''));
define('SMS_API_KEY', getEnvVar('SMS_API_KEY', ''));
define('SMS_SENDER_ID', getEnvVar('SMS_SENDER_ID', ''));

// =========================
// Debug Mode (Optional - Remove in production)
// =========================
if (getEnvVar('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}