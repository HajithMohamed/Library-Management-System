<?php

namespace App\Models;

class Book
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Get all books with optional search
     */
    public function getAllBooks($search = '')
    {
        $sql = "SELECT * FROM books";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE bookName LIKE ? OR authorName LIKE ? OR publisherName LIKE ?";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql .= " ORDER BY bookName ASC";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Search books by name, author, or publisher
     */
    public function searchBooks($search = '')
    {
        return $this->getAllBooks($search);
    }

    /**
     * Get a book by ISBN
     */
    public function getBookByISBN($isbn)
    {
        $sql = "SELECT * FROM books WHERE isbn = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Create a new book
     */
    public function createBook($data)
    {
        $sql = "INSERT INTO books (isbn, bookName, authorName, publisherName, available, borrowed) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssssii', 
            $data['isbn'], 
            $data['bookName'], 
            $data['authorName'], 
            $data['publisherName'], 
            $data['available'], 
            $data['borrowed']
        );
        
        return $stmt->execute();
    }

    /**
     * Update a book
     */
    public function updateBook($isbn, $data)
    {
        $sql = "UPDATE books SET bookName = ?, authorName = ?, publisherName = ?, available = ? WHERE isbn = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssis', 
            $data['bookName'], 
            $data['authorName'], 
            $data['publisherName'], 
            $data['available'], 
            $isbn
        );
        
        return $stmt->execute();
    }

    /**
     * Delete a book
     */
    public function deleteBook($isbn)
    {
        // Check if book has active transactions
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE isbn = ? AND returnDate IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return false; // Cannot delete book with active transactions
        }
        
        $sql = "DELETE FROM books WHERE isbn = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $isbn);
        
        return $stmt->execute();
    }

    /**
     * Get books borrowed by a user
     */
    public function getBorrowedBooks($userId)
    {
        $sql = "SELECT b.*, t.borrowDate, t.fine 
                FROM books b 
                INNER JOIN transactions t ON b.isbn = t.isbn 
                WHERE t.userId = ? AND t.returnDate IS NULL 
                ORDER BY t.borrowDate DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if a book is available for borrowing
     */
    public function isBookAvailable($isbn)
    {
        $book = $this->getBookByISBN($isbn);
        return $book && $book['available'] > 0;
    }

    /**
     * Decrease available count when book is borrowed
     */
    public function decreaseAvailable($isbn)
    {
        $sql = "UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ? AND available > 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $isbn);
        
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    /**
     * Increase available count when book is returned
     */
    public function increaseAvailable($isbn)
    {
        $sql = "UPDATE books SET available = available + 1, borrowed = borrowed - 1 WHERE isbn = ? AND borrowed > 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $isbn);
        
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    /**
     * Get book statistics
     */
    public function getBookStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_books,
                    SUM(available) as total_available,
                    SUM(borrowed) as total_borrowed
                FROM books";
        
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Get popular books (most borrowed)
     */
    public function getPopularBooks($limit = 10)
    {
        $sql = "SELECT b.*, COUNT(t.tid) as borrow_count
                FROM books b
                LEFT JOIN transactions t ON b.isbn = t.isbn
                GROUP BY b.isbn
                ORDER BY borrow_count DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Validate book data
     */
    public function validateBookData($data)
    {
        $errors = [];
        
        if (empty($data['isbn'])) {
            $errors[] = 'ISBN is required';
        } elseif (!preg_match('/^\d{13}$/', $data['isbn'])) {
            $errors[] = 'ISBN must be 13 digits';
        }
        
        if (empty($data['bookName'])) {
            $errors[] = 'Book name is required';
        }
        
        if (empty($data['authorName'])) {
            $errors[] = 'Author name is required';
        }
        
        if (empty($data['publisherName'])) {
            $errors[] = 'Publisher name is required';
        }
        
        if (!isset($data['available']) || $data['available'] < 0) {
            $errors[] = 'Available quantity must be 0 or greater';
        }
        
        return $errors;
    }
}
?>
