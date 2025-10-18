<?php
// Define application paths
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . '/public');

// Base URL configuration
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

define("DIR_URL", APP_ROOT . "/");

define("ADMIN_CODE","hello_world");
// Replace the Admin Registration Code above in place of hello_world to register as an admin in the Library

date_default_timezone_set('Asia/Kolkata');
// Change the default timezone above if you want the app to run in a different time zone

// Database configuration for Docker
define("DB_HOST", "db");
define("DB_PORT", "3306");
define("DB_USER", "library_user");
define("DB_PASSWORD", "library_password");
define("DB_NAME", "integrated_library_system");

// Outbound Email via PHPMailer (SMTP)
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 587);
define("SMTP_USERNAME", "your full Gmail address"); // e.g., your full Gmail address
define("SMTP_PASSWORD", "Gmail App Password");  // e.g., Gmail App Password
define("SMTP_ENCRYPTION", "tls"); // ssl or tls
define("SMTP_FROM_EMAIL", "your full Gmail address");
define("SMTP_FROM_NAME", "Library managemene system university of Ruhuna");

// SMS Gateway placeholders (used for OTP link to mobile). Leave blank to disable
define("SMS_API_URL", ""); // e.g., https://api.textlocal.in/send/
define("SMS_API_KEY", "");
define("SMS_SENDER_ID", "");
?>

