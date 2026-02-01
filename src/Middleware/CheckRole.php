<?php

namespace App\Middleware;

use App\Models\User;

class CheckRole
{
    /**
     * Handle role check
     * 
     * @param string|array $roles Single role slug or array of role slugs
     */
    public static function handle($roles)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            // Not authenticated
            http_response_code(401);
            if (self::isJsonRequest()) {
                echo json_encode(['error' => 'Unauthorized']);
            } else {
                header('Location: /login');
            }
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userModel = new User();

        // Convert string to array
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!$userModel->hasAnyRole($userId, $roles)) {
            // Forbidden
            http_response_code(403);
            if (self::isJsonRequest()) {
                echo json_encode(['error' => 'Forbidden: Insufficient Role']);
            } else {
                $_SESSION['error'] = 'You do not have permission to access this area.';
                // Redirect to dashboard or show 403 view
                global $router;
                if (isset($router) && method_exists($router, 'show403')) {
                    $router->show403();
                } else {
                    echo "403 Forbidden";
                }
            }
            exit;
        }
    }

    private static function isJsonRequest()
    {
        return (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );
    }
}
