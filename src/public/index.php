<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Router\Router;

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

// Initialize router (remove duplicate)
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

// 2FA Routes
$router->addRoute('GET', '/2fa/setup', 'AuthController', 'setup2fa');
$router->addRoute('POST', '/2fa/verify-setup', 'AuthController', 'verifySetup2fa');
$router->addRoute('GET', '/2fa/verify', 'AuthController', 'verify2fa');
$router->addRoute('POST', '/2fa/verify', 'AuthController', 'verify2fa');
$router->addRoute('POST', '/2fa/disable', 'AuthController', 'disable2fa');

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
// Support both path parameter and query parameter formats
$router->addRoute('GET', '/user/book', 'BookController', 'viewBook');
$router->addRoute('GET', '/user/book/{isbn}', 'BookController', 'viewBook');
$router->addRoute('GET', '/user/reserve', 'UserController', 'reserve');
$router->addRoute('POST', '/user/reserve', 'UserController', 'reserve');
$router->addRoute('GET', '/user/reserve/{isbn}', 'UserController', 'reserve');
$router->addRoute('POST', '/user/reserve/{isbn}', 'UserController', 'reserve');
$router->addRoute('GET', '/user/reserved-books', 'UserController', 'reservedBooks');
$router->addRoute('GET', '/user/borrow-history', 'UserController', 'borrowHistory');
$router->addRoute('POST', '/user/submit-review', 'UserController', 'submitReview');
$router->addRoute('GET', '/user/borrow', 'BookController', 'borrow');
$router->addRoute('POST', '/user/borrow', 'BookController', 'borrowBook');
$router->addRoute('GET', '/user/return', 'BookController', 'return');
$router->addRoute('POST', '/user/return', 'BookController', 'returnBook');
$router->addRoute('GET', '/user/returns', 'UserController', 'returns');

// User Fines
$router->addRoute('GET', '/user/fines', 'UserController', 'fines');
$router->addRoute('GET', '/user/payFine', 'UserController', 'showPaymentForm');
$router->addRoute('POST', '/user/payFine', 'UserController', 'payFine');
$router->addRoute('GET', '/user/payment-form', 'UserController', 'showPaymentForm');
$router->addRoute('POST', '/user/payment-form', 'UserController', 'payFine');
$router->addRoute('POST', '/user/fines', 'UserController', 'payFine');
$router->addRoute('POST', '/user/pay-all-fines', 'UserController', 'payAllFines');

// User Notifications
$router->addRoute('GET', '/user/notifications', 'UserController', 'notifications');
$router->addRoute('POST', '/user/notifications', 'UserController', 'notifications');
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
$router->addRoute('GET', '/faculty/payment-form', 'FacultyController', 'showPaymentForm');
$router->addRoute('POST', '/faculty/payment-form', 'FacultyController', 'payFine');

// Faculty Profile
$router->addRoute('GET', '/faculty/profile', 'FacultyController', 'profile');
$router->addRoute('POST', '/faculty/profile', 'FacultyController', 'profile');

// Faculty Notifications
$router->addRoute('GET', '/faculty/notifications', 'FacultyController', 'notifications');
$router->addRoute('POST', '/faculty/notifications', 'FacultyController', 'notifications');

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
$router->addRoute('POST', '/admin/users', 'AdminController', 'users'); // FIXED: Handle POST for add/edit/delete
$router->addRoute('POST', '/admin/users/delete', 'AdminController', 'deleteUser');

// Admin Roles Management
$router->addRoute('GET', '/admin/roles', 'AdminController', 'roles');
$router->addRoute('POST', '/admin/roles', 'AdminController', 'roles');

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

// Admin Transactions (redirect to borrowed books)
$router->addRoute('GET', '/admin/transactions', 'AdminController', 'booksBorrowed');
$router->addRoute('POST', '/admin/transactions', 'AdminController', 'booksBorrowed');

// Admin Fines
$router->addRoute('GET', '/admin/fines', 'AdminController', 'fines');
$router->addRoute('POST', '/admin/fines', 'AdminController', 'updateFines');

