<?php

namespace App\Models;

class Book extends BaseModel
{
    protected $table = 'books';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Search books by title, author, or ISBN
     */
    public function search($searchTerm = '')
    {
        if (empty($searchTerm)) {
            return $this->all('bookName', 'ASC');
        }

        $searchTerm = '%' . $searchTerm . '%';
        $sql = "SELECT * FROM {$this->table} 
                WHERE bookName LIKE ? 
                OR authorName LIKE ? 
                OR isbn LIKE ? 
                OR publisherName LIKE ?
                ORDER BY bookName ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Advanced search with category filter
     */
    public function advancedSearch($searchTerm = '', $category = '')
    {
        $sql = "SELECT * FROM books WHERE 1";
        $params = [];
        $types = '';

        if ($searchTerm) {
            $sql .= " AND (bookName LIKE ? OR authorName LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
            $types .= 'ss';
        }
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
            $types .= 's';
        }

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Find book by ISBN
     */
    public function findByISBN($isbn)
    {
        $sql = "SELECT * FROM {$this->table} WHERE isbn = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $isbn);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all available books
     */
    public function getAvailableBooks()
    {
        $sql = "SELECT * FROM {$this->table} WHERE available > 0 ORDER BY bookName ASC";
        $result = $this->db->query($sql);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all books
     */
    public function getAllBooks($limit = null, $offset = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY bookName ASC";
            
            if ($limit) {
                $sql .= " LIMIT ?";
                if ($offset) {
                    $sql .= " OFFSET ?";
                }
            }
            
            $stmt = $this->db->prepare($sql);
            
            if ($limit && $offset) {
                $stmt->bind_param('ii', $limit, $offset);
            } elseif ($limit) {
                $stmt->bind_param('i', $limit);
            } else {
                // No parameters, execute directly
                $result = $this->db->query("SELECT * FROM {$this->table} ORDER BY bookName ASC");
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting all books: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get popular books based on borrow count
     */
    public function getPopularBooks($limit = 10, $startDate = null, $endDate = null)
    {
        try {
            if ($startDate && $endDate) {
                $stmt = $this->db->prepare("
                    SELECT b.*, COUNT(t.tid) as borrow_count
                    FROM books b
                    LEFT JOIN transactions t ON b.isbn = t.isbn
                    WHERE t.borrowDate BETWEEN ? AND ?
                    GROUP BY b.isbn
                    ORDER BY borrow_count DESC
                    LIMIT ?
                ");
                $stmt->bind_param("ssi", $startDate, $endDate, $limit);
            } else {
                $stmt = $this->db->prepare("
                    SELECT b.*, COUNT(t.tid) as borrow_count
                    FROM books b
                    LEFT JOIN transactions t ON b.isbn = t.isbn
                    GROUP BY b.isbn
                    ORDER BY borrow_count DESC
                    LIMIT ?
                ");
                $stmt->bind_param("i", $limit);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting popular books: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trending books
     */
    public function getTrendingBooks($limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE isTrending = 1 
                ORDER BY createdAt DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get books by category
     */
    public function getByCategory($category)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY bookName ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $category);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        $sql = "SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL ORDER BY category ASC";
        $result = $this->db->query($sql);
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }

    /**
     * Add a new book
     */
    public function addBook($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (isbn, barcode, bookName, authorName, publisherName, description, 
                category, publicationYear, totalCopies, available, bookImage)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssssssiiis',
            $data['isbn'],
            $data['barcode'],
            $data['bookName'],
            $data['authorName'],
            $data['publisherName'],
            $data['description'],
            $data['category'],
            $data['publicationYear'],
            $data['totalCopies'],
            $data['available'],
            $data['bookImage']
        );
        
        return $stmt->execute();
    }

    /**
     * Update book information
     */
    public function updateBook($isbn, $data)
    {
        $fields = [];
        $values = [];
        $types = '';

        foreach ($data as $key => $value) {
            if ($key !== 'isbn') {
                $fields[] = "{$key} = ?";
                $values[] = $value;
                $types .= is_int($value) ? 'i' : 's';
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $isbn;
        $types .= 's';

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE isbn = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }

    /**
     * Delete a book
     */
    public function deleteBook($isbn)
    {
        $sql = "DELETE FROM {$this->table} WHERE isbn = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $isbn);
        
        return $stmt->execute();
    }

    /**
     * Update book availability when borrowed
     */
    public function decreaseAvailability($isbn)
    {
        $sql = "UPDATE {$this->table} 
                SET available = available - 1, borrowed = borrowed + 1 
                WHERE isbn = ? AND available > 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $isbn);
        
        return $stmt->execute();
    }

    /**
     * Update book availability when returned
     */
    public function increaseAvailability($isbn)
    {
        $sql = "UPDATE {$this->table} 
                SET available = available + 1, borrowed = borrowed - 1 
                WHERE isbn = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $isbn);
        
        return $stmt->execute();
    }

    /**
     * Get book statistics
     */
    public function getBookStats()
    {
        $sql = "SELECT 
                COUNT(*) as total_books,
                SUM(totalCopies) as total_copies,
                SUM(available) as available_copies,
                SUM(borrowed) as borrowed_copies
                FROM {$this->table}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Get total books count
     */
    public function getTotalBooksCount()
    {
        try {
            global $mysqli;
            // Count distinct books (not total copies)
            $sql = "SELECT COUNT(*) as count FROM books";
            $result = $mysqli->query($sql);
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['count'];
            }
            
            return 0;
        } catch (\Exception $e) {
            error_log("Error getting total books count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get available books count
     */
    public function getAvailableBooksCount()
    {
        try {
            global $mysqli;
            // Sum available copies from all books
            $sql = "SELECT COALESCE(SUM(available), 0) as count FROM books";
            $result = $mysqli->query($sql);
            
            if ($row = $result->fetch_assoc()) {
                return (int)$row['count'];
            }
            
            return 0;
        } catch (\Exception $e) {
            error_log("Error getting available books count: " . $e->getMessage());
            return 0;
        }
    }
}
