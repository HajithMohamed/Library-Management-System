<?php

/**
 * Front Controller - Entry point for all requests
 * Routes requests to appropriate controllers based on URL patterns
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
error_log("APP_ROOT: " . APP_ROOT);
error_log("PUBLIC_ROOT: " . PUBLIC_ROOT);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Include Composer autoloader
$vendorPath = dirname(APP_ROOT) . '/vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
    error_log("Composer autoloader loaded from: {$vendorPath}");
} else {
    error_log("WARNING: Composer autoloader not found at {$vendorPath}");
    error_log("Please run 'composer install' in the project root.");
}

// Include configuration
require_once APP_ROOT . '/config/config.php';
error_log("Config loaded");

// Include dbConnection for backwards compatibility
require_once APP_ROOT . '/config/dbConnection.php';
error_log("DB Connection loaded");

// Verify database connection
if (!$mysqli || !($mysqli instanceof mysqli)) {
    error_log("ERROR: Database connection failed - mysqli not initialized");
    die("Database connection failed");
}

if (!$conn || !($conn instanceof mysqli)) {
    error_log("ERROR: Database connection failed - conn not initialized");
    die("Database connection failed - conn alias not created");
}

error_log("Database connections verified (mysqli and conn)");

class Router
{
    private $routes = [];
    private $beforeMiddleware = [];
    private $afterMiddleware = [];

    /**
     * Add a route to the router
     */
    public function addRoute($method, $path, $controller, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Add multiple routes at once
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute(
                $route['method'],
                $route['path'],
                $route['controller'],
                $route['action']
            );
        }
    }

    /**
     * Add a route group with common prefix
     */
    public function group($prefix, callable $callback)
    {
        $originalRoutes = $this->routes;
        $this->routes = [];
        
        $callback($this);
        
        $groupRoutes = $this->routes;
        $this->routes = $originalRoutes;
        
        foreach ($groupRoutes as $route) {
            $route['path'] = rtrim($prefix, '/') . $route['path'];
            $this->routes[] = $route;
        }
    }

    /**
     * Add middleware to run before routing
     */
    public function addBeforeMiddleware(callable $middleware)
    {
        $this->beforeMiddleware[] = $middleware;
    }

    /**
     * Add middleware to run after routing
     */
    public function addAfterMiddleware(callable $middleware)
    {
        $this->afterMiddleware[] = $middleware;
    }

    /**
     * Dispatch the request to the appropriate controller
     */
    public function dispatch()
    {
        // Run before middleware
        foreach ($this->beforeMiddleware as $middleware) {
            $middleware();
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = rtrim($path, '/');

        // Remove base path if running in subdirectory
        $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_ROOT);
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        // Remove /index.php if present
        $path = str_replace('/index.php', '', $path);

        // Default route
        if (empty($path) || $path === '/') {
            $path = '/';
        }

        error_log("Routing: {$method} {$path}");

        // First try exact matches
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                error_log("Exact route matched: {$route['controller']}::{$route['action']}");
                $this->callController($route['controller'], $route['action']);
                
                // Run after middleware
                foreach ($this->afterMiddleware as $middleware) {
                    $middleware();
                }
                return;
            }
        }

        // Then try pattern matches for dynamic routes
        foreach ($this->routes as $route) {
            // Convert route pattern to regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                error_log("Dynamic route matched: {$route['controller']}::{$route['action']}");

                // Extract parameter names
                preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route['path'], $paramNames);

                // Build params array
                $params = [];
                for ($i = 0; $i < count($paramNames[1]); $i++) {
                    $params[$paramNames[1][$i]] = $matches[$i + 1];
                }

                $this->callController($route['controller'], $route['action'], $params);
                
                // Run after middleware
                foreach ($this->afterMiddleware as $middleware) {
                    $middleware();
                }
                return;
            }
        }

        // 404 Not Found
        error_log("No route matched - 404");
        $this->show404();
    }

    /**
     * Call the controller action
     */
    private function callController($controller, $action, $params = [])
    {
        $controllerClass = "App\\Controllers\\{$controller}";

        error_log("Attempting to load controller: {$controllerClass}");

        // Check if controller class exists
        if (!class_exists($controllerClass)) {
            error_log("ERROR: Controller class not found: {$controllerClass}");
            
            // Try to require the controller file
            $controllerFile = APP_ROOT . '/Controllers/' . $controller . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                error_log("Controller file loaded: {$controllerFile}");
                
                // Check again after requiring
                if (!class_exists($controllerClass)) {
                    error_log("ERROR: Controller class still not found after requiring file");
                    $this->show404();
                    return;
                }
            } else {
                error_log("ERROR: Controller file not found: {$controllerFile}");
                $this->show404();
                return;
            }
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

        // Check if method exists
        if (!method_exists($controllerInstance, $action)) {
            error_log("ERROR: Method not found: {$controllerClass}::{$action}");
            $this->show404();
            return;
        }

        error_log("Calling action: {$action}");

        try {
            // Call the controller action with params
            if (!empty($params)) {
                $controllerInstance->$action($params);
            } else {
                $controllerInstance->$action();
            }
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

    /**
     * Show detailed error page
     */
    private function showDetailedError($exception, $context)
    {
        http_response_code(500);
        
        // Check if we should display errors
        $displayErrors = ini_get('display_errors');
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Application Error</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
                    background: #f5f5f5;
                }
                .error-container {
                    background: white;
                    border-radius: 8px;
                    padding: 30px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #d32f2f;
                    margin-top: 0;
                }
                .context {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                }
                .message {
                    background: #f8d7da;
                    border-left: 4px solid #dc3545;
                    padding: 15px;
                    margin: 20px 0;
                }
                .trace {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    padding: 15px;
                    overflow-x: auto;
                    margin: 20px 0;
                }
                pre {
                    margin: 0;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                }
                .file-line {
                    color: #6c757d;
                    font-size: 0.9em;
                }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin-top: 20px;
                }
                .btn:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>⚠️ Application Error</h1>
                
                <div class="context">
                    <strong>Context:</strong> <?php echo htmlspecialchars($context); ?>
                </div>
                
                <div class="message">
                    <strong>Error Message:</strong><br>
                    <?php echo htmlspecialchars($exception->getMessage()); ?>
                </div>
                
                <?php if ($displayErrors): ?>
                <div class="file-line">
                    <strong>File:</strong> <?php echo htmlspecialchars($exception->getFile()); ?><br>
                    <strong>Line:</strong> <?php echo $exception->getLine(); ?>
                </div>
                
                <div class="trace">
                    <strong>Stack Trace:</strong>
                    <pre><?php echo htmlspecialchars($exception->getTraceAsString()); ?></pre>
                </div>
                <?php else: ?>
                <p>Error details have been logged. Please contact the system administrator.</p>
                <?php endif; ?>
                
                <a href="<?php echo BASE_URL; ?>" class="btn">Return to Home</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Show 404 error page
     */
    private function show404()
    {
        http_response_code(404);
        if (file_exists(APP_ROOT . '/views/errors/404.php')) {
            include APP_ROOT . '/views/errors/404.php';
        } else {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>404 - Page Not Found</title>
                <style>
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                    }
                    .container {
                        text-align: center;
                    }
                    h1 {
                        font-size: 120px;
                        margin: 0;
                    }
                    p {
                        font-size: 24px;
                    }
                    a {
                        color: white;
                        text-decoration: none;
                        border: 2px solid white;
                        padding: 10px 30px;
                        border-radius: 30px;
                        display: inline-block;
                        margin-top: 20px;
                        transition: all 0.3s;
                    }
                    a:hover {
                        background: white;
                        color: #667eea;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>404</h1>
                    <p>Page Not Found</p>
                    <a href="<?php echo BASE_URL; ?>">Go Home</a>
                </div>
            </body>
            </html>
            <?php
        }
        exit;
    }

    /**
     * Show 403 error page
     */
    public function show403()
    {
        http_response_code(403);
        if (file_exists(APP_ROOT . '/views/errors/403.php')) {
            include APP_ROOT . '/views/errors/403.php';
        } else {
            echo '<h1>403 - Access Forbidden</h1><p>You do not have permission to access this resource.</p>';
        }
        exit;
    }

    /**
     * Show 500 error page
     */
    public function show500()
    {
        http_response_code(500);
        if (file_exists(APP_ROOT . '/views/errors/500.php')) {
            include APP_ROOT . '/views/errors/500.php';
        } else {
            echo '<h1>500 - Internal Server Error</h1><p>An error occurred while processing your request.</p>';
        }
        exit;
    }

    /**
     * Get all registered routes (for debugging)
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Check if a route exists
     */
    public function routeExists($method, $path)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                return true;
            }
        }
        return false;
    }
}


