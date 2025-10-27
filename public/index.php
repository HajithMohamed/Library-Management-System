<?php
/**
 * Integrated Library System - Public Entry Point
 * This file handles all incoming requests and routes them to the appropriate controllers
 */

// Autoload classes
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Start session
session_start();

// Database connection
$database = new App\Config\Database();
$conn = $database->getConnection();

// Define constants
define('APP_ROOT', dirname(__DIR__));
define('BASE_URL', $_ENV['BASE_URL'] ?? '/');
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? '');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? '');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? '');
define('OTP_EXPIRY_MINUTES', 15);

// Route the request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Instantiate controllers
$authController = new AuthController();
$adminController = new AdminController();
$facultyController = new FacultyController(); // Make sure this line exists

// Public routes
if (preg_match('#^/$#', $requestUri)) {
    // Home page
    include APP_ROOT . '/views/home.php';
} elseif (preg_match('#^/login$#', $requestUri)) {
    $authController->login();
} elseif (preg_match('#^/signup$#', $requestUri)) {
    $authController->signup();
} elseif (preg_match('#^/verify-otp$#', $requestUri)) {
    $authController->verifyOtp();
} elseif (preg_match('#^/forgot-password$#', $requestUri)) {
    $authController->forgotPassword();
} elseif (preg_match('#^/reset-password$#', $requestUri)) {
    $authController->resetPassword();
} elseif (preg_match('#^/logout$#', $requestUri)) {
    $authController->logout();
} elseif (preg_match('#^/admin$#', $requestUri)) {
    $adminController->dashboard();
} elseif (preg_match('#^/admin/users$#', $requestUri)) {
    $adminController->users();
} elseif (preg_match('#^/admin/fines$#', $requestUri)) {
    $adminController->fines();
} elseif (preg_match('#^/admin/borrow-requests$#', $requestUri)) {
    $adminController->borrowRequests();
} elseif (preg_match('#^/admin/notifications$#', $requestUri)) {
    $adminController->notifications();
} elseif (preg_match('#^/admin/maintenance$#', $requestUri)) {
    $adminController->maintenance();
} elseif (preg_match('#^/admin/reports$#', $requestUri)) {
    $adminController->reports();
} elseif (preg_match('#^/admin/settings$#', $requestUri)) {
    $adminController->settings();
} elseif (preg_match('#^/admin/analytics$#', $requestUri)) {
    $adminController->analytics();
}

// Faculty/Student routes
if (preg_match('#^/faculty/books$#', $requestUri)) {
    $facultyController->books();
} elseif (preg_match('#^/faculty/book/([^/]+)$#', $requestUri, $matches)) {
    $facultyController->viewBook($matches[1]);
} elseif (preg_match('#^/faculty/reserve$#', $requestUri)) {
    // Handle query parameter: ?isbn=xxx
    $isbn = $_GET['isbn'] ?? null;
    $facultyController->reserve($isbn);
} elseif (preg_match('#^/faculty/reserve/([^/]+)$#', $requestUri, $matches)) {
    // Handle path parameter: /faculty/reserve/isbn
    $facultyController->reserve($matches[1]);
} elseif (preg_match('#^/faculty/book-request$#', $requestUri)) {
    $facultyController->bookRequest();
} elseif (preg_match('#^/faculty/dashboard$#', $requestUri)) {
    $facultyController->dashboard();
} elseif (preg_match('#^/faculty/transactions$#', $requestUri)) {
    $facultyController->transactions();
} elseif (preg_match('#^/faculty/profile$#', $requestUri)) {
    $facultyController->profile();
}

// Fallback for 404
http_response_code(404);
include APP_ROOT . '/views/errors/404.php';