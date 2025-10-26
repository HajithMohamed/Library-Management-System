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
     * Get book statistics - FIXED VERSION
     * Uses $conn consistently and matches database column names
     */
    public function getBookStats()
    {
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
            
            $result = $this->conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stats['total_books'] = (int)($row['total_books'] ?? 0);
                $stats['available_books'] = (int)($row['available_books'] ?? 0);
                $stats['borrowed_books'] = (int)($row['borrowed_books'] ?? 0);
            }
            
            // Get book count by category
            $query = "SELECT category, COUNT(*) as count FROM books WHERE category IS NOT NULL GROUP BY category ORDER BY count DESC";
            $result = $this->conn->query($query);
            
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
     * Get popular books - FIXED VERSION
     * Includes all columns from books table in GROUP BY
     */
    public function getPopularBooks($limit = 5)
    {
        try {
            $query = "SELECT b.*, COUNT(t.tid) as borrow_count 
                     FROM books b 
                     LEFT JOIN transactions t ON b.isbn = t.isbn 
                     GROUP BY b.isbn, b.barcode, b.bookName, b.authorName, b.publisherName, 
                              b.description, b.category, b.publicationYear, b.totalCopies, 
                              b.available, b.borrowed, b.bookImage, b.isTrending, b.isSpecial, 
                              b.specialBadge, b.createdAt, b.updatedAt
                     ORDER BY borrow_count DESC 
                     LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                // Add 'image' key for backward compatibility if code uses it
                $row['image'] = $row['bookImage'];
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
     */
    public function isBookAvailable($isbn)
    {
        try {
            $stmt = $this->conn->prepare("SELECT available FROM books WHERE isbn = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return (int)($row['available'] ?? 0) > 0;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error checking book availability: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all books - FIXED with image alias
     */
    public function getAllBooks()
    {
        try {
            $result = $this->conn->query("SELECT *, bookImage as image FROM books ORDER BY bookName");
            
            if (!$result) {
                throw new \Exception("Query failed: " . $this->conn->error);
            }
            
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
        try {
            $stmt = $this->conn->prepare("SELECT *, bookImage as image FROM books WHERE isbn = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error getting book by ISBN: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new book
     */
    public function createBook($data)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO books (isbn, barcode, bookName, authorName, publisherName, description, category, publicationYear, totalCopies, available, borrowed, bookImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssssssiiiis",
                $data['isbn'] ?? null,
                $data['barcode'] ?? null,
                $data['bookName'] ?? null,
                $data['authorName'] ?? null,
                $data['publisherName'] ?? null,
                $data['description'] ?? null,
                $data['category'] ?? null,
                $data['publicationYear'] ?? null,
                $data['totalCopies'] ?? 0,
                $data['available'] ?? 0,
                $data['borrowed'] ?? 0,
                $data['bookImage'] ?? null
            );
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error creating book: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update book
     */
    public function updateBook($isbn, $data)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE books SET bookName = ?, authorName = ?, publisherName = ?, description = ?, category = ?, publicationYear = ?, totalCopies = ?, available = ?, borrowed = ?, bookImage = ? WHERE isbn = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssssiiiiis",
                $data['bookName'] ?? null,
                $data['authorName'] ?? null,
                $data['publisherName'] ?? null,
                $data['description'] ?? null,
                $data['category'] ?? null,
                $data['publicationYear'] ?? null,
                $data['totalCopies'] ?? 0,
                $data['available'] ?? 0,
                $data['borrowed'] ?? 0,
                $data['bookImage'] ?? null,
                $isbn
            );
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating book: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete book
     */
    public function deleteBook($isbn)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM books WHERE isbn = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $isbn);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error deleting book: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrease available book count when borrowed
     */
    public function decreaseAvailable($isbn)
    {
        try {
            // Update available and borrowed counts
            $stmt = $this->conn->prepare("UPDATE books SET available = available - 1, borrowed = borrowed + 1 WHERE isbn = ? AND available > 0");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
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
     */
    public function increaseAvailable($isbn)
    {
        try {
            // Update available and borrowed counts
            $stmt = $this->conn->prepare("UPDATE books SET available = available + 1, borrowed = borrowed - 1 WHERE isbn = ? AND borrowed > 0");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
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
     * Search books by title, author, ISBN, or publisher - FIXED VERSION
     */
    public function searchBooks($searchTerm = '', $category = '', $limit = 100)
    {
        try {
            $query = "SELECT *, bookImage as image FROM books WHERE 1=1";
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
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
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
     * Get books by author names - FIXED VERSION
     */
    public function getBooksByAuthors($authors, $limit = 50)
    {
        try {
            if (empty($authors)) {
                return [];
            }
            
            // Create placeholders for IN clause
            $placeholders = str_repeat('?,', count($authors) - 1) . '?';
            $query = "SELECT *, bookImage as image FROM books WHERE authorName IN ({$placeholders}) ORDER BY authorName, bookName LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
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
     */
    public function getBooksByCategory($category, $limit = 50)
    {
        try {
            $stmt = $this->conn->prepare("SELECT *, bookImage as image FROM books WHERE category = ? ORDER BY bookName LIMIT ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
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
     */
    public function getAllCategories()
    {
        try {
            $result = $this->conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category");
            
            if (!$result) {
                throw new \Exception("Query failed: " . $this->conn->error);
            }
            
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

    /**
     * Get book by ID (new method for general retrieval)
     */
    public function getBookById($bookId)
    {
        return $this->getBookByIsbn($bookId);
    }

    /**
     * Get books count
     */
    public function getBooksCount()
    {
        try {
            $result = $this->conn->query("SELECT COUNT(*) as count FROM books");
            
            if (!$result) {
                throw new \Exception("Query failed: " . $this->conn->error);
            }
            
            $row = $result->fetch_assoc();
            return (int)($row['count'] ?? 0);
        } catch (\Exception $e) {
            error_log("Error getting books count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get low stock books (available <= threshold)
     */
    public function getLowStockBooks($threshold = 2)
    {
        try {
            $stmt = $this->conn->prepare("SELECT *, bookImage as image FROM books WHERE available <= ? ORDER BY available ASC");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $threshold);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            
            return $books;
        } catch (\Exception $e) {
            error_log("Error getting low stock books: " . $e->getMessage());
            return [];
        }
    }
}
?>
