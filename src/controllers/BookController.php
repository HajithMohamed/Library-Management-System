<?php
// src/controllers/BookController.php

namespace App\Controllers;

use App\Helpers\BarcodeHelper;

class BookController
{
    /**
     * Display books for admin
     */
    public function adminBooks()
    {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }

        global $mysqli;
        
        // Check if connection exists
        if (!$mysqli) {
            die("Database connection failed");
        }
        
        // Fetch all books - ONLY columns that exist in your table
        $sql = "SELECT 
                    isbn,
                    barcode,
                    bookName,
                    authorName,
                    publisherName,
                    totalCopies,
                    available,
                    borrowed,
                    isTrending
                FROM books 
                ORDER BY bookName ASC";
        
        $result = $mysqli->query($sql);
        
        // Check for query errors
        if (!$result) {
            error_log("SQL Error in adminBooks: " . $mysqli->error);
            die("Error fetching books: " . $mysqli->error);
        }
        
        $books = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add missing columns with default values for view compatibility
                $row['isSpecial'] = 0;
                $row['specialBadge'] = null;
                $books[] = $row;
            }
            $result->free();
        }
        
        // Debug: Log the number of books fetched
        error_log("Books fetched: " . count($books));
        
        // Get unique publishers for filter - with error handling
        $publishers = [];
        try {
            $publisherSql = "SELECT DISTINCT publisherName 
                            FROM books 
                            WHERE publisherName IS NOT NULL AND publisherName != '' 
                            ORDER BY publisherName ASC";
            $publisherResult = $mysqli->query($publisherSql);
            
            if ($publisherResult) {
                while ($row = $publisherResult->fetch_assoc()) {
                    if (!empty($row['publisherName'])) {
                        $publishers[] = $row['publisherName'];
                    }
                }
                $publisherResult->free();
            }
        } catch (\Exception $e) {
            error_log("Error fetching publishers: " . $e->getMessage());
            // Continue with empty publishers array
        }
        
        // Pass data to view
        $pageTitle = 'Books Management';
        include APP_ROOT . '/views/admin/books.php';
    }

    /**
     * Add new book
     */
    public function addBook()
    {
        // Check if user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }

        header('Content-Type: application/json');
        
        try {
            global $mysqli;
            
            // Validate connection
            if (!$mysqli) {
                throw new \Exception("Database connection failed");
            }
            
            $isbn = trim($_POST['isbn'] ?? '');
            $bookName = trim($_POST['bookName'] ?? '');
            $authorName = trim($_POST['authorName'] ?? '');
            $publisherName = trim($_POST['publisherName'] ?? '');
            $totalCopies = (int)($_POST['totalCopies'] ?? 1);
            $available = (int)($_POST['available'] ?? $totalCopies);
            $isTrending = isset($_POST['isTrending']) ? 1 : 0;
            
            // Validate required fields
            if (empty($isbn) || empty($bookName) || empty($authorName) || empty($publisherName)) {
                echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
                exit();
            }
            
            // Validate totalCopies and available
            if ($totalCopies < 1) {
                echo json_encode(['success' => false, 'message' => 'Total copies must be at least 1']);
                exit();
            }
            
            if ($available > $totalCopies) {
                echo json_encode(['success' => false, 'message' => 'Available copies cannot exceed total copies']);
                exit();
            }
            
            // Check if ISBN already exists
            $checkStmt = $mysqli->prepare("SELECT isbn FROM books WHERE isbn = ?");
            if (!$checkStmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $checkStmt->bind_param("s", $isbn);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Book with this ISBN already exists']);
                exit();
            }
            $checkStmt->close();
            
            // Generate barcode (barcode column exists but set to NULL in your data)
            $cleanIsbn = str_replace('-', '', $isbn);
            $barcodeValue = null; // Set to NULL as per your table structure
            
            // Calculate borrowed (totalCopies - available)
            $borrowed = $totalCopies - $available;
            
            // Insert book - matching exact table structure
            $stmt = $mysqli->prepare("INSERT INTO books 
                (isbn, barcode, bookName, authorName, publisherName, totalCopies, available, borrowed, isTrending) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $stmt->bind_param("sssssiii", 
                $isbn, 
                $barcodeValue,
                $bookName, 
                $authorName, 
                $publisherName, 
                $totalCopies, 
                $available,
                $borrowed,
                $isTrending
            );
            
            if ($stmt->execute()) {
                $stmt->close();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Book added successfully!',
                    'isbn' => $isbn
                ]);
            } else {
                $error = $stmt->error;
                $stmt->close();
                throw new \Exception("Failed to add book: " . $error);
            }
            
        } catch (\Exception $e) {
            error_log("Error adding book: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
        
        exit();
    }

    /**
     * Edit book
     */
    public function editBook()
    {
        // Check if user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }

        header('Content-Type: application/json');
        
        try {
            global $mysqli;
            
            if (!$mysqli) {
                throw new \Exception("Database connection failed");
            }
            
            $isbn = trim($_POST['isbn'] ?? '');
            $bookName = trim($_POST['bookName'] ?? '');
            $authorName = trim($_POST['authorName'] ?? '');
            $publisherName = trim($_POST['publisherName'] ?? '');
            $totalCopies = (int)($_POST['totalCopies'] ?? 1);
            $available = (int)($_POST['available'] ?? 0);
            $borrowed = (int)($_POST['borrowed'] ?? 0);
            $isTrending = isset($_POST['isTrending']) ? 1 : 0;
            
            // Validate
            if (empty($isbn) || empty($bookName) || empty($authorName) || empty($publisherName)) {
                echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
                exit();
            }
            
            // Validate copies
            if ($totalCopies < ($available + $borrowed)) {
                echo json_encode(['success' => false, 'message' => 'Total copies must be at least ' . ($available + $borrowed)]);
                exit();
            }
            
            // Update book - matching exact table structure
            $stmt = $mysqli->prepare("UPDATE books SET 
                bookName = ?, 
                authorName = ?, 
                publisherName = ?, 
                totalCopies = ?, 
                available = ?, 
                borrowed = ?,
                isTrending = ?
                WHERE isbn = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $stmt->bind_param("sssiiiis", 
                $bookName, 
                $authorName, 
                $publisherName, 
                $totalCopies, 
                $available, 
                $borrowed,
                $isTrending,
                $isbn
            );
            
            if ($stmt->execute()) {
                $stmt->close();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Book updated successfully!'
                ]);
            } else {
                $error = $stmt->error;
                $stmt->close();
                throw new \Exception("Failed to update book: " . $error);
            }
            
        } catch (\Exception $e) {
            error_log("Error updating book: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
        
        exit();
    }

    /**
     * Delete book
     */
    public function deleteBook()
    {
        // Check if user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }

        header('Content-Type: application/json');
        
        try {
            global $mysqli;
            
            if (!$mysqli) {
                throw new \Exception("Database connection failed");
            }
            
            $isbn = trim($_POST['isbn'] ?? '');
            
            if (empty($isbn)) {
                echo json_encode(['success' => false, 'message' => 'ISBN is required']);
                exit();
            }
            
            // Check if book has active borrowings
            $checkStmt = $mysqli->prepare("SELECT COUNT(*) as count FROM transactions WHERE isbn = ? AND returnDate IS NULL");
            if (!$checkStmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $checkStmt->bind_param("s", $isbn);
            $checkStmt->execute();
            $result = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();
            
            if ($result['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete book with active borrowings']);
                exit();
            }
            
            // Delete book
            $stmt = $mysqli->prepare("DELETE FROM books WHERE isbn = ?");
            if (!$stmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $stmt->bind_param("s", $isbn);
            
            if ($stmt->execute()) {
                $stmt->close();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Book deleted successfully!'
                ]);
            } else {
                $error = $stmt->error;
                $stmt->close();
                throw new \Exception("Failed to delete book: " . $error);
            }
            
        } catch (\Exception $e) {
            error_log("Error deleting book: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
        
        exit();
    }

    /**
     * Display books for users (public/student view)
     */
    public function userBooks()
    {
        global $mysqli;
        
        if (!$mysqli) {
            die("Database connection failed");
        }
        
        // Fetch all available books
        $sql = "SELECT 
                    isbn,
                    bookName,
                    authorName,
                    publisherName,
                    totalCopies,
                    available,
                    borrowed,
                    isTrending
                FROM books
                WHERE available > 0
                ORDER BY bookName ASC";
        
        $result = $mysqli->query($sql);
        
        if (!$result) {
            error_log("SQL Error in userBooks: " . $mysqli->error);
            die("Error fetching books: " . $mysqli->error);
        }
        
        $books = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add missing columns with default values for view compatibility
                $row['isSpecial'] = 0;
                $row['specialBadge'] = null;
                $books[] = $row;
            }
            $result->free();
        }
        
        $pageTitle = 'Available Books';
        include APP_ROOT . '/views/user/books.php';
    }

    /**
     * Search books (API endpoint)
     */
    public function searchBooks()
    {
        header('Content-Type: application/json');
        
        global $mysqli;
        
        if (!$mysqli) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed', 'books' => []]);
            exit();
        }
        
        $query = trim($_GET['q'] ?? '');
        
        if (empty($query)) {
            echo json_encode(['success' => false, 'books' => []]);
            exit();
        }
        
        $searchTerm = '%' . $query . '%';
        $stmt = $mysqli->prepare("SELECT 
                isbn, 
                bookName, 
                authorName, 
                publisherName, 
                available,
                isTrending
            FROM books 
            WHERE bookName LIKE ? OR authorName LIKE ? OR isbn LIKE ? OR publisherName LIKE ?
            ORDER BY bookName ASC
            LIMIT 20");
        
        if (!$stmt) {
            error_log("Prepare statement failed in searchBooks: " . $mysqli->error);
            echo json_encode(['success' => false, 'message' => 'Search failed', 'books' => []]);
            exit();
        }
        
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $books = [];
        while ($row = $result->fetch_assoc()) {
            // Add missing columns with default values for view compatibility
            $row['isSpecial'] = 0;
            $row['specialBadge'] = null;
            $books[] = $row;
        }
        
        $stmt->close();
        
        echo json_encode(['success' => true, 'books' => $books]);
        exit();
    }
}