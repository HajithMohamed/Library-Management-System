<?php

namespace App\Controllers;

use App\Services\AdminService;
use App\Helpers\AuthHelper;

class AdminController
{
    private $adminService;
    private $authHelper;

    public function __construct()
    {
        $this->adminService = new AdminService();
        $this->authHelper = new AuthHelper();
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $this->authHelper->requireAdmin();
        
        $stats = $this->adminService->getDashboardStats();
        $recentTransactions = $this->adminService->getRecentTransactions(10);
        $popularBooks = $this->adminService->getPopularBooks(5);
        $notifications = $this->adminService->getAllNotifications(null, null, true);
        $systemHealth = $this->adminService->getSystemHealth();
        
        $this->render('admin/dashboard', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'popularBooks' => $popularBooks,
            'notifications' => $notifications,
            'systemHealth' => $systemHealth
        ]);
    }

    /**
     * Manage Users
     */
    public function users()
    {
        $this->authHelper->requireAdmin();
        
        $search = $_GET['search'] ?? '';
        $users = $this->adminService->getAllUsers($search);
        
        $this->render('admin/users', [
            'users' => $users,
            'search' => $search
        ]);
    }

    /**
     * Delete User
     */
    public function deleteUser()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['userId'] ?? '';
            
            if ($this->adminService->deleteUser($userId)) {
                $_SESSION['success'] = 'User deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete user. User may have active transactions.';
            }
        }
        
        $this->redirect('/admin/users');
    }

    /**
     * Fine Management
     */
    public function fines()
    {
        $this->authHelper->requireAdmin();
        
        $status = $_GET['status'] ?? null;
        $userId = $_GET['userId'] ?? null;
        $fines = $this->adminService->getAllFines($status, $userId);
        $fineSettings = $this->adminService->getFineSettings();
        
        $this->render('admin/fines', [
            'fines' => $fines,
            'fineSettings' => $fineSettings,
            'currentStatus' => $status,
            'currentUserId' => $userId
        ]);
    }

    /**
     * Update Fine Status
     */
    public function updateFines()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'update_status':
                    $transactionId = $_POST['transactionId'] ?? '';
                    $status = $_POST['status'] ?? '';
                    $paymentMethod = $_POST['paymentMethod'] ?? null;
                    
                    if ($this->adminService->updateFineStatus($transactionId, $status, $paymentMethod)) {
                        $_SESSION['success'] = 'Fine status updated successfully.';
                    } else {
                        $_SESSION['error'] = 'Failed to update fine status.';
                    }
                    break;
                    
                case 'update_settings':
                    $settings = $_POST['settings'] ?? [];
                    $updated = $this->adminService->updateFineSettings($settings);
                    $_SESSION['success'] = "Updated {$updated} fine settings.";
                    break;
                    
                case 'update_all_fines':
                    $updatedCount = $this->adminService->updateAllFines();
                    $_SESSION['success'] = "Updated {$updatedCount} fines.";
                    break;
            }
        }
        
        $this->redirect('/admin/fines');
    }

    /**
     * Borrow Requests Management
     */
    public function borrowRequests()
    {
        $this->authHelper->requireAdmin();
        
        $status = $_GET['status'] ?? 'pending';
        $requests = $this->adminService->getBorrowRequests($status);
        
        $this->render('admin/borrow-requests', [
            'requests' => $requests,
            'currentStatus' => $status
        ]);
    }

    /**
     * Handle Borrow Request Actions
     */
    public function handleBorrowRequest()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestId = $_POST['requestId'] ?? '';
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'approve':
                    if ($this->adminService->approveBorrowRequest($requestId)) {
                        $_SESSION['success'] = 'Borrow request approved successfully.';
                    } else {
                        $_SESSION['error'] = 'Failed to approve borrow request.';
                    }
                    break;
                    
                case 'reject':
                    $reason = $_POST['reason'] ?? '';
                    if ($this->adminService->rejectBorrowRequest($requestId, $reason)) {
                        $_SESSION['success'] = 'Borrow request rejected.';
                    } else {
                        $_SESSION['error'] = 'Failed to reject borrow request.';
                    }
                    break;
            }
        }
        
        $this->redirect('/admin/borrow-requests');
    }

    /**
     * Notifications Management
     */
    public function notifications()
    {
        $this->authHelper->requireAdmin();
        
        $type = $_GET['type'] ?? null;
        $unreadOnly = isset($_GET['unread']) ? true : false;
        $notifications = $this->adminService->getAllNotifications(null, $type, $unreadOnly);
        
        $this->render('admin/notifications', [
            'notifications' => $notifications,
            'currentType' => $type,
            'unreadOnly' => $unreadOnly
        ]);
    }

    /**
     * Mark Notification as Read
     */
    public function markNotificationRead()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationId = $_POST['notificationId'] ?? '';
            
            if ($this->adminService->markNotificationAsRead($notificationId)) {
                $_SESSION['success'] = 'Notification marked as read.';
            } else {
                $_SESSION['error'] = 'Failed to mark notification as read.';
            }
        }
        
        $this->redirect('/admin/notifications');
    }

    /**
     * System Maintenance
     */
    public function maintenance()
    {
        $this->authHelper->requireAdmin();
        
        $systemHealth = $this->adminService->getSystemHealth();
        $backupHistory = $this->adminService->getBackupHistory(10);
        $maintenanceLog = $this->adminService->getMaintenanceLog(20);
        
        $this->render('admin/maintenance', [
            'systemHealth' => $systemHealth,
            'backupHistory' => $backupHistory,
            'maintenanceLog' => $maintenanceLog
        ]);
    }

    /**
     * Create Database Backup
     */
    public function createBackup()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $backupType = $_POST['backupType'] ?? 'manual';
            
            $filename = $this->adminService->createDatabaseBackup($backupType);
            
            if ($filename) {
                $_SESSION['success'] = "Backup created successfully: {$filename}";
            } else {
                $_SESSION['error'] = 'Failed to create backup.';
            }
        }
        
        $this->redirect('/admin/maintenance');
    }

    /**
     * Perform Maintenance Tasks
     */
    public function performMaintenance()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tasks = $_POST['tasks'] ?? [];
            
            if (!empty($tasks)) {
                $results = $this->adminService->performMaintenance($tasks);
                
                $successCount = 0;
                foreach ($results as $task => $result) {
                    if ($result > 0) {
                        $successCount++;
                    }
                }
                
                $_SESSION['success'] = "Completed {$successCount} maintenance tasks.";
            }
        }
        
        $this->redirect('/admin/maintenance');
    }

    /**
     * Reports
     */
    public function reports()
    {
        $this->authHelper->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $reportType = $_GET['type'] ?? 'overview';
        
        $report = $this->adminService->generateReport($reportType, $startDate, $endDate);
        
        $this->render('admin/reports', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType
        ]);
    }

    /**
     * Settings
     */
    public function settings()
    {
        $this->authHelper->requireAdmin();
        
        $fineSettings = $this->adminService->getFineSettings();
        
        $this->render('admin/settings', [
            'fineSettings' => $fineSettings
        ]);
    }

    /**
     * Update Settings
     */
    public function updateSettings()
    {
        $this->authHelper->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = $_POST['settings'] ?? [];
            $updated = $this->adminService->updateFineSettings($settings);
            $_SESSION['success'] = "Updated {$updated} settings.";
        }
        
        $this->redirect('/admin/settings');
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
