<?php
// src/services/BookService.php

namespace App\Services;

use App\Models\Book;

class BookService
{
    private $bookModel;

    public function __construct()
    {
        $this->bookModel = new Book();
    }

    /**
     * Get all books with pagination
     */
    public function getAllBooks($page = 1, $perPage = 12)
    {
        global $conn;

        $offset = ($page - 1) * $perPage;

        try {
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM books";
            $countResult = $conn->query($countQuery);
            $total = $countResult->fetch_assoc()['total'];

            // Get paginated books - use actual column names from books table
            $query = "SELECT isbn, bookName, authorName, publisherName, 
                             totalCopies, available, borrowed,
                             bookImage, description,
                             isTrending, isSpecial, specialBadge,
                             createdAt, updatedAt
                      FROM books 
                      ORDER BY createdAt DESC 
                      LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $perPage, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }

            return [
                'books' => $books,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage)
            ];
        } catch (\Exception $e) {
            error_log("Error getting all books: " . $e->getMessage());
            return [
                'books' => [],
                'total' => 0,
                'page' => 1,
                'perPage' => $perPage,
                'totalPages' => 0
            ];
        }
    }

    /**
     * Get all categories (publishers used as categories if no category column exists)
     */
    public function getCategories()
    {
        global $conn;

        try {
            // Since there's no category column, return unique publishers
            $query = "SELECT DISTINCT publisherName FROM books WHERE publisherName IS NOT NULL AND publisherName != '' ORDER BY publisherName";
            $result = $conn->query($query);

            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row['publisherName'];
            }

            return $categories;
        } catch (\Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get book by ISBN
     */
    public function getBookByIsbn($isbn)
    {
        global $conn;

        try {
            $query = "SELECT * FROM books WHERE isbn = ?";
            $stmt = $conn->prepare($query);
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
     * Search books
     */
    public function searchBooks($query, $category = '', $status = '')
    {
        global $conn;

        try {
            $sql = "SELECT * FROM books WHERE 1=1";
            $params = [];
            $types = "";

            if (!empty($query)) {
                $sql .= " AND (bookName LIKE ? OR authorName LIKE ? OR isbn LIKE ?)";
                $searchTerm = "%{$query}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "sss";
            }

            if (!empty($category)) {
                $sql .= " AND publisherName = ?";
                $params[] = $category;
                $types .= "s";
            }

            if (!empty($status)) {
                if ($status === 'available') {
                    $sql .= " AND available > 0";
                } elseif ($status === 'borrowed') {
                    $sql .= " AND available = 0";
                }
            }

            $sql .= " ORDER BY createdAt DESC";

            $stmt = $conn->prepare($sql);
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
     * Create a new book
     */
    public function createBook($data)
    {
        global $conn;

        try {
            $query = "INSERT INTO books (isbn, bookName, authorName, publisherName, totalCopies, available, borrowed, bookImage, description, isTrending, isSpecial, specialBadge) 
                      VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, 0, 0, '')";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssiiiss", 
                $data['isbn'],
                $data['bookName'],
                $data['authorName'],
                $data['publisherName'],
                $data['totalCopies'],
                $data['available'],
                $data['bookImage'],
                $data['description']
            );

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error creating book: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update book information - SINGLE DECLARATION
     */
    public function updateBook($isbn, $data)
    {
        global $conn;

        try {
            $query = "UPDATE books SET 
                      bookName = ?, 
                      authorName = ?, 
                      publisherName = ?, 
                      totalCopies = ?, 
                      available = ?,
                      bookImage = ?,
                      description = ?
                      WHERE isbn = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssiisss", 
                $data['bookName'],
                $data['authorName'],
                $data['publisherName'],
                $data['totalCopies'],
                $data['available'],
                $data['bookImage'],
                $data['description'],
                $isbn
            );

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating book: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a book
     */
    public function deleteBook($isbn)
    {
        global $conn;

        try {
            // Check if book has active transactions
            $checkQuery = "SELECT COUNT(*) as count FROM transactions WHERE isbn = ? AND returnDate IS NULL";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $isbn);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                return false; // Cannot delete book with active transactions
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
     * Get popular books
     */
    public function getPopularBooks($limit = 10)
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
     * Get low stock books
     */
    public function getLowStockBooks($threshold = 5)
    {
        global $conn;

        try {
            $query = "SELECT * FROM books WHERE available <= ? ORDER BY available ASC";
            $stmt = $conn->prepare($query);
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

    /**
     * Update book availability
     */
    public function updateAvailability($isbn, $changeAmount)
    {
        global $conn;

        try {
            $query = "UPDATE books SET 
                      available = available + ?, 
                      borrowed = borrowed - ? 
                      WHERE isbn = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iis", $changeAmount, $changeAmount, $isbn);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating book availability: " . $e->getMessage());
            return false;
        }
    }
}
?>