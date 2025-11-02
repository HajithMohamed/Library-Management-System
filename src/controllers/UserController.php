<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\Transaction;

class UserController extends BaseController
{
    private $userModel;
    private $bookModel;
    private $borrowModel;
    private $transactionModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->bookModel = new Book();
        $this->borrowModel = new BorrowRecord();
        $this->transactionModel = new Transaction();
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
        
        $userId = $_SESSION['userId'] ?? null;
        $userType = $_SESSION['userType'] ?? 'Student';
        
        if (!$userId) {
            $_SESSION['error'] = 'User ID not found. Please login again.';
            $this->redirect('login');
            return;
        }
        
        global $mysqli;
        
        try {
            // Get count of currently borrowed books (not returned)
            $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM books_borrowed WHERE userid = ? AND returnDate IS NULL");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $borrowedCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
            $stmt->close();
            
            // Get count of overdue books (dueDate passed and not returned)
            $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM books_borrowed WHERE userid = ? AND returnDate IS NULL AND dueDate < CURDATE()");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $overdueCount = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
            $stmt->close();
            
            // Calculate total fines (₹5 per day for overdue books)
            $stmt = $mysqli->prepare("SELECT isbn, dueDate FROM books_borrowed WHERE userid = ? AND returnDate IS NULL AND dueDate < CURDATE()");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $totalFines = 0;
            while ($row = $result->fetch_assoc()) {
                $dueDate = new \DateTime($row['dueDate']);
                $today = new \DateTime();
                $interval = $today->diff($dueDate);
                $daysOverdue = $interval->days;
                $totalFines += $daysOverdue * 5; // ₹5 per day
            }
            $stmt->close();
            
            // Get user statistics
            $userStats = [
                'borrowed_books' => $borrowedCount,
                'overdue_books' => $overdueCount,
                'total_fines' => $totalFines,
                'max_books' => $userType === 'Faculty' ? 5 : 3
            ];
            
            // Get recent activity (last 5 transactions)
            $stmt = $mysqli->prepare("
                SELECT 
                    bb.borrowDate as borrow_date,
                    bb.returnDate as return_date,
                    bb.dueDate as due_date,
                    b.bookName as title,
                    b.authorName as author
                FROM books_borrowed bb
                LEFT JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userid = ?
                ORDER BY bb.borrowDate DESC
                LIMIT 5
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $recentActivity = [];
            while ($row = $result->fetch_assoc()) {
                $recentActivity[] = $row;
            }
            $stmt->close();
            
        } catch (\Exception $e) {
            error_log("Error loading dashboard data: " . $e->getMessage());
            // Set default values on error
            $userStats = [
                'borrowed_books' => 0,
                'overdue_books' => 0,
                'total_fines' => 0,
                'max_books' => $userType === 'Faculty' ? 5 : 3
            ];
            $recentActivity = [];
        }
        
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
        
        // Get fines from Transaction model
        $fines = $this->transactionModel->getFinesByUserId($userId);
        
        // Ensure all records have proper structure
        foreach ($fines as &$fine) {
            $fine['title'] = $fine['bookName'] ?? 'Unknown Book';
            $fine['borrowDate'] = $fine['borrowDate'] ?? date('Y-m-d');
            $fine['fineAmount'] = $fine['fineAmount'] ?? 0;
        }
        
        $this->data['fines'] = $fines;
        $this->view('users/fines', $this->data);
    }

