<?php

/**
 * Front Controller - Entry point for all requests
 * Simple routing without Router class
 */

// Start session
session_start();

// Define application paths
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
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

// Include configuration
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/dbConnection.php';

// Verify database connection
if (!$mysqli || !($mysqli instanceof mysqli)) {
  die("Database connection failed");
}

error_log("Database connected successfully");

// Get request URI and method
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove base path if exists
$requestUri = str_replace('/index.php', '', $requestUri);
$requestUri = rtrim($requestUri, '/');
if (empty($requestUri)) {
    $requestUri = '/';
}

error_log("Routing to: $requestUri");

// Simple routing logic
try {
    // Parse the URI into segments
    $segments = explode('/', trim($requestUri, '/'));
    $controller = $segments[0] ?? 'home';
    $action = $segments[1] ?? 'index';
    
    error_log("Controller: $controller, Action: $action");
    
    // Route based on controller
    switch ($controller) {
        case '':
        case 'home':
            require_once APP_ROOT . '/Controllers/HomeController.php';
            $ctrl = new App\Controllers\HomeController();
            $ctrl->index();
            break;
            
        case 'login':
            require_once APP_ROOT . '/Controllers/AuthController.php';
            $ctrl = new App\Controllers\AuthController();
            $ctrl->login();
            break;
            
        case 'logout':
            require_once APP_ROOT . '/Controllers/AuthController.php';
            $ctrl = new App\Controllers\AuthController();
            $ctrl->logout();
            break;
            
        case 'user':
            require_once APP_ROOT . '/Controllers/UserController.php';
            $ctrl = new App\Controllers\UserController();
            
            switch ($action) {
                case 'dashboard':
                    $ctrl->dashboard();
                    break;
                case 'books':
                    require_once APP_ROOT . '/Controllers/BookController.php';
                    $bookCtrl = new App\Controllers\BookController();
                    $bookCtrl->userBooks();
                    break;
                case 'book':
                    $ctrl->viewBook();
                    break;
                case 'reserve':
                    $ctrl->reserve();
                    break;
                case 'profile':
                    $ctrl->profile();
                    break;
                case 'fines':
                    $ctrl->fines();
                    break;
                case 'notifications':
                    $ctrl->notifications();
                    break;
                default:
                    $ctrl->dashboard();
            }
            break;
            
        case 'admin':
            require_once APP_ROOT . '/Controllers/AdminController.php';
            $ctrl = new App\Controllers\AdminController();
            
            switch ($action) {
                case 'dashboard':
                    $ctrl->dashboard();
                    break;
                case 'books':
                    require_once APP_ROOT . '/Controllers/BookController.php';
                    $bookCtrl = new App\Controllers\BookController();
                    $bookCtrl->adminBooks();
                    break;
                default:
                    $ctrl->dashboard();
            }
            break;
            
        case 'faculty':
            require_once APP_ROOT . '/Controllers/FacultyController.php';
            $ctrl = new App\Controllers\FacultyController();
            
            switch ($action) {
                case 'dashboard':
                    $ctrl->dashboard();
                    break;
                case 'books':
                    $ctrl->books();
                    break;
                case 'reserve':
                    $ctrl->reserve();
                    break;
                default:
                    $ctrl->dashboard();
            }
            break;
            
        default:
            http_response_code(404);
            echo "404 - Page Not Found";
    }
    
} catch (\Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h1>Application Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    if (ini_get('display_errors')) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    echo "</body></html>";
}

error_log("=== Request Complete ===");

