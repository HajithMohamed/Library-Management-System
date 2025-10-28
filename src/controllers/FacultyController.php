<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\BookReservation;
use App\Helpers\AuthHelper;
use App\Services\UserService;
use App\Services\BookService;

class FacultyController
{
    private $authHelper;

    public function __construct()
    {
        $this->authHelper = new AuthHelper();
    }

    /**
     * Get user ID from session - FIXED: Handle both userId and user_id
     */
    private function getUserId()
    {
        // Try different session keys
        if (isset($_SESSION['userId']) && !is_array($_SESSION['userId'])) {
            return $_SESSION['userId'];
        }
        
        if (isset($_SESSION['user_id']) && !is_array($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        
        return null;
    }

    /**
     * Faculty/Student dashboard
     */
    public function dashboard()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // Dashboard logic here
        $pageTitle = 'Dashboard';
        $this->render('faculty/dashboard');
    }

    /**
     * Browse books catalog
     */
    public function books()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        global $mysqli;
        
        // Fetch all books
        $stmt = $mysqli->prepare("SELECT * FROM books ORDER BY bookName ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $books = [];
        while ($row = $result->fetch_assoc()) {
            // Ensure bookImage path is correct
            if (!empty($row['bookImage']) && strpos($row['bookImage'], 'uploads/') !== 0) {
                // If the image path doesn't start with 'uploads/', prepend it
                if (!preg_match('#^(https?://|/)#', $row['bookImage'])) {
                    $row['bookImage'] = 'uploads/books/' . $row['bookImage'];
                }
            }
            $books[] = $row;
        }
        
        // Get categories for filter
        $categoriesStmt = $mysqli->prepare("SELECT DISTINCT publisherName FROM books ORDER BY publisherName");
        $categoriesStmt->execute();
        $categoriesResult = $categoriesStmt->get_result();
        
        $categories = [];
        while ($row = $categoriesResult->fetch_assoc()) {
            $categories[] = $row['publisherName'];
        }
        
        $pageTitle = 'Browse Books';
        $this->render('faculty/books', [
            'books' => $books,
            'categories' => $categories
        ]);
    }

    /**
     * View single book details
     */
    public function viewBook($isbn = null)
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // Debug: Log the received ISBN
        error_log("viewBook called with ISBN: " . var_export($isbn, true));
        
        // FIXED: Get ISBN from parameter or URL
        if ($isbn === null && isset($_GET['isbn'])) {
            $isbn = $_GET['isbn'];
            error_log("ISBN from GET parameter: " . $isbn);
        }
        
        // Extract ISBN from URL path if it's part of the route
        if ($isbn === null) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            error_log("Request URI path: " . $path);
            if (preg_match('#/faculty/book/([^/]+)$#', $path, $matches)) {
                $isbn = urldecode($matches[1]);
                error_log("ISBN extracted from path: " . $isbn);
            }
        }
        
        if (empty($isbn)) {
            error_log("No ISBN found - redirecting to books page");
            $_SESSION['error_message'] = 'Invalid book ISBN.';
            $this->redirect('/faculty/books');
            return;
        }
        
        global $mysqli;
        
