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
        
        // Get reserved books (placeholder - implement if you have reservations table)
        $reservedBooks = [];
        
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

    public function reserve($params = [])
    {
        $this->requireLogin(['Faculty']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('faculty/books');
            return;
        }
        
        $isbn = $params['isbn'] ?? $_POST['isbn'] ?? '';
        $userId = $_SESSION['userId'];
        
        if ($this->borrowModel->createReservation($userId, $isbn)) {
            $_SESSION['success'] = 'Book reserved successfully';
        } else {
            $_SESSION['error'] = 'Failed to reserve book';
        }
        
        $this->redirect('faculty/books');
    }

    public function fines()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $borrowId = $_POST['borrow_id'] ?? 0;
            $amount = $_POST['amount'] ?? 0;
            
            if ($this->borrowModel->payFine($borrowId, $amount)) {
                $_SESSION['success'] = 'Fine paid successfully';
            } else {
                $_SESSION['error'] = 'Failed to process payment';
            }
        }
        
        $fines = $this->borrowModel->getUserFines($userId);
        
        $this->data['fines'] = $fines;
        $this->view('faculty/fines', $this->data);
    }

    public function returnBook()
    {
        $this->requireLogin(['Faculty']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $borrowId = $_POST['borrow_id'] ?? 0;
            
            if ($this->borrowModel->returnBook($borrowId)) {
                $_SESSION['success'] = 'Book returned successfully';
            } else {
                $_SESSION['error'] = 'Failed to return book';
            }
        }
        
        $userId = $_SESSION['userId'];
        $borrowedBooks = $this->borrowModel->getActiveBorrows($userId);
        
        $this->data['borrowedBooks'] = $borrowedBooks;
        $this->view('faculty/return', $this->data);
    }

    public function borrowHistory()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        $history = $this->borrowModel->getBorrowHistory($userId);
        
        $this->data['history'] = $history;
        $this->view('faculty/borrow-history', $this->data);
    }

    public function profile()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'department' => $_POST['department'] ?? ''
            ];
            
            if ($this->userModel->updateProfile($userId, $data)) {
                $_SESSION['success'] = 'Profile updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }
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