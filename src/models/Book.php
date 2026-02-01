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
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

        return $stmt->fetchAll();
    }

    /**
     * Advanced search with category filter
     */
    public function advancedSearch($searchTerm = '', $category = '')
    {
        $sql = "SELECT * FROM books WHERE 1";
        $params = [];

        if ($searchTerm) {
            $sql .= " AND (bookName LIKE ? OR authorName LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
        }
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find book by ISBN
     */
    public function findByISBN($isbn)
    {
        $sql = "SELECT * FROM {$this->table} WHERE isbn = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$isbn]);

        return $stmt->fetch();
    }

    /**
     * Get all available books
     */
    public function getAvailableBooks()
    {
        $sql = "SELECT * FROM {$this->table} WHERE available > 0 ORDER BY bookName ASC";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Get all books
     */
    public function getAllBooks($limit = null, $offset = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY bookName ASC";
            $params = [];

            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = (int) $limit;
                if ($offset) {
                    $sql .= " OFFSET ?";
                    $params[] = (int) $offset;
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
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
                $stmt->execute([$startDate, $endDate, (int) $limit]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT b.*, COUNT(t.tid) as borrow_count
                    FROM books b
                    LEFT JOIN transactions t ON b.isbn = t.isbn
                    GROUP BY b.isbn
                    ORDER BY borrow_count DESC
                    LIMIT ?
                ");
                $stmt->execute([(int) $limit]);
            }

            return $stmt->fetchAll();
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
        $stmt->execute([(int) $limit]);

        return $stmt->fetchAll();
    }

    /**
     * Get books by category
     */
    public function getByCategory($category)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY bookName ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category]);

        return $stmt->fetchAll();
    }

    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        $sql = "SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL ORDER BY category ASC";
        $stmt = $this->db->query($sql);

        $categories = [];
        while ($row = $stmt->fetch()) {
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
        return $stmt->execute([
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
        ]);
    }

    /**
     * Update book information
     */
    public function updateBook($isbn, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if ($key !== 'isbn') {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $isbn;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE isbn = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Delete a book
     */
    public function deleteBook($isbn)
    {
        $sql = "DELETE FROM {$this->table} WHERE isbn = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$isbn]);
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

        return $stmt->execute([$isbn]);
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

        return $stmt->execute([$isbn]);
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

        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    /**
     * Get total books count
     */
    public function getTotalBooksCount()
    {
        try {
            // Count distinct books (not total copies)
            $sql = "SELECT COUNT(*) as count FROM books";
            $stmt = $this->db->query($sql);

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
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
            // Sum available copies from all books
            $sql = "SELECT COALESCE(SUM(available), 0) as count FROM books";
            $stmt = $this->db->query($sql);

            if ($row = $stmt->fetch()) {
                return (int) $row['count'];
            }

            return 0;
        } catch (\Exception $e) {
            error_log("Error getting available books count: " . $e->getMessage());
            return 0;
        }
    }
}
