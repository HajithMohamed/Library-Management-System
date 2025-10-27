<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\BookReservation;
use App\Helpers\AuthHelper;
use App\Services\UserService;

class FacultyController
{
    public function dashboard()
    {
        AuthHelper::requireRole(['Faculty']);

        $userId = $_SESSION['user_id'];
        $userModel = new User();
        $user = $userModel->getUserById($userId);

        $transactionModel = new Transaction();
        $borrowedBooks = $transactionModel->getBorrowedBooks($userId);
        $overdueBooks = $transactionModel->getOverdueBooks($userId);
        $transactionHistory = $transactionModel->getTransactionsByUser($userId);

        $bookReservationModel = new BookReservation();
        $reservedBooks = $bookReservationModel->getReservationsByUser($userId);

        $notifications = $userModel->getNotifications($userId);

        $totalFines = UserService::calculateTotalFines($userId);

        // Load the view and pass the data
        include __DIR__ . '../../views/faculty/dashboard.php';
    }

    public function search()
    {
        AuthHelper::requireRole(['Faculty']);

        $searchTerm = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';

        $bookModel = new \App\Models\Book();
        $books = $bookModel->searchBooks($searchTerm, $category);
        $categories = $bookModel->getAllCategories();

        include __DIR__ . '../../views/faculty/search.php';
    }

    public function viewBook($params)
    {
        AuthHelper::requireRole(['Faculty']);

        $isbn = $params['isbn'];
        $bookModel = new \App\Models\Book();
        $book = $bookModel->getBookByIsbn($isbn);

        if (!$book) {
            // Handle book not found
            include __DIR__ . '../../views/errors/404.php';
            exit;
        }

        include __DIR__ . '../../views/faculty/view-book.php';
    }

    public function reserve($params)
    {
        AuthHelper::requireRole(['Faculty']);

        $isbn = $params['isbn'];
        $userId = $_SESSION['user_id'];

        $reservationModel = new \App\Models\BookReservation();
        $reservationModel->createReservation($userId, $isbn);

        // Redirect to the dashboard with a success message
        $_SESSION['success_message'] = 'Book reserved successfully!';
        header('Location: /faculty/dashboard');
        exit;
    }

    public function borrowHistory()
    {
        AuthHelper::requireRole(['Faculty']);

        $userId = $_SESSION['user_id'];
        $transactionModel = new \App\Models\Transaction();
        $transactions = $transactionModel->getTransactionsByUserId($userId);

        include __DIR__ . '../../views/faculty/borrow-history.php';
    }

    public function bookRequest()
    {
        AuthHelper::requireRole(['Faculty']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $bookTitle = $_POST['book_title'];
            $author = $_POST['author'];
            $isbn = $_POST['isbn'];
            $reason = $_POST['reason'];

            $bookRequestModel = new \App\Models\BookRequest();
            $bookRequestModel->createRequest($userId, $bookTitle, $author, $isbn, $reason);

            $_SESSION['success_message'] = 'Book request submitted successfully!';
            header('Location: /faculty/dashboard');
            exit;
        } else {
            $userId = $_SESSION['user_id'];
            $bookRequestModel = new \App\Models\BookRequest();
            $requests = $bookRequestModel->getRequestsByUserId($userId);
            include __DIR__ . '../../views/faculty/book-request.php';
        }
    }

    public function notifications()
    {
        AuthHelper::requireRole(['Faculty']);

        $userId = $_SESSION['user_id'];
        $notificationModel = new \App\Models\Notification();
        $notifications = $notificationModel->getNotificationsByUserId($userId);

        include __DIR__ . '../../views/faculty/notifications.php';
    }

    public function profile()
    {
        AuthHelper::requireRole(['Faculty']);

        $userId = $_SESSION['user_id'];
        $userModel = new \App\Models\User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'department' => $_POST['department'],
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            $userModel->updateUser($userId, $data);

            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: /faculty/profile');
            exit;
        } else {
            $user = $userModel->getUserById($userId);
            include __DIR__ . '../../views/faculty/profile.php';
        }
    }

    public function feedback()
    {
        $this->requireRole('faculty');
        $user = $this->getCurrentUser();
        $feedbackModel = new \App\Models\Feedback();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';

            if (empty($subject) || empty($message)) {
                $errors[] = 'Subject and message are required.';
            } else {
                if ($feedbackModel->createFeedback($user['userId'], $subject, $message)) {
                    $success = 'Feedback submitted successfully.';
                } else {
                    $errors[] = 'Failed to submit feedback.';
                }
            }
        }

        $feedbacks = $feedbackModel->getFeedbackByUserId($user['userId']);

        $this->view('faculty/feedback', [
            'user' => $user,
            'feedbacks' => $feedbacks,
            'errors' => $errors ?? [],
            'success' => $success ?? null
        ]);
    }
}