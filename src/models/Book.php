<?php

namespace App\Models;

class Book
{
    /**
     * Get book statistics
     *
     * @return array Book statistics
     */
    public function getBookStats()
    {
        global $mysqli;  // Changed from $conn to $mysqli
        
        $stats = [
            'total_books' => 0,
            'available_books' => 0,
            'borrowed_books' => 0,
            'categories' => []
        ];
        
        try {
            // Get total, available, and borrowed books
            $query = "SELECT 
                        COUNT(*) as total_books, 
                        SUM(available) as available_books,
                        SUM(borrowed) as borrowed_books
                     FROM books";
            
            $result = $mysqli->query($query);  // Changed to use $mysqli
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stats['total_books'] = (int)$row['total_books'];
                $stats['available_books'] = (int)$row['available_books'];
                $stats['borrowed_books'] = (int)$row['borrowed_books'];
            }
            
            // Get book count by category
            $query = "SELECT category, COUNT(*) as count FROM books GROUP BY category ORDER BY count DESC";
            $result = $mysqli->query($query);  // Changed to use $mysqli
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $stats['categories'][$row['category']] = (int)$row['count'];
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            error_log("Error getting book stats: " . $e->getMessage());
            return $stats;
        }
    }
    
    /**
     * Get popular books
     *
     * @param int $limit Number of books to return
     * @return array Popular books
     */
    public function getPopularBooks($limit = 5)
    {
        global $mysqli;  // Changed from $conn to $mysqli
        
        try {
            $query = "SELECT b.*, COUNT(t.tid) as borrow_count 
                     FROM books b 
                     LEFT JOIN transactions t ON b.isbn = t.isbn 
                     GROUP BY b.isbn 
                     ORDER BY borrow_count DESC 
                     LIMIT ?";
            
            $stmt = $mysqli->prepare($query);  // Changed to use $mysqli
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error getting popular books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a book is available for borrowing
     * 
     * @param string $isbn Book ISBN
     * @return bool Whether the book is available
     */
    public function isBookAvailable($isbn)
    {
        global $mysqli;  // Changed from $conn to $mysqli
        
        try {
            $stmt = $mysqli->prepare("SELECT available FROM books WHERE isbn = ?");  // Changed to use $mysqli
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['available'] > 0;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error checking book availability: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all books
     * 
     * @return array All books in the database
     */
    public function getAllBooks()
    {
        global $mysqli;  // Changed from $conn to $mysqli
        
        try {
            $result = $mysqli->query("SELECT * FROM books ORDER BY bookName");  // Changed to use $mysqli
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error getting all books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get book by ISBN
     */
    public function getBookByIsbn($isbn)
    {
        global $mysqli;
        
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Create a new book
     */
    public function createBook($data)
    {
        global $mysqli;
        
        $stmt = $mysqli->prepare("INSERT INTO books (isbn, barcode, bookName, authorName, publisherName, description, totalCopies, available, borrowed, bookImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssiiis",
            $data['isbn'],
            $data['barcode'],
            $data['bookName'],
            $data['authorName'],
            $data['publisherName'],
            $data['description'],
            $data['totalCopies'],
            $data['available'],
            $data['borrowed'],
            $data['bookImage']
        );
        
        return $stmt->execute();
    }
    
    /**
     * Update book
     */
    public function updateBook($isbn, $data)
    {
        global $mysqli;
        
        $stmt = $mysqli->prepare("UPDATE books SET bookName = ?, authorName = ?, publisherName = ?, description = ?, totalCopies = ?, available = ?, borrowed = ?, bookImage = ? WHERE isbn = ?");
        
        $stmt->bind_param("ssssiiiis",
            $data['bookName'],
            $data['authorName'],
            $data['publisherName'],
            $data['description'],
            $data['totalCopies'],
            $data['available'],
            $data['borrowed'],
            $data['bookImage'],
            $isbn
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete book
     */
    public function deleteBook($isbn)
    {
        global $mysqli;
        
        $stmt = $mysqli->prepare("DELETE FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        
        return $stmt->execute();
    }
    
    /**
     * Decrease available book count when borrowed
     * 
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function decreaseAvailable($isbn)
    {
        global $mysqli;
        
        try {
            // Update available and borrowed counts
            $stmt = $mysqli->prepare("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ? AND available > 0");
            $stmt->bind_param("s", $isbn);
            $result = $stmt->execute();
            
            // Check if update was successful and affected a row
            if ($result && $stmt->affected_rows > 0) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error decreasing book availability: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Increase available book count when returned
     * 
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function increaseAvailable($isbn)
    {
        global $mysqli;
        
        try {
            // Update available and borrowed counts
            $stmt = $mysqli->prepare("UPDATE books SET available = available + 1, borrowed = borrowed - 1 WHERE isbn = ? AND borrowed > 0");
            $stmt->bind_param("s", $isbn);
            $result = $stmt->execute();
            
            // Check if update was successful and affected a row
            if ($result && $stmt->affected_rows > 0) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error increasing book availability: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search books by title, author, ISBN, or publisher
     * 
     * @param string $searchTerm Search term
     * @param string $category Optional category filter
     * @param int $limit Maximum number of results
     * @return array Matching books
     */
    public function searchBooks($searchTerm = '', $category = '', $limit = 100)
    {
        global $mysqli;
        
        try {
            $query = "SELECT * FROM books WHERE 1=1";
            $params = [];
            $types = "";
            
            // Add search term filter
            if (!empty($searchTerm)) {
                $query .= " AND (bookName LIKE ? OR authorName LIKE ? OR isbn LIKE ? OR publisherName LIKE ?)";
                $searchParam = "%{$searchTerm}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $types .= "ssss";
            }
            
            // Add category filter
            if (!empty($category)) {
                $query .= " AND category = ?";
                $params[] = $category;
                $types .= "s";
            }
            
            $query .= " ORDER BY bookName LIMIT ?";
            $params[] = $limit;
            $types .= "i";
            
            $stmt = $mysqli->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error searching books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get books by author names
     * 
     * @param array $authors Array of author names
     * @param int $limit Maximum number of results per author
     * @return array Books by specified authors
     */
    public function getBooksByAuthors($authors, $limit = 50)
    {
        global $mysqli;
        
        if (empty($authors)) {
            return [];
        }
        
        try {
            // Create placeholders for IN clause
            $placeholders = str_repeat('?,', count($authors) - 1) . '?';
            $query = "SELECT * FROM books WHERE authorName IN ({$placeholders}) ORDER BY authorName, bookName LIMIT ?";
            
            $stmt = $mysqli->prepare($query);
            
            // Build parameter types string
            $types = str_repeat('s', count($authors)) . 'i';
            
            // Merge authors array with limit
            $params = array_merge($authors, [$limit]);
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error getting books by authors: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get books by category
     * 
     * @param string $category Category name
     * @param int $limit Maximum number of results
     * @return array Books in specified category
     */
    public function getBooksByCategory($category, $limit = 50)
    {
        global $mysqli;
        
        try {
            $stmt = $mysqli->prepare("SELECT * FROM books WHERE category = ? ORDER BY bookName LIMIT ?");
            $stmt->bind_param("si", $category, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error getting books by category: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all unique categories
     * 
     * @return array List of categories
     */
    public function getAllCategories()
    {
        global $mysqli;
        
        try {
            $result = $mysqli->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category");
            
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row['category'];
            }
            
            return $categories;
        } catch (\Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }
}