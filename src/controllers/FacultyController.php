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
    public function dashboard()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }
        
        // Get user type and redirect if not faculty
        $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;
        
        // Redirect non-faculty users to their appropriate dashboards
        if ($userType === 'Admin') {
            header('Location: /admin/dashboard');
            exit();
        }
        
        if ($userType === 'Student' || $userType === 'User') {
            header('Location: /user/dashboard');
            exit();
        }
        
        // Verify user is faculty
        if ($userType !== 'Faculty') {
            $_SESSION['error_message'] = 'Access denied. Faculty access required.';
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
        $userModel = new User();
        $user = $userModel->getUserById($userId);
        
        if (!$user) {
            $_SESSION['error_message'] = 'User not found';
            header('Location: /login');
            exit();
        }

        $transactionModel = new Transaction();
        
        // Get borrowed books safely
        try {
            if (method_exists($transactionModel, 'getBorrowedBooks')) {
                $borrowedBooks = $transactionModel->getBorrowedBooks($userId) ?? [];
            } else {
                $borrowedBooks = $transactionModel->getActiveTransactionsByUser($userId) ?? [];
            }
        } catch (\Exception $e) {
            error_log("Error getting borrowed books: " . $e->getMessage());
            $borrowedBooks = [];
        }
        
        // Get overdue books safely
        try {
            if (method_exists($transactionModel, 'getOverdueBooks')) {
                $overdueBooks = $transactionModel->getOverdueBooks($userId) ?? [];
            } else {
                // Calculate overdue books manually
                $overdueBooks = [];
                foreach ($borrowedBooks as $book) {
                    $dueDate = $book['dueDate'] ?? $book['returnDate'] ?? null;
                    if ($dueDate && strtotime($dueDate) < time()) {
                        $overdueBooks[] = $book;
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error getting overdue books: " . $e->getMessage());
            $overdueBooks = [];
        }
        
        // Get transaction history safely
        try {
            if (method_exists($transactionModel, 'getTransactionsByUser')) {
                $transactionHistory = $transactionModel->getTransactionsByUser($userId) ?? [];
            } elseif (method_exists($transactionModel, 'getTransactionsByUserId')) {
                $transactionHistory = $transactionModel->getTransactionsByUserId($userId) ?? [];
            } else {
                $transactionHistory = [];
            }
        } catch (\Exception $e) {
            error_log("Error getting transaction history: " . $e->getMessage());
            $transactionHistory = [];
        }

        // Get reserved books safely
        try {
            $bookReservationModel = new BookReservation();
            if (method_exists($bookReservationModel, 'getReservationsByUser')) {
                $reservedBooks = $bookReservationModel->getReservationsByUser($userId) ?? [];
            } else {
                $reservedBooks = [];
            }
        } catch (\Exception $e) {
            error_log("Error getting reserved books: " . $e->getMessage());
            $reservedBooks = [];
        }

        // Get notifications safely
        try {
            if (method_exists($userModel, 'getNotifications')) {
                $notifications = $userModel->getNotifications($userId) ?? [];
            } else {
                $notifications = [];
            }
        } catch (\Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            $notifications = [];
        }

        // Calculate total fines safely
        try {
            $totalFines = UserService::calculateTotalFines($userId);
        } catch (\Exception $e) {
            error_log("Error calculating fines: " . $e->getMessage());
            $totalFines = 0;
        }
        
        // Initialize UserService for the view
        $userService = new UserService();

        // Ensure all borrowed books have required keys
        foreach ($borrowedBooks as &$book) {
            $book['title'] = $book['title'] ?? $book['bookName'] ?? 'Unknown';
            $book['dueDate'] = $book['dueDate'] ?? $book['returnDate'] ?? 'N/A';
        }
        
        // Ensure all transactions have required keys
        foreach ($transactionHistory as &$transaction) {
            $transaction['transactionId'] = $transaction['transactionId'] ?? $transaction['tid'] ?? 'N/A';
            $transaction['title'] = $transaction['title'] ?? $transaction['bookName'] ?? 'Unknown';
            $transaction['borrowDate'] = $transaction['borrowDate'] ?? $transaction['issueDate'] ?? 'N/A';
            $transaction['returnDate'] = $transaction['returnDate'] ?? null;
        }
        
        // Ensure notifications have required keys
        foreach ($notifications as &$notification) {
            $notification['message'] = $notification['message'] ?? $notification['content'] ?? '';
            $notification['createdAt'] = $notification['createdAt'] ?? $notification['created_at'] ?? date('Y-m-d H:i:s');
        }

        // Calculate stats for dashboard
        $stats = [
            'borrowed_books' => count($borrowedBooks),
            'overdue_books' => count($overdueBooks),
            'total_fines' => $totalFines,
            'reserved_books' => count($reservedBooks)
        ];

        // Load the view and pass the data
        include __DIR__ . '/../views/faculty/dashboard.php';
    }

    public function books()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }
        
        $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;
        if ($userType !== 'Faculty') {
            $_SESSION['error_message'] = 'Access denied. Faculty access required.';
            header('Location: /login');
            exit();
        }

        $bookService = new BookService();
        
        // Get search parameters
        $searchTerm = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? '';
        
        // Get all books or search results
        if (!empty($searchTerm) || !empty($category) || !empty($status)) {
            $books = $bookService->searchBooks($searchTerm, $category, $status);
            $totalBooks = count($books);
        } else {
            $booksData = $bookService->getAllBooks(1, 1000);
            $books = $booksData['books'];
            $totalBooks = $booksData['total'];
        }
        
        // Apply sorting if requested
        if (!empty($sort) && !empty($books)) {
            switch ($sort) {
                case 'title':
                    usort($books, function($a, $b) {
                        return strcmp($a['bookName'] ?? '', $b['bookName'] ?? '');
                    });
                    break;
                case 'author':
                    usort($books, function($a, $b) {
                        return strcmp($a['authorName'] ?? '', $b['authorName'] ?? '');
                    });
                    break;
                case 'available':
                    usort($books, function($a, $b) {
                        return ($b['available'] ?? 0) - ($a['available'] ?? 0);
                    });
                    break;
            }
        }
        
        // Get categories for filter dropdown
        $categories = $bookService->getCategories();

        include __DIR__ . '/../views/faculty/books.php';
    }

    public function search()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        // Redirect to books page with search parameters
        $queryParams = http_build_query($_GET);
        header('Location: /faculty/books?' . $queryParams);
        exit;
    }

    public function viewBook($params)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        $isbn = $params['isbn'] ?? '';
        $bookService = new BookService();
        $book = $bookService->getBookByIsbn($isbn);

        if (!$book) {
            $_SESSION['error_message'] = 'Book not found!';
            header('Location: /faculty/books');
            exit;
        }

        include __DIR__ . '/../views/faculty/view-book.php';
    }

    public function reserve($params)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        $isbn = $params['isbn'] ?? '';
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];

        try {
            $bookService = new BookService();
            $book = $bookService->getBookByIsbn($isbn);
            
            if (!$book) {
                $_SESSION['error_message'] = 'Book not found!';
                header('Location: /faculty/books');
                exit;
            }
            
            $reservationModel = new BookReservation();
            
            // Process based on availability
            if (($book['available'] ?? 0) > 0) {
                $reservationModel->createReservation($userId, $isbn);
                $_SESSION['success_message'] = 'Book reserved successfully! Please visit the library to collect it within 24 hours.';
            } else {
                $reservationModel->createReservation($userId, $isbn);
                $_SESSION['success_message'] = 'Book is currently unavailable. You have been added to the waiting list.';
            }
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'An error occurred while processing your request. Please try again.';
            error_log("Reservation error: " . $e->getMessage());
        }

        header('Location: /faculty/books');
        exit;
    }

    public function borrowHistory()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
        $transactionModel = new Transaction();
        
        try {
            if (method_exists($transactionModel, 'getTransactionsByUserId')) {
                $transactions = $transactionModel->getTransactionsByUserId($userId);
            } elseif (method_exists($transactionModel, 'getTransactionsByUser')) {
                $transactions = $transactionModel->getTransactionsByUser($userId);
            } else {
                $transactions = [];
            }
        } catch (\Exception $e) {
            error_log("Error getting borrow history: " . $e->getMessage());
            $transactions = [];
        }

        include __DIR__ . '/../views/faculty/borrow-history.php';
    }

    public function bookRequest()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
            $bookTitle = $_POST['book_title'] ?? '';
            $author = $_POST['author'] ?? '';
            $isbn = $_POST['isbn'] ?? '';
            $reason = $_POST['reason'] ?? '';

            if (empty($bookTitle) || empty($author)) {
                $_SESSION['error_message'] = 'Book title and author are required!';
                header('Location: /faculty/book-request');
                exit;
            }

            try {
                $bookRequestModel = new \App\Models\BookRequest();
                $bookRequestModel->createRequest($userId, $bookTitle, $author, $isbn, $reason);
                $_SESSION['success_message'] = 'Book request submitted successfully!';
            } catch (\Exception $e) {
                $_SESSION['error_message'] = 'Failed to submit book request.';
                error_log("Book request error: " . $e->getMessage());
            }

            header('Location: /faculty/dashboard');
            exit;
        } else {
            $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
            try {
                $bookRequestModel = new \App\Models\BookRequest();
                $requests = $bookRequestModel->getRequestsByUserId($userId);
            } catch (\Exception $e) {
                error_log("Error getting book requests: " . $e->getMessage());
                $requests = [];
            }
            include __DIR__ . '/../views/faculty/book-request.php';
        }
    }

    public function notifications()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
        try {
            $notificationModel = new \App\Models\Notification();
            $notifications = $notificationModel->getNotificationsByUserId($userId);
        } catch (\Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            $notifications = [];
        }

        include __DIR__ . '/../views/faculty/notifications.php';
    }

    public function profile()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
        $userModel = new User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'department' => $_POST['department'] ?? '',
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            try {
                $userModel->updateUser($userId, $data);
                $_SESSION['success_message'] = 'Profile updated successfully!';
            } catch (\Exception $e) {
                $_SESSION['error_message'] = 'Failed to update profile.';
                error_log("Profile update error: " . $e->getMessage());
            }

            header('Location: /faculty/profile');
            exit;
        } else {
            $user = $userModel->getUserById($userId);
            include __DIR__ . '/../views/faculty/profile.php';
        }
    }

    public function feedback()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }
        
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
        $userModel = new User();
        $user = $userModel->getUserById($userId);
        
        try {
            $feedbackModel = new \App\Models\Feedback();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $subject = $_POST['subject'] ?? '';
                $message = $_POST['message'] ?? '';

                if (empty($subject) || empty($message)) {
                    $_SESSION['error_message'] = 'Subject and message are required.';
                } else {
                    if ($feedbackModel->createFeedback($userId, $subject, $message)) {
                        $_SESSION['success_message'] = 'Feedback submitted successfully.';
                        header('Location: /faculty/feedback');
                        exit;
                    } else {
                        $_SESSION['error_message'] = 'Failed to submit feedback.';
                    }
                }
            }

            $feedbacks = $feedbackModel->getFeedbackByUserId($userId);
        } catch (\Exception $e) {
            error_log("Feedback error: " . $e->getMessage());
            $feedbacks = [];
        }

        include __DIR__ . '/../views/faculty/feedback.php';
    }
}