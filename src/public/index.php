<?php

/**
 * Front Controller - Entry point for all requests
 * Routes requests to appropriate controllers based on URL patterns
 */

use App\Controllers\AdminController;

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

// Simple routing system
class Router
{
  private $routes = [];

  public function addRoute($method, $path, $controller, $action)
  {
    $this->routes[] = [
      'method' => $method,
      'path' => $path,
      'controller' => $controller,
      'action' => $action
    ];
  }

  public function dispatch()
  {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = rtrim($path, '/');

    // Remove base path if running in subdirectory
    $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_ROOT);
    if (strpos($path, $basePath) === 0) {
      $path = substr($path, strlen($basePath));
    }

    // Default route
    if (empty($path) || $path === '/') {
      $path = '/';
    }

    error_log("Routing: {$method} {$path}");

    foreach ($this->routes as $route) {
      if ($route['method'] === $method && $route['path'] === $path) {
        error_log("Route matched: {$route['controller']}::{$route['action']}");
        $this->callController($route['controller'], $route['action']);
        return;
      }
    }

    // 404 Not Found
    error_log("No route matched - 404");
    http_response_code(404);
    include APP_ROOT . '/views/errors/404.php';
  }

  private function callController($controller, $action)
  {
    $controllerClass = "App\\Controllers\\{$controller}";

    error_log("Attempting to load controller: {$controllerClass}");

    if (!class_exists($controllerClass)) {
      error_log("ERROR: Controller class not found: {$controllerClass}");
      $this->show404();
      return;
    }

    error_log("Controller class found: {$controllerClass}");

    try {
      $controllerInstance = new $controllerClass();
      error_log("Controller instance created");
    } catch (\Exception $e) {
      error_log("ERROR creating controller instance: " . $e->getMessage());
      error_log("Stack trace: " . $e->getTraceAsString());
      $this->showDetailedError($e, "Controller Instantiation Error");
      return;
    }

    if (!method_exists($controllerInstance, $action)) {
      error_log("ERROR: Method not found: {$action}");
      $this->show404();
      return;
    }

    error_log("Calling action: {$action}");

    try {
      // Call the controller action
      $controllerInstance->$action();
      error_log("Action completed successfully");
    } catch (\Exception $e) {
      // Log the error
      error_log("ERROR in {$controller}::{$action}");
      error_log("Error message: " . $e->getMessage());
      error_log("Error code: " . $e->getCode());
      error_log("Error file: " . $e->getFile() . ":" . $e->getLine());
      error_log("Stack trace: " . $e->getTraceAsString());

      // Show detailed error page
      $this->showDetailedError($e, "{$controller}::{$action}");
    } catch (\Error $e) {
      // Catch PHP 7+ errors (like undefined variable, etc)
      error_log("PHP ERROR in {$controller}::{$action}");
      error_log("Error message: " . $e->getMessage());
      error_log("Error file: " . $e->getFile() . ":" . $e->getLine());
      error_log("Stack trace: " . $e->getTraceAsString());

      // Show detailed error page
      $this->showDetailedError($e, "{$controller}::{$action}");
    }
  }

  private function showDetailedError($exception, $context)
  {
    http_response_code(500);
?>
    <!DOCTYPE html>
    <html>

    <head>
      <title>Application Error</title>
      <style>
        body {
          font-family: 'Segoe UI', Arial, sans-serif;
          background: #f5f5f5;
          padding: 20px;
        }

        .error-container {
          max-width: 1200px;
          margin: 0 auto;
          background: white;
          border-radius: 8px;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
          overflow: hidden;
        }

        .error-header {
          background: #ef4444;
          color: white;
          padding: 20px 30px;
        }

        .error-header h1 {
          margin: 0;
          font-size: 24px;
        }

        .error-header p {
          margin: 10px 0 0 0;
          opacity: 0.9;
        }

        .error-body {
          padding: 30px;
        }

        .error-section {
          margin-bottom: 30px;
        }

        .error-section h2 {
          color: #1f2937;
          font-size: 18px;
          margin-bottom: 15px;
          border-bottom: 2px solid #e5e7eb;
          padding-bottom: 10px;
        }

        .error-code {
          background: #fee2e2;
          border-left: 4px solid #ef4444;
          padding: 15px;
          border-radius: 4px;
          font-family: 'Courier New', monospace;
          font-size: 14px;
          color: #991b1b;
          overflow-x: auto;
        }

        .stack-trace {
          background: #f9fafb;
          border: 1px solid #e5e7eb;
          padding: 15px;
          border-radius: 4px;
          font-family: 'Courier New', monospace;
          font-size: 12px;
          color: #374151;
          overflow-x: auto;
          white-space: pre-wrap;
        }

        .info-box {
          background: #dbeafe;
          border-left: 4px solid #3b82f6;
          padding: 15px;
          border-radius: 4px;
          margin-bottom: 15px;
        }

        .info-box strong {
          color: #1e40af;
        }
      </style>
    </head>

    <body>
      <div class="error-container">
        <div class="error-header">
          <h1>⚠️ Application Error</h1>
          <p>An error occurred while processing your request</p>
        </div>
        <div class="error-body">
          <div class="error-section">
            <h2>Context</h2>
            <div class="info-box">
              <strong>Location:</strong> <?= htmlspecialchars($context) ?>
            </div>
          </div>

          <div class="error-section">
            <h2>Error Message</h2>
            <div class="error-code">
              <?= htmlspecialchars($exception->getMessage()) ?>
            </div>
          </div>

          <div class="error-section">
            <h2>Error Details</h2>
            <div class="info-box">
              <strong>Type:</strong> <?= get_class($exception) ?><br>
              <strong>File:</strong> <?= htmlspecialchars($exception->getFile()) ?><br>
              <strong>Line:</strong> <?= $exception->getLine() ?>
            </div>
          </div>

          <div class="error-section">
            <h2>Stack Trace</h2>
            <div class="stack-trace"><?= htmlspecialchars($exception->getTraceAsString()) ?></div>
          </div>

          <div class="error-section">
            <h2>Request Information</h2>
            <div class="info-box">
              <strong>Method:</strong> <?= htmlspecialchars($_SERVER['REQUEST_METHOD']) ?><br>
              <strong>URI:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?><br>
              <strong>Session User:</strong> <?= isset($_SESSION['userId']) ? htmlspecialchars($_SESSION['userId']) : 'Not logged in' ?><br>
              <strong>User Type:</strong> <?= isset($_SESSION['userType']) ? htmlspecialchars($_SESSION['userType']) : 'N/A' ?>
            </div>
          </div>
        </div>
      </div>
    </body>

    </html>
<?php
    exit;
  }

  private function show404()
  {
    http_response_code(404);
    if (file_exists(APP_ROOT . '/views/errors/404.php')) {
      include APP_ROOT . '/views/errors/404.php';
    } else {
      echo '<h1>404 - Page Not Found</h1><p>The requested page could not be found.</p>';
    }
  }

  private function show403()
  {
    http_response_code(403);
    if (file_exists(APP_ROOT . '/views/errors/403.php')) {
      include APP_ROOT . '/views/errors/403.php';
    } else {
      echo '<h1>403 - Access Forbidden</h1><p>You do not have permission to access this resource.</p>';
    }
  }

  private function show500()
  {
    http_response_code(500);
    if (file_exists(APP_ROOT . '/views/errors/500.php')) {
      include APP_ROOT . '/views/errors/500.php';
    } else {
      echo '<h1>500 - Internal Server Error</h1><p>An error occurred while processing your request.</p>';
    }
  }
}

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

