<?php

namespace App\Helpers;

class AuthHelper
{
    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['userId']) && !empty($_SESSION['userId']);
    }

    /**
     * Get current user data
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'userId' => $_SESSION['userId'],
            'userType' => $_SESSION['userType'],
            'emailId' => $_SESSION['emailId'] ?? null
        ];
    }

    /**
     * Require authentication
     */
    public function requireAuth($allowedTypes = [])
    {
        if (!$this->isLoggedIn()) {
            $this->redirectToLogin();
        }

        if (!empty($allowedTypes)) {
            $userType = $_SESSION['userType'];
            if (!in_array($userType, $allowedTypes)) {
                $this->showAccessDenied();
            }
        }
    }

    /**
     * Require admin access
     */
    public function requireAdmin()
    {
        $this->requireAuth(['Admin']);
    }

    /**
     * Require user access (Student or Faculty)
     */
    public function requireUser()
    {
        $this->requireAuth(['Student', 'Faculty']);
    }

    /**
     * Redirect to login page
     */
    public function redirectToLogin()
    {
        header('Location: ' . BASE_URL);
        exit;
    }

    /**
     * Show access denied page
     */
    public function showAccessDenied()
    {
        http_response_code(403);
        include APP_ROOT . '/views/errors/403.php';
        exit;
    }

    /**
     * Redirect based on user type
     */
    public function redirectByUserType()
    {
        if (!$this->isLoggedIn()) {
            $this->redirectToLogin();
        }

        $userType = $_SESSION['userType'];
        
        switch ($userType) {
            case 'Admin':
                header('Location: ' . BASE_URL . 'admin/dashboard');
                break;
            case 'Student':
            case 'Faculty':
                header('Location: ' . BASE_URL . 'user/dashboard');
                break;
            default:
                $this->redirectToLogin();
        }
        exit;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        session_destroy();
        $this->redirectToLogin();
    }

    /**
     * Hash password
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate CSRF token
     */
    public function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if user can borrow books
     */
    public function canBorrowBooks($userId)
    {
        // This would typically check user status, fines, etc.
        // For now, just check if user is verified
        return $this->isLoggedIn() && in_array($_SESSION['userType'], ['Student', 'Faculty']);
    }

    /**
     * Get user borrowing limits
     */
    public function getBorrowingLimits($userType)
    {
        switch ($userType) {
            case 'Faculty':
                return 5;
            case 'Student':
                return 3;
            default:
                return 0;
        }
    }
}
?>
