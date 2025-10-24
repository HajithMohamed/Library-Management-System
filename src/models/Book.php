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
        global $conn;
        
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
            
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stats['total_books'] = (int)$row['total_books'];
                $stats['available_books'] = (int)$row['available_books'];
                $stats['borrowed_books'] = (int)$row['borrowed_books'];
            }
            
            // Get book count by category
            $query = "SELECT category, COUNT(*) as count FROM books GROUP BY category ORDER BY count DESC";
            $result = $conn->query($query);
            
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
        global $conn;
        
        try {
            $stmt = $conn->prepare("SELECT available FROM books WHERE isbn = ?");
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
        global $conn;
        
        try {
            $result = $conn->query("SELECT * FROM books ORDER BY bookName");
            
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
}
?>
