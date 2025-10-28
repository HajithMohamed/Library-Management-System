<?php

namespace App\Router;

use App\Controllers\AdminController;

class Router{
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

    // First try exact matches
    foreach ($this->routes as $route) {
      if ($route['method'] === $method && $route['path'] === $path) {
        error_log("Route matched: {$route['controller']}::{$route['action']}");
        $this->callController($route['controller'], $route['action']);
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
        return;
      }
    }

    // 404 Not Found
    error_log("No route matched - 404");
    http_response_code(404);
    include APP_ROOT . '/views/errors/404.php';
  }

  private function callController($controller, $action, $params = [])
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

private function showDetailedError($exception, $context)
{
  http_response_code(500);
  $errorView = APP_ROOT . '/views/errors/detailed_error.php';

  if (file_exists($errorView)) {
    include $errorView;
  } else {
    echo "<h1>500 - Internal Server Error</h1>";
    echo "<p>" . htmlspecialchars($exception->getMessage()) . "</p>";
  }
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


?>