<?php
// src/controllers/BookController.php

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
        // Ensure user is logged in
        if (!isset($_SESSION['userId'])) {
            $_SESSION['error'] = 'Please login to access this page';
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        // Ensure user is admin (case-insensitive check)
        $userType = strtolower($_SESSION['userType'] ?? '');
        if ($userType !== 'admin') {
            $_SESSION['error'] = 'You do not have permission to access this page. Admin access required.';
            error_log("Access denied to books page. User type: " . $_SESSION['userType'] . ", User ID: " . $_SESSION['userId']);
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        try {
            // Get pagination parameters
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 12;
            
            // Get search parameters
            $search = $_GET['q'] ?? '';
            $category = $_GET['category'] ?? '';
            $status = $_GET['status'] ?? '';
            
            // Get books with pagination
            $result = $this->bookService->getAllBooks($page, $perPage);
            
            // Extract books array from result
            $books = $result['books'] ?? [];
            
            // Set up pagination
            $pagination = [
                'page' => $result['page'] ?? $page,
                'perPage' => $result['perPage'] ?? $perPage,
                'total' => $result['total'] ?? 0,
                'totalPages' => $result['totalPages'] ?? 1
            ];
            
            // Get all categories for filter dropdown
            $categories = $this->bookService->getCategories();
            if (!is_array($categories)) {
                $categories = [];
            }
            
            // Calculate statistics
            $stats = [
                'totalBooks' => $pagination['total'],
                'availableBooks' => 0,
                'borrowedBooks' => 0,
                'categories' => count($categories)
            ];
            
            // Calculate available and borrowed books
            foreach ($books as $book) {
                if (isset($book['available'])) {
                    $stats['availableBooks'] += (int)$book['available'];
                }
                if (isset($book['borrowed'])) {
                    $stats['borrowedBooks'] += (int)$book['borrowed'];
                }
            }
            
            // Set page title
            $pageTitle = 'Books Management';
            
            // Load admin book view
            include APP_ROOT . '/views/admin/books.php';
            
        } catch (\Exception $e) {
            error_log("Error in adminBooks: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Error loading books: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit();
        }
    }
    
    /**
     * Add a new book (GET: show form, POST: process form)
     */
    public function addBook() {
        // Check admin permission
        if (!isset($_SESSION['userId']) || strtolower($_SESSION['userType'] ?? '') !== 'admin') {
            http_response_code(403);
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                // Validate required fields
                $required = ['isbn', 'bookName', 'author', 'publisher', 'totalCopies'];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
                        exit();
                    }
                }
                
                // Create book data
                $bookData = [
                    'isbn' => trim($_POST['isbn']),
                    'bookName' => trim($_POST['bookName']),
                    'authorName' => trim($_POST['author']),
                    'publisherName' => trim($_POST['publisher']),
                    'totalCopies' => (int)$_POST['totalCopies'],
                    'available' => (int)$_POST['totalCopies'],
                    'borrowed' => 0,
                    'description' => trim($_POST['description'] ?? ''),
                ];
                
                // Handle file upload
                if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = APP_ROOT . '/public/uploads/books/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $extension = pathinfo($_FILES['coverImage']['name'], PATHINFO_EXTENSION);
                    $filename = $bookData['isbn'] . '_' . time() . '.' . $extension;
                    
                    if (move_uploaded_file($_FILES['coverImage']['tmp_name'], $uploadDir . $filename)) {
                        $bookData['bookImage'] = $filename;
                    }
                }
                
                // Add book
                if ($this->bookService->addBook($bookData)) {
                    echo json_encode(['success' => true, 'message' => 'Book added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add book']);
                }
                
            } catch (\Exception $e) {
                error_log("Error adding book: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit();
        }
    }
    
    /**
     * Edit a book (GET: show form, POST: process form)
     */
    public function editBook() {
        // Check admin permission
        if (!isset($_SESSION['userId']) || strtolower($_SESSION['userType'] ?? '') !== 'admin') {
            http_response_code(403);
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $isbn = trim($_POST['isbn'] ?? '');
                if (empty($isbn)) {
                    echo json_encode(['success' => false, 'message' => 'ISBN is required']);
                    exit();
                }
                
                // Create update data
                $bookData = [
                    'bookName' => trim($_POST['bookName']),
                    'authorName' => trim($_POST['author']),
                    'publisherName' => trim($_POST['publisher']),
                    'totalCopies' => (int)$_POST['totalCopies'],
                    'available' => (int)$_POST['available'],
                    'description' => trim($_POST['description'] ?? ''),
                ];
                
                // Handle file upload
                if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = APP_ROOT . '/public/uploads/books/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $extension = pathinfo($_FILES['coverImage']['name'], PATHINFO_EXTENSION);
                    $filename = $isbn . '_' . time() . '.' . $extension;
                    
                    if (move_uploaded_file($_FILES['coverImage']['tmp_name'], $uploadDir . $filename)) {
                        $bookData['bookImage'] = $filename;
                    }
                }
                
                // Update book
                if ($this->bookService->updateBook($isbn, $bookData)) {
                    echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update book']);
                }
                
            } catch (\Exception $e) {
                error_log("Error updating book: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit();
        }
    }
    
    /**
     * Delete a book
     */
    public function deleteBook() {
        // Check admin permission
        if (!isset($_SESSION['userId']) || strtolower($_SESSION['userType'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $isbn = trim($_POST['isbn'] ?? '');
                if (empty($isbn)) {
                    echo json_encode(['success' => false, 'message' => 'ISBN is required']);
                    exit();
                }
                
                // Delete book
                if ($this->bookService->deleteBook($isbn)) {
                    echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete book. It may have active transactions.']);
                }
                
            } catch (\Exception $e) {
                error_log("Error deleting book: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit();
        }
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload() {
        if (!isset($_FILES['coverImage']) || $_FILES['coverImage']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        $file = $_FILES['coverImage'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5242880) {
            return null;
        }
        
        // Create uploads directory if it doesn't exist
        $uploadDir = APP_ROOT . '/public/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_', true) . '.' . $extension;
        $destination = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $filename;
        }
        
        return null;
    }
}