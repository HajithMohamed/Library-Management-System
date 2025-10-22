<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;

class BookService
{
    private $bookModel;
    private $transactionModel;
    private $userModel;

    public function __construct()
    {
        $this->bookModel = new Book();
        $this->transactionModel = new Transaction();
        $this->userModel = new User();
    }

    /**
     * Get all books
     *
     * @return array List of books
     */
    public function getAllBooks()
    {
        global $conn;
        
        try {
            $query = "SELECT * FROM books ORDER BY bookName ASC";
            $result = $conn->query($query);
            
            $books = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $books[] = $row;
                }
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error fetching books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get book by ISBN
     *
     * @param string $isbn Book ISBN
     * @return array|null Book data or null if not found
     */
    public function getBookByISBN($isbn)
    {
        global $conn;
        
        try {
            $query = "SELECT * FROM books WHERE isbn = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Error fetching book by ISBN: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add a new book with validation
     *
     * @param array $bookData Book data
     * @return bool Success status
     */
    public function addBook($bookData)
    {
        global $conn;
        
        try {
            // Validate book data
            $errors = $this->validateBookData($bookData);
            if (!empty($errors)) {
                $_SESSION['validation_errors'] = $errors;
                return false;
            }

            // Check if book already exists
            $existingBook = $this->getBookByISBN($bookData['isbn']);
            if ($existingBook) {
                $_SESSION['error'] = 'A book with this ISBN already exists.';
                return false;
            }
            
            $query = "INSERT INTO books (isbn, bookName, authorName, publisherName, category, description, totalCopies, available, borrowed";
            
            if (isset($bookData['bookImage'])) {
                $query .= ", bookImage";
            }
            
            $query .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0";
            
            if (isset($bookData['bookImage'])) {
                $query .= ", ?";
            }
            
            $query .= ")";
            
            $stmt = $conn->prepare($query);
            
            if (isset($bookData['bookImage'])) {
                $stmt->bind_param(
                    "ssssssiis",
                    $bookData['isbn'],
                    $bookData['bookName'],
                    $bookData['authorName'],
                    $bookData['publisherName'],
                    $bookData['category'],
                    $bookData['description'],
                    $bookData['totalCopies'],
                    $bookData['available'],
                    $bookData['bookImage']
                );
            } else {
                $stmt->bind_param(
                    "ssssssii",
                    $bookData['isbn'],
                    $bookData['bookName'],
                    $bookData['authorName'],
                    $bookData['publisherName'],
                    $bookData['category'],
                    $bookData['description'],
                    $bookData['totalCopies'],
                    $bookData['available']
                );
            }
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error adding book: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update an existing book - UNIFIED METHOD
     *
     * @param string $isbn Book ISBN
     * @param array $bookData Book data to update
     * @return bool Success status
     */
    public function updateBook($isbn, $bookData)
    {
        global $conn;
        
        try {
            // Validate book data (with ISBN for context)
            $errors = $this->validateBookData(array_merge($bookData, ['isbn' => $isbn]));
            if (!empty($errors)) {
                $_SESSION['validation_errors'] = $errors;
                return false;
            }

            // Check if book exists
            $existingBook = $this->getBookByISBN($isbn);
            if (!$existingBook) {
                $_SESSION['error'] = 'Book not found.';
                return false;
            }
            
            // Build the query dynamically based on provided fields
            $query = "UPDATE books SET ";
            $params = [];
            $types = "";
            
            foreach ($bookData as $field => $value) {
                $query .= "$field = ?, ";
                $params[] = $value;
                
                if (is_int($value)) {
                    $types .= "i";
                } else {
                    $types .= "s";
                }
            }
            
            // Remove the trailing comma and space
            $query = rtrim($query, ", ");
            
            // Add the WHERE clause
            $query .= " WHERE isbn = ?";
            $params[] = $isbn;
            $types .= "s";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating book: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a book with checks
     *
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function deleteBook($isbn)
    {
        global $conn;
        
        try {
            // Check if book exists
            $existingBook = $this->getBookByISBN($isbn);
            if (!$existingBook) {
                $_SESSION['error'] = 'Book not found.';
                return false;
            }
            
            // Check if book has active borrowings
            $query = "SELECT COUNT(*) as active FROM transactions WHERE isbn = ? AND returnDate IS NULL";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['active'] > 0) {
                $_SESSION['error'] = 'Cannot delete book with active borrowings.';
                return false;
            }
            
            // Delete the book
            $query = "DELETE FROM books WHERE isbn = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $isbn);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error deleting book: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate book data
     * 
     * @param array $data Book data to validate
     * @return array Validation errors (empty if valid)
     */
    private function validateBookData($data)
    {
        $errors = [];

        if (empty($data['isbn'])) {
            $errors['isbn'] = 'ISBN is required';
        }

        if (empty($data['bookName'])) {
            $errors['bookName'] = 'Book name is required';
        }

        if (empty($data['authorName'])) {
            $errors['authorName'] = 'Author name is required';
        }

        if (empty($data['publisherName'])) {
            $errors['publisherName'] = 'Publisher name is required';
        }

        if (isset($data['totalCopies']) && (!is_numeric($data['totalCopies']) || $data['totalCopies'] < 0)) {
            $errors['totalCopies'] = 'Total copies must be a positive number';
        }

        if (isset($data['available']) && (!is_numeric($data['available']) || $data['available'] < 0)) {
            $errors['available'] = 'Available copies must be a positive number';
        }

        return $errors;
    }
    
    /**
     * Search books with advanced filters
     *
     * @param string $query Search term
     * @param string $category Book category
     * @return array List of matching books
     */
    public function searchBooks($query = '', $category = '')
    {
        global $conn;
        
        try {
            $sqlQuery = "SELECT * FROM books WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($query)) {
                $searchTerm = "%$query%";
                $sqlQuery .= " AND (bookName LIKE ? OR authorName LIKE ? OR publisherName LIKE ? OR isbn LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "ssss";
            }
            
            if (!empty($category)) {
                $sqlQuery .= " AND category = ?";
                $params[] = $category;
                $types .= "s";
            }
            
            $sqlQuery .= " ORDER BY bookName ASC";
            
            $stmt = $conn->prepare($sqlQuery);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $books[] = $row;
                }
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error searching books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Borrow a book
     */
    public function borrowBook($userId, $isbn)
    {
        // Check if user exists and is active
        $user = $this->userModel->getUserById($userId);
        if (!$user || !$user['isVerified']) {
            $_SESSION['error'] = 'User not found or not verified.';
            return false;
        }

        // Check if book is available
        if (!$this->isBookAvailable($isbn)) {
            $_SESSION['error'] = 'Book is not available for borrowing.';
            return false;
        }

        // Check if user has already borrowed this book
        if ($this->transactionModel->hasActiveBorrowing($userId, $isbn)) {
            $_SESSION['error'] = 'You have already borrowed this book.';
            return false;
        }

        // Check borrowing limits based on user type
        $borrowedCount = $this->transactionModel->getActiveBorrowingCount($userId);
        $maxBooks = ($user['userType'] === 'Faculty') ? 5 : 3;
        
        if ($borrowedCount >= $maxBooks) {
            $_SESSION['error'] = "You have reached the maximum borrowing limit ({$maxBooks} books).";
            return false;
        }

        // Create transaction
        $transactionId = $this->generateTransactionId();
        $borrowDate = date('Y-m-d');
        
        if ($this->transactionModel->createTransaction([
            'tid' => $transactionId,
            'userId' => $userId,
            'isbn' => $isbn,
            'borrowDate' => $borrowDate,
            'fine' => 0
        ])) {
            // Update book availability
            $this->decreaseAvailable($isbn);
            return true;
        }

        return false;
    }

    /**
     * Return a book
     */
    public function returnBook($userId, $isbn)
    {
        // Check if user has an active borrowing for this book
        $transaction = $this->transactionModel->getActiveBorrowing($userId, $isbn);
        if (!$transaction) {
            $_SESSION['error'] = 'No active borrowing found for this book.';
            return false;
        }

        $returnDate = date('Y-m-d');
        $fine = $this->calculateFine($transaction['borrowDate'], $returnDate);

        // Update transaction
        if ($this->transactionModel->returnBook($transaction['tid'], $returnDate, $fine)) {
            // Update book availability
            $this->increaseAvailable($isbn);
            return true;
        }

        return false;
    }

    /**
     * Calculate fine for overdue books
     */
    public function calculateFine($borrowDate, $returnDate = null)
    {
        $returnDate = $returnDate ?: date('Y-m-d');
        $borrowTimestamp = strtotime($borrowDate);
        $returnTimestamp = strtotime($returnDate);
        
        $daysDiff = ($returnTimestamp - $borrowTimestamp) / (60 * 60 * 24);
        $maxDays = 14; // 2 weeks borrowing period
        
        if ($daysDiff > $maxDays) {
            $overdueDays = $daysDiff - $maxDays;
            return $overdueDays * 5; // 5 rupees per day fine
        }
        
        return 0;
    }

    /**
     * Update fines for all overdue books
     */
    public function updateAllFines()
    {
        $overdueTransactions = $this->transactionModel->getOverdueTransactions();
        $updatedCount = 0;

        foreach ($overdueTransactions as $transaction) {
            $fine = $this->calculateFine($transaction['borrowDate']);
            if ($fine > $transaction['fine']) {
                $this->transactionModel->updateFine($transaction['tid'], $fine);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Get book borrowing statistics
     */
    public function getBorrowingStats()
    {
        return [
            'total_books' => $this->bookModel->getBookStats(),
            'active_borrowings' => $this->transactionModel->getActiveBorrowingCount(),
            'overdue_books' => count($this->transactionModel->getOverdueTransactions()),
            'popular_books' => $this->bookModel->getPopularBooks(5)
        ];
    }

    /**
     * Upload a book image
     *
     * @param array $file File upload data ($_FILES['bookImage'])
     * @return array Result with success status and path
     */
    public function uploadBookImage($file)
    {
        $result = ['success' => false, 'path' => '', 'error' => ''];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'File upload failed with error code ' . $file['error'];
            return $result;
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $result['error'] = 'Invalid file type. Only JPG, PNG, and WebP are allowed.';
            return $result;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = PUBLIC_ROOT . '/uploads/books/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $result['error'] = 'Failed to create upload directory';
                return $result;
            }
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_') . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $result['success'] = true;
            $result['path'] = 'uploads/books/' . $filename;
            return $result;
        }
        
        $result['error'] = 'Failed to move uploaded file';
        return $result;
    }
    
    /**
     * Check if a book is available for borrowing
     *
     * @param string $isbn Book ISBN
     * @return bool Availability status
     */
    public function isBookAvailable($isbn)
    {
        global $conn;
        
        try {
            $query = "SELECT available FROM books WHERE isbn = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['available'] > 0;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error checking book availability: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update book availability (increase/decrease available copies)
     *
     * @param string $isbn Book ISBN
     * @param int $change Change amount (+1 for return, -1 for borrow)
     * @return bool Success status
     */
    private function updateAvailability($isbn, $change)
    {
        global $conn;
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            $query = "UPDATE books SET available = available + ?, borrowed = borrowed - ? WHERE isbn = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iis", $change, $change, $isbn);
            $result = $stmt->execute();
            
            if ($result) {
                $conn->commit();
                return true;
            }
            
            $conn->rollback();
            return false;
        } catch (\Exception $e) {
            $conn->rollback();
            error_log("Error updating book availability: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrease available count when book is borrowed
     *
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function decreaseAvailable($isbn)
    {
        return $this->updateAvailability($isbn, -1);
    }
    
    /**
     * Increase available count when book is returned
     *
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function increaseAvailable($isbn)
    {
        return $this->updateAvailability($isbn, 1);
    }
    
    /**
     * Get popular books
     *
     * @param int $limit Number of books to return
     * @return array List of popular books
     */
    public function getPopularBooks($limit = 5)
    {
        global $conn;
        
        try {
            $query = "SELECT b.*, COUNT(t.tid) as borrow_count 
                      FROM books b 
                      LEFT JOIN transactions t ON b.isbn = t.isbn 
                      GROUP BY b.isbn 
                      ORDER BY borrow_count DESC 
                      LIMIT ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $books[] = $row;
                }
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error fetching popular books: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId()
    {
        return 'TXN' . date('Ymd') . rand(1000, 9999);
    }
}
?>