    /**
     * Show payment form for fine payment
     */
    public function showPaymentForm()
    {
        $this->requireLogin();
        
        // Redirect Faculty users to their own fines page
        if (isset($_SESSION['userType']) && $_SESSION['userType'] === 'Faculty') {
            $this->redirect('faculty/fines');
            return;
        }
        
        $transactionId = $_GET['tid'] ?? '';
        $amount = $_GET['amount'] ?? 0;
        
        if (!$transactionId || !$amount) {
            $_SESSION['error'] = 'Invalid payment request';
            $this->redirect('user/fines');
            return;
        }
        
        global $mysqli;
        
        // Verify the transaction belongs to this user
        $userId = $_SESSION['userId'];
        $stmt = $mysqli->prepare("
            SELECT t.*, b.bookName 
            FROM transactions t
            LEFT JOIN books b ON t.isbn = b.isbn
            WHERE t.tid = ? AND t.userId = ? AND t.fineStatus = 'unpaid'
        ");
        $stmt->bind_param("ss", $transactionId, $userId);
        $stmt->execute();
        $transaction = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$transaction) {
            $_SESSION['error'] = 'Transaction not found or already paid';
            $this->redirect('user/fines');
            return;
        }
        
        $this->data['transaction'] = $transaction;
        $this->data['amount'] = $amount;
        $this->view('users/payment-form', $this->data);
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
        
        $transactionId = $_POST['borrow_id'] ?? $_POST['transaction_id'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $cardNumber = $_POST['card_number'] ?? '';
        $cardName = $_POST['card_name'] ?? '';
        $expiryDate = $_POST['expiry_date'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'credit_card';
        
        // Validate payment details
        $errors = [];
        
        if (empty($cardName)) {
            $errors[] = 'Cardholder name is required';
        }
        
        if (empty($cardNumber)) {
            $errors[] = 'Card number is required';
        } elseif (!$this->validateCardNumber($cardNumber)) {
            $errors[] = 'Invalid card number';
        }
        
        if (empty($expiryDate)) {
            $errors[] = 'Expiry date is required';
        } elseif (!$this->validateExpiryDate($expiryDate)) {
            $errors[] = 'Invalid or expired card';
        }
        
        if (empty($cvv)) {
            $errors[] = 'CVV is required';
        } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
            $errors[] = 'Invalid CVV';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL . 'user/payFine?tid=' . $transactionId . '&amount=' . $amount);
            exit;
        }
        
        global $mysqli;
        
        try {
            // Update fine status and set finePaymentDate
            $stmt = $mysqli->prepare("
                UPDATE transactions 
                SET fineStatus = 'paid', 
                    finePaymentDate = CURDATE(), 
                    lastFinePaymentDate = CURDATE() 
                WHERE tid = ?
            ");
            $stmt->bind_param("s", $transactionId);
            
            if ($stmt->execute()) {
                // Log the payment
                $logStmt = $mysqli->prepare("
                    INSERT INTO payment_logs 
                    (userId, transactionId, amount, paymentMethod, cardLastFour, paymentDate, status) 
                    VALUES (?, ?, ?, ?, ?, NOW(), 'success')
                ");
                $cardLastFour = substr($cardNumber, -4);
                $logStmt->bind_param("ssdss", 
                    $_SESSION['userId'], 
                    $transactionId, 
                    $amount, 
                    $paymentMethod, 
                    $cardLastFour
                );
                $logStmt->execute();
                $logStmt->close();
                
                $_SESSION['success'] = 'Payment of ₹' . number_format($amount, 2) . ' processed successfully!';
            } else {
                $_SESSION['error'] = 'Failed to process payment';
            }
            $stmt->close();
        } catch (\Exception $e) {
            error_log("Error paying fine: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to process payment';
        }
        
        $this->redirect('user/fines');
    }
    
    /**
     * Validate card number using Luhn algorithm
     */
    private function validateCardNumber($number)
    {
        $number = preg_replace('/\s+/', '', $number);
        
        if (!preg_match('/^\d{13,19}$/', $number)) {
            return false;
        }
        
        $sum = 0;
        $length = strlen($number);
        
        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$length - $i - 1];
            
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        return ($sum % 10 === 0);
    }
    
    /**
     * Validate expiry date
     */
    private function validateExpiryDate($expiryDate)
    {
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiryDate, $matches)) {
            return false;
        }
        
        $month = (int)$matches[1];
        $year = (int)$matches[2] + 2000;
        
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        
        if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
            return false;
        }
        
        return true;
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
    
