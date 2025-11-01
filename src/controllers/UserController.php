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
     * Reserve a book (send to borrow_requests table)
     */
    public function reserve($params = []) {
        error_log("=== RESERVE METHOD CALLED ===");
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("GET params: " . print_r($_GET, true));
        error_log("POST params: " . print_r($_POST, true));
        error_log("Route params: " . print_r($params, true));
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        
        $this->requireLogin();

        $userId = $_SESSION['userId'];
        error_log("User ID: $userId");
        
        // FIXED: Support both /user/reserve?isbn=... and /user/reserve/{isbn}
        $isbn = $_GET['isbn'] ?? ($params['isbn'] ?? null);
        error_log("ISBN extracted: " . ($isbn ?? 'NULL'));

        if (!$isbn) {
            error_log("ERROR: No ISBN provided");
            $_SESSION['error'] = 'No book specified for reservation';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }

        global $mysqli;
        error_log("Database connection check: " . ($mysqli ? 'OK' : 'FAILED'));

        // Only allow POST for reservation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Processing POST reservation request");
            
            // Check if already has a pending/approved request for this book
            $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE userId = ? AND isbn = ? AND status IN ('Pending','Approved') AND dueDate >= CURDATE()");
            $stmt->bind_param("ss", $userId, $isbn);
            $stmt->execute();
            $existingCount = $stmt->get_result()->num_rows;
            error_log("Existing requests count: $existingCount");
            
            if ($existingCount > 0) {
                error_log("User already has pending/approved request");
                $_SESSION['error'] = 'You already have a pending or approved request for this book.';
                $stmt->close();
                header('Location: ' . BASE_URL . 'user/books');
                exit;
            }
            $stmt->close();

            // Only allow reservation for 1 day
            $dueDate = date('Y-m-d', strtotime('+1 day'));
            error_log("Due date set to: $dueDate");

            // Insert into borrow_requests
            $stmt = $mysqli->prepare("INSERT INTO borrow_requests (userId, isbn, dueDate, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("sss", $userId, $isbn, $dueDate);
            
            if ($stmt->execute()) {
                error_log("✓ Reservation inserted successfully");
                $_SESSION['success'] = 'Reservation request sent! Awaiting admin approval.';
            } else {
                error_log("✗ Failed to insert reservation: " . $stmt->error);
                $_SESSION['error'] = 'Failed to send reservation request.';
            }
            $stmt->close();

            error_log("Redirecting to reserved-books page");
            header('Location: ' . BASE_URL . 'user/reserved-books');
            exit;
        }

        // GET: Show confirmation page
        error_log("Loading reservation confirmation page");
        
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$book) {
            error_log("ERROR: Book not found for ISBN: $isbn");
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("Book found: " . print_r($book, true));
        error_log("Loading view: users/reserve");

        $this->data['book'] = $book;
        $this->view('users/reserve', $this->data);
        
        error_log("=== RESERVE METHOD COMPLETED ===");
    }

    /**
     * Show user's reserved books (borrow requests)
     */
    public function reservedBooks() {
        $this->requireLogin();
        global $mysqli;
        $userId = $_SESSION['userId'];

        $sql = "SELECT br.*, b.bookName, b.authorName 
                FROM borrow_requests br
                LEFT JOIN books b ON br.isbn = b.isbn
                WHERE br.userId = ?
                ORDER BY br.requestDate DESC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();

        $this->data['requests'] = $requests;
        $this->view('users/reserved-books', $this->data);
    }

    /**
     * View book details - FIXED VERSION
     */
    public function viewBook($params = []) {
        error_log("=== VIEW BOOK METHOD CALLED ===");
        error_log("Full URL: " . $_SERVER['REQUEST_URI']);
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("GET params: " . print_r($_GET, true));
        error_log("Route params: " . print_r($params, true));
        
        // SIMPLIFIED AUTHENTICATION - Just check if logged in
        if (!isset($_SESSION['userId'])) {
            error_log("ERROR: User not logged in");
            $_SESSION['error'] = 'Please login to view book details';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $userId = $_SESSION['userId'];
        $userType = $_SESSION['userType'] ?? 'Unknown';
        error_log("User authenticated - ID: $userId, Type: $userType");
        
        // FIXED: Get ISBN from URL parameter or query string
        $isbn = $_GET['isbn'] ?? ($params['isbn'] ?? null);
        error_log("ISBN extracted: " . ($isbn ?? 'NULL'));
        
        if (!$isbn) {
            error_log("ERROR: No ISBN provided");
            $_SESSION['error'] = 'No book specified';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("Fetching book details for ISBN: $isbn");
        
        global $mysqli;
        error_log("Database connection check: " . ($mysqli ? 'OK' : 'FAILED'));
        
        // Fetch book details
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();
        
        if (!$book) {
            error_log("ERROR: Book not found for ISBN: $isbn");
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'user/books');
            exit;
        }
        
        error_log("Book found: " . print_r($book, true));
        error_log("Loading view: users/view-book");
        
        // FIXED: Use $this->view() method instead of require_once
        $this->data['book'] = $book;
        $this->view('users/view-book', $this->data);
        
        error_log("=== VIEW BOOK METHOD COMPLETED ===");
    }
}