<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\Transaction;
use App\Services\AdminService;
use App\Helpers\AuthHelper;

class AdminController
{
    private $userModel;
    private $bookModel;
    private $transactionModel;
    private $adminService;
    private $authHelper;

    public function __construct()
    {
        $this->userModel = new User();
        $this->bookModel = new Book();
        $this->transactionModel = new Transaction();
        $this->adminService = new AdminService();
        $this->authHelper = new AuthHelper();
    }

    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        $this->authHelper->requireAdmin();
        
        $stats = $this->adminService->getDashboardStats();
        $recentTransactions = $this->transactionModel->getAllTransactions(10);
        $popularBooks = $this->bookModel->getPopularBooks(5);
        
        $this->render('admin/dashboard', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'popularBooks' => $popularBooks
        ]);
    }

    /**
     * Display all users
     */
    public function users()
    {
        $this->authHelper->requireAdmin();
        
        $search = $_GET['search'] ?? '';
        $users = !empty($search) ? $this->userModel->searchUsers($search) : $this->userModel->getAllUsers();
        
        $this->render('admin/users', [
            'users' => $users,
            'search' => $search
        ]);
    }

    /**
     * Delete a user
     */
    public function deleteUser()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        $userId = $_POST['userId'] ?? '';
        
        if (empty($userId)) {
            $_SESSION['error'] = 'User ID is required.';
            $this->redirect('/admin/users');
            return;
        }

        // Prevent admin from deleting themselves
        if ($userId === $_SESSION['userId']) {
            $_SESSION['error'] = 'You cannot delete your own account.';
            $this->redirect('/admin/users');
            return;
        }

        if ($this->userModel->deleteUser($userId)) {
            $_SESSION['success'] = 'User deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete user. User may have active transactions.';
        }
        
        $this->redirect('/admin/users');
    }

    /**
     * Display system reports
     */
    public function reports()
    {
        $this->authHelper->requireAdmin();
        
        $reportType = $_GET['type'] ?? 'overview';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $reportData = $this->adminService->generateReport($reportType, $startDate, $endDate);
        
        $this->render('admin/reports', [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData
        ]);
    }

    /**
     * Display system settings
     */
    public function settings()
    {
        $this->authHelper->requireAdmin();
        
        $this->render('admin/settings');
    }

    /**
     * Update system settings
     */
    public function updateSettings()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }

        // This would typically update system settings in a database
        // For now, we'll just show a success message
        $_SESSION['success'] = 'Settings updated successfully!';
        $this->redirect('/admin/settings');
    }

    /**
     * Display fine management
     */
    public function fines()
    {
        $this->authHelper->requireAdmin();
        
        $overdueTransactions = $this->transactionModel->getOverdueTransactions();
        $fineStats = $this->transactionModel->getFineStats();
        
        $this->render('admin/fines', [
            'overdueTransactions' => $overdueTransactions,
            'fineStats' => $fineStats
        ]);
    }

    /**
     * Update all fines
     */
    public function updateFines()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/fines');
            return;
        }

        $updatedCount = $this->adminService->updateAllFines();
        $_SESSION['success'] = "Updated fines for {$updatedCount} overdue transactions.";
        $this->redirect('/admin/fines');
    }

    /**
     * Display backup and maintenance
     */
    public function maintenance()
    {
        $this->authHelper->requireAdmin();
        
        $this->render('admin/maintenance');
    }

    /**
     * Create database backup
     */
    public function createBackup()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/maintenance');
            return;
        }

        $backupFile = $this->adminService->createDatabaseBackup();
        if ($backupFile) {
            $_SESSION['success'] = "Database backup created: {$backupFile}";
        } else {
            $_SESSION['error'] = 'Failed to create database backup.';
        }
        
        $this->redirect('/admin/maintenance');
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