    /**
     * Show user's borrow history
     */
    public function borrowHistory() {
        $this->requireLogin();
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        
        // Query from books_borrowed table with correct column names
        $sql = "SELECT 
                    bb.id,
                    bb.userid,
                    bb.isbn,
                    bb.borrowDate,
                    bb.dueDate,
                    bb.returnDate,
                    bb.status,
                    bb.notes,
                    bb.addedBy,
                    b.bookName,
                    b.authorName
                FROM books_borrowed bb
                LEFT JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userid = ?
                ORDER BY bb.borrowDate DESC";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $borrowHistory = [];
        while ($row = $result->fetch_assoc()) {
            // Map to format expected by the view
            $borrowHistory[] = [
                'id' => $row['id'],
                'isbn' => $row['isbn'],
                'bookName' => $row['bookName'] ?? 'Unknown',
                'authorName' => $row['authorName'] ?? 'N/A',
                'borrowDate' => $row['borrowDate'],
                'returnDate' => $row['returnDate'],
                'dueDate' => $row['dueDate'],
                'status' => $row['status'],
                'fineAmount' => 0, // Calculate fine if needed
                'fineStatus' => 'Paid' // Default value
            ];
        }
        $stmt->close();
        
        // Calculate fines for overdue books
        foreach ($borrowHistory as &$record) {
            if (!$record['returnDate'] && $record['dueDate']) {
                $dueDate = new \DateTime($record['dueDate']);
                $today = new \DateTime();
                
                if ($today > $dueDate) {
                    $interval = $today->diff($dueDate);
                    $daysOverdue = $interval->days;
                    $record['fineAmount'] = $daysOverdue * 5; // ₹5 per day
                    $record['fineStatus'] = 'Unpaid';
                }
            }
        }
        
        $this->data['borrowHistory'] = $borrowHistory;
        $this->view('users/borrow-history', $this->data);
    }
    
    /**
     * Submit a book review
     */
    public function submitReview() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/borrow-history');
            return;
        }
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        $isbn = $_POST['isbn'] ?? '';
        $rating = $_POST['rating'] ?? 0;
        $reviewText = $_POST['reviewText'] ?? '';
        
        if (!$isbn || !$rating) {
            $_SESSION['error'] = 'Please provide both ISBN and rating';
            $this->redirect('user/borrow-history');
            return;
        }
        
        // Check if user has borrowed this book from books_borrowed table
        $stmt = $mysqli->prepare("SELECT id FROM books_borrowed WHERE userid = ? AND isbn = ?");
        $stmt->bind_param("ss", $userId, $isbn);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $_SESSION['error'] = 'You can only review books you have borrowed';
            $stmt->close();
            $this->redirect('user/borrow-history');
            return;
        }
        $stmt->close();
        
        // Check if review already exists
        $stmt = $mysqli->prepare("SELECT id FROM book_reviews WHERE userId = ? AND isbn = ?");
        $stmt->bind_param("ss", $userId, $isbn);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $_SESSION['error'] = 'You have already reviewed this book';
            $stmt->close();
            $this->redirect('user/borrow-history');
            return;
        }
        $stmt->close();
        
        // Insert review
        $stmt = $mysqli->prepare("INSERT INTO book_reviews (userId, isbn, rating, reviewText, createdAt) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssis", $userId, $isbn, $rating, $reviewText);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Thank you for your review!';
        } else {
            $_SESSION['error'] = 'Failed to submit review';
        }
        $stmt->close();
        
        $this->redirect('user/borrow-history');
    }
    
    /**
     * Show all books returned by the user
     */
    public function returns()
    {
        $this->requireLogin();
        
        global $mysqli;
        $userId = $_SESSION['userId'];
        
        $sql = "SELECT 
                    bb.isbn,
                    bb.returnDate,
                    b.bookName,
                    b.authorName
                FROM books_borrowed bb
                LEFT JOIN books b ON bb.isbn = b.isbn
                WHERE bb.userid = ? AND bb.returnDate IS NOT NULL
                ORDER BY bb.returnDate DESC";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $returnedBooks = [];
        while ($row = $result->fetch_assoc()) {
            $returnedBooks[] = $row;
        }
        $stmt->close();
        
        $this->data['returnedBooks'] = $returnedBooks;
        $this->view('users/returns', $this->data);
    }
}