<?php
// src/controllers/BookController.php

namespace App\Controllers;

use App\Helpers\BarcodeHelper;

class BookController
{
    /**
     * Display books for admin
     */
    public function adminBooks()
    {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . '403');
            exit();
        }

        global $mysqli;
        
        // Check if connection exists
        if (!$mysqli) {
            die("Database connection failed");
        }
        
        // Fetch all books - UPDATED: Fetch all fields
        $sql = "SELECT 
                    isbn,
                    barcode,
                    bookName,
                    authorName,
                    publisherName,
                    description,
                    category,
                    publicationYear,
                    totalCopies,
                    bookImage,
                    available,
                    borrowed,
                    isTrending,
                    isSpecial,
                    specialBadge
                FROM books 
                ORDER BY bookName ASC";
        
        $result = $mysqli->query($sql);
        
        // Check for query errors
        if (!$result) {
            error_log("SQL Error in adminBooks: " . $mysqli->error);
            die("Error fetching books: " . $mysqli->error);
        }
        
        $books = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Debug: Log each book's image path
                error_log("Book: {$row['bookName']}, Image: " . ($row['bookImage'] ?? 'NULL'));
                $books[] = $row;
            }
            $result->free();
        }
        
        // Debug: Log the number of books fetched
        error_log("Books fetched: " . count($books));
        
        // Get unique publishers for filter - with error handling
        $publishers = [];
        try {
            $publisherSql = "SELECT DISTINCT publisherName 
                            FROM books 
                            WHERE publisherName IS NOT NULL AND publisherName != '' 
                            ORDER BY publisherName ASC";
            $publisherResult = $mysqli->query($publisherSql);
            
            if ($publisherResult) {
                while ($row = $publisherResult->fetch_assoc()) {
                    if (!empty($row['publisherName'])) {
                        $publishers[] = $row['publisherName'];
                    }
                }
                $publisherResult->free();
            }
        } catch (\Exception $e) {
            error_log("Error fetching publishers: " . $e->getMessage());
            // Continue with empty publishers array
        }
        
        // Pass data to view
        $pageTitle = 'Books Management';
        include APP_ROOT . '/views/admin/books.php';
    }

    /**
     * Add new book
     */
    public function addBook()
    {
        // Check if user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        try {
            global $mysqli;
            
            if (!$mysqli) {
                throw new \Exception("Database connection failed");
            }
            
            $isbn = trim($_POST['isbn'] ?? '');
            $bookName = trim($_POST['bookName'] ?? '');
            $authorName = trim($_POST['authorName'] ?? '');
            $publisherName = trim($_POST['publisherName'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $publicationYear = !empty($_POST['publicationYear']) ? (int)$_POST['publicationYear'] : null;
            $totalCopies = (int)($_POST['totalCopies'] ?? 1);
            $available = (int)($_POST['available'] ?? $totalCopies);
            $borrowed = (int)($_POST['borrowed'] ?? 0);
            $isTrending = isset($_POST['isTrending']) ? 1 : 0;
            $isSpecial = isset($_POST['isSpecial']) ? 1 : 0;
            $specialBadge = $isSpecial ? trim($_POST['specialBadge'] ?? '') : null;
            
            // Validate required fields
            if (empty($isbn) || empty($bookName) || empty($authorName) || empty($publisherName)) {
                $_SESSION['error'] = 'All required fields must be filled';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            
            // Validate totalCopies and available
            if ($totalCopies < 1) {
                $_SESSION['error'] = 'Total copies must be at least 1';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            
            if ($available > $totalCopies) {
                $_SESSION['error'] = 'Available copies cannot exceed total copies';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            
            // Check if ISBN already exists
            $checkStmt = $mysqli->prepare("SELECT isbn FROM books WHERE isbn = ?");
            if (!$checkStmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $checkStmt->bind_param("s", $isbn);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $_SESSION['error'] = 'Book with this ISBN already exists';
                $checkStmt->close();
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            $checkStmt->close();
            
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = APP_ROOT . '/public/uploads/books/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                    error_log("Created upload directory: {$uploadDir}");
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    $_SESSION['error'] = 'Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.';
                    header('Location: ' . BASE_URL . 'admin/books');
                    exit();
                }
                
                // Check file size (500KB max after compression on client)
                if ($_FILES['image']['size'] > 1024 * 1024 * 2) { // 2MB hard limit server-side
                    $_SESSION['error'] = 'Image file is too large. Maximum size is 2MB.';
                    header('Location: ' . BASE_URL . 'admin/books');
                    exit();
                }
                
                $fileName = uniqid('book_') . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = 'uploads/books/' . $fileName;
                    error_log("✓ Image uploaded successfully: {$imagePath}");
                } else {
                    error_log("✗ Failed to move uploaded file to: {$targetPath}");
                    $_SESSION['error'] = 'Failed to upload image. Please try again.';
                    header('Location: ' . BASE_URL . 'admin/books');
                    exit();
                }
            }
            
            // GENERATE BARCODE - TEXT ONLY VERSION (No dependencies required)
            $cleanIsbn = str_replace(['-', ' '], '', $isbn);
            $barcodeValue = 'BK' . strtoupper(substr(md5($cleanIsbn . time()), 0, 10));
            
            // Just store the barcode value - no image generation
            try {
                error_log("✓ Barcode generated successfully: {$barcodeValue}");
            } catch (\Exception $e) {
                error_log('Barcode generation failed: ' . $e->getMessage());
            }
            // END BARCODE GENERATION
            
            // Insert book - UPDATED: Include all fields
            $stmt = $mysqli->prepare("INSERT INTO books 
                (isbn, barcode, bookName, authorName, publisherName, description, category, publicationYear, 
                 totalCopies, bookImage, available, borrowed, isTrending, isSpecial, specialBadge) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            error_log("Inserting book with image path: " . ($imagePath ?? 'NULL'));
            
            $stmt->bind_param("sssssssississis", 
                $isbn, 
                $barcodeValue,
                $bookName, 
                $authorName, 
                $publisherName,
                $description,
                $category,
                $publicationYear,
                $totalCopies,
                $imagePath,
                $available,
                $borrowed,
                $isTrending,
                $isSpecial,
                $specialBadge
            );
            
            if ($stmt->execute()) {
                $stmt->close();
                $_SESSION['success'] = 'Book added successfully!';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            } else {
                $error = $stmt->error;
                $stmt->close();
                throw new \Exception("Failed to add book: " . $error);
            }
            
        } catch (\Exception $e) {
            error_log("Error adding book: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
    }

    /**
     * Edit book
     */
    public function editBook()
    {
        // Check if user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        try {
            global $mysqli;
            
            if (!$mysqli) {
                throw new \Exception("Database connection failed");
            }
            
            $isbn = trim($_POST['isbn'] ?? '');
            $bookName = trim($_POST['bookName'] ?? '');
            $authorName = trim($_POST['authorName'] ?? '');
            $publisherName = trim($_POST['publisherName'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $publicationYear = !empty($_POST['publicationYear']) ? (int)$_POST['publicationYear'] : null;
            $totalCopies = (int)($_POST['totalCopies'] ?? 1);
            $available = (int)($_POST['available'] ?? 0);
            $borrowed = (int)($_POST['borrowed'] ?? 0);
            $isTrending = isset($_POST['isTrending']) ? 1 : 0;
            $isSpecial = isset($_POST['isSpecial']) ? 1 : 0;
            $specialBadge = $isSpecial ? trim($_POST['specialBadge'] ?? '') : null;
            
            // Validate required fields
            if (empty($isbn) || empty($bookName) || empty($authorName) || empty($publisherName)) {
                $_SESSION['error'] = 'All required fields must be filled';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            
            // Get current book data
            $currentStmt = $mysqli->prepare("SELECT bookImage, totalCopies, available FROM books WHERE isbn = ?");
            if (!$currentStmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $currentStmt->bind_param("s", $isbn);
            $currentStmt->execute();
            $currentBook = $currentStmt->get_result()->fetch_assoc();
            $currentStmt->close();
            
            if (!$currentBook) {
                $_SESSION['error'] = 'Book not found';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            
            $imagePath = $currentBook['bookImage'];
            
            // Handle new image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = APP_ROOT . '/public/uploads/books/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    $_SESSION['error'] = 'Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.';
                    header('Location: ' . BASE_URL . 'admin/books');
                    exit();
                }
                
                $fileName = uniqid('book_') . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Delete old image if it exists
                    if (!empty($imagePath) && file_exists(APP_ROOT . '/public/' . $imagePath)) {
                        unlink(APP_ROOT . '/public/' . $imagePath);
                    }
                    $imagePath = 'uploads/books/' . $fileName;
                }
            }
            
            // Update book - UPDATED: Include all fields
            $stmt = $mysqli->prepare("UPDATE books SET 
                bookName = ?, 
                authorName = ?, 
                publisherName = ?,
                description = ?,
                category = ?,
                publicationYear = ?,
                totalCopies = ?,
                bookImage = ?,
                available = ?, 
                borrowed = ?,
                isTrending = ?,
                isSpecial = ?,
                specialBadge = ?
                WHERE isbn = ?");
            
            if (!$stmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $stmt->bind_param("sssssiisiiisss", 
                $bookName, 
                $authorName, 
                $publisherName,
                $description,
                $category,
                $publicationYear,
                $totalCopies,
                $imagePath,
                $available, 
                $borrowed,
                $isTrending,
                $isSpecial,
                $specialBadge,
                $isbn
            );
            
            if ($stmt->execute()) {
                $stmt->close();
                $_SESSION['success'] = 'Book updated successfully!';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            } else {
                $error = $stmt->error;
                $stmt->close();
                throw new \Exception("Failed to update book: " . $error);
            }
            
        } catch (\Exception $e) {
            error_log("Error updating book: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
    }

    /**
     * Delete book
     */
    public function deleteBook()
    {
        // Check if user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
        
        try {
            global $mysqli;
            
            if (!$mysqli) {
                throw new \Exception("Database connection failed");
            }
            
            $isbn = trim($_POST['isbn'] ?? '');
            
            if (empty($isbn)) {
                $_SESSION['error'] = 'ISBN is required';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            
            // Check if book has active borrowings
            $checkStmt = $mysqli->prepare("SELECT COUNT(*) as count FROM transactions WHERE isbn = ? AND returnDate IS NULL");
            if (!$checkStmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $checkStmt->bind_param("s", $isbn);
            $checkStmt->execute();
            $result = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();
            
            if ($result['count'] > 0) {
                $_SESSION['error'] = 'Cannot delete book with active borrowings';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            }
            
            // Delete book
            $stmt = $mysqli->prepare("DELETE FROM books WHERE isbn = ?");
            if (!$stmt) {
                throw new \Exception("Prepare statement failed: " . $mysqli->error);
            }
            
            $stmt->bind_param("s", $isbn);
            
            if ($stmt->execute()) {
                $stmt->close();
                $_SESSION['success'] = 'Book deleted successfully!';
                header('Location: ' . BASE_URL . 'admin/books');
                exit();
            } else {
                $error = $stmt->error;
                $stmt->close();
                throw new \Exception("Failed to delete book: " . $error);
            }
            
        } catch (\Exception $e) {
            error_log("Error deleting book: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'admin/books');
            exit();
        }
    }

    /**
     * Display books for users (public/student view)
     * NOTE: Faculty users will be redirected to /faculty/books
     */
    public function userBooks()
    {
        // Only redirect if user is Faculty or Admin
        if (isset($_SESSION['userType']) || isset($_SESSION['user_type'])) {
            $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;
            if ($userType === 'Faculty') {
                header('Location: /faculty/books');
                exit();
            }
            if ($userType === 'Admin') {
                header('Location: /admin/books');
                exit();
            }
        }
        
        global $mysqli;
        
        if (!$mysqli) {
            die("Database connection failed");
        }
        
        // Fetch all available books - UPDATED: Fetch all fields
        $sql = "SELECT 
                    isbn,
                    bookName,
                    authorName,
                    publisherName,
                    description,
                    category,
                    publicationYear,
                    totalCopies,
                    bookImage,
                    available,
                    borrowed,
                    isTrending,
                    isSpecial,
                    specialBadge
                FROM books
                WHERE available > 0
                ORDER BY bookName ASC";
        
        $result = $mysqli->query($sql);
        
        if (!$result) {
            error_log("SQL Error in userBooks: " . $mysqli->error);
            die("Error fetching books: " . $mysqli->error);
        }
        
        $books = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add 'image' alias for backward compatibility if needed
                $row['image'] = $row['bookImage'];
                $books[] = $row;
            }
            $result->free();
        }
        
        // Calculate statistics
        $totalBooks = count($books);
        $totalAvailable = 0;
        $totalBorrowed = 0;
        
        foreach ($books as $book) {
            $totalAvailable += ($book['available'] ?? 0);
            $totalBorrowed += ($book['borrowed'] ?? 0);
        }
        
        // Get categories for filter dropdown (publishers in this case)
        $categories = [];
        $publisherSql = "SELECT DISTINCT publisherName 
                        FROM books 
                        WHERE publisherName IS NOT NULL AND publisherName != '' 
                        ORDER BY publisherName ASC";
        $publisherResult = $mysqli->query($publisherSql);
        
        if ($publisherResult) {
            while ($row = $publisherResult->fetch_assoc()) {
                if (!empty($row['publisherName'])) {
                    $categories[] = $row['publisherName'];
                }
            }
            $publisherResult->free();
        }
        
        $pageTitle = 'Available Books';
        
        // Check if user view file exists (plural "users"), otherwise use faculty view as fallback
        $userViewPath = APP_ROOT . '/views/users/books.php';
        $facultyViewPath = APP_ROOT . '/views/faculty/books.php';
        
        if (file_exists($userViewPath)) {
            include $userViewPath;
        } elseif (file_exists($facultyViewPath)) {
            include $facultyViewPath;
        } else {
            error_log("ERROR: No books view found. Checked: {$userViewPath} and {$facultyViewPath}");
            die("Books view template not found");
        }
    }

    /**
     * Search books (API endpoint)
     */
    public function searchBooks()
    {
        header('Content-Type: application/json');
        
        global $mysqli;
        
        if (!$mysqli) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed', 'books' => []]);
            exit();
        }
        
        $query = trim($_GET['q'] ?? '');
        
        if (empty($query)) {
            echo json_encode(['success' => false, 'books' => []]);
            exit();
        }
        
        $searchTerm = '%' . $query . '%';
        // UPDATED: Fetch all fields
        $stmt = $mysqli->prepare("SELECT 
                isbn, 
                bookName, 
                authorName, 
                publisherName,
                description,
                category,
                publicationYear,
                bookImage,
                available,
                isTrending,
                isSpecial,
                specialBadge
            FROM books 
            WHERE bookName LIKE ? OR authorName LIKE ? OR isbn LIKE ? OR publisherName LIKE ?
            ORDER BY bookName ASC
            LIMIT 20");
        
        if (!$stmt) {
            error_log("Prepare statement failed in searchBooks: " . $mysqli->error);
            echo json_encode(['success' => false, 'message' => 'Search failed', 'books' => []]);
            exit();
        }
        
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $books = [];
        while ($row = $result->fetch_assoc()) {
            // Add 'image' alias for backward compatibility if needed
            $row['image'] = $row['bookImage'];
            $books[] = $row;
        }
        
        $stmt->close();
        
        echo json_encode(['success' => true, 'books' => $books]);
        exit();
    }

    /**
     * Return book page - redirects Faculty to faculty/return
     */
    public function return()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }
        
        // Get user type and redirect to appropriate page
        $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;
        
        // Redirect faculty to faculty return page
        if ($userType === 'Faculty') {
            header('Location: /faculty/return');
            exit();
        }
        
        // Redirect admin to admin dashboard
        if ($userType === 'Admin') {
            header('Location: /admin/dashboard');
            exit();
        }
        
        // Continue with student/user return page
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];
        
        global $mysqli;
        
        if (!$mysqli) {
            die("Database connection failed");
        }
        
        // Get borrowed books for student/user
        $stmt = $mysqli->prepare("
            SELECT t.*, b.bookName, b.authorName, b.bookImage 
            FROM transactions t
            JOIN books b ON t.isbn = b.isbn
            WHERE t.userId = ? AND t.returnDate IS NULL
            ORDER BY t.borrowDate DESC
        ");
        
        $borrowedBooks = [];
        if ($stmt) {
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $borrowedBooks = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        // Handle return submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionId = $_POST['transaction_id'] ?? '';
            
            if (!empty($transactionId)) {
                $updateStmt = $mysqli->prepare("
                    UPDATE transactions 
                    SET returnDate = CURDATE() 
                    WHERE tid = ? AND userId = ? AND returnDate IS NULL
                ");
                
                if ($updateStmt) {
                    $updateStmt->bind_param("ss", $transactionId, $userId);
                    
                    if ($updateStmt->execute() && $updateStmt->affected_rows > 0) {
                        // Update book availability
                        $getIsbn = $mysqli->prepare("SELECT isbn FROM transactions WHERE tid = ?");
                        $getIsbn->bind_param("s", $transactionId);
                        $getIsbn->execute();
                        $isbnResult = $getIsbn->get_result()->fetch_assoc();
                        
                        if ($isbnResult) {
                            $updateBook = $mysqli->prepare("
                                UPDATE books 
                                SET available = available + 1, borrowed = borrowed - 1 
                                WHERE isbn = ?
                            ");
                            $updateBook->bind_param("s", $isbnResult['isbn']);
                            $updateBook->execute();
                            $updateBook->close();
                        }
                        $getIsbn->close();
                        
                        $_SESSION['success_message'] = 'Book returned successfully!';
                    } else {
                        $_SESSION['error_message'] = 'Failed to return book. Please try again.';
                    }
                    $updateStmt->close();
                }
                
                header('Location: /user/return');
                exit();
            }
        }
        
        // Load user return view
        $pageTitle = 'Return Books';
        include APP_ROOT . '/views/users/return.php';
    }

    /**
     * Get reservation queue for a book
     */
    public function getReservationQueue($isbn)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM book_reservations WHERE isbn = ? AND reservationStatus IN ('Active', 'Notified') ORDER BY createdAt ASC");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        $queue = [];
        while ($row = $result->fetch_assoc()) {
            $queue[] = $row;
        }
        $stmt->close();
        return $queue;
    }

    /**
     * View details for a single book (user/student)
     * Supports both path parameters (from route) and query parameters (from links)
     */
    public function viewBook($params = [])
    {
        // Log access attempt for debugging
        error_log("=== BookController::viewBook() CALLED ===");
        error_log("Session userType: " . ($_SESSION['userType'] ?? 'NOT SET'));
        error_log("Session userId: " . ($_SESSION['userId'] ?? 'NOT SET'));
        error_log("Params: " . print_r($params, true));
        error_log("GET: " . print_r($_GET, true));
        
        // Check authentication FIRST - only require login, no role restriction
        if (!isset($_SESSION['userId'])) {
            error_log("BookController::viewBook() - User not logged in, redirecting to login");
            $_SESSION['error'] = 'Please login to view book details';
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        // Only redirect if user is Faculty or Admin - Students can access this page
        $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;
        if ($userType === 'Faculty') {
            error_log("BookController::viewBook() - Faculty user, redirecting to faculty/books");
            header('Location: /faculty/books');
            exit();
        }
        if ($userType === 'Admin') {
            error_log("BookController::viewBook() - Admin user, redirecting to admin/books");
            header('Location: /admin/books');
            exit();
        }
        
        // If we get here, user is logged in and is Student (or userType is null/empty)
        error_log("BookController::viewBook() - Allowing access for userType: " . ($userType ?? 'null/Student'));
        
        global $mysqli;
        
        // Get ISBN from either path parameter (route) or query parameter (link)
        $isbn = $params['isbn'] ?? $_GET['isbn'] ?? '';
        
        if (empty($isbn)) {
            // Redirect to books page if no ISBN provided
            header('Location: ' . BASE_URL . 'user/books');
            exit();
        }

        // Fetch book details
        $stmt = $mysqli->prepare("SELECT isbn, bookName, authorName, publisherName, description, category, publicationYear, totalCopies, bookImage, available, borrowed, isTrending, isSpecial, specialBadge FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();

        if (!$book) {
            // Book not found
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'user/books');
            exit();
        }

        // Make sure $book is available in the view scope
        $pageTitle = 'Book Details';
        
        // Log for debugging
        error_log("BookController::viewBook() - Book found: " . ($book['bookName'] ?? 'Unknown'));
        error_log("BookController::viewBook() - User Type: " . ($_SESSION['userType'] ?? 'Not set'));
        error_log("BookController::viewBook() - User ID: " . ($_SESSION['userId'] ?? 'Not set'));
        
        // Include view with book variable in scope
        include APP_ROOT . '/views/users/view-book.php';
    }
}