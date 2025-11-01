<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\User;

class FacultyController extends BaseController
{
    private $bookModel;
    private $borrowModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookModel = new Book();
        $this->borrowModel = new BorrowRecord();
        $this->userModel = new User();
    }

    public function dashboard()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        // Get faculty statistics
        $userStats = [
            'borrowed_books' => $this->borrowModel->getActiveBorrowCount($userId),
            'overdue_books' => $this->borrowModel->getOverdueCount($userId),
            'total_fines' => $this->borrowModel->getTotalFines($userId),
            'max_books' => 5
        ];
        
        // Get recent activity
        $recentActivity = $this->borrowModel->getRecentActivity($userId, 10);
        
        // Get user info
        $user = $this->userModel->findById($userId);
        
        // Get borrowed books
        $borrowedBooks = $this->borrowModel->getActiveBorrows($userId);
        
        // Get overdue books
        $overdueBooks = array_filter($borrowedBooks, function($book) {
            $dueDate = $book['dueDate'] ?? date('Y-m-d', strtotime($book['borrowDate'] . ' + 14 days'));
            return strtotime($dueDate) < time();
        });
        
        // Get reserved books
        global $mysqli;
        $reservedBooks = [];
        $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE userId = ? AND status IN ('Pending','Approved')");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reservedBooks[] = $row;
        }
        $stmt->close();
        
        // Get notifications
        $notifications = $this->userModel->getNotifications($userId);
        
        // Get transaction history
        $transactionHistory = $this->borrowModel->getBorrowHistory($userId);
        
        // Get analytics stats
        $stats = $this->getPersonalStats($userId);
        
        // Pass all data to view
        $this->data['userStats'] = $userStats;
        $this->data['recentActivity'] = $recentActivity;
        $this->data['user'] = $user;
        $this->data['borrowedBooks'] = $borrowedBooks;
        $this->data['overdueBooks'] = $overdueBooks;
        $this->data['reservedBooks'] = $reservedBooks;
        $this->data['notifications'] = $notifications;
        $this->data['transactionHistory'] = $transactionHistory;
        $this->data['stats'] = $stats;
        
        $this->view('faculty/dashboard', $this->data);
    }
    
    /**
     * Get personal statistics for analytics
     */
    private function getPersonalStats($userId)
    {
        $stats = [
            'total_books' => 0,
            'reviews' => [
                'total_reviews' => 0,
                'avg_rating' => 0
            ],
            'categories' => [],
            'monthly' => []
        ];
        
        // Get total books borrowed
        $sql = "SELECT COUNT(DISTINCT isbn) as total FROM transactions WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stats['total_books'] = $result['total'] ?? 0;
        
        // Get reviews stats (if book_reviews table exists)
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'book_reviews'");
        if ($tableCheck->num_rows > 0) {
            $sql = "SELECT COUNT(*) as total, AVG(rating) as avg_rating 
                    FROM book_reviews WHERE userId = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stats['reviews']['total_reviews'] = $result['total'] ?? 0;
            $stats['reviews']['avg_rating'] = $result['avg_rating'] ?? 0;
        }
        
        // Get category distribution
        $sql = "SELECT b.category, COUNT(*) as borrow_count 
                FROM transactions t 
                JOIN books b ON t.isbn = b.isbn 
                WHERE t.userId = ? AND b.category IS NOT NULL 
                GROUP BY b.category 
                ORDER BY borrow_count DESC 
                LIMIT 6";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $stats['categories'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get monthly trend (last 6 months)
        $sql = "SELECT DATE_FORMAT(borrowDate, '%Y-%m') as month, COUNT(*) as count 
                FROM transactions 
                WHERE userId = ? AND borrowDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY month 
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $stats['monthly'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return $stats;
    }

    public function books()
    {
        $this->requireLogin(['Faculty']);
        
        $searchTerm = $_GET['search'] ?? '';
        $books = $this->bookModel->search($searchTerm);
        
        $this->data['books'] = $books;
        $this->data['searchTerm'] = $searchTerm;
        
        $this->view('faculty/books', $this->data);
    }

    public function viewBook($params)
    {
        $this->requireLogin(['Faculty']);
        
        $isbn = $params['isbn'] ?? '';
        $book = $this->bookModel->findByISBN($isbn);
        
        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            $this->redirect('faculty/books');
            return;
        }
        
        $this->data['book'] = $book;
        $this->view('faculty/book-details', $this->data);
    }

    /**
     * Reserve a book (send to borrow_requests table)
     */
    public function reserve($params = [])
    {
        $this->requireLogin(['Faculty']);

        $userId = $_SESSION['userId'];
        // Support both /faculty/reserve?isbn=... and /faculty/reserve/{isbn}
        $isbn = $_GET['isbn'] ?? ($params['isbn'] ?? null);

        if (!$isbn) {
            $_SESSION['error'] = 'No book specified for reservation';
            header('Location: ' . BASE_URL . 'faculty/books');
            exit;
        }

        global $mysqli;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if already has a pending/approved request for this book
            $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE userId = ? AND isbn = ? AND status IN ('Pending','Approved') AND dueDate >= CURDATE()");
            $stmt->bind_param("ss", $userId, $isbn);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['error'] = 'You already have a pending or approved request for this book.';
                $stmt->close();
                header('Location: ' . BASE_URL . 'faculty/books');
                exit;
            }
            $stmt->close();

            // Only allow reservation for 1 day
            $dueDate = date('Y-m-d', strtotime('+1 day'));

            // Insert into borrow_requests
            $stmt = $mysqli->prepare("INSERT INTO borrow_requests (userId, isbn, dueDate, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("sss", $userId, $isbn, $dueDate);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Reservation request sent! Awaiting admin approval.';
            } else {
                $_SESSION['error'] = 'Failed to send reservation request.';
            }
            $stmt->close();

            header('Location: ' . BASE_URL . 'faculty/reserved-books');
            exit;
        }

        // GET: Show confirmation page
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'faculty/books');
            exit;
        }

        $this->data['book'] = $book;
        $this->view('faculty/reserve', $this->data);
    }

    /**
     * Show faculty's reserved books (borrow requests)
     */
    public function reservedBooks() {
        $this->requireLogin(['Faculty']);
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
        $this->view('faculty/reserved-books', $this->data);
    }

    public function fines()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $borrowId = $_POST['borrow_id'] ?? 0;
            $amount = $_POST['amount'] ?? 0;

            // Online payment
            if (isset($_POST['pay_online'])) {
                $cardHolder = trim($_POST['card_holder'] ?? '');
                $cardNumber = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
                $cardExpiry = $_POST['card_expiry'] ?? '';
                $saveCard = !empty($_POST['save_card']);
                // Card type detection (simple)
                $cardType = '';
                if (preg_match('/^4/', $cardNumber)) $cardType = 'Visa';
                elseif (preg_match('/^5[1-5]/', $cardNumber)) $cardType = 'MasterCard';
                elseif (preg_match('/^3[47]/', $cardNumber)) $cardType = 'Amex';
                elseif (preg_match('/^6/', $cardNumber)) $cardType = 'Discover';

                // Save card if requested (mask number, never save CVV)
                if ($saveCard && strlen($cardNumber) >= 4) {
                    global $mysqli;
                    $masked = str_repeat('X', strlen($cardNumber) - 4) . substr($cardNumber, -4);
                    $stmt = $mysqli->prepare("INSERT INTO saved_cards (userId, card_holder, card_number_masked, card_expiry, card_type) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param('sssss', $userId, $cardHolder, $masked, $cardExpiry, $cardType);
                    $stmt->execute();
                    $stmt->close();
                }

                // Mark fine as paid (simulate payment)
                if ($this->borrowModel->payFine($borrowId, $amount, 'online')) {
                    $_SESSION['success'] = 'Fine paid successfully via online payment';
                } else {
                    $_SESSION['error'] = 'Failed to process online payment';
                }
            } else {
                // Cash payment
                if ($this->borrowModel->payFine($borrowId, $amount)) {
                    $_SESSION['success'] = 'Fine paid successfully';
                } else {
                    $_SESSION['error'] = 'Failed to process payment';
                }
            }
        }
        
        $fines = $this->borrowModel->getUserFines($userId);
        
        $this->data['fines'] = $fines;
        $this->view('faculty/fines', $this->data);
    }

    public function returnBook()
    {
        $this->requireLogin(['Faculty']);
        
        // Only show books that have been returned by this user
        $userId = $_SESSION['userId'];
        // Fetch only returned books (returnDate is not null)
        $returnedBooks = $this->borrowModel->getBorrowHistory($userId);
        $returnedBooks = array_filter($returnedBooks, function($book) {
            return !empty($book['returnDate']);
        });
        $this->data['returnedBooks'] = $returnedBooks;
        $this->view('faculty/return', $this->data);
    }

    public function borrowHistory()
    {
        $this->requireLogin(['Faculty']);
        $userId = $_SESSION['userId'];
        // Get all borrow records (active and returned)
        $history = $this->borrowModel->getBorrowHistory($userId);
        // Optionally, sort by borrowDate descending (if not already sorted)
        usort($history, function($a, $b) {
            return strtotime($b['borrowDate']) <=> strtotime($a['borrowDate']);
        });
        $this->data['history'] = $history;
        $this->view('faculty/borrow-history', $this->data);
    }

    public function profile()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_profile'])) {
                $data = [
                    'username' => $_POST['name'] ?? '',
                    'emailId' => $_POST['email'] ?? '',
                    'gender' => $_POST['gender'] ?? '',
                    'dob' => $_POST['dob'] ?? '',
                    'phoneNumber' => $_POST['phoneNumber'] ?? '',
                    'address' => $_POST['address'] ?? '',
                ];
                
                if ($this->userModel->updateProfile($userId, $data)) {
                    $_SESSION['success'] = 'Profile updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update profile';
                }
            } elseif (isset($_POST['change_password'])) {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = 'New password and confirmation do not match.';
                } else {
                    if ($this->userModel->changePassword($userId, $currentPassword, $newPassword)) {
                        $_SESSION['success'] = 'Password changed successfully.';
                    } else {
                        $_SESSION['error'] = $this->userModel->getLastError() ?? 'Failed to change password.';
                    }
                }
            }
            
            $this->redirect('faculty/profile');
            return;
        }
        
        $user = $this->userModel->findById($userId);
        
        $this->data['user'] = $user;
        $this->view('faculty/profile', $this->data);
    }

    public function search()
    {
        $this->requireLogin(['Faculty']);
        $searchTerm = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        $books = $this->bookModel->advancedSearch($searchTerm, $category);
        $this->data['books'] = $books;
        $this->data['searchTerm'] = $searchTerm;
        $this->data['category'] = $category;
        $this->view('faculty/search', $this->data);
    }

    public function feedback()
    {
        $this->requireLogin(['Faculty']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle feedback submission
            $feedback = $_POST['feedback'] ?? '';
            $bookId = $_POST['book_id'] ?? 0;
            // Save feedback logic here
            $_SESSION['success'] = 'Feedback submitted successfully';
        }
        $this->view('faculty/feedback', $this->data);
    }

    public function bookRequest()
    {
        $this->requireLogin(['Faculty']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle book request
            $bookTitle = $_POST['book_title'] ?? '';
            $author = $_POST['author'] ?? '';
            $reason = $_POST['reason'] ?? '';
            // Save book request logic here
            $_SESSION['success'] = 'Book request submitted successfully';
        }
        $this->view('faculty/book-request', $this->data);
    }

    public function notifications()
    {
        $this->requireLogin(['Faculty']);
        $userId = $_SESSION['userId'];
        $userType = 'Faculty';
        // Handle mark as read
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
            $notificationId = $_POST['notification_id'] ?? 0;
            if ($notificationId) {
                global $mysqli;
                $sql = "UPDATE notifications SET isRead = 1 WHERE id = ? AND userId = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('is', $notificationId, $userId);
                $stmt->execute();
            }
            $this->redirect('faculty/notifications');
            return;
        }
        // Get notifications with proper user email and type based on userId
        global $mysqli;
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
        $this->view('faculty/notifications', $this->data);
    }
}