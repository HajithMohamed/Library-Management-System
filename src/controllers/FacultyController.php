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
    public function viewBook($isbn)
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        global $mysqli;
        
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = 'Book not found.';
            $this->redirect('/faculty/books');
            return;
        }
        
        $book = $result->fetch_assoc();
        
        $pageTitle = $book['bookName'];
        $this->render('faculty/view-book', ['book' => $book]);
    }

    /**
     * Handle book reservation/borrowing
     */
    public function reserve($isbn = null)
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        $userId = $_SESSION['userId'];
        
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
     * Show book request page
     */
    public function bookRequest()
    {
        $this->authHelper->requireAuth(['Faculty', 'Student']);
        
        $userId = $_SESSION['userId'];
        
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