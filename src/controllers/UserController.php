<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\BorrowRecord;

class UserController extends BaseController
{
    private $userModel;
    private $bookModel;
    private $borrowModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->bookModel = new Book();
        $this->borrowModel = new BorrowRecord();
    }

    /**
     * Display user dashboard
     */
    public function dashboard()
    {
        $this->requireLogin();
        
        // Redirect Faculty users to their own dashboard
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/dashboard');
            return;
        }
        
        $userId = $_SESSION['userId'];
        $userType = $_SESSION['userType'];
        
        // Get user statistics
        $userStats = [
            'borrowed_books' => $this->borrowModel->getActiveBorrowCount($userId),
            'overdue_books' => $this->borrowModel->getOverdueCount($userId),
            'total_fines' => $this->borrowModel->getTotalFines($userId),
            'max_books' => $userType === 'Faculty' ? 5 : 3
        ];
        
        // Get recent activity
        $recentActivity = $this->borrowModel->getRecentActivity($userId, 5);
        
        // Pass data to view
        $this->data['userStats'] = $userStats;
        $this->data['recentActivity'] = $recentActivity;
        
        $this->view('users/dashboard', $this->data);
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updateProfile();
        }
        
        $userId = $_SESSION['userId'];
        $user = $this->userModel->findById($userId);
        
        $this->data['user'] = $user;
        $this->view('users/profile', $this->data);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $this->requireLogin();
        
        $userId = $_SESSION['userId'];
        $data = [
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];
        
        if ($this->userModel->updateProfile($userId, $data)) {
            $_SESSION['success'] = 'Profile updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update profile';
        }
        
        $this->redirect('user/profile');
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/profile');
            return;
        }
        
        $userId = $_SESSION['userId'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match';
            $this->redirect('user/profile');
            return;
        }
        
        if ($this->userModel->changePassword($userId, $currentPassword, $newPassword)) {
            $_SESSION['success'] = 'Password changed successfully';
        } else {
            $_SESSION['error'] = 'Current password is incorrect';
        }
        
        $this->redirect('user/profile');
    }

    /**
     * Display user fines
     */
    public function fines()
    {
        $this->requireLogin();
        
        $userId = $_SESSION['userId'];
        $fines = $this->borrowModel->getUserFines($userId);
        
        $this->data['fines'] = $fines;
        $this->view('users/fines', $this->data);
    }

    /**
     * Process fine payment
     */
    public function payFine()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/fines');
            return;
        }
        
        $borrowId = $_POST['borrow_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        
        if ($this->borrowModel->payFine($borrowId, $amount)) {
            $_SESSION['success'] = 'Fine paid successfully';
        } else {
            $_SESSION['error'] = 'Failed to process payment';
        }
        
        $this->redirect('user/fines');
    }
}
