<?php

namespace App\Middleware;

use App\Models\User;

/**
 * PermissionMiddleware - Unified middleware for permission and role checking
 * 
 * This class provides static methods for checking user permissions and roles
 * in a backward-compatible way with existing code.
 */
class PermissionMiddleware
{
    /**
     * Check if user has a specific permission
     * 
     * @param string $userId The user ID to check
     * @param string $permission The permission slug to check (e.g., 'books.create')
     * @return bool True if user has permission, false otherwise
     */
    public static function checkPermission($userId, $permission)
    {
        if (empty($userId)) {
            return false;
        }

        $userModel = new User();
        return $userModel->hasPermission($userId, $permission);
    }

    /**
     * Check if user has a specific role
     * 
     * @param string $userId The user ID to check
     * @param string $role The role slug to check (e.g., 'admin', 'student')
     * @return bool True if user has role, false otherwise
     */
    public static function checkRole($userId, $role)
    {
        if (empty($userId)) {
            return false;
        }

        $userModel = new User();
        return $userModel->hasRole($userId, $role);
    }

    /**
     * Check if user has any of the specified roles
     * 
     * @param string $userId The user ID to check
     * @param array $roles Array of role slugs
     * @return bool True if user has at least one role, false otherwise
     */
    public static function hasAnyRole($userId, $roles)
    {
        if (empty($userId) || empty($roles)) {
            return false;
        }

        $userModel = new User();
        return $userModel->hasAnyRole($userId, $roles);
    }

    /**
     * Check if user has all of the specified permissions
     * 
     * @param string $userId The user ID to check
     * @param array $permissions Array of permission slugs
     * @return bool True if user has all permissions, false otherwise
     */
    public static function hasAllPermissions($userId, $permissions)
    {
        if (empty($userId) || empty($permissions)) {
            return false;
        }

        $userModel = new User();
        
        foreach ($permissions as $permission) {
            if (!$userModel->hasPermission($userId, $permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Require permission (exit if not authorized)
     * 
     * This method checks if the user has the required permission and exits with
     * appropriate error if not.
     * 
     * @param string $permission The permission slug required
     */
    public static function requirePermission($permission)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            if (self::isJsonRequest()) {
                echo json_encode(['error' => 'Unauthorized']);
            } else {
                header('Location: /login');
            }
            exit;
        }

        if (!self::checkPermission($_SESSION['user_id'], $permission)) {
            http_response_code(403);
            if (self::isJsonRequest()) {
                echo json_encode(['error' => 'Forbidden: Missing Permission ' . $permission]);
            } else {
                self::show403();
            }
            exit;
        }
    }

    /**
     * Require role (exit if not authorized)
     * 
     * This method checks if the user has the required role and exits with
     * appropriate error if not.
     * 
     * @param string|array $roles Single role slug or array of role slugs
     */
    public static function requireRole($roles)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            if (self::isJsonRequest()) {
                echo json_encode(['error' => 'Unauthorized']);
            } else {
                header('Location: /login');
            }
            exit;
        }

        // Convert string to array for uniform handling
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!self::hasAnyRole($_SESSION['user_id'], $roles)) {
            http_response_code(403);
            if (self::isJsonRequest()) {
                echo json_encode(['error' => 'Forbidden: Insufficient Role']);
            } else {
                self::show403();
            }
            exit;
        }
    }

    /**
     * Check if current request is a JSON request
     * 
     * @return bool
     */
    private static function isJsonRequest()
    {
        return (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );
    }

    /**
     * Show 403 forbidden page
     */
    private static function show403()
    {
        $_SESSION['error'] = 'You do not have permission to access this resource.';
        
        // Try to use router's 403 handler if available
        global $router;
        if (isset($router) && method_exists($router, 'show403')) {
            $router->show403();
        } else {
            // Fallback to simple 403 page
            echo "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body>";
            echo "<h1>403 Forbidden</h1>";
            echo "<p>You do not have permission to access this resource.</p>";
            echo "</body></html>";
        }
    }
}
