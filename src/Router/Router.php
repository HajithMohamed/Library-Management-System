<?php

namespace App\Router;

class Router
{
    private $routes = [];
    
    public function addRoute($method, $path, $controller, $action)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function getRoutes()
    {
        return $this->routes;
    }
    
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove leading slash for comparison
        $requestUri = trim($requestUri, '/');
        
        foreach ($this->routes as $route) {
            $routePath = trim($route['path'], '/');
            
            // Simple pattern matching for dynamic routes
            $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';
            
            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                
                $controllerName = 'App\\Controllers\\' . $route['controller'];
                $actionName = $route['action'];
                
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $actionName)) {
                        call_user_func_array([$controller, $actionName], $matches);
                        return;
                    }
                }
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo "404 - Route not found";
    }
}