// Student routes
$router->addRoute('GET', '/student/dashboard', 'UserController', 'dashboard');
$router->addRoute('GET', '/student/profile', 'UserController', 'profile');
$router->addRoute('POST', '/student/profile', 'UserController', 'updateProfile');
$router->addRoute('POST', '/student/change-password', 'UserController', 'changePassword');

// Faculty routes
$router->addRoute('GET', '/faculty/dashboard', 'FacultyController', 'dashboard');
$router->addRoute('GET', '/faculty/books', 'FacultyController', 'books');
$router->addRoute('GET', '/faculty/search', 'FacultyController', 'search');
$router->addRoute('GET', '/faculty/book/{isbn}', 'FacultyController', 'viewBook');
$router->addRoute('POST', '/faculty/reserve/{isbn}', 'FacultyController', 'reserve');
$router->addRoute('GET', '/faculty/reserve/{isbn}', 'FacultyController', 'reserve');
$router->addRoute('GET', '/faculty/borrow-history', 'FacultyController', 'borrowHistory');
$router->addRoute('GET', '/faculty/book-request', 'FacultyController', 'bookRequest');
$router->addRoute('GET', '/faculty/notifications', 'FacultyController', 'notifications');
$router->addRoute('GET', '/faculty/profile', 'FacultyController', 'profile');
$router->addRoute('POST', '/faculty/profile', 'FacultyController', 'profile');
$router->addRoute('GET', '/faculty/feedback', 'FacultyController', 'feedback');
$router->addRoute('POST', '/faculty/feedback', 'FacultyController', 'feedback');

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