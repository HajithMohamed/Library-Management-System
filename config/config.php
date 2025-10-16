<?php
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    // Running locally (XAMPP)
    define("BASE_URL", "http://localhost/Integrated-Library-System/");
    define("DIR_URL", $_SERVER['DOCUMENT_ROOT'] . "/Integrated-Library-System/");

    define("DB_HOST", "localhost");
    define("DB_PORT", "3307"); // XAMPP MySQL port
    define("DB_USER", "root");
    define("DB_PASSWORD", "");
    define("DB_NAME", "integrated_library_system");
} else {
    // Running inside Docker container
    define("BASE_URL", "http://localhost:8080/");
    define("DIR_URL", "/var/www/html/");

    define("DB_HOST", "db");          // Docker MySQL service
    define("DB_PORT", "3306");         // MySQL default port
    define("DB_USER", "user");         // Must match docker-compose
    define("DB_PASSWORD", "user123");  // Must match docker-compose
    define("DB_NAME", "library_db");   // Must match docker-compose
}

// Common constants
define("ADMIN_CODE", "hello_world");
date_default_timezone_set('Asia/Kolkata');

// v2.0 security and flows
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600); // 1 hour
if (!defined('OTP_EXPIRY_MINUTES')) define('OTP_EXPIRY_MINUTES', 10);

// PHPMailer SMTP
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 587);

define("SMTP_USERNAME", "your full Gmail address"); // e.g., your full Gmail address
define("SMTP_PASSWORD", "Gmail App Password");  // e.g., Gmail App Password
define("SMTP_ENCRYPTION", "tls"); // ssl or tls
define("SMTP_FROM_EMAIL", "your full Gmail address");
define("SMTP_FROM_NAME", "Library managemene system university of Ruhuna");

// SMS Gateway placeholders
define("SMS_API_URL", "");
define("SMS_API_KEY", "");
define("SMS_SENDER_ID", "");
?>