        // Debug: Log the SQL query
        error_log("Searching for book with ISBN: " . $isbn);
        
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("Book not found in database for ISBN: " . $isbn);
            $_SESSION['error_message'] = 'Book not found.';
            $this->redirect('/faculty/books');
            return;
        }
        
        $book = $result->fetch_assoc();
        error_log("Book found: " . $book['bookName']);
        
        // Ensure bookImage path is correct
        if (!empty($book['bookImage']) && strpos($book['bookImage'], 'uploads/') !== 0) {
            if (!preg_match('#^(https?://|/)#', $book['bookImage'])) {
                $book['bookImage'] = 'uploads/books/' . $book['bookImage'];
            }
        }
        
        $pageTitle = $book['bookName'];
        $this->render('faculty/view-book', ['book' => $book]);
    }

    /**
     * Handle book reservation/borrowing - FIXED userId handling
     */
    public function reserve($isbn = null)
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // FIXED: Get userId properly
        $userId = $this->getUserId();
        
        if (!$userId) {
            $_SESSION['error_message'] = 'User session invalid. Please login again.';
            $this->redirect('/login');
            return;
        }
        
        // Get ISBN from URL parameter if not passed
        if ($isbn === null && isset($_GET['isbn'])) {
            $isbn = $_GET['isbn'];
        }
        
        if (empty($isbn)) {
            $_SESSION['error_message'] = 'Invalid book ISBN.';
            $this->redirect('/faculty/books');
            return;
        }
        
        global $mysqli;
        
        if (!$mysqli) {
            $_SESSION['error_message'] = 'Database connection error.';
            $this->redirect('/faculty/books');
            return;
        }
        
        // Get book details
        $bookStmt = $mysqli->prepare("SELECT isbn, bookName, authorName, available FROM books WHERE isbn = ?");
        $bookStmt->bind_param("s", $isbn);
        $bookStmt->execute();
        $bookResult = $bookStmt->get_result();
        
        if ($bookResult->num_rows === 0) {
            $_SESSION['error_message'] = 'Book not found.';
            $this->redirect('/faculty/books');
            return;
        }
        
        $book = $bookResult->fetch_assoc();
        
        // Check if user already has a pending request for this book
        $checkStmt = $mysqli->prepare("SELECT id FROM borrow_requests WHERE userId = ? AND isbn = ? AND status = 'Pending'");
        $checkStmt->bind_param("ss", $userId, $isbn);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $_SESSION['error_message'] = 'You already have a pending request for this book.';
            $this->redirect('/faculty/books');
            return;
        }
        
        // Check if user already borrowed this book and hasn't returned it
        $borrowedStmt = $mysqli->prepare("SELECT tid FROM transactions WHERE userId = ? AND isbn = ? AND returnDate IS NULL");
        $borrowedStmt->bind_param("ss", $userId, $isbn);
        $borrowedStmt->execute();
        $borrowedResult = $borrowedStmt->get_result();
        
        if ($borrowedResult->num_rows > 0) {
            $_SESSION['error_message'] = 'You have already borrowed this book and haven\'t returned it yet.';
            $this->redirect('/faculty/books');
            return;
        }
        
        // Insert borrow request
        $stmt = $mysqli->prepare("INSERT INTO borrow_requests (userId, isbn, status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param("ss", $userId, $isbn);
        
        if ($stmt->execute()) {
            $requestId = $mysqli->insert_id;
            
            // Create notification for admin
            $notifStmt = $mysqli->prepare("
                INSERT INTO notifications (userId, title, message, type, priority, relatedId) 
                VALUES (NULL, ?, ?, 'approval', 'high', ?)
            ");
            $notifTitle = 'New Borrow Request';
            $notifMessage = "User {$userId} requested to borrow '{$book['bookName']}' by {$book['authorName']}";
            $notifStmt->bind_param("ssi", $notifTitle, $notifMessage, $requestId);
            $notifStmt->execute();
            
            if ($book['available'] > 0) {
                $_SESSION['success_message'] = "Borrow request submitted successfully! The book is available and waiting for admin approval.";
            } else {
                $_SESSION['success_message'] = "Reservation request submitted successfully! You will be notified when the book becomes available and admin approves your request.";
            }
        } else {
            $_SESSION['error_message'] = 'Failed to submit request. Please try again.';
        }
        
        $this->redirect('/faculty/books');
    }

    /**
     * Show book request page - FIXED userId handling
     */
    public function bookRequest()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // FIXED: Get userId properly
        $userId = $this->getUserId();
        
        if (!$userId) {
            $_SESSION['error_message'] = 'User session invalid. Please login again.';
            $this->redirect('/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isbn = trim($_POST['isbn'] ?? '');
            $bookTitle = trim($_POST['book_title'] ?? '');
            $author = trim($_POST['author'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            
            // Validate inputs
            if (empty($isbn) || empty($bookTitle) || empty($author)) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                $this->redirect('/faculty/book-request');
                return;
            }
            
            global $mysqli;
            
            if (!$mysqli) {
                $_SESSION['error'] = 'Database connection error.';
                $this->redirect('/faculty/book-request');
                return;
            }
            
            // Check if book exists in catalog
            $bookStmt = $mysqli->prepare("SELECT isbn, bookName FROM books WHERE isbn = ?");
            $bookStmt->bind_param("s", $isbn);
            $bookStmt->execute();
            $bookResult = $bookStmt->get_result();
            
            if ($bookResult->num_rows === 0) {
                $_SESSION['error'] = 'Book not found in catalog. Please check the ISBN.';
                $this->redirect('/faculty/book-request');
                return;
            }
            
            // Check for duplicate pending requests
            $checkStmt = $mysqli->prepare("SELECT id FROM borrow_requests WHERE userId = ? AND isbn = ? AND status = 'Pending'");
            $checkStmt->bind_param("ss", $userId, $isbn);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $_SESSION['error'] = 'You already have a pending request for this book.';
                $this->redirect('/faculty/book-request');
                return;
            }
            
            // Insert borrow request
            $stmt = $mysqli->prepare("INSERT INTO borrow_requests (userId, isbn, status) VALUES (?, ?, 'Pending')");
            $stmt->bind_param("ss", $userId, $isbn);
            
            if ($stmt->execute()) {
                // Create notification for admin
                $notifStmt = $mysqli->prepare("
                    INSERT INTO notifications (userId, title, message, type, priority, relatedId) 
                    VALUES (NULL, ?, ?, 'approval', 'high', ?)
                ");
                $notifTitle = 'New Borrow Request';
                $notifMessage = "User {$userId} requested to borrow '{$bookTitle}' by {$author}";
                $requestId = $mysqli->insert_id;
                $notifStmt->bind_param("ssi", $notifTitle, $notifMessage, $requestId);
                $notifStmt->execute();
                
                $_SESSION['success'] = 'Book request submitted successfully! You will be notified once approved.';
            } else {
                $_SESSION['error'] = 'Failed to submit request. Please try again.';
            }
            
            $this->redirect('/faculty/book-request');
            return;
        }
        
        // Get user's requests
        $requests = [];
        global $mysqli;
        if ($mysqli) {
            $stmt = $mysqli->prepare("
                SELECT br.id, br.isbn, br.requestDate, br.status, br.dueDate, br.rejectionReason,
                       b.bookName, b.authorName as author
                FROM borrow_requests br
                LEFT JOIN books b ON br.isbn = b.isbn
                WHERE br.userId = ?
                ORDER BY br.requestDate DESC
            ");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        }
        
        $pageTitle = 'Book Request';
        $this->render('faculty/book-request', ['requests' => $requests]);
    }

    /**
     * View transactions/borrowing history
     */
    public function transactions()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // Transaction history logic
        $pageTitle = 'My Transactions';
        $this->render('faculty/transactions');
    }

    /**
     * View/Edit profile
     */
    public function profile()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // Profile logic
        $pageTitle = 'My Profile';
        $this->render('faculty/profile');
    }

    /**
     * Handle book return - FIXED userId handling
     */
    public function returnBook()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // FIXED: Get userId properly
        $userId = $this->getUserId();
        
        if (!$userId) {
            $_SESSION['error_message'] = 'User session invalid. Please login again.';
            $this->redirect('/login');
            return;
        }
        
        global $mysqli;
        
        // Get borrowed books for this user
        $stmt = $mysqli->prepare("
            SELECT t.*, b.bookName, b.authorName, b.bookImage 
            FROM transactions t
            JOIN books b ON t.isbn = b.isbn
            WHERE t.userId = ? AND t.returnDate IS NULL
            ORDER BY t.borrowDate DESC
        ");
        
        $borrowedBooks = [];
        if ($stmt) {
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $borrowedBooks = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        // Handle return submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionId = $_POST['transaction_id'] ?? '';
            
            if (!empty($transactionId)) {
                $updateStmt = $mysqli->prepare("
                    UPDATE transactions 
                    SET returnDate = CURDATE() 
                    WHERE tid = ? AND userId = ? AND returnDate IS NULL
                ");
                
                if ($updateStmt) {
                    $updateStmt->bind_param("ss", $transactionId, $userId);
                    
                    if ($updateStmt->execute() && $updateStmt->affected_rows > 0) {
                        // Update book availability
                        $getIsbn = $mysqli->prepare("SELECT isbn FROM transactions WHERE tid = ?");
                        $getIsbn->bind_param("s", $transactionId);
                        $getIsbn->execute();
                        $isbnResult = $getIsbn->get_result()->fetch_assoc();
                        
                        if ($isbnResult) {
                            $updateBook = $mysqli->prepare("
                                UPDATE books 
                                SET available = available + 1, borrowed = borrowed - 1 
                                WHERE isbn = ?
                            ");
                            $updateBook->bind_param("s", $isbnResult['isbn']);
                            $updateBook->execute();
                            $updateBook->close();
                        }
                        $getIsbn->close();
                        
                        $_SESSION['success_message'] = 'Book returned successfully!';
                    } else {
                        $_SESSION['error_message'] = 'Failed to return book. Please try again.';
                    }
                    $updateStmt->close();
                }
                
                $this->redirect('/faculty/return');
                return;
            }
        }
        
        $pageTitle = 'Return Books';
        $this->render('faculty/return', ['borrowedBooks' => $borrowedBooks]);
    }

    /**
     * View fines - FIXED userId handling
     */
    public function fines()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // FIXED: Get userId properly
        $userId = $this->getUserId();
        
        if (!$userId) {
            $_SESSION['error_message'] = 'User session invalid. Please login again.';
            $this->redirect('/login');
            return;
        }
        
        global $mysqli;
        
        // Get all transactions with fines for this user
        $stmt = $mysqli->prepare("
            SELECT t.*, b.bookName, b.authorName, b.bookImage 
            FROM transactions t
            JOIN books b ON t.isbn = b.isbn
            WHERE t.userId = ? AND t.fineAmount > 0
            ORDER BY t.borrowDate DESC
        ");
        
        $fines = [];
        if ($stmt) {
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $fines = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        $pageTitle = 'My Fines';
        $this->render('faculty/fines', ['fines' => $fines]);
    }

    /**
     * View borrow history - FIXED userId handling
     */
    public function borrowHistory()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // FIXED: Get userId properly
        $userId = $this->getUserId();
        
        if (!$userId) {
            $_SESSION['error_message'] = 'User session invalid. Please login again.';
            $this->redirect('/login');
            return;
        }
        
        global $mysqli;
        
        // Get all transactions for this user
        $stmt = $mysqli->prepare("
            SELECT t.*, b.bookName, b.authorName, b.bookImage 
            FROM transactions t
            JOIN books b ON t.isbn = b.isbn
            WHERE t.userId = ?
            ORDER BY t.borrowDate DESC
        ");
        
        $history = [];
        if ($stmt) {
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $history = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        $pageTitle = 'Borrow History';
        $this->render('faculty/borrow-history', ['history' => $history]);
    }

    /**
     * View notifications - FIXED userId handling
     */
    public function notifications()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        // FIXED: Get userId properly
        $userId = $this->getUserId();
        
        if (!$userId) {
            $_SESSION['error_message'] = 'User session invalid. Please login again.';
            $this->redirect('/login');
            return;
        }
        
        global $mysqli;
        
        // Get notifications for this user
        $stmt = $mysqli->prepare("
            SELECT * FROM notifications 
            WHERE userId = ? OR userId IS NULL
            ORDER BY createdAt DESC
            LIMIT 50
        ");
        
        $notifications = [];
        if ($stmt) {
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $notifications = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        // Mark as read if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
            $notifId = $_POST['notification_id'] ?? '';
            if (!empty($notifId)) {
                $markStmt = $mysqli->prepare("UPDATE notifications SET isRead = 1 WHERE id = ? AND userId = ?");
                $markStmt->bind_param("is", $notifId, $userId);
                $markStmt->execute();
                $markStmt->close();
                
                $this->redirect('/faculty/notifications');
                return;
            }
        }
        
        $pageTitle = 'Notifications';
        $this->render('faculty/notifications', ['notifications' => $notifications]);
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
     * Redirect to a URL - FIXED: Check if headers not sent
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header('Location: ' . BASE_URL . ltrim($url, '/'));
            exit;
        } else {
            // Fallback to JavaScript redirect if headers already sent
            echo '<script>window.location.href = "' . BASE_URL . ltrim($url, '/') . '";</script>';
            exit;
        }
    }
}