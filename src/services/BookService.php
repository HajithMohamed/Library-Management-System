<?php

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
     *
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @return array Books and pagination info
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

            // Get paginated books
            $query = "SELECT * FROM books ORDER BY createdAt DESC LIMIT ? OFFSET ?";
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
     * Get a single book by ISBN
     *
     * @param string $isbn Book ISBN
     * @return array|null Book data or null
     */
    public function getBookByISBN($isbn)
    {
        global $conn;

        try {
            $stmt = $conn->prepare("SELECT * FROM books WHERE isbn = ?");
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
     * Add a new book
     *
     * @param array $bookData Book information
     * @return bool Success status
     */
    public function addBook($bookData)
    {
        global $conn;

        try {
            $query = "INSERT INTO books (
                isbn, bookName, authorName, publisherName, 
                category, description, totalCopies, available, 
                bookImage, isTrending, isSpecial, specialBadge, createdAt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($query);

            $isTrending = isset($bookData['isTrending']) ? (int)$bookData['isTrending'] : 0;
            $isSpecial = isset($bookData['isSpecial']) ? (int)$bookData['isSpecial'] : 0;
            $specialBadge = $bookData['specialBadge'] ?? null;
            $bookImage = $bookData['bookImage'] ?? 'default-book.jpg';

            $stmt->bind_param(
                "ssssssiisiis",
                $bookData['isbn'],
                $bookData['bookName'],
                $bookData['authorName'],
                $bookData['publisherName'],
                $bookData['category'],
                $bookData['description'],
                $bookData['totalCopies'],
                $bookData['available'],
                $bookImage,
                $isTrending,
                $isSpecial,
                $specialBadge
            );

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error adding book: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing book
     *
     * @param string $isbn Book ISBN
     * @param array $bookData Updated book information
     * @return bool Success status
     */
    public function updateBook($isbn, $bookData)
    {
        global $conn;

        try {
            // Get current book to check if we need to update image
            $currentBook = $this->getBookByISBN($isbn);
            if (!$currentBook) {
                return false;
            }

            // Build dynamic query based on provided fields
            $fields = [];
            $types = "";
            $values = [];

            if (isset($bookData['bookName'])) {
                $fields[] = "bookName = ?";
                $types .= "s";
                $values[] = $bookData['bookName'];
            }

            if (isset($bookData['authorName'])) {
                $fields[] = "authorName = ?";
                $types .= "s";
                $values[] = $bookData['authorName'];
            }

            if (isset($bookData['publisherName'])) {
                $fields[] = "publisherName = ?";
                $types .= "s";
                $values[] = $bookData['publisherName'];
            }

            if (isset($bookData['category'])) {
                $fields[] = "category = ?";
                $types .= "s";
                $values[] = $bookData['category'];
            }

            if (isset($bookData['description'])) {
                $fields[] = "description = ?";
                $types .= "s";
                $values[] = $bookData['description'];
            }

            if (isset($bookData['totalCopies'])) {
                $fields[] = "totalCopies = ?";
                $types .= "i";
                $values[] = $bookData['totalCopies'];

                // Update available count proportionally
                $borrowed = $currentBook['borrowed'] ?? 0;
                $newAvailable = $bookData['totalCopies'] - $borrowed;
                $fields[] = "available = ?";
                $types .= "i";
                $values[] = max(0, $newAvailable);
            }

            if (isset($bookData['bookImage'])) {
                $fields[] = "bookImage = ?";
                $types .= "s";
                $values[] = $bookData['bookImage'];
            }

            if (isset($bookData['isTrending'])) {
                $fields[] = "isTrending = ?";
                $types .= "i";
                $values[] = (int)$bookData['isTrending'];
            }

            if (isset($bookData['isSpecial'])) {
                $fields[] = "isSpecial = ?";
                $types .= "i";
                $values[] = (int)$bookData['isSpecial'];
            }

            if (isset($bookData['specialBadge'])) {
                $fields[] = "specialBadge = ?";
                $types .= "s";
                $values[] = $bookData['specialBadge'];
            }

            if (empty($fields)) {
                return true; // Nothing to update
            }

            $fields[] = "updatedAt = NOW()";

            $query = "UPDATE books SET " . implode(", ", $fields) . " WHERE isbn = ?";
            $types .= "s";
            $values[] = $isbn;

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$values);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating book: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a book
     *
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function deleteBook($isbn)
    {
        global $conn;

        try {
            // Check if book has active borrows
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transactions WHERE isbn = ? AND status = 'borrowed'");
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                error_log("Cannot delete book with active borrows");
                return false;
            }

            // Get book image to delete file
            $book = $this->getBookByISBN($isbn);
            if ($book && isset($book['bookImage']) && $book['bookImage'] !== 'default-book.jpg') {
                $imagePath = APP_ROOT . '/public/uploads/books/' . $book['bookImage'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Delete book
            $stmt = $conn->prepare("DELETE FROM books WHERE isbn = ?");
            $stmt->bind_param("s", $isbn);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error deleting book: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search books
     *
     * @param string $query Search query
     * @param string $category Category filter
     * @return array Search results
     */
    public function searchBooks($query = '', $category = '')
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
                $sql .= " AND category = ?";
                $params[] = $category;
                $types .= "s";
            }

            $sql .= " ORDER BY bookName";

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
     * Get trending books
     *
     * @param int $limit Number of books to return
     * @return array Trending books
     */
    public function getTrendingBooks($limit = 6)
    {
        global $conn;

        try {
            $stmt = $conn->prepare("SELECT * FROM books WHERE isTrending = 1 ORDER BY createdAt DESC LIMIT ?");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }

            return $books;
        } catch (\Exception $e) {
            error_log("Error getting trending books: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get special feature books
     *
     * @param int $limit Number of books to return
     * @return array Special books
     */
    public function getSpecialBooks($limit = 6)
    {
        global $conn;

        try {
            $stmt = $conn->prepare("SELECT * FROM books WHERE isSpecial = 1 ORDER BY createdAt DESC LIMIT ?");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }

            return $books;
        } catch (\Exception $e) {
            error_log("Error getting special books: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Upload book image
     *
     * @param array $file Uploaded file data
     * @return array Result with success status and path
     */
    public function uploadBookImage($file)
    {
        $uploadDir = APP_ROOT . '/public/uploads/books/';

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }

        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'File too large'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_') . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'path' => $filename];
        }

        return ['success' => false, 'error' => 'Failed to upload file'];
    }

    /**
     * Get all categories
     *
     * @return array List of categories
     */
    public function getCategories()
    {
        global $conn;

        try {
            $query = "SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category";
            $result = $conn->query($query);

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
     * Toggle trending status
     *
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function toggleTrending($isbn)
    {
        global $conn;

        try {
            $stmt = $conn->prepare("UPDATE books SET isTrending = NOT isTrending WHERE isbn = ?");
            $stmt->bind_param("s", $isbn);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error toggling trending: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle special status
     *
     * @param string $isbn Book ISBN
     * @return bool Success status
     */
    public function toggleSpecial($isbn)
    {
        global $conn;

        try {
            $stmt = $conn->prepare("UPDATE books SET isSpecial = NOT isSpecial WHERE isbn = ?");
            $stmt->bind_param("s", $isbn);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error toggling special: " . $e->getMessage());
            return false;
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
     * Update book availability (increase/decrease available copies)
     * 
     * @param string $isbn Book ISBN
     * @param int $change Change in availability (+1 or -1)
     * @return bool Success status
     */
    public function updateAvailability($isbn, $change)
    {
        global $conn;

        try {
            if ($change > 0) {
                // Returning a book
                $stmt = $conn->prepare("UPDATE books SET available = available + ?, borrowed = borrowed - ? WHERE isbn = ?");
                $positiveChange = abs($change);
                $stmt->bind_param("iis", $positiveChange, $positiveChange, $isbn);
            } else {
                // Borrowing a book
                $stmt = $conn->prepare("UPDATE books SET available = available - ?, borrowed = borrowed + ? WHERE isbn = ? AND available > 0");
                $positiveChange = abs($change);
                $stmt->bind_param("iis", $positiveChange, $positiveChange, $isbn);
            }

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (\Exception $e) {
            error_log("Error updating availability: " . $e->getMessage());
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
     * @return array Popular books
     */
    public function getPopularBooks($limit = 5)
    {
        return $this->bookModel->getPopularBooks($limit);
    }
}
?>