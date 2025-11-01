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
    $subAction = $segments[2] ?? null;
    
    error_log("Controller: $controller, Action: $action, SubAction: " . ($subAction ?? 'none'));
    
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
                    // Book details page
                    require_once APP_ROOT . '/Controllers/BookController.php';
                    $bookCtrl = new App\Controllers\BookController();
                    // Add a method to show book details if not present, or call viewBook()
                    if (method_exists($bookCtrl, 'viewBook')) {
                        $bookCtrl->viewBook();
                    } else {
                        // fallback: show books
                        $bookCtrl->userBooks();
                    }
                    break;
                case 'reserve':
                    // Book reserve page
                    require_once APP_ROOT . '/Controllers/UserController.php';
                    if (method_exists($ctrl, 'reserve')) {
                        $ctrl->reserve();
                    } else {
                        // fallback: show books
                        require_once APP_ROOT . '/Controllers/BookController.php';
                        $bookCtrl = new App\Controllers\BookController();
                        $bookCtrl->userBooks();
                    }
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
                case 'index':
                case '':
                    $ctrl->dashboard();
                    break;
                    
                case 'books':
                    require_once APP_ROOT . '/Controllers/BookController.php';
                    $bookCtrl = new App\Controllers\BookController();
                    
                    // Handle book sub-actions
                    if ($subAction === 'add' && $requestMethod === 'POST') {
                        $bookCtrl->addBook();
                    } elseif ($subAction === 'edit' && $requestMethod === 'POST') {
                        $bookCtrl->editBook();
                    } elseif ($subAction === 'delete' && $requestMethod === 'POST') {
                        $bookCtrl->deleteBook();
                    } else {
                        $bookCtrl->adminBooks();
                    }
                    break;
                    
                case 'users':
                    $ctrl->users();
                    break;
                    
                case 'fines':
                    if ($requestMethod === 'POST') {
                        $ctrl->updateFines();
                    } else {
                        $ctrl->fines();
                    }
                    break;
                    
                case 'borrow-requests':
                    $ctrl->borrowRequests();
                    break;
                    
                case 'borrow-requests-handle':
                    if ($requestMethod === 'POST') {
                        $ctrl->handleBorrowRequest();
                    } else {
                        header('Location: ' . BASE_URL . 'admin/borrow-requests');
                        exit;
                    }
                    break;
                    
                case 'borrowed-books':
                    if ($requestMethod === 'POST') {
                        $ctrl->booksBorrowed(); // Handles POST internally
                    } else {
                        $ctrl->booksBorrowed();
                    }
                    break;
                    
                case 'notifications':
                    if ($requestMethod === 'POST' && $subAction === 'mark-read') {
                        $ctrl->markNotificationRead();
                    } else {
                        $ctrl->notifications();
                    }
                    break;
                    
                case 'reports':
                    $ctrl->reports();
                    break;
                    
                case 'analytics':
                    $ctrl->analytics();
                    break;
                    
                case 'maintenance':
                    if ($requestMethod === 'POST') {
                        if ($subAction === 'backup') {
                            $ctrl->createBackup();
                        } else {
                            $ctrl->performMaintenance();
                        }
                    } else {
                        $ctrl->maintenance();
                    }
                    break;
                    
                case 'settings':
                    if ($requestMethod === 'POST') {
                        $ctrl->updateSettings();
                    } else {
                        $ctrl->settings();
                    }
                    break;
                    
                case 'profile':
                    if ($requestMethod === 'POST') {
                        // Handle profile update
                        require_once APP_ROOT . '/Controllers/UserController.php';
                        $userCtrl = new App\Controllers\UserController();
                        $userCtrl->updateProfile();
                    } else {
                        // Show profile page
                        $pageTitle = 'Admin Profile';
                        $_SESSION['admin'] = [
                            'adminId' => $_SESSION['userId'] ?? '',
                            'username' => $_SESSION['username'] ?? '',
                            'emailId' => $_SESSION['emailId'] ?? '',
                            'firstName' => $_SESSION['firstName'] ?? '',
                            'lastName' => $_SESSION['lastName'] ?? '',
                            'phoneNumber' => $_SESSION['phoneNumber'] ?? '',
                            'gender' => $_SESSION['gender'] ?? '',
                            'dob' => $_SESSION['dob'] ?? '',
                            'address' => $_SESSION['address'] ?? '',
                            'department' => $_SESSION['department'] ?? 'Library Management',
                            'position' => $_SESSION['position'] ?? 'Administrator'
                        ];
                        include APP_ROOT . '/views/admin/admin-profile.php';
                    }
                    break;
                    
                default:
                    error_log("Unknown admin action: $action");
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
                case 'book':
                    // Support /faculty/book/{isbn}
                    $isbn = $segments[2] ?? null;
                    $ctrl->viewBook(['isbn' => $isbn]);
                    break;
                case 'reserve':
                    // Support /faculty/reserve/{isbn}
                    $isbn = $segments[2] ?? null;
                    $ctrl->reserve(['isbn' => $isbn]);
                    break;
                case 'reserved-books':
                    $ctrl->reservedBooks();
                    break;
                case 'profile':
                    $ctrl->profile();
                    break;
                case 'fines':
                    $ctrl->fines();
                    break;
                case 'return':
                    $ctrl->returnBook();
                    break;
                case 'borrow-history':
                    $ctrl->borrowHistory();
                    break;
                default:
                    $ctrl->dashboard();
            }
            break;
            
        default:
            error_log("Unknown controller: $controller");
            http_response_code(404);
            echo "404 - Page Not Found: /$controller/$action";
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

