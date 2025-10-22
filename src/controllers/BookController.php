<?php

namespace App\Controllers;

use App\Models\Book;
use App\Services\BookService;

class BookController {
    private $bookService;
    
    public function __construct() {
        $this->bookService = new BookService();
    }
    
    /**
     * Display books for regular users
     */
    public function userBooks() {
        $books = $this->bookService->getAllBooks();
        
        // Load user book view
        include APP_ROOT . '/views/user/books.php';
    }
    
    /**
     * Display book management page for admins
     */
    public function adminBooks() {
        // Ensure user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        $books = $this->bookService->getAllBooks();
        
        $pageTitle = 'Manage Books';
        $currentPage = 'books';
        $contentView = APP_ROOT . '/views/admin/books.php';
        
        include APP_ROOT . '/views/admin/layout.php';
    }
    
    /**
     * Display add book form
     */
    public function addBook() {
        // Ensure user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        $pageTitle = 'Add New Book';
        $currentPage = 'books';
        $contentView = APP_ROOT . '/views/admin/books/add.php';
        
        include APP_ROOT . '/views/admin/layout.php';
    }
    
    /**
     * Handle book creation
     */
    public function createBook() {
        // Ensure user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        // Process form data
        $bookData = [
            'isbn' => $_POST['isbn'],
            'bookName' => $_POST['bookName'],
            'authorName' => $_POST['authorName'],
            'publisherName' => $_POST['publisherName'],
            'category' => $_POST['category'],
            'description' => $_POST['description'],
            'totalCopies' => (int)$_POST['totalCopies'],
            'available' => (int)$_POST['totalCopies']
        ];
        
        // Handle file upload if present
        if (isset($_FILES['bookImage']) && $_FILES['bookImage']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->bookService->uploadBookImage($_FILES['bookImage']);
            if ($uploadResult['success']) {
                $bookData['bookImage'] = $uploadResult['path'];
            }
        }
        
        $result = $this->bookService->addBook($bookData);
        
        if ($result) {
            $_SESSION['success'] = 'Book added successfully!';
        } else {
            $_SESSION['error'] = 'Failed to add book. Please try again.';
        }
        
        header('Location: ' . BASE_URL . 'admin/books');
        exit();
    }
    
    /**
     * Display edit book form
     */
    public function editBook() {
        // Ensure user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        if (!isset($_GET['isbn'])) {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        $isbn = $_GET['isbn'];
        $book = $this->bookService->getBookByISBN($isbn);
        
        if (!$book) {
            $_SESSION['error'] = 'Book not found.';
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        $pageTitle = 'Edit Book';
        $currentPage = 'books';
        $contentView = APP_ROOT . '/views/admin/books/edit.php';
        
        include APP_ROOT . '/views/admin/layout.php';
    }
    
    /**
     * Handle book update
     */
    public function updateBook() {
        // Ensure user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        $isbn = $_POST['isbn'];
        
        // Process form data
        $bookData = [
            'bookName' => $_POST['bookName'],
            'authorName' => $_POST['authorName'],
            'publisherName' => $_POST['publisherName'],
            'category' => $_POST['category'],
            'description' => $_POST['description'],
            'totalCopies' => (int)$_POST['totalCopies']
        ];
        
        // Handle file upload if present
        if (isset($_FILES['bookImage']) && $_FILES['bookImage']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->bookService->uploadBookImage($_FILES['bookImage']);
            if ($uploadResult['success']) {
                $bookData['bookImage'] = $uploadResult['path'];
            }
        }
        
        $result = $this->bookService->updateBook($isbn, $bookData);
        
        if ($result) {
            $_SESSION['success'] = 'Book updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update book. Please try again.';
        }
        
        header('Location: ' . BASE_URL . 'admin/books');
        exit();
    }
    
    /**
     * Handle book deletion
     */
    public function deleteBook() {
        // Ensure user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['isbn'])) {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        $isbn = $_POST['isbn'];
        $result = $this->bookService->deleteBook($isbn);
        
        if ($result) {
            $_SESSION['success'] = 'Book deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete book. Please try again.';
        }
        
        header('Location: ' . BASE_URL . 'admin/books');
        exit();
    }
    
    /**
     * Search books API
     */
    public function searchBooks() {
        $query = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        
        $books = $this->bookService->searchBooks($query, $category);
        
        // If AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($books);
            exit;
        }
        
        // Otherwise show search results page
        include APP_ROOT . '/views/books/search.php';
    }
    
    /**
     * Get book details API
     */
    public function getBookDetails() {
        if (!isset($_GET['isbn'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ISBN parameter is required']);
            exit;
        }
        
        $isbn = $_GET['isbn'];
        $book = $this->bookService->getBookByISBN($isbn);
        
        if (!$book) {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found']);
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode($book);
    }
}
