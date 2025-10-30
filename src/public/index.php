<?php

/**
 * Front Controller - Entry point for all requests
 * Routes requests to appropriate controllers based on URL patterns
 */

// Start session
session_start();



// Define application paths (needed before including config)
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);

// FORCE DEBUG MODE - ENABLE ALL ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/logs/error.log');

// Log startup
error_log("=== Application Starting ===");
error_log("APP_ROOT: " . APP_ROOT);
error_log("PUBLIC_ROOT: " . PUBLIC_ROOT);

// Include Composer autoloader - FIXED PATH (use root vendor, not src/vendor)
$vendorPath = dirname(APP_ROOT) . '/vendor/autoload.php';
if (file_exists($vendorPath)) {
  require_once $vendorPath;
  error_log("Composer autoloader loaded from: {$vendorPath}");
} else {
  error_log("WARNING: Composer autoloader not found at {$vendorPath}");
  error_log("Please run 'composer install' in the project root.");
}


// Include configuration (this creates $mysqli)
require_once APP_ROOT . '/config/config.php';
error_log("Config loaded");

// Include dbConnection for backwards compatibility (creates $conn alias)
require_once APP_ROOT . '/config/dbConnection.php';
error_log("DB Connection loaded");

// Verify connection
if (!$mysqli || !($mysqli instanceof mysqli)) {
  error_log("ERROR: Database connection failed - mysqli not initialized");
  die("Database connection failed in index.php");
}

if (!$conn || !($conn instanceof mysqli)) {
  error_log("ERROR: Database connection failed - conn not initialized");
  die("Database connection failed - conn alias not created");
}

error_log("Database connections verified (mysqli and conn)");

use App\Router\Router;



// Initialize router and define routes
$router = new Router();

// Home page routes
$router->addRoute('GET', '/', 'HomeController', 'index');
$router->addRoute('GET', '/about', 'HomeController', 'about');
$router->addRoute('GET', '/contact', 'HomeController', 'contact');
$router->addRoute('GET', '/library', 'HomeController', 'library');

