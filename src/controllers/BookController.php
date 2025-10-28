<?php
// src/controllers/BookController.php

namespace App\Controllers;

use App\Helpers\BarcodeHelper;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\Types\TypeCode128;

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

    // Fetch all books - FIXED: Changed 'image' to 'bookImage'
    $sql = "SELECT
                    isbn,
                    barcode,
                    bookName,
                    authorName,
                    publisherName,
                    totalCopies,
                    bookImage,
                    available,
                    borrowed,
                    isTrending
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
        // Add 'image' alias for backward compatibility if needed
        $row['image'] = $row['bookImage'];
        // Add missing columns with default values for view compatibility
        $row['isSpecial'] = 0;
        $row['specialBadge'] = null;
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
      $totalCopies = (int)($_POST['totalCopies'] ?? 1);
      $available = (int)($_POST['available'] ?? $totalCopies);
      $isTrending = isset($_POST['isTrending']) ? 1 : 0;

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
          $imagePath = 'uploads/books/' . $fileName;
        }
      }

      // GENERATE BARCODE - ENHANCED VERSION
      $cleanIsbn = str_replace(['-', ' '], '', $isbn);
      $barcodeValue = 'BK' . strtoupper(substr(md5($cleanIsbn . time()), 0, 10));

      // Generate barcode image using Picqer library
      try {
        $barcodeDir = APP_ROOT . '/public/uploads/barcodes/';
        if (!is_dir($barcodeDir)) {
          mkdir($barcodeDir, 0755, true);
        }

        // Create barcode using TypeCode128
        $barcodeObj = (new TypeCode128())->getBarcode($barcodeValue);

        // Render as PNG
        $generator = new BarcodeGeneratorPNG();
        $barcodeImage = $generator->render($barcodeObj, 2, 50);

        // Save barcode image
        $barcodePath = $barcodeDir . $cleanIsbn . '_barcode.png';
        file_put_contents($barcodePath, $barcodeImage);

        // Create barcode label with text and book info
        $this->createBarcodeLabel($cleanIsbn, $barcodeValue, $bookName, $barcodeImage);

        error_log("✓ Barcode generated successfully: {$barcodeValue}");
      } catch (\Exception $e) {
        error_log('Barcode generation failed: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        // Continue even if barcode generation fails
      }
      // END BARCODE GENERATION

      $borrowed = $totalCopies - $available;

      // Insert book - FIXED: Changed 'image' to 'bookImage'
      $stmt = $mysqli->prepare("INSERT INTO books
                (isbn, barcode, bookName, authorName, publisherName, totalCopies, bookImage, available, borrowed, isTrending)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      if (!$stmt) {
        throw new \Exception("Prepare statement failed: " . $mysqli->error);
      }

      $stmt->bind_param(
        "sssssissii",
        $isbn,
        $barcodeValue,
        $bookName,
        $authorName,
        $publisherName,
        $totalCopies,
        $imagePath,
        $available,
        $borrowed,
        $isTrending
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
   * Create barcode label with text and book information
   */
  private function createBarcodeLabel($cleanIsbn, $barcodeValue, $bookName, $barcodeImage)
  {
    try {
      $barcodeDir = APP_ROOT . '/public/uploads/barcodes/';
      $labelPath = $barcodeDir . $cleanIsbn . '_label.png';

      // Create label image (500x250 - larger for better quality)
      $label = imagecreatetruecolor(500, 250);
      $white = imagecolorallocate($label, 255, 255, 255);
      $black = imagecolorallocate($label, 0, 0, 0);
      $gray = imagecolorallocate($label, 100, 100, 100);
      $blue = imagecolorallocate($label, 37, 99, 235); // Modern blue color

      // Fill background
      imagefilledrectangle($label, 0, 0, 500, 250, $white);

      // Add border
      imagerectangle($label, 0, 0, 499, 249, $gray);
      imagerectangle($label, 1, 1, 498, 248, $gray);

      // Load barcode image
      $barcode = imagecreatefromstring($barcodeImage);
      $barcodeWidth = imagesx($barcode);
      $barcodeHeight = imagesy($barcode);

      // Center barcode on label
      $x = (500 - $barcodeWidth) / 2;
      $y = 60;
      imagecopy($label, $barcode, $x, $y, 0, 0, $barcodeWidth, $barcodeHeight);

      // Add book title at top (truncate if too long)
      $maxTitleLength = 45;
      $displayTitle = strlen($bookName) > $maxTitleLength
        ? substr($bookName, 0, $maxTitleLength) . '...'
        : $bookName;

      $font = 5; // Built-in font
      $titleWidth = strlen($displayTitle) * imagefontwidth($font);
      $titleX = (500 - $titleWidth) / 2;
      imagestring($label, $font, $titleX, 20, $displayTitle, $blue);

      // Add barcode value below barcode
      $textWidth = strlen($barcodeValue) * imagefontwidth($font);
      $textX = (500 - $textWidth) / 2;
      $textY = $y + $barcodeHeight + 15;
      imagestring($label, $font, $textX, $textY, $barcodeValue, $black);

      // Add "Scan to Identify" text at bottom
      $footerText = "Library Management System";
      $footerWidth = strlen($footerText) * imagefontwidth(3);
      $footerX = (500 - $footerWidth) / 2;
      imagestring($label, 3, $footerX, 220, $footerText, $gray);

      // Save label
      imagepng($label, $labelPath);
      imagedestroy($label);
      imagedestroy($barcode);

      error_log("✓ Barcode label created: {$labelPath}");
    } catch (\Exception $e) {
      error_log('Label creation failed: ' . $e->getMessage());
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
      $totalCopies = (int)($_POST['totalCopies'] ?? 1);
      $available = (int)($_POST['available'] ?? 0);
      $isTrending = isset($_POST['isTrending']) ? 1 : 0;

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

      $borrowed = $totalCopies - $available;

      // Update book - FIXED: Changed 'image' to 'bookImage'
      $stmt = $mysqli->prepare("UPDATE books SET
                bookName = ?,
                authorName = ?,
                publisherName = ?,
                totalCopies = ?,
                bookImage = ?,
                available = ?,
                borrowed = ?,
                isTrending = ?
                WHERE isbn = ?");

      if (!$stmt) {
        throw new \Exception("Prepare statement failed: " . $mysqli->error);
      }

      $stmt->bind_param(
        "sssisiiis",
        $bookName,
        $authorName,
        $publisherName,
        $totalCopies,
        $imagePath,
        $available,
        $borrowed,
        $isTrending,
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
    // Ensure the user is logged in
    if (!isset($_SESSION['userType']) && !isset($_SESSION['user_type'])) {
        header('Location: /login');
        exit();
    }

    $userType = $_SESSION['userType'] ?? $_SESSION['user_type'] ?? null;

    // Redirect other roles to their specific routes
    if ($userType === 'Faculty') {
        header('Location: /faculty/books');
        exit();
    }

    if ($userType === 'Admin') {
        header('Location: /admin/books');
        exit();
    }

    // Database connection
    global $mysqli;
    if (!$mysqli) {
        die("Database connection failed");
    }

    // Fetch available books
    $sql = "SELECT
                isbn,
                bookName,
                authorName,
                publisherName,
                totalCopies,
                bookImage,
                available,
                borrowed,
                isTrending
            FROM books
            WHERE available > 0
            ORDER BY bookName ASC";

    $result = $mysqli->query($sql);

    if (!$result) {
        error_log("SQL Error in userBooks: " . $mysqli->error);
        die("Error fetching books: " . $mysqli->error);
    }

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $row['image'] = $row['bookImage']; // backward compatibility alias
        $row['isSpecial'] = 0;
        $row['specialBadge'] = null;
        $books[] = $row;
    }
    $result->free();

    // Calculate statistics
    $totalBooks = count($books);
    $totalAvailable = 0;
    $totalBorrowed = 0;

    foreach ($books as $book) {
        $totalAvailable += ($book['available'] ?? 0);
        $totalBorrowed += ($book['borrowed'] ?? 0);
    }

    // Get distinct publisher names for filtering
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

    // View paths
    $userViewPath = APP_ROOT . '/views/users/books.php';
    $facultyViewPath = APP_ROOT . '/views/faculty/books.php';
    // $fallbackViewPath = APP_ROOT . '/views/shared/books.php'; // optional fallback

    // Debug (optional)
    // var_dump($userViewPath);

    // Load appropriate view
    if ($userType === "Faculty" && file_exists($facultyViewPath)) {
        include $facultyViewPath;
    } elseif (file_exists($userViewPath)) {
        include $userViewPath; // for regular users (students, members, etc.)
    } elseif (file_exists($fallbackViewPath)) {
        include $fallbackViewPath; // fallback if others not found
    } else {
        // Graceful error message instead of die()
        http_response_code(404);
        echo "<h2>Books view template not found for your user type.</h2>";
        echo "<p>Expected path: <code>{$userViewPath}</code></p>";
        exit();
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
    // FIXED: Changed 'image' to 'bookImage'
    $stmt = $mysqli->prepare("SELECT
                isbn,
                bookName,
                authorName,
                publisherName,
                bookImage,
                available,
                isTrending
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
      // Add missing columns with default values for view compatibility
      $row['isSpecial'] = 0;
      $row['specialBadge'] = null;
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
    include APP_ROOT . '/views/user/return.php';
  }
}
