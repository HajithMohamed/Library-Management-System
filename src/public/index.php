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
define('BASE_URL', 'http://localhost:8080/');

// Include Composer autoloader
require_once APP_ROOT . '/../vendor/autoload.php';

// Include configuration
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/dbConnection.php';

// Simple routing system
class Router {
    private $routes = [];
    
    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch() {
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
    
    private function callController($controller, $action) {
        $controllerClass = "App\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            http_response_code(404);
            include APP_ROOT . '/views/errors/404.php';
            return;
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $action)) {
            http_response_code(404);
            include APP_ROOT . '/views/errors/404.php';
            return;
        }
        
        $controllerInstance->$action();
    }
}

// Initialize router and define routes
$router = new Router();

// Authentication routes
$router->addRoute('GET', '/', 'AuthController', 'login');
$router->addRoute('POST', '/', 'AuthController', 'login');
$router->addRoute('GET', '/signup', 'AuthController', 'signup');
$router->addRoute('POST', '/signup', 'AuthController', 'signup');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

// User dashboard routes
$router->addRoute('GET', '/user/dashboard', 'UserController', 'dashboard');
$router->addRoute('GET', '/user/books', 'BookController', 'userBooks');
$router->addRoute('GET', '/user/borrow', 'BookController', 'borrow');
$router->addRoute('POST', '/user/borrow', 'BookController', 'borrowBook');
$router->addRoute('GET', '/user/return', 'BookController', 'return');
$router->addRoute('POST', '/user/return', 'BookController', 'returnBook');
$router->addRoute('GET', '/user/fines', 'UserController', 'fines');
$router->addRoute('POST', '/user/pay-fine', 'UserController', 'payFine');

// Admin dashboard routes
$router->addRoute('GET', '/admin/dashboard', 'AdminController', 'dashboard');
$router->addRoute('GET', '/admin/books', 'BookController', 'adminBooks');
$router->addRoute('GET', '/admin/books/add', 'BookController', 'addBook');
$router->addRoute('POST', '/admin/books/add', 'BookController', 'createBook');
$router->addRoute('GET', '/admin/books/edit', 'BookController', 'editBook');
$router->addRoute('POST', '/admin/books/edit', 'BookController', 'updateBook');
$router->addRoute('POST', '/admin/books/delete', 'BookController', 'deleteBook');
$router->addRoute('GET', '/admin/users', 'AdminController', 'users');
$router->addRoute('POST', '/admin/users/delete', 'AdminController', 'deleteUser');

// API routes
$router->addRoute('GET', '/api/books/search', 'BookController', 'searchBooks');
$router->addRoute('GET', '/api/book/details', 'BookController', 'getBookDetails');

// Dispatch the request
$router->dispatch();
?>
