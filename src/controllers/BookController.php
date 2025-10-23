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
        // Ensure user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }
        
        try {
            // Get books without pagination for simplicity
            $booksData = $this->bookService->getAllBooks();
            
            // Check if we have the books property in the returned data
            if (isset($booksData['books'])) {
                $books = $booksData['books'];
            } else {
                // Fall back to using the bookModel directly if needed
                $books = $this->bookModel->getAllBooks();
            }
            
            // Verify $books is an array
            if (!is_array($books)) {
                $books = [];
                error_log("Invalid books data returned from service: " . print_r($books, true));
            }
            
            $pageTitle = 'Manage Books';
            $currentPage = 'books';
            $contentView = APP_ROOT . '/views/admin/books.php';
            
            include APP_ROOT . '/views/admin/layout.php';
        } catch (\Exception $e) {
            error_log("Error in adminBooks: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while loading books. Please try again.';
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit();
        }
    }
    
    // Display all books
    public function index() {
        // Get filters from request
        $filters = [];
        if(isset($_GET['category'])) {
            $filters['category'] = $_GET['category'];
        }
        if(isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if(isset($_GET['trending'])) {
            $filters['is_trending'] = true;
        }
        if(isset($_GET['featured'])) {
            $filters['is_featured'] = true;
        }

        $result = $this->book->read($filters);
        $books = $result->fetchAll(PDO::FETCH_ASSOC);
        
        // Get categories for filter
        $categoriesResult = $this->book->getCategories();
        $categories = $categoriesResult->fetchAll(PDO::FETCH_COLUMN);

        require_once __DIR__ . '/../views/books/index.php';
    }

    // Show single book
    public function show($id) {
        $this->book->id = $id;
        
        if($this->book->readOne()) {
            require_once __DIR__ . '/../views/books/show.php';
        } else {
            $_SESSION['error'] = "Book not found";
            header('Location: /books');
            exit();
        }
    }

    // Show create form
    public function create() {
        require_once __DIR__ . '/../views/books/create.php';
    }

    // Store new book
    public function store() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /books/create');
            exit();
        }

        // Handle image upload
        $coverImage = $this->handleImageUpload();

        // Set book properties
        $this->book->title = $_POST['title'] ?? '';
        $this->book->author = $_POST['author'] ?? '';
        $this->book->isbn = $_POST['isbn'] ?? '';
        $this->book->publisher = $_POST['publisher'] ?? '';
        $this->book->published_year = $_POST['published_year'] ?? null;
        $this->book->category = $_POST['category'] ?? '';
        $this->book->description = $_POST['description'] ?? '';
        $this->book->cover_image = $coverImage;
        $this->book->total_copies = $_POST['total_copies'] ?? 1;
        $this->book->available_copies = $_POST['total_copies'] ?? 1;
        $this->book->is_trending = isset($_POST['is_trending']) ? 1 : 0;
        $this->book->is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $this->book->special_tag = $_POST['special_tag'] ?? null;

        if($this->book->create()) {
            $_SESSION['success'] = "Book created successfully!";
            header('Location: /books');
            exit();
        } else {
            $_SESSION['error'] = "Failed to create book";
            header('Location: /books/create');
            exit();
        }
    }

    // Show edit form
    public function edit($id) {
        $this->book->id = $id;
        
        if($this->book->readOne()) {
            require_once __DIR__ . '/../views/books/edit.php';
        } else {
            $_SESSION['error'] = "Book not found";
            header('Location: /books');
            exit();
        }
    }

    // Update book
    public function update($id) {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /books/' . $id . '/edit');
            exit();
        }

        $this->book->id = $id;
        
        if(!$this->book->readOne()) {
            $_SESSION['error'] = "Book not found";
            header('Location: /books');
            exit();
        }

        // Handle image upload (if new image provided)
        $coverImage = $this->handleImageUpload();
        if($coverImage) {
            // Delete old image if exists
            if($this->book->cover_image && file_exists(__DIR__ . '/../../public/assets/uploads/' . $this->book->cover_image)) {
                unlink(__DIR__ . '/../../public/assets/uploads/' . $this->book->cover_image);
            }
            $this->book->cover_image = $coverImage;
        }

        // Update book properties
        $this->book->title = $_POST['title'] ?? '';
        $this->book->author = $_POST['author'] ?? '';
        $this->book->isbn = $_POST['isbn'] ?? '';
        $this->book->publisher = $_POST['publisher'] ?? '';
        $this->book->published_year = $_POST['published_year'] ?? null;
        $this->book->category = $_POST['category'] ?? '';
        $this->book->description = $_POST['description'] ?? '';
        $this->book->total_copies = $_POST['total_copies'] ?? 1;
        $this->book->available_copies = $_POST['available_copies'] ?? 1;
        $this->book->is_trending = isset($_POST['is_trending']) ? 1 : 0;
        $this->book->is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $this->book->special_tag = $_POST['special_tag'] ?? null;

        if($this->book->update()) {
            $_SESSION['success'] = "Book updated successfully!";
            header('Location: /books/' . $id);
            exit();
        } else {
            $_SESSION['error'] = "Failed to update book";
            header('Location: /books/' . $id . '/edit');
            exit();
        }
    }

    // Delete book
    public function delete($id) {
        $this->book->id = $id;
        
        if(!$this->book->readOne()) {
            $_SESSION['error'] = "Book not found";
            header('Location: /books');
            exit();
        }

        // Delete book image if exists
        if($this->book->cover_image && file_exists(__DIR__ . '/../../public/assets/uploads/' . $this->book->cover_image)) {
            unlink(__DIR__ . '/../../public/assets/uploads/' . $this->book->cover_image);
        }

        if($this->book->delete()) {
            $_SESSION['success'] = "Book deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete book";
        }

        header('Location: /books');
        exit();
    }

    // Toggle trending status
    public function toggleTrending($id) {
        $this->book->id = $id;
        
        if($this->book->toggleTrending()) {
            $_SESSION['success'] = "Trending status updated!";
        } else {
            $_SESSION['error'] = "Failed to update trending status";
        }

        header('Location: /books');
        exit();
    }

    // Toggle featured status
    public function toggleFeatured($id) {
        $this->book->id = $id;
        
        if($this->book->toggleFeatured()) {
            $_SESSION['success'] = "Featured status updated!";
        } else {
            $_SESSION['error'] = "Failed to update featured status";
        }

        header('Location: /books');
        exit();
    }

    // Handle image upload
    private function handleImageUpload() {
        if(!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['cover_image'];

        // Check for upload errors
        if($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "File upload error";
            return null;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if(!in_array($mimeType, $allowedTypes)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
            return null;
        }

        // Validate file size (5MB max)
        if($file['size'] > 5242880) {
            $_SESSION['error'] = "File too large. Maximum size is 5MB.";
            return null;
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_', true) . '.' . $extension;
        $destination = $uploadDir . $filename;

        // Move uploaded file
        if(move_uploaded_file($file['tmp_name'], $destination)) {
            return $filename;
        } else {
            $_SESSION['error'] = "Failed to upload file";
            return null;
        }
    }
}