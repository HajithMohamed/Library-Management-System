<?php
// Centralized notification handler for session-based toasts/modals
namespace App\Helpers;

class NotificationHelper
{
    const SESSION_KEY = 'notifications';
    const TYPES = ['success', 'error', 'warning', 'info'];

    public static function add($type, $message)
    {
        if (!in_array($type, self::TYPES)) {
            $type = 'info';
        }
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION[self::SESSION_KEY][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public static function success($message)
    {
        self::add('success', $message);
    }

    public static function error($message)
    {
        self::add('error', $message);
    }

    public static function warning($message)
    {
        self::add('warning', $message);
    }

    public static function info($message)
    {
        self::add('info', $message);
    }

    public static function getNotifications()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $notifications = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]); // Flash: clear after read
        return $notifications;
    }
}
