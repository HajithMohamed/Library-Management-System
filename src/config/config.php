<?php
// Define application paths (APP_ROOT is already defined in index.php)
// When running scripts outside the front controller (e.g., CI), resolve to project root
if (!defined('APP_ROOT')) {
  $defaultAppRoot = dirname(__DIR__); // typically .../src
  $projectRootCandidate = dirname($defaultAppRoot); // move up to project root
  if (file_exists($projectRootCandidate . '/vendor/autoload.php')) {
    define('APP_ROOT', $projectRootCandidate);
  } else {
    define('APP_ROOT', $defaultAppRoot);
  }
}
define('PUBLIC_ROOT', APP_ROOT . '/public');

// Base URL configuration
if (!defined('BASE_URL')) {
  if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];

    // For Docker setup
    if ($host === 'localhost' && $port === '8080') {
      define("BASE_URL", "http://localhost:8080/");
    } else {
      define("BASE_URL", $protocol . "://" . $host . "/");
    }
  } else {
    define("BASE_URL", "http://localhost:8080/");
  }
}

define("DIR_URL", APP_ROOT . "/");

// Load environment variables using Dotenv
// Include autoloader if not already loaded
if (!class_exists('Dotenv\Dotenv')) {
    $autoloadPath = APP_ROOT . '/vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }
}

use Dotenv\Dotenv;

// Load .env file from project root
$dotenv = Dotenv::createImmutable(dirname(APP_ROOT));
$dotenv->safeLoad();

// Helper function to get environment variable with fallback
function getEnvVar($key, $default = '') {
    return $_ENV[$key] ?? $default;
}

define("ADMIN_CODE", getEnvVar('ADMIN_CODE', 'hello_world'));
// Replace the Admin Registration Code above in place of hello_world to register as an admin in the Library

date_default_timezone_set(getEnvVar('TZ', 'Asia/Kolkata'));
// Change the default timezone above if you want the app to run in a different time zone

// Database configuration for Docker
define("DB_HOST", getEnvVar('DB_HOST', 'db'));
define("DB_PORT", getEnvVar('DB_PORT', '3306'));
define("DB_USER", getEnvVar('DB_USER', 'library_user'));
define("DB_PASSWORD", getEnvVar('DB_PASSWORD', 'library_password'));
define("DB_NAME", getEnvVar('DB_NAME', 'integrated_library_system'));

// Outbound Email via PHPMailer (SMTP)
define("SMTP_HOST", getEnvVar('SMTP_HOST', 'smtp.gmail.com'));
define("SMTP_PORT", getEnvVar('SMTP_PORT', '587'));
define("SMTP_USERNAME", getEnvVar('SMTP_USERNAME', 'your full Gmail address')); // e.g., your full Gmail address
define("SMTP_PASSWORD", getEnvVar('SMTP_PASSWORD', 'Gmail App Password'));  // e.g., Gmail App Password
define("SMTP_ENCRYPTION", getEnvVar('SMTP_ENCRYPTION', 'tls')); // ssl or tls
define("SMTP_FROM_EMAIL", getEnvVar('SMTP_FROM_EMAIL', 'your full Gmail address'));
define("SMTP_FROM_NAME", getEnvVar('SMTP_FROM_NAME', 'Library Management System University of Ruhuna'));

// SMS Gateway placeholders (used for OTP link to mobile). Leave blank to disable
define("SMS_API_URL", getEnvVar('SMS_API_URL', '')); // e.g., https://api.textlocal.in/send/
define("SMS_API_KEY", getEnvVar('SMS_API_KEY', ''));
define("SMS_SENDER_ID", getEnvVar('SMS_SENDER_ID', ''));
