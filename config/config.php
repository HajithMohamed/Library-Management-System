<?php
// Lightweight .env loader (no Composer). Loads keys as getenv/$_ENV.
(function () {
    $root = dirname(__DIR__);
    $envPath = $root . '/.env';
    if (!file_exists($envPath)) return;
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $k = trim($k);
        // Strip optional quotes
        $v = trim($v);
        if ($v !== '' && ($v[0] === '"' || $v[0] === "'")) $v = trim($v, "\"'");
        if ($k !== '') {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
})();

// Helper to read env with default
function envd($key, $default) {
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    // Running locally (XAMPP)
    define("BASE_URL", envd("BASE_URL", "http://localhost/Integrated-Library-System/"));
    define("DIR_URL", envd("DIR_URL", $_SERVER['DOCUMENT_ROOT'] . "/Integrated-Library-System/"));

    define("DB_HOST", envd("DB_HOST", "localhost"));
    define("DB_PORT", envd("DB_PORT", "3307")); // Always use 3307 unless overridden
    define("DB_USER", envd("DB_USER", "root"));
    define("DB_PASSWORD", envd("DB_PASSWORD", ""));
    define("DB_NAME", envd("DB_NAME", "integrated_library_system"));
} else {
    // Running inside Docker/container or hosted
    define("BASE_URL", envd("BASE_URL", "http://localhost:8080/"));
    define("DIR_URL", envd("DIR_URL", "/var/www/html/"));

    define("DB_HOST", envd("DB_HOST", "db"));
    define("DB_PORT", envd("DB_PORT", "3307")); // Always use 3307 unless overridden
    define("DB_USER", envd("DB_USER", "user"));
    define("DB_PASSWORD", envd("DB_PASSWORD", "user123"));
    define("DB_NAME", envd("DB_NAME", "library_db"));
}

// Common constants
define("ADMIN_CODE", envd("ADMIN_CODE", "hello_world"));
date_default_timezone_set(envd('TZ', 'Asia/Kolkata'));

// v2.0 security and flows
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', (int)envd('SESSION_TIMEOUT', 3600)); // 1 hour
if (!defined('OTP_EXPIRY_MINUTES')) define('OTP_EXPIRY_MINUTES', (int)envd('OTP_EXPIRY_MINUTES', 10));

// PHPMailer SMTP (keep env override if present)
define("SMTP_HOST", envd("SMTP_HOST", "smtp.gmail.com"));
define("SMTP_PORT", (int)envd("SMTP_PORT", 587));
define("SMTP_USERNAME", envd("SMTP_USERNAME", "yourgmail.com"));
define("SMTP_PASSWORD", envd("SMTP_PASSWORD", "your app passs"));  // Gmail App Password
define("SMTP_ENCRYPTION", envd("SMTP_ENCRYPTION", "tls"));
define("SMTP_FROM_EMAIL", envd("SMTP_FROM_EMAIL", "your@gmail.com"));
define("SMTP_FROM_NAME", envd("SMTP_FROM_NAME", "Library Management System - University of Ruhuna"));

// SMS Gateway placeholders
define("SMS_API_URL", envd("SMS_API_URL", ""));
define("SMS_API_KEY", envd("SMS_API_KEY", ""));
define("SMS_SENDER_ID", envd("SMS_SENDER_ID", ""));

// DO NOT create a mysqli connection here!
// Only define constants and environment logic.
?>

