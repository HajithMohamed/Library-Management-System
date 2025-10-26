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

// Include Composer autoloader (if exists)
if (file_exists(APP_ROOT . '/../vendor/autoload.php')) {
  require_once APP_ROOT . '/../vendor/autoload.php';
}

// Include configuration (this creates $mysqli)
require_once APP_ROOT . '/config/config.php';

// Error Reporting
if (APP_DEBUG === "true") {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
} else {
  error_reporting(0);
  ini_set('display_errors', 0);
}

// Include dbConnection for backwards compatibility (creates $conn alias)
require_once APP_ROOT . '/config/dbConnection.php';

// Verify connection
if (!$mysqli || !($mysqli instanceof mysqli)) {
  die("Database connection failed in index.php");
}

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

    foreach ($this->routes as $route) {
      if ($route['method'] === $method && $route['path'] === $path) {
        $this->callController($route['controller'], $route['action']);
        return;
      }
    }

    // 404 Not Found
    http_response_code(404);
    include APP_ROOT . '/views/errors/404.php';
  }

  private function callController($controller, $action)
  {
    $controllerClass = "App\\Controllers\\{$controller}";

    if (!class_exists($controllerClass)) {
      $this->show404();
      return;
    }

    $controllerInstance = new $controllerClass();

    if (!method_exists($controllerInstance, $action)) {
      $this->show404();
      return;
    }

    try {
      // Call the controller action
      $controllerInstance->$action();
    } catch (\Exception $e) {
      // Log the error
      error_log("Error in {$controller}::{$action} - " . $e->getMessage());
      error_log("Stack trace: " . $e->getTraceAsString());

      // Show appropriate error page
      if ($e->getCode() == 403) {
        $this->show403();
      } else {
        $this->show500();
      }
    }
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
$router->addRoute('POST', '/user/pay-fine', 'UserController', 'payFine');

// Admin dashboard routes
//
//
$router->addRoute('GET', '/admin/users', 'AdminController', 'users');
$router->addRoute('GET', '/admin/dashboard', 'AdminController', 'dashboard');
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

//admin analytics page route
$router->addRoute('GET', '/admin/analytics', 'AdminController', 'analytics');
// Admin borrow requests routes
$router->addRoute('GET', '/admin/borrow-requests', 'AdminController', 'borrowRequests');
$router->addRoute('POST', '/admin/borrow-requests/handle', 'AdminController', 'handleBorrowRequest');

// Admin notifications routes
$router->addRoute('GET', '/admin/notifications', 'AdminController', 'notifications');
$router->addRoute('POST', '/admin/notifications/mark-read', 'AdminController', 'markNotificationRead');

// Admin book management routes - UPDATED
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

// Dispatch the request
$router->dispatch();

