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
     * Get book by ID
     */
    public function getBookById($bookId)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM books WHERE bookId = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $bookId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
        } catch (\Exception $e) {
            error_log("Error getting book by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get book by ISBN
     */
    public function getBookByIsbn($isbn)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM books WHERE isbn = ?");
            
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
            $stmt = $this->conn->prepare("INSERT INTO books (title, author, isbn, category, available, borrowed) VALUES (?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ssssis",
                $data['title'],
                $data['author'],
                $data['isbn'],
                $data['category'] ?? '',
                $data['available'] ?? 0,
                $data['borrowed'] ?? 0
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
    public function updateBook($bookId, $data)
    {
        try {
            $fields = [];
            $values = [];
            $types = "";
            
            // Build dynamic UPDATE query based on provided data
            if (isset($data['title'])) {
                $fields[] = "title = ?";
                $values[] = $data['title'];
                $types .= "s";
            }
            if (isset($data['author'])) {
                $fields[] = "author = ?";
                $values[] = $data['author'];
                $types .= "s";
            }
            if (isset($data['isbn'])) {
                $fields[] = "isbn = ?";
                $values[] = $data['isbn'];
                $types .= "s";
            }
            if (isset($data['category'])) {
                $fields[] = "category = ?";
                $values[] = $data['category'];
                $types .= "s";
            }
            if (isset($data['available'])) {
                $fields[] = "available = ?";
                $values[] = $data['available'];
                $types .= "i";
            }
            if (isset($data['borrowed'])) {
                $fields[] = "borrowed = ?";
                $values[] = $data['borrowed'];
                $types .= "i";
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $query = "UPDATE books SET " . implode(", ", $fields) . " WHERE bookId = ?";
            $values[] = $bookId;
            $types .= "i";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param($types, ...$values);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating book: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete book
     */
    public function deleteBook($bookId)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM books WHERE bookId = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $bookId);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error deleting book: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all books
     */
    public function getAllBooks()
    {
        try {
            $result = $this->conn->query("SELECT * FROM books ORDER BY title");
            
            if (!$result) {
                throw new \Exception("Query failed: " . $this->conn->error);
            }
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting all books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get books by category
     */
    public function getBooksByCategory($category)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM books WHERE category = ? ORDER BY title");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting books by category: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search books
     */
    public function searchBooks($keyword)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? ORDER BY title");
            
            if (!$stmt) {
                throw new \Exception("Prepare failed: " . $this->conn->error);
            }
            
            $likeKeyword = "%" . $keyword . "%";
            $stmt->bind_param("ss", $likeKeyword, $likeKeyword);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error searching books: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get book count
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
}