// Authentication routes
$router->addRoute('GET', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('GET', '/signup', 'AuthController', 'signup');
$router->addRoute('POST', '/signup', 'AuthController', 'signup');
$router->addRoute('GET', '/verify-otp', 'AuthController', 'verifyOtp');
$router->addRoute('POST', '/verify-otp', 'AuthController', 'verifyOtp');
$router->addRoute('GET', '/forgot-password', 'AuthController', 'forgotPassword');
$router->addRoute('POST', '/forgot-password', 'AuthController', 'forgotPassword');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

// User dashboard routes
$router->addRoute('GET', '/user/dashboard', 'UserController', 'dashboard');
$router->addRoute('GET', '/user/profile', 'UserController', 'profile');
$router->addRoute('POST', '/user/profile', 'UserController', 'updateProfile');
$router->addRoute('POST', '/user/change-password', 'UserController', 'changePassword');

// User book management routes
$router->addRoute('GET', '/user/books', 'BookController', 'userBooks');
$router->addRoute('GET', '/user/borrow', 'BookController', 'borrow');
$router->addRoute('POST', '/user/borrow', 'BookController', 'borrowBook');
$router->addRoute('GET', '/user/return', 'BookController', 'return');
$router->addRoute('POST', '/user/return', 'BookController', 'returnBook');

// User fines routes
$router->addRoute('GET', '/user/fines', 'UserController', 'fines');
$router->addRoute('POST', '/user/fines', 'UserController', 'payFine');

// User notifications routes
$router->addRoute('GET', '/user/notifications', 'UserController', 'notifications');
$router->addRoute('POST', '/user/notifications', 'UserController', 'notifications');

// Student routes
$router->addRoute('GET', '/student/dashboard', 'UserController', 'dashboard');
$router->addRoute('GET', '/student/profile', 'UserController', 'profile');
$router->addRoute('POST', '/student/profile', 'UserController', 'updateProfile');
$router->addRoute('POST', '/student/change-password', 'UserController', 'changePassword');

// Faculty routes
$router->addRoute('GET', '/faculty/dashboard', 'FacultyController', 'dashboard');
$router->addRoute('GET', '/faculty/books', 'FacultyController', 'books');
$router->addRoute('GET', '/faculty/book/{isbn}', 'FacultyController', 'viewBook');
$router->addRoute('GET', '/faculty/fines', 'FacultyController', 'fines');
$router->addRoute('POST', '/faculty/fines', 'FacultyController', 'fines');
$router->addRoute('GET', '/faculty/return', 'FacultyController', 'returnBook');
$router->addRoute('POST', '/faculty/return', 'FacultyController', 'returnBook');
$router->addRoute('GET', '/faculty/search', 'FacultyController', 'search');
$router->addRoute('POST', '/faculty/reserve/{isbn}', 'FacultyController', 'reserve');
$router->addRoute('GET', '/faculty/reserve/{isbn}', 'FacultyController', 'reserve');
$router->addRoute('GET', '/faculty/borrow-history', 'FacultyController', 'borrowHistory');
$router->addRoute('GET', '/faculty/profile', 'FacultyController', 'profile');
$router->addRoute('POST', '/faculty/profile', 'FacultyController', 'profile');
$router->addRoute('GET', '/faculty/feedback', 'FacultyController', 'feedback');
$router->addRoute('POST', '/faculty/feedback', 'FacultyController', 'feedback');
$router->addRoute('GET', '/faculty/book-request', 'FacultyController', 'bookRequest');
$router->addRoute('POST', '/faculty/book-request', 'FacultyController', 'bookRequest');
$router->addRoute('GET', '/faculty/notifications', 'FacultyController', 'notifications');
$router->addRoute('POST', '/faculty/notifications', 'FacultyController', 'notifications');

// Admin routes
$router->addRoute('GET', '/admin/dashboard', 'AdminController', 'dashboard');
$router->addRoute('GET', '/admin/users', 'AdminController', 'users');
$router->addRoute('POST', '/admin/users/add', 'AdminController', 'addUser');
$router->addRoute('POST', '/admin/users/edit', 'AdminController', 'editUser');
$router->addRoute('POST', '/admin/users/delete', 'AdminController', 'deleteUser');
$router->addRoute('GET', '/admin/reports', 'AdminController', 'reports');
$router->addRoute('GET', '/admin/settings', 'AdminController', 'settings');
$router->addRoute('POST', '/admin/settings', 'AdminController', 'updateSettings');
$router->addRoute('GET', '/admin/fines', 'AdminController', 'fines');
$router->addRoute('POST', '/admin/fines', 'AdminController', 'updateFines');
$router->addRoute('GET', '/admin/maintenance', 'AdminController', 'maintenance');
$router->addRoute('POST', '/admin/backup', 'AdminController', 'createBackup');
$router->addRoute('POST', '/admin/maintenance/perform', 'AdminController', 'performMaintenance');

// Admin borrow requests routes
$router->addRoute('GET', '/admin/borrow-requests', 'AdminController', 'borrowRequests');
$router->addRoute('POST', '/admin/borrow-requests/handle', 'AdminController', 'handleBorrowRequest');
$router->addRoute('GET', '/admin/analytics', 'AdminController', 'analytics');

// Admin notifications routes
$router->addRoute('GET', '/admin/notifications', 'AdminController', 'notifications');
$router->addRoute('POST', '/admin/notifications/mark-read', 'AdminController', 'markNotificationRead');

// Admin book management routes
$router->addRoute('GET', '/admin/books', 'BookController', 'adminBooks');
$router->addRoute('GET', '/admin/books/add', 'BookController', 'addBook');
$router->addRoute('POST', '/admin/books/add', 'BookController', 'addBook');
$router->addRoute('GET', '/admin/books/edit', 'BookController', 'editBook');
$router->addRoute('POST', '/admin/books/edit', 'BookController', 'editBook');
$router->addRoute('POST', '/admin/books/delete', 'BookController', 'deleteBook');

// Admin books borrowed management routes
$router->addRoute('GET', '/admin/borrowed-books', 'AdminController', 'booksBorrowed');
$router->addRoute('POST', '/admin/borrowed-books', 'AdminController', 'booksBorrowed');

// Public book browsing routes (accessible without login)
$router->addRoute('GET', '/books', 'BookController', 'userBooks');
$router->addRoute('GET', '/books/search', 'BookController', 'searchBooks');

// API routes
$router->addRoute('GET', '/api/books/search', 'BookController', 'searchBooks');
$router->addRoute('GET', '/api/book/details', 'BookController', 'getBookDetails');

// Error handling routes
$router->addRoute('GET', '/403', 'AuthController', 'show403');
$router->addRoute('GET', '/404', 'AuthController', 'show404');

// Health check and system routes
$router->addRoute('GET', '/health', 'AuthController', 'healthCheck');
$router->addRoute('GET', '/status', 'AuthController', 'systemStatus');

// Debug routes (remove in production)
$router->addRoute('GET', '/debug/video', 'HomeController', 'videoDebug');

error_log("Routes registered, dispatching...");

// Dispatch the request
try {
  $router->dispatch();
} catch (\Exception $e) {
  error_log("FATAL ERROR during dispatch: " . $e->getMessage());
  error_log("Stack trace: " . $e->getTraceAsString());

  // Show error page
  http_response_code(500);
  echo "<!DOCTYPE html><html><head><title>Fatal Error</title></head><body>";
  echo "<h1>Fatal Application Error</h1>";
  echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
  echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
  echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
  echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
  echo "</body></html>";
}

error_log("=== Request Complete ===");

