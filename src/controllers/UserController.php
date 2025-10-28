<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Services\UserService;
use App\Helpers\AuthHelper;

class UserController
{
    private $userModel;
    private $transactionModel;
    private $userService;
    private $authHelper;

    public function __construct()
    {
        $this->userModel = new User();
        $this->transactionModel = new Transaction();
        $this->userService = new UserService();
        $this->authHelper = new AuthHelper();
    }

    /**
     * Display user dashboard
     */
    public function dashboard()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }
        
        // Get user type and redirect to appropriate dashboard
        $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;
        
        // Redirect faculty to faculty dashboard
        if ($userType === 'Faculty') {
            header('Location: /faculty/dashboard');
            exit();
        }
        
        // Redirect admin to admin dashboard
        if ($userType === 'Admin') {
            header('Location: /admin/dashboard');
            exit();
        }
        
        // Continue with student/user dashboard
        $this->authHelper->requireAuth(['Student', 'User']);

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
        $user = $this->userModel->getUserById($userId);
        $borrowedBooks = $this->transactionModel->getBorrowedBooks($userId);
        $transactionHistory = $this->transactionModel->getUserTransactionHistory($userId, 10);
        
        // Calculate stats
        $stats = [
            'borrowed_books' => count($borrowedBooks),
            'overdue_books' => 0,
            'total_fines' => 0,
            'max_books' => $this->authHelper->getBorrowingLimits($user['userType'])
        ];
        
        // Calculate overdue books and total fines
        foreach ($borrowedBooks as $book) {
            $fine = $this->userService->calculateFine($book['borrowDate']);
            if ($fine > 0) {
                $stats['overdue_books']++;
                $stats['total_fines'] += $fine;
            }
        }
        
        $this->render('users/dashboard', [
            'user' => $user,
            'borrowedBooks' => $borrowedBooks,
            'transactionHistory' => $transactionHistory,
            'stats' => $stats
        ]);
    }

    /**
     * Display user fines
     */
    public function fines()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }
        
        // Get user type and redirect to appropriate page
        $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;
        
        // Redirect faculty to faculty fines page
        if ($userType === 'Faculty') {
            header('Location: /faculty/fines');
            exit();
        }
        
        // Redirect admin to admin dashboard
        if ($userType === 'Admin') {
            header('Location: /admin/dashboard');
            exit();
        }
        
        // Continue with student/user fines page
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        $userId = $_SESSION['userId'];
        $borrowedBooks = $this->transactionModel->getBorrowedBooks($userId);
        $totalFine = 0;
        
        // Calculate fines for each borrowed book
        foreach ($borrowedBooks as &$book) {
            $fine = $this->userService->calculateFine($book['borrowDate']);
            $book['calculated_fine'] = $fine;
            $totalFine += $fine;
        }
        
        $this->render('users/fines', [
            'borrowedBooks' => $borrowedBooks,
            'totalFine' => $totalFine
        ]);
    }

    /**
     * Process fine payment
     */
    public function payFine()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/fines');
            return;
        }

        $tid = $_POST['tid'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        
        if (empty($tid) || $amount <= 0) {
            $_SESSION['error'] = 'Invalid payment details.';
            $this->redirect('/user/fines');
            return;
        }

        if ($this->transactionModel->payFine($tid, $amount)) {
            $_SESSION['success'] = 'Fine payment processed successfully!';
        } else {
            $_SESSION['error'] = 'Failed to process fine payment.';
        }
        
        $this->redirect('/user/fines');
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        $userId = $_SESSION['userId'];
        $user = $this->userModel->getUserById($userId);
        
        $this->render('users/profile', ['user' => $user]);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
            return;
        }

        $userId = $_SESSION['userId'];
        $data = [
            'gender' => $_POST['gender'] ?? '',
            'dob' => $_POST['dob'] ?? '',
            'emailId' => $_POST['emailId'] ?? '',
            'phoneNumber' => $_POST['phoneNumber'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        // Validate data
        $errors = [];
        if (empty($data['emailId']) || !filter_var($data['emailId'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }
        if (empty($data['phoneNumber']) || !preg_match('/^\d{10}$/', $data['phoneNumber'])) {
            $errors[] = 'Valid phone number is required';
        }
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required';
        }
        if (empty($data['dob'])) {
            $errors[] = 'Date of birth is required';
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            $this->redirect('/user/profile');
            return;
        }

        // Check if email is already taken by another user
        if ($this->userModel->emailExists($data['emailId'], $userId)) {
            $_SESSION['error'] = 'Email address is already taken by another user.';
            $this->redirect('/user/profile');
            return;
        }

        $updated = $this->userModel->updateUser($userId, $data);

        // Handle profile image upload (optional)
        if (!empty($_FILES['profileImage']) && is_uploaded_file($_FILES['profileImage']['tmp_name'])) {
            $file = $_FILES['profileImage'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            if (isset($allowed[$file['type']]) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = $allowed[$file['type']];
                $targetDir = APP_ROOT . '/public/assets/images/users/';
                if (!is_dir($targetDir)) { @mkdir($targetDir, 0775, true); }
                // Remove older formats for this user
                foreach (['jpg','jpeg','png'] as $oldExt) {
                    $oldPath = $targetDir . $userId . '.' . $oldExt;
                    if (file_exists($oldPath)) { @unlink($oldPath); }
                }
                $target = $targetDir . $userId . '.' . $ext;
                if (@move_uploaded_file($file['tmp_name'], $target)) {
                    // Persist relative path in DB
                    $relativePath = 'assets/images/users/' . $userId . '.' . $ext;
                    $data['profileImage'] = $relativePath;
                    $this->userModel->updateUser($userId, $data);
                    $updated = true;
                } else {
                    $_SESSION['error'] = 'Profile saved, but image upload failed.';
                }
            } else {
                $_SESSION['error'] = 'Invalid image. Use JPG/PNG under 2 MB.';
            }
        }

        if ($updated) {
            $_SESSION['success'] = 'Profile updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update profile. Please try again.';
        }
        
        $this->redirect('/user/profile');
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
            return;
        }

        $userId = $_SESSION['userId'];
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'All password fields are required.';
            $this->redirect('/user/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New password and confirmation do not match.';
            $this->redirect('/user/profile');
            return;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'New password must be at least 6 characters long.';
            $this->redirect('/user/profile');
            return;
        }

        // Verify current password
        $user = $this->userModel->getUserById($userId);
        if (!$this->authHelper->verifyPassword($currentPassword, $user['password'])) {
            $_SESSION['error'] = 'Current password is incorrect.';
            $this->redirect('/user/profile');
            return;
        }

        // Update password
        $hashedPassword = $this->authHelper->hashPassword($newPassword);
        if ($this->userModel->updatePassword($userId, $hashedPassword)) {
            $_SESSION['success'] = 'Password changed successfully!';
        } else {
            $_SESSION['error'] = 'Failed to change password. Please try again.';
        }
        
        $this->redirect('/user/profile');
    }

    /**
     * Render a view with data
     */
    private function render($view, $data = [])
    {
        extract($data);
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            http_response_code(404);
            include APP_ROOT . '/views/errors/404.php';
        }
    }

    /**
     * Redirect to a URL
     */
    private function redirect($url)
    {
        header('Location: ' . BASE_URL . ltrim($url, '/'));
        exit;
    }
}
?>
