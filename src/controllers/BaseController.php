<?php

namespace App\Controllers;

class BaseController
{
    protected $db;
    protected $data = [];

    public function __construct()
    {
        global $mysqli;
        
        if (!isset($mysqli) || !($mysqli instanceof \mysqli)) {
            throw new \Exception("Database connection not available");
        }
        
        $this->db = $mysqli;
        
        // Set common data available to all views
        $this->data['pageTitle'] = 'Library System';
        $this->data['currentUser'] = $_SESSION['userId'] ?? null;
        $this->data['userType'] = $_SESSION['userType'] ?? null;
        $this->data['username'] = $_SESSION['username'] ?? null;
    }

    /**
     * Load a view file
     */
    protected function view($view, $data = [])
    {
        // Merge controller data with passed data
        $data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($data);
        
        // Build view path
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }
        
        require_once $viewFile;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect($path)
    {
        $url = BASE_URL . $path;
        header("Location: {$url}");
        exit;
    }

    /**
     * Check if user is logged in
     */
    protected function requireLogin($allowedTypes = [])
    {
        if (!isset($_SESSION['userId'])) {
            $_SESSION['error'] = 'Please login to access this page';
            $this->redirect('login');
            exit;
        }
        if (!empty($allowedTypes)) {
            $userType = strtolower($_SESSION['userType'] ?? '');
            $allowedTypesLower = array_map('strtolower', $allowedTypes);
            if (!in_array($userType, $allowedTypesLower)) {
                $_SESSION['error'] = 'Access denied';
                $this->redirect('login');
                exit;
            }
        }
    }

    /**
     * Check if user is admin
     */
    protected function requireAdmin()
    {
        $this->requireLogin(['Admin']);
    }

    /**
     * Set flash message
     */
    protected function setFlash($type, $message)
    {
        $_SESSION[$type] = $message;
    }

    /**
     * Get and clear flash message
     */
    protected function getFlash($type)
    {
        if (isset($_SESSION[$type])) {
            $message = $_SESSION[$type];
            unset($_SESSION[$type]);
            return $message;
        }
        return null;
    }

    /**
     * Validate CSRF token
     */
    protected function validateCSRF()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    /**
     * Generate CSRF token
     */
    protected function generateCSRF()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Sanitize input
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Require user authentication
     */
    protected function requireAuth()
    {
        if (!isset($_SESSION['userId'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Require user to have specific role
     */
    protected function requireRole($allowedRoles)
    {
        $this->requireAuth();
        
        $userType = $_SESSION['userType'] ?? null;
        
        if (!in_array($userType, (array)$allowedRoles)) {
            header('Location: ' . BASE_URL . '403');
            exit;
        }
    }
}
