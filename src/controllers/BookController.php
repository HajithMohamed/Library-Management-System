<?php

namespace App\Controllers;

use App\Models\Book;
use App\Services\BookService;
use App\Helpers\AuthHelper;

class BookController
{
    private $bookModel;
    private $bookService;
    private $authHelper;

    public function __construct()
    {
        $this->bookModel = new Book();
        $this->bookService = new BookService();
        $this->authHelper = new AuthHelper();
    }

    /**
     * Display books for regular users
     */
    public function userBooks()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        $search = $_GET['search'] ?? '';
        $books = $this->bookModel->searchBooks($search);
        
        $this->render('books/user-books', [
            'books' => $books,
            'search' => $search
        ]);
    }

    /**
     * Display books for admin management
     */
    public function adminBooks()
    {
        $this->authHelper->requireAuth(['Admin']);
        
        $search = $_GET['search'] ?? '';
        $books = $this->bookModel->searchBooks($search);
        
        $this->render('books/admin-books', [
            'books' => $books,
            'search' => $search
        ]);
    }

    /**
     * Show add book form
     */
    public function addBook()
    {
        $this->authHelper->requireAuth(['Admin']);
        
        $this->render('books/add-book');
    }

    /**
     * Create a new book
     */
    public function createBook()
    {
        $this->authHelper->requireAuth(['Admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/books/add');
            return;
        }

        $data = [
            'isbn' => $_POST['isbn'] ?? '',
            'bookName' => $_POST['bookName'] ?? '',
            'authorName' => $_POST['authorName'] ?? '',
            'publisherName' => $_POST['publisherName'] ?? '',
            'available' => (int)($_POST['available'] ?? 0),
            'borrowed' => 0
        ];

        if ($this->bookService->createBook($data)) {
            $_SESSION['success'] = 'Book added successfully!';
            $this->redirect('/admin/books');
        } else {
            $_SESSION['error'] = 'Failed to add book. Please try again.';
            $this->redirect('/admin/books/add');
        }
    }

    /**
     * Show edit book form
     */
    public function editBook()
    {
        $this->authHelper->requireAuth(['Admin']);
        
        $isbn = $_GET['isbn'] ?? '';
        if (empty($isbn)) {
            $this->redirect('/admin/books');
            return;
        }

        $book = $this->bookModel->getBookByISBN($isbn);
        if (!$book) {
            $_SESSION['error'] = 'Book not found.';
            $this->redirect('/admin/books');
            return;
        }

        $this->render('books/edit-book', ['book' => $book]);
    }

    /**
     * Update a book
     */
    public function updateBook()
    {
        $this->authHelper->requireAuth(['Admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/books');
            return;
        }

        $isbn = $_POST['isbn'] ?? '';
        $data = [
            'bookName' => $_POST['bookName'] ?? '',
            'authorName' => $_POST['authorName'] ?? '',
            'publisherName' => $_POST['publisherName'] ?? '',
            'available' => (int)($_POST['available'] ?? 0)
        ];

        if ($this->bookService->updateBook($isbn, $data)) {
            $_SESSION['success'] = 'Book updated successfully!';
            $this->redirect('/admin/books');
        } else {
            $_SESSION['error'] = 'Failed to update book. Please try again.';
            $this->redirect('/admin/books/edit?isbn=' . $isbn);
        }
    }

    /**
     * Delete a book
     */
    public function deleteBook()
    {
        $this->authHelper->requireAuth(['Admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/books');
            return;
        }

        $isbn = $_POST['isbn'] ?? '';
        
        if ($this->bookService->deleteBook($isbn)) {
            $_SESSION['success'] = 'Book deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete book. Please try again.';
        }
        
        $this->redirect('/admin/books');
    }

    /**
     * Show borrow book form
     */
    public function borrow()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        $isbn = $_GET['isbn'] ?? '';
        if (empty($isbn)) {
            $this->redirect('/user/books');
            return;
        }

        $book = $this->bookModel->getBookByISBN($isbn);
        if (!$book) {
            $_SESSION['error'] = 'Book not found.';
            $this->redirect('/user/books');
            return;
        }

        $this->render('books/borrow-book', ['book' => $book]);
    }

    /**
     * Process book borrowing
     */
    public function borrowBook()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/books');
            return;
        }

        $isbn = $_POST['isbn'] ?? '';
        $userId = $_SESSION['userId'];

        if ($this->bookService->borrowBook($userId, $isbn)) {
            $_SESSION['success'] = 'Book borrowed successfully!';
            $this->redirect('/user/dashboard');
        } else {
            $_SESSION['error'] = 'Failed to borrow book. Please try again.';
            $this->redirect('/user/books');
        }
    }

    /**
     * Show return book form
     */
    public function return()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        $userId = $_SESSION['userId'];
        $borrowedBooks = $this->bookModel->getBorrowedBooks($userId);
        
        $this->render('books/return-book', ['books' => $borrowedBooks]);
    }

    /**
     * Process book return
     */
    public function returnBook()
    {
        $this->authHelper->requireAuth(['Student', 'Faculty']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/return');
            return;
        }

        $isbn = $_POST['isbn'] ?? '';
        $userId = $_SESSION['userId'];

        if ($this->bookService->returnBook($userId, $isbn)) {
            $_SESSION['success'] = 'Book returned successfully!';
            $this->redirect('/user/dashboard');
        } else {
            $_SESSION['error'] = 'Failed to return book. Please try again.';
            $this->redirect('/user/return');
        }
    }

    /**
     * Search books API endpoint
     */
    public function searchBooks()
    {
        $search = $_GET['q'] ?? '';
        $books = $this->bookModel->searchBooks($search);
        
        header('Content-Type: application/json');
        echo json_encode($books);
    }

    /**
     * Get book details API endpoint
     */
    public function getBookDetails()
    {
        $isbn = $_GET['isbn'] ?? '';
        $book = $this->bookModel->getBookByISBN($isbn);
        
        header('Content-Type: application/json');
        echo json_encode($book);
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
?>
