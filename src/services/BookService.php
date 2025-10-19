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
     * Create a new book with validation
     */
    public function createBook($data)
    {
        // Validate book data
        $errors = $this->bookModel->validateBookData($data);
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return false;
        }

        // Check if ISBN already exists
        if ($this->bookModel->getBookByISBN($data['isbn'])) {
            $_SESSION['error'] = 'A book with this ISBN already exists.';
            return false;
        }

        return $this->bookModel->createBook($data);
    }

    /**
     * Update a book with validation
     */
    public function updateBook($isbn, $data)
    {
        // Validate book data
        $errors = $this->bookModel->validateBookData(array_merge($data, ['isbn' => $isbn]));
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            return false;
        }

        return $this->bookModel->updateBook($isbn, $data);
    }

    /**
     * Delete a book with checks
     */
    public function deleteBook($isbn)
    {
        // Check if book exists
        if (!$this->bookModel->getBookByISBN($isbn)) {
            $_SESSION['error'] = 'Book not found.';
            return false;
        }

        return $this->bookModel->deleteBook($isbn);
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
        if (!$this->bookModel->isBookAvailable($isbn)) {
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
            $this->bookModel->decreaseAvailable($isbn);
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
            $this->bookModel->increaseAvailable($isbn);
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
     * Generate unique transaction ID
     */
    private function generateTransactionId()
    {
        return 'TXN' . date('Ymd') . rand(1000, 9999);
    }

    /**
     * Search books with advanced filters
     */
    public function searchBooks($search, $filters = [])
    {
        $books = $this->bookModel->searchBooks($search);
        
        // Apply additional filters
        if (!empty($filters['available_only']) && $filters['available_only']) {
            $books = array_filter($books, function($book) {
                return $book['available'] > 0;
            });
        }
        
        if (!empty($filters['author'])) {
            $books = array_filter($books, function($book) use ($filters) {
                return stripos($book['authorName'], $filters['author']) !== false;
            });
        }
        
        return array_values($books);
    }
}
?>
