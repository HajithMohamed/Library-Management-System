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
        
        $this->data['userStats'] = $userStats;
        $this->data['recentActivity'] = $recentActivity;
        
        $this->view('users/dashboard', $this->data);
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
        // Get notifications logic here
        
        $this->view('faculty/notifications', $this->data);
    }
}