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
        
        // Redirect Faculty users to their own profile page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/profile');
            return;
        }
        
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
        
        // Redirect Faculty users to their own fines page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/fines');
            return;
        }
        
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
        
        // Redirect Faculty users to their own fines page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/fines');
            return;
        }
        
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

    /**
     * Display user notifications
     */
    public function notifications()
    {
        $this->requireLogin();
        
        // Redirect Faculty users to their own notifications page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/notifications');
            return;
        }
        
        $userId = $_SESSION['userId'];
        $userType = $_SESSION['userType'] ?? 'Student';
        
        // Handle mark as read
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
            $notificationId = $_POST['notification_id'] ?? 0;
            $this->markNotificationRead($notificationId);
            $this->redirect('user/notifications');
            return;
        }
        
        // Get notifications with proper user email and type based on userId
        global $mysqli;
        
        // Simpler query - get all notifications for this user OR system-wide notifications
        $sql = "SELECT 
                    n.id,
                    n.userId,
                    n.title,
                    n.message,
                    n.type,
                    n.priority,
                    n.isRead,
                    n.relatedId,
                    n.createdAt,
                    u.userType,
                    u.username,
                    u.emailId
                FROM notifications n
                LEFT JOIN users u ON n.userId = u.userId
                WHERE n.userId = ? OR n.userId IS NULL
                ORDER BY n.isRead ASC, n.createdAt DESC";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        $this->data['notifications'] = $notifications;
        $this->data['userType'] = $userType;
        $this->view('users/notifications', $this->data);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId = null)
    {
        $this->requireLogin();
        
        if (!$notificationId && isset($_POST['notification_id'])) {
            $notificationId = $_POST['notification_id'];
        }
        
        if ($notificationId) {
            global $mysqli;
            $sql = "UPDATE notifications SET isRead = 1 WHERE id = ? AND userId = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('is', $notificationId, $_SESSION['userId']);
            $stmt->execute();
        }
        
        return true;
    }
    
    /**
     * Reserve a book
     */
    public function reserve() {
        // DEBUG: Log session data
        error_log("=== RESERVE METHOD CALLED ===");
        error_log("Full URL: " . $_SERVER['REQUEST_URI']);
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("GET data: " . print_r($_GET, true));
        error_log("POST data: " . print_r($_POST, true));
        
        // Check authentication
        if (!isset($_SESSION['userId'])) {
            error_log("ERROR: userId not in session - redirecting to login");
            error_log("Available session keys: " . implode(', ', array_keys($_SESSION)));
            $_SESSION['error'] = 'Please login to reserve books';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $userId = $_SESSION['userId'];
        $userType = $_SESSION['userType'] ?? null;
        
        error_log("SUCCESS: User authenticated - ID: $userId, Type: " . ($userType ?? 'NULL'));
        
        // Check user role - allow 'Student', 'Faculty'
        if (!in_array($userType, ['Student', 'Faculty'])) {
            error_log("ERROR: Invalid user type - Type: " . ($userType ?? 'NULL'));
            error_log("Allowed types: Student, Faculty");
            $_SESSION['error'] = 'Access denied. Only students and faculty can reserve books.';
            header('Location: ' . BASE_URL . 'user/dashboard');
            exit;
        }
        
        error_log("SUCCESS: User role validated");
        
        // Get ISBN from query parameter
        $isbn = $_GET['isbn'] ?? null;
        error_log("ISBN from GET: " . ($isbn ?? 'NULL'));
        
        if (!$isbn) {
            error_log("ERROR: No ISBN provided in URL");
            $_SESSION['error'] = 'No book specified for reservation';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("SUCCESS: ISBN received: $isbn");
        
        global $mysqli;
        
        // If POST request, process the reservation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("=== PROCESSING RESERVATION (POST) ===");
            // Check if book exists
            $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();
            $stmt->close();
            
            if (!$book) {
                $_SESSION['error'] = 'Book not found';
                header('Location: ' . BASE_URL . 'user/books');
                exit;
            }
            
            // Check if user already has an active reservation for this book
            $checkStmt = $mysqli->prepare("SELECT * FROM book_reservations WHERE userId = ? AND isbn = ? AND reservationStatus = 'Active'");
            $checkStmt->bind_param("ss", $userId, $isbn);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows > 0) {
                $_SESSION['error'] = 'You already have an active reservation for this book';
                $checkStmt->close();
                header('Location: ' . BASE_URL . 'user/books');
                exit;
            }
            $checkStmt->close();
            
            // Calculate expiry date (7 days from now)
            $expiryDate = date('Y-m-d', strtotime('+7 days'));
            
            // Insert reservation into book_reservations table
            $insertStmt = $mysqli->prepare("INSERT INTO book_reservations (userId, isbn, reservationStatus, expiryDate) VALUES (?, ?, 'Active', ?)");
            $insertStmt->bind_param("sss", $userId, $isbn, $expiryDate);
            
            if ($insertStmt->execute()) {
                $_SESSION['success'] = 'Book reserved successfully! Your reservation will expire in 7 days.';
            } else {
                $_SESSION['error'] = 'Failed to reserve book. Please try again.';
            }
            $insertStmt->close();
            
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        // GET request - show reservation confirmation page
        error_log("=== SHOWING RESERVATION PAGE (GET) ===");
        // Fetch book details
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();
        
        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        // Check if user already has reservation
        $checkStmt = $mysqli->prepare("SELECT * FROM book_reservations WHERE userId = ? AND isbn = ? AND reservationStatus = 'Active'");
        $checkStmt->bind_param("ss", $userId, $isbn);
        $checkStmt->execute();
        $existingReservation = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();
        
        error_log("=== LOADING RESERVE VIEW ===");
        // Load reserve view
        $pageTitle = 'Reserve Book';
        require_once APP_ROOT . '/views/users/reserve.php';
    }
    
    public function viewBook() {
        // DEBUG: Log session data
        error_log("=== VIEW BOOK METHOD CALLED ===");
        error_log("Full URL: " . $_SERVER['REQUEST_URI']);
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("GET data: " . print_r($_GET, true));
        
        // Check authentication
        if (!isset($_SESSION['userId'])) {
            error_log("ERROR: userId not in session for viewBook");
            error_log("Available session keys: " . implode(', ', array_keys($_SESSION)));
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        error_log("SUCCESS: User authenticated for viewBook - ID: " . $_SESSION['userId']);
        
        // Get ISBN from URL parameter
        $isbn = $_GET['isbn'] ?? null;
        error_log("ISBN from GET: " . ($isbn ?? 'NULL'));
        
        if (!$isbn) {
            error_log("ERROR: No ISBN provided for viewBook");
            $_SESSION['error'] = 'No book specified';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("SUCCESS: Fetching book details for ISBN: $isbn");
        global $mysqli;
        
        // Fetch book details
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();
        
        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        // Check if user has active reservation
        $userId = $_SESSION['userId'];
        $resStmt = $mysqli->prepare("SELECT * FROM book_reservations WHERE userId = ? AND isbn = ? AND reservationStatus = 'Active'");
        $resStmt->bind_param("ss", $userId, $isbn);
        $resStmt->execute();
        $hasReservation = $resStmt->get_result()->num_rows > 0;
        $resStmt->close();
        
        error_log("=== LOADING VIEW-BOOK VIEW ===");
        // Load view-book view
        $pageTitle = 'Book Details';
        require_once APP_ROOT . '/views/users/view-book.php';
    }
}