// Initialize router
$router = new Router();

// ============================================================================
// HOME & PUBLIC ROUTES
// ============================================================================

$router->addRoute('GET', '/', 'HomeController', 'index');
$router->addRoute('GET', '/home', 'HomeController', 'index');
$router->addRoute('GET', '/about', 'HomeController', 'about');
$router->addRoute('GET', '/contact', 'HomeController', 'contact');
$router->addRoute('GET', '/library', 'HomeController', 'library');

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================

$router->addRoute('GET', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('GET', '/signup', 'AuthController', 'signup');
$router->addRoute('POST', '/signup', 'AuthController', 'signup');
$router->addRoute('GET', '/verify-otp', 'AuthController', 'verifyOtp');
$router->addRoute('POST', '/verify-otp', 'AuthController', 'verifyOtp');
$router->addRoute('GET', '/forgot-password', 'AuthController', 'forgotPassword');
$router->addRoute('POST', '/forgot-password', 'AuthController', 'forgotPassword');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

// ============================================================================
// USER ROUTES
// ============================================================================

// User Dashboard
$router->addRoute('GET', '/user/dashboard', 'UserController', 'dashboard');
$router->addRoute('GET', '/user/index', 'UserController', 'dashboard');

// User Profile
$router->addRoute('GET', '/user/profile', 'UserController', 'profile');
$router->addRoute('POST', '/user/profile', 'UserController', 'updateProfile');
$router->addRoute('POST', '/user/change-password', 'UserController', 'changePassword');

// User Books
$router->addRoute('GET', '/user/books', 'BookController', 'userBooks');
$router->addRoute('GET', '/user/book/{isbn}', 'BookController', 'viewBook');
$router->addRoute('GET', '/user/reserve', 'UserController', 'reserve');
$router->addRoute('POST', '/user/reserve', 'UserController', 'reserve');
$router->addRoute('GET', '/user/reserve/{isbn}', 'UserController', 'reserve');
$router->addRoute('POST', '/user/reserve/{isbn}', 'UserController', 'reserve');
$router->addRoute('GET', '/user/borrow', 'BookController', 'borrow');
$router->addRoute('POST', '/user/borrow', 'BookController', 'borrowBook');
$router->addRoute('GET', '/user/return', 'BookController', 'return');
$router->addRoute('POST', '/user/return', 'BookController', 'returnBook');

// User Fines
$router->addRoute('GET', '/user/fines', 'UserController', 'fines');
$router->addRoute('POST', '/user/fines', 'UserController', 'payFine');

// User Notifications
$router->addRoute('GET', '/user/notifications', 'UserController', 'notifications');
$router->addRoute('POST', '/user/notifications/mark-read', 'UserController', 'markNotificationRead');

// ============================================================================
// STUDENT ROUTES (alias to user routes)
// ============================================================================

$router->addRoute('GET', '/student/dashboard', 'UserController', 'dashboard');
$router->addRoute('GET', '/student/profile', 'UserController', 'profile');
$router->addRoute('POST', '/student/profile', 'UserController', 'updateProfile');
$router->addRoute('POST', '/student/change-password', 'UserController', 'changePassword');

// ============================================================================
// FACULTY ROUTES
// ============================================================================

// Faculty Dashboard
$router->addRoute('GET', '/faculty/dashboard', 'FacultyController', 'dashboard');
$router->addRoute('GET', '/faculty/index', 'FacultyController', 'dashboard');

// Faculty Books
$router->addRoute('GET', '/faculty/books', 'FacultyController', 'books');
$router->addRoute('GET', '/faculty/book', 'FacultyController', 'viewBook');
$router->addRoute('GET', '/faculty/book/{isbn}', 'FacultyController', 'viewBook');
$router->addRoute('GET', '/faculty/search', 'FacultyController', 'search');

// Faculty Reserve & Borrow
$router->addRoute('GET', '/faculty/reserve', 'FacultyController', 'reserve');
$router->addRoute('POST', '/faculty/reserve', 'FacultyController', 'reserve');
$router->addRoute('GET', '/faculty/reserve/{isbn}', 'FacultyController', 'reserve');
$router->addRoute('POST', '/faculty/reserve/{isbn}', 'FacultyController', 'reserve');
$router->addRoute('GET', '/faculty/reserved-books', 'FacultyController', 'reservedBooks');
$router->addRoute('GET', '/faculty/borrow-history', 'FacultyController', 'borrowHistory');

// Faculty Return
$router->addRoute('GET', '/faculty/return', 'FacultyController', 'returnBook');
$router->addRoute('POST', '/faculty/return', 'FacultyController', 'returnBook');

// Faculty Fines
$router->addRoute('GET', '/faculty/fines', 'FacultyController', 'fines');
$router->addRoute('POST', '/faculty/fines', 'FacultyController', 'fines');

// Faculty Profile
$router->addRoute('GET', '/faculty/profile', 'FacultyController', 'profile');
$router->addRoute('POST', '/faculty/profile', 'FacultyController', 'profile');

// Faculty Notifications
$router->addRoute('GET', '/faculty/notifications', 'FacultyController', 'notifications');

// Faculty Feedback & Requests
$router->addRoute('GET', '/faculty/feedback', 'FacultyController', 'feedback');
$router->addRoute('POST', '/faculty/feedback', 'FacultyController', 'feedback');
$router->addRoute('GET', '/faculty/book-request', 'FacultyController', 'bookRequest');
$router->addRoute('POST', '/faculty/book-request', 'FacultyController', 'bookRequest');

// ============================================================================
// ADMIN ROUTES
// ============================================================================

// Admin Dashboard
$router->addRoute('GET', '/admin/dashboard', 'AdminController', 'dashboard');
$router->addRoute('GET', '/admin/index', 'AdminController', 'dashboard');
$router->addRoute('GET', '/admin', 'AdminController', 'dashboard');

// Admin Users Management
$router->addRoute('GET', '/admin/users', 'AdminController', 'users');
$router->addRoute('POST', '/admin/users/add', 'AdminController', 'addUser');
$router->addRoute('POST', '/admin/users/edit', 'AdminController', 'editUser');
$router->addRoute('POST', '/admin/users/delete', 'AdminController', 'deleteUser');

// Admin Books Management
$router->addRoute('GET', '/admin/books', 'BookController', 'adminBooks');
$router->addRoute('GET', '/admin/books/add', 'BookController', 'addBook');
$router->addRoute('POST', '/admin/books/add', 'BookController', 'addBook');
$router->addRoute('GET', '/admin/books/edit', 'BookController', 'editBook');
$router->addRoute('POST', '/admin/books/edit', 'BookController', 'editBook');
$router->addRoute('POST', '/admin/books/delete', 'BookController', 'deleteBook');

// Admin Borrow Requests
$router->addRoute('GET', '/admin/borrow-requests', 'AdminController', 'borrowRequests');
$router->addRoute('POST', '/admin/borrow-requests/handle', 'AdminController', 'handleBorrowRequest');
$router->addRoute('POST', '/admin/borrow-requests-handle', 'AdminController', 'handleBorrowRequest');

// Admin Borrowed Books
$router->addRoute('GET', '/admin/borrowed-books', 'AdminController', 'booksBorrowed');
$router->addRoute('POST', '/admin/borrowed-books', 'AdminController', 'booksBorrowed');

// Admin Fines
$router->addRoute('GET', '/admin/fines', 'AdminController', 'fines');
$router->addRoute('POST', '/admin/fines', 'AdminController', 'updateFines');

// Admin Notifications
$router->addRoute('GET', '/admin/notifications', 'AdminController', 'notifications');
$router->addRoute('POST', '/admin/notifications/mark-read', 'AdminController', 'markNotificationRead');

// Admin Reports & Analytics
$router->addRoute('GET', '/admin/reports', 'AdminController', 'reports');
$router->addRoute('GET', '/admin/analytics', 'AdminController', 'analytics');

// Admin Settings
$router->addRoute('GET', '/admin/settings', 'AdminController', 'settings');
$router->addRoute('POST', '/admin/settings', 'AdminController', 'updateSettings');

// Admin Maintenance
$router->addRoute('GET', '/admin/maintenance', 'AdminController', 'maintenance');
$router->addRoute('POST', '/admin/maintenance', 'AdminController', 'performMaintenance');
$router->addRoute('POST', '/admin/maintenance/perform', 'AdminController', 'performMaintenance');
$router->addRoute('POST', '/admin/backup', 'AdminController', 'createBackup');
$router->addRoute('POST', '/admin/maintenance/backup', 'AdminController', 'createBackup');

// Admin Profile
$router->addRoute('GET', '/admin/profile', 'AdminController', 'profile');
$router->addRoute('POST', '/admin/profile', 'AdminController', 'updateProfile');

// ============================================================================
// PUBLIC BOOK BROWSING ROUTES (accessible without login)
// ============================================================================

$router->addRoute('GET', '/books', 'BookController', 'userBooks');
$router->addRoute('GET', '/books/search', 'BookController', 'searchBooks');

// ============================================================================
// API ROUTES
// ============================================================================

$router->addRoute('GET', '/api/books/search', 'BookController', 'searchBooks');
$router->addRoute('GET', '/api/book/details', 'BookController', 'getBookDetails');
$router->addRoute('POST', '/api/book/reserve', 'BookController', 'reserveBook');

// ============================================================================
// ERROR & SYSTEM ROUTES
// ============================================================================

$router->addRoute('GET', '/403', 'AuthController', 'show403');
$router->addRoute('GET', '/404', 'AuthController', 'show404');
$router->addRoute('GET', '/500', 'AuthController', 'show500');

// Health check and system routes
$router->addRoute('GET', '/health', 'AuthController', 'healthCheck');
$router->addRoute('GET', '/status', 'AuthController', 'systemStatus');



// ============================================================================
// MIDDLEWARE (Optional - implement as needed)
// ============================================================================

// Example: Add authentication middleware
// $router->addBeforeMiddleware(function() {
//     // Check if user is authenticated for protected routes
// });

// ============================================================================
// DISPATCH REQUEST
// ============================================================================

error_log("Routes registered (" . count($router->getRoutes()) . " total), dispatching...");

try {
    $router->dispatch();
} catch (\Exception $e) {
    error_log("FATAL ERROR during dispatch: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Show error page
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fatal Error</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 1200px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .error-box {
                background: white;
                border-radius: 8px;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #d32f2f;
                border-bottom: 2px solid #d32f2f;
                padding-bottom: 10px;
            }
            .details {
                background: #f8f9fa;
                border-left: 4px solid #d32f2f;
                padding: 15px;
                margin: 20px 0;
            }
            pre {
                background: #263238;
                color: #aed581;
                padding: 15px;
                border-radius: 4px;
                overflow-x: auto;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 20px;
            }
            .btn:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>🔴 Fatal Application Error</h1>
            <div class="details">
                <p><strong>Message:</strong> <?php echo htmlspecialchars($e->getMessage()); ?></p>
                <p><strong>File:</strong> <?php echo htmlspecialchars($e->getFile()); ?></p>
                <p><strong>Line:</strong> <?php echo $e->getLine(); ?></p>
            </div>
            
            <?php if (ini_get('display_errors')): ?>
            <h3>Stack Trace:</h3>
            <pre><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
            <?php endif; ?>
            
            <a href="<?php echo BASE_URL; ?>" class="btn">← Return to Home</a>
        </div>
    </body>
    </html>
    <?php
}

error_log("=== Request Complete ===");