<?php

namespace App\Middleware;

use App\Models\User;

class CheckPermission
{
    /**
     * Handle permission check
     * 
     * @param string $permission Permission slug
     */
    public static function handle($permission)
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

        $userId = $_SESSION['user_id'];
        $userModel = new User();

        if (!$userModel->hasPermission($userId, $permission)) {
            // Log access attempt failure
            $audit = new \App\Services\AuditLogger();
            $audit->log($userId, 'access_denied', ['permission' => $permission, 'url' => $_SERVER['REQUEST_URI']]);

            http_response_code(403);
            if (self::isJsonRequest()) {
                echo json_encode(['error' => 'Forbidden: Missing Permission ' . $permission]);
            } else {
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
