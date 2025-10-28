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

// Initialize controllers
$authController = new App\Controllers\AuthController();
$facultyController = new App\Controllers\FacultyController();
$adminController = new App\Controllers\AdminController();

// Define routes
$routes = [
    // Public routes
    ['GET', '#^/$#', function() { include APP_ROOT . '/views/home.php'; }],
    ['GET|POST', '#^/login$#', [$authController, 'login']],
    ['GET|POST', '#^/signup$#', [$authController, 'signup']],
    ['GET|POST', '#^/verify-otp$#', [$authController, 'verifyOtp']],
    ['GET|POST', '#^/forgot-password$#', [$authController, 'forgotPassword']],
    ['GET|POST', '#^/reset-password$#', [$authController, 'resetPassword']],
    ['GET', '#^/logout$#', [$authController, 'logout']],
    
    // Admin routes
    ['GET', '#^/admin$#', [$adminController, 'dashboard']],
    ['GET|POST', '#^/admin/users$#', [$adminController, 'users']],
    ['GET|POST', '#^/admin/fines$#', [$adminController, 'fines']],
    ['GET|POST', '#^/admin/borrow-requests$#', [$adminController, 'borrowRequests']],
    ['GET|POST', '#^/admin/notifications$#', [$adminController, 'notifications']],
    ['GET|POST', '#^/admin/maintenance$#', [$adminController, 'maintenance']],
    ['GET|POST', '#^/admin/reports$#', [$adminController, 'reports']],
    ['GET|POST', '#^/admin/settings$#', [$adminController, 'settings']],
    ['GET', '#^/admin/analytics$#', [$adminController, 'analytics']],
    
    // Faculty/Student routes
    ['GET', '#^/faculty/books$#', [$facultyController, 'books']],
    ['GET', '#^/faculty/book/([^/]+)$#', function($isbn) use ($facultyController) {
        // Decode the ISBN in case it has special characters
        $isbn = urldecode($isbn);
        $facultyController->viewBook($isbn);
    }],
    // Simplified: Let controller handle ISBN extraction from both URL and query
    ['GET|POST', '#^/faculty/reserve(/([^/]+))?$#', function($fullMatch = '', $isbn = null) use ($facultyController) {
        $facultyController->reserve($isbn);
    }],
    ['GET|POST', '#^/faculty/book-request$#', [$facultyController, 'bookRequest']],
    ['GET', '#^/faculty/transactions$#', [$facultyController, 'transactions']],
    ['GET|POST', '#^/faculty/profile$#', [$facultyController, 'profile']],
    ['GET|POST', '#^/faculty/return$#', [$facultyController, 'returnBook']],
    ['GET', '#^/faculty/fines$#', [$facultyController, 'fines']],
    ['GET', '#^/faculty/borrow-history$#', [$facultyController, 'borrowHistory']],
    ['GET|POST', '#^/faculty/notifications$#', [$facultyController, 'notifications']],
];

// Route matching
$matched = false;
foreach ($routes as $route) {
    list($methods, $pattern, $handler) = $route;
    
    // Check if method matches
    if (!preg_match('#' . $requestMethod . '#', $methods)) {
        continue;
    }
    
    // Check if pattern matches
    if (preg_match($pattern, $requestUri, $matches)) {
        $matched = true;
        
        // Remove the full match from parameters
        array_shift($matches);
            // Handle controller method calls
            list($controller, $method) = $handler;
            call_user_func_array([$controller, $method], $matches);
        }
        
        exit;
    }
}
   call_user_func_array($handler, $matches);
// No route matched - 404} elseif (is_array($handler) && count($handler) === 2) {
if (!$matched) {/ Handle controller method calls
    http_response_code(404);       list($controller, $method) = $handler;
    include APP_ROOT . '/views/errors/404.php';           call_user_func_array([$controller, $method], $matches);
}// No route matched - 404
if (!$matched) {
    http_response_code(404);
    include APP_ROOT . '/views/errors/404.php';
}