// Admin Notifications
$router->addRoute('GET', '/admin/notifications', 'AdminController', 'notifications');
$router->addRoute('POST', '/admin/notifications', 'AdminController', 'notifications');
$router->addRoute('POST', '/admin/notifications/mark-read', 'AdminController', 'markNotificationRead');
$router->addRoute('GET', '/admin/notifications/mark-all-read', 'AdminController', 'markAllNotificationsRead');
$router->addRoute('POST', '/admin/notifications/mark-all-read', 'AdminController', 'markAllNotificationsRead');
$router->addRoute('GET', '/admin/notifications/check-overdue', 'AdminController', 'checkOverdueNotifications');
$router->addRoute('POST', '/admin/notifications/check-overdue', 'AdminController', 'checkOverdueNotifications');
$router->addRoute('GET', '/admin/notifications/check-stock', 'AdminController', 'checkOutOfStockNotifications');
$router->addRoute('POST', '/admin/notifications/check-stock', 'AdminController', 'checkOutOfStockNotifications');
$router->addRoute('GET', '/admin/notifications/clear-old', 'AdminController', 'clearOldNotifications');
$router->addRoute('POST', '/admin/notifications/clear-old', 'AdminController', 'clearOldNotifications');

// Admin Reports & Analytics
$router->addRoute('GET', '/admin/reports', 'AdminController', 'reports');
$router->addRoute('GET', '/admin/reports/export', 'AdminController', 'exportReport');
$router->addRoute('GET', '/admin/analytics', 'AdminController', 'analytics');

// Admin Settings
$router->addRoute('GET', '/admin/settings', 'AdminController', 'settings');
$router->addRoute('POST', '/admin/settings', 'AdminController', 'updateSettings');

// Admin Maintenance
$router->addRoute('GET', '/admin/maintenance', 'AdminController', 'maintenance');
$router->addRoute('POST', '/admin/maintenance', 'AdminController', 'performMaintenance');
$router->addRoute('POST', '/admin/maintenance/perform', 'AdminController', 'performMaintenance');
$router->addRoute('POST', '/admin/maintenance/run', 'AdminController', 'performMaintenance'); // ADD THIS LINE
$router->addRoute('POST', '/admin/backup', 'AdminController', 'createBackup');
$router->addRoute('POST', '/admin/maintenance/backup', 'AdminController', 'createBackup');

// Admin Profile
$router->addRoute('GET', '/admin/profile', 'AdminController', 'profile');
$router->addRoute('POST', '/admin/profile', 'AdminController', 'profile');

// ============================================================================
// E-RESOURCES ROUTES
// ============================================================================

$router->addRoute('GET', '/e-resources', 'EResourceController', 'index');
$router->addRoute('GET', '/e-resources/index', 'EResourceController', 'index');
$router->addRoute('GET', '/e-resources/list', 'EResourceController', 'list');
$router->addRoute('GET', '/e-resources/upload', 'EResourceController', 'showUpload');
$router->addRoute('POST', '/e-resources/upload', 'EResourceController', 'upload');
$router->addRoute('GET', '/e-resources/edit/{id}', 'EResourceController', 'showEdit');
$router->addRoute('POST', '/e-resources/update/{id}', 'EResourceController', 'update');

// Admin/Faculty Actions
$router->addRoute('GET', '/e-resources/approve/{id}', 'EResourceController', 'approve');
$router->addRoute('GET', '/e-resources/reject/{id}', 'EResourceController', 'reject');
$router->addRoute('GET', '/e-resources/delete/{id}', 'EResourceController', 'delete');

// User Actions
$router->addRoute('GET', '/e-resources/obtain/{id}', 'EResourceController', 'obtain');
$router->addRoute('GET', '/my-e-resources', 'EResourceController', 'myResources');
$router->addRoute('GET', '/e-resources/my-library', 'EResourceController', 'myResources');

// Admin E-Resources view
$router->addRoute('GET', '/admin/eresources', 'EResourceController', 'index');

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

// TEMPORARY DEBUG - Remove after fixing
error_log("=== REGISTERED ROUTES ===");
$allRoutes = $router->getRoutes();
error_log("Total routes registered: " . count($allRoutes));

// Group routes by controller for better debugging
$routesByController = [];
foreach ($allRoutes as $route) {
    $controller = $route['controller'];
    if (!isset($routesByController[$controller])) {
        $routesByController[$controller] = [];
    }
    $routesByController[$controller][] = "{$route['method']} {$route['path']} -> {$route['action']}";
}

foreach ($routesByController as $controller => $routes) {
    error_log("$controller:");
    foreach ($routes as $routeInfo) {
        error_log("  $routeInfo");
    }
}

error_log("=== STARTING DISPATCH ===");

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
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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