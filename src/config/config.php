<?php
// Prevent multiple inclusions
if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

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

if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', APP_ROOT . '/public');
}

if (!defined('DIR_URL')) {
    define('DIR_URL', APP_ROOT . '/');
}

// =========================
// Base URL
// =========================
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $port = $_SERVER['SERVER_PORT'] ?? '80';

    // Docker-specific URL handling
    if (file_exists('/.dockerenv') || isset($_ENV['DOCKER_CONTAINER'])) {
        // Running in Docker
        if (isset($_ENV['DOCKER_HOST'])) {
            $host = $_ENV['DOCKER_HOST'];
        }
        
        // Handle common Docker port mappings
        if ($port === '80' || $port === '8080') {
            define('BASE_URL', $protocol . "://" . $host . "/");
        } else {
            define('BASE_URL', $protocol . "://" . $host . ":" . $port . "/");
        }
    } else {
        // Local development
        if ($host === 'localhost' && in_array($port, ['8080', '80'])) {
            define('BASE_URL', "http://localhost:8080/");
        } else {
            define('BASE_URL', $protocol . "://$host" . ($port !== '80' ? ":" . $port : "") . "/");
        }
    }
}

// =========================
// Load Composer Autoloader & Dotenv (only once)
// =========================
if (!class_exists('Dotenv\Dotenv')) {
    $autoloadPath = APP_ROOT . '/vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        die("Composer autoload not found! Run 'composer install' in " . APP_ROOT);
    }
    require_once $autoloadPath;
}

use Dotenv\Dotenv;

// Load .env file from project root (only if not already loaded)
if (!function_exists('getEnvVar')) {
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
}

// =========================
// Admin & Timezone
// =========================
if (!defined('ADMIN_CODE')) {
    define('ADMIN_CODE', getEnvVar('ADMIN_CODE', 'hello_world'));
}

if (!defined('OTP_EXPIRY_MINUTES')) {
    define('OTP_EXPIRY_MINUTES', getEnvVar('OTP_EXPIRY_MINUTES', '15'));
}

date_default_timezone_set(getEnvVar('TZ', 'Asia/Kolkata'));

// =========================
// Database Configuration
// =========================
if (!defined('DB_HOST')) {
    define('DB_HOST', getEnvVar('DB_HOST', 'db'));
}

if (!defined('DB_PORT')) {
    define('DB_PORT', getEnvVar('DB_PORT', '3306'));
}

if (!defined('DB_USER')) {
    define('DB_USER', getEnvVar('DB_USER', 'library_user'));
}

if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', getEnvVar('DB_PASSWORD', 'library_password'));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', getEnvVar('DB_NAME', 'integrated_library_system'));
}

// =========================
// MySQL Connection with Retry Logic
// =========================
if (!function_exists('createDatabaseConnection')) {
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
}

// Create database connection (only if not already created)
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $mysqli = createDatabaseConnection();
}

// Store in GLOBALS for easy access
$GLOBALS['mysqli'] = $mysqli;

// =========================
// SMTP / Email Configuration
// =========================
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getEnvVar('SMTP_HOST', 'smtp.gmail.com'));
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', getEnvVar('SMTP_PORT', '587'));
}

if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', getEnvVar('SMTP_USERNAME', 'youremail@gmail.com'));
}

if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', getEnvVar('SMTP_PASSWORD', 'yourapppassword'));
}

if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', getEnvVar('SMTP_ENCRYPTION', 'tls'));
}

if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', getEnvVar('SMTP_FROM_EMAIL', getEnvVar('SMTP_USERNAME', 'youremail@gmail.com')));
}

if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', getEnvVar('SMTP_FROM_NAME', 'Library Management System'));
}

// =========================
// SMS Gateway (Optional)
// =========================
if (!defined('SMS_API_URL')) {
    define('SMS_API_URL', getEnvVar('SMS_API_URL', ''));
}

if (!defined('SMS_API_KEY')) {
    define('SMS_API_KEY', getEnvVar('SMS_API_KEY', ''));
}

if (!defined('SMS_SENDER_ID')) {
    define('SMS_SENDER_ID', getEnvVar('SMS_SENDER_ID', ''));
}

// =========================
// Debug Mode (Optional - Remove in production)
// =========================
if (getEnvVar('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}