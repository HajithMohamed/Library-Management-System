<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\User;
use App\Models\Transaction;
use App\Helpers\ValidationHelper; // FIXED: Add this

class FacultyController extends BaseController
{
    private $bookModel;
    private $borrowModel;
    private $userModel;
    private $transactionModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookModel = new Book();
        $this->borrowModel = new BorrowRecord();
        $this->userModel = new User();
        $this->transactionModel = new Transaction();
    }

    public function dashboard()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        // Get faculty statistics
        $privileges = $this->userModel->getUserPrivileges($userId);
        $userStats = [
            'borrowed_books' => $this->borrowModel->getActiveBorrowCount($userId),
            'overdue_books' => $this->borrowModel->getOverdueCount($userId),
            'total_fines' => $this->borrowModel->getTotalFines($userId),
            'max_books' => $privileges['max_borrow_limit'] ?? 10,
            'borrow_period_days' => $privileges['borrow_period_days'] ?? 60,
            'max_renewals' => $privileges['max_renewals'] ?? 2,
            'remaining_slots' => max(0, ($privileges['max_borrow_limit'] ?? 10) - $this->borrowModel->getActiveBorrowCount($userId))
        ];
        
        // Get recent activity
        $recentActivity = $this->borrowModel->getRecentActivity($userId, 10);
        
        // Get user info
        $user = $this->userModel->findById($userId);
        
        // Get borrowed books
        $borrowedBooks = $this->borrowModel->getActiveBorrows($userId);
        
        // Get overdue books
        $overdueBooks = array_filter($borrowedBooks, function($book) {
            $dueDate = $book['dueDate'] ?? date('Y-m-d', strtotime($book['borrowDate'] . ' + 14 days'));
            return strtotime($dueDate) < time();
        });
        
        // Get reserved books
        global $mysqli;
        $reservedBooks = [];
        $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE userId = ? AND status IN ('Pending','Approved')");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reservedBooks[] = $row;
        }
        $stmt->close();
        
        // Get notifications
        $notifications = $this->userModel->getNotifications($userId);
        
        // Get transaction history
        $transactionHistory = $this->borrowModel->getBorrowHistory($userId);
        
        // Get analytics stats
        $stats = $this->getPersonalStats($userId);
        
        // Pass all data to view
        $this->data['userStats'] = $userStats;
        $this->data['recentActivity'] = $recentActivity;
        $this->data['user'] = $user;
        $this->data['borrowedBooks'] = $borrowedBooks;
        $this->data['overdueBooks'] = $overdueBooks;
        $this->data['reservedBooks'] = $reservedBooks;
        $this->data['notifications'] = $notifications;
        $this->data['transactionHistory'] = $transactionHistory;
        $this->data['stats'] = $stats;
        
        $this->view('faculty/dashboard', $this->data);
    }
    
    /**
     * Get personal statistics for analytics
     */
    private function getPersonalStats($userId)
    {
        $stats = [
            'total_books' => 0,
            'reviews' => [
                'total_reviews' => 0,
                'avg_rating' => 0
            ],
            'categories' => [],
            'monthly' => []
        ];
        
        // Get total books borrowed
        $sql = "SELECT COUNT(DISTINCT isbn) as total FROM transactions WHERE userId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stats['total_books'] = $result['total'] ?? 0;
        
        // Get reviews stats (if book_reviews table exists)
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'book_reviews'");
        if ($tableCheck->num_rows > 0) {
            $sql = "SELECT COUNT(*) as total, AVG(rating) as avg_rating 
                    FROM book_reviews WHERE userId = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stats['reviews']['total_reviews'] = $result['total'] ?? 0;
            $stats['reviews']['avg_rating'] = $result['avg_rating'] ?? 0;
        }
        
        // Get category distribution
        $sql = "SELECT b.category, COUNT(*) as borrow_count 
                FROM transactions t 
                JOIN books b ON t.isbn = b.isbn 
                WHERE t.userId = ? AND b.category IS NOT NULL 
                GROUP BY b.category 
                ORDER BY borrow_count DESC 
                LIMIT 6";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $stats['categories'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get monthly trend (last 6 months)
        $sql = "SELECT DATE_FORMAT(borrowDate, '%Y-%m') as month, COUNT(*) as count 
                FROM transactions 
                WHERE userId = ? AND borrowDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY month 
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $stats['monthly'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return $stats;
    }

    public function books()
    {
        $this->requireLogin(['Faculty']);
        
        global $mysqli;
        
        if (!$mysqli) {
            die("Database connection failed");
        }
        
        // Get filter parameters
        $searchQuery = trim($_GET['q'] ?? '');
        $categoryFilter = trim($_GET['category'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');
        $sortBy = trim($_GET['sort'] ?? '');
        
        // Build SQL query with filters
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
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Apply search filter
        if (!empty($searchQuery)) {
            $sql .= " AND (bookName LIKE ? OR authorName LIKE ? OR isbn LIKE ? OR publisherName LIKE ?)";
            $searchTerm = '%' . $searchQuery . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ssss';
        }
        
        // Apply publisher/category filter
        if (!empty($categoryFilter)) {
            $sql .= " AND publisherName = ?";
            $params[] = $categoryFilter;
            $types .= 's';
        }
        
        // Apply availability status filter
        if ($statusFilter === 'available') {
            $sql .= " AND available > 0";
        } elseif ($statusFilter === 'borrowed') {
            $sql .= " AND borrowed > 0";
        }
        
        // Apply sorting
        switch ($sortBy) {
            case 'title':
                $sql .= " ORDER BY bookName ASC";
                break;
            case 'author':
                $sql .= " ORDER BY authorName ASC";
                break;
            case 'available':
                $sql .= " ORDER BY available DESC, bookName ASC";
                break;
            default:
                $sql .= " ORDER BY bookName ASC";
        }
        
        // Prepare and execute query
        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                error_log("SQL Error in faculty books: " . $mysqli->error);
                die("Error fetching books: " . $mysqli->error);
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $mysqli->query($sql);
            if (!$result) {
                error_log("SQL Error in faculty books: " . $mysqli->error);
                die("Error fetching books: " . $mysqli->error);
            }
        }
        
        $books = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            if (isset($stmt)) {
                $stmt->close();
            } else {
                $result->free();
            }
        }
        
        // Get all publishers for filter dropdown
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
        
        // Calculate stats
        $totalBooks = count($books);
        $availableBooks = 0;
        foreach ($books as $book) {
            if ($book['available'] > 0) {
                $availableBooks++;
            }
        }
        $totalCategories = count($categories);
        
        // Pass data to view
        $this->data['books'] = $books;
        $this->data['categories'] = $categories;
        $this->data['totalBooks'] = $totalBooks;
        $this->data['availableBooks'] = $availableBooks;
        $this->data['totalCategories'] = $totalCategories;
        $this->data['searchTerm'] = $searchQuery;
        
        $this->view('faculty/books', $this->data);
    }

    public function viewBook($params)
    {
        $this->requireLogin(['Faculty']);
        
        $isbn = $params['isbn'] ?? '';
        $book = $this->bookModel->findByISBN($isbn);
        
        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            $this->redirect('faculty/books');
            return;
        }
        
        $this->data['book'] = $book;
        $this->view('faculty/book-details', $this->data);
    }

    /**
     * Reserve a book (send to borrow_requests table)
     */
    public function reserve($params = [])
    {
        $this->requireLogin(['Faculty']);

        $userId = $_SESSION['userId'];
        // Support both /faculty/reserve?isbn=... and /faculty/reserve/{isbn}
        $isbn = $_GET['isbn'] ?? ($params['isbn'] ?? null);

        if (!$isbn) {
            $_SESSION['error'] = 'No book specified for reservation';
            header('Location: ' . BASE_URL . 'faculty/books');
            exit;
        }

        global $mysqli;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check borrow limit before allowing reservation
            $privileges = $this->userModel->getUserPrivileges($userId);
            $maxLimit = $privileges['max_borrow_limit'] ?? 10;
            $currentBorrows = $this->borrowModel->getActiveBorrowCount($userId);
            
            // Also count pending/approved requests
            $pendingStmt = $mysqli->prepare("SELECT COUNT(*) as count FROM borrow_requests WHERE userId = ? AND status IN ('Pending','Approved')");
            $pendingCount = 0;
            if ($pendingStmt) {
                $pendingStmt->bind_param("s", $userId);
                $pendingStmt->execute();
                $pendingResult = $pendingStmt->get_result()->fetch_assoc();
                $pendingCount = (int)($pendingResult['count'] ?? 0);
                $pendingStmt->close();
            }
            
            if (($currentBorrows + $pendingCount) >= $maxLimit) {
                $_SESSION['error'] = "You have reached your borrowing limit ({$currentBorrows}/{$maxLimit} books borrowed" . ($pendingCount > 0 ? ", {$pendingCount} pending requests" : "") . "). Please return some books before requesting new ones.";
                header('Location: ' . BASE_URL . 'faculty/books');
                exit;
            }

            // Check if already has a pending/approved request for this book
            $stmt = $mysqli->prepare("SELECT * FROM borrow_requests WHERE userId = ? AND isbn = ? AND status IN ('Pending','Approved') AND dueDate >= CURDATE()");
            $stmt->bind_param("ss", $userId, $isbn);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['error'] = 'You already have a pending or approved request for this book.';
                $stmt->close();
                header('Location: ' . BASE_URL . 'faculty/books');
                exit;
            }
            $stmt->close();

            // Only allow reservation for 1 day
            $dueDate = date('Y-m-d', strtotime('+1 day'));

            // Insert into borrow_requests
            $stmt = $mysqli->prepare("INSERT INTO borrow_requests (userId, isbn, dueDate, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("sss", $userId, $isbn, $dueDate);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Reservation request sent! Awaiting admin approval.';
            } else {
                $_SESSION['error'] = 'Failed to send reservation request.';
            }
            $stmt->close();

            header('Location: ' . BASE_URL . 'faculty/reserved-books');
            exit;
        }

        // GET: Show confirmation page
        $stmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$book) {
            $_SESSION['error'] = 'Book not found';
            header('Location: ' . BASE_URL . 'faculty/books');
            exit;
        }

        $this->data['book'] = $book;
        $this->view('faculty/reserve', $this->data);
    }

    /**
     * Show faculty's reserved books (borrow requests)
     */
    public function reservedBooks() {
        $this->requireLogin(['Faculty']);
        global $mysqli;
        $userId = $_SESSION['userId'];

        $sql = "SELECT br.*, b.bookName, b.authorName 
                FROM borrow_requests br
                LEFT JOIN books b ON br.isbn = b.isbn
                WHERE br.userId = ?
                ORDER BY br.requestDate DESC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();

        $this->data['requests'] = $requests;
        $this->view('faculty/reserved-books', $this->data);
    }

    public function fines()
    {
        // Use consistent session check with the rest of the controller
        $this->requireLogin(['Faculty']);
        
        global $conn;
        
        // Use the correct session variable name (userId with capital I)
        $userId = $_SESSION['userId'];

        // Handle payment submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $borrowId = $_POST['borrow_id'] ?? '';
            $amount = $_POST['amount'] ?? 0;
            $payOnline = isset($_POST['pay_online']) && $_POST['pay_online'] == '1';
            
            if (!empty($borrowId) && $amount > 0) {
                try {
                    $conn->begin_transaction();
                    
                    $paymentMethod = 'cash';
                    $paymentDetails = null;
                    
                    if ($payOnline) {
                        $paymentMethod = $_POST['payment_method'] ?? 'credit_card';
                        
                        // Process card payment
                        $cardNumber = $_POST['card_number'] ?? '';
                        $cardHolder = $_POST['card_name'] ?? '';
                        $expiryDate = $_POST['expiry_date'] ?? '';
                        $cvv = $_POST['cvv'] ?? '';
                        
                        if (!empty($cardNumber) && !empty($cardHolder) && !empty($expiryDate) && !empty($cvv)) {
                            $cardLast4 = substr(str_replace(' ', '', $cardNumber), -4);
                            $cardType = $this->detectCardType($cardNumber);
                            
                            $paymentDetails = json_encode([
                                'card_last4' => $cardLast4,
                                'card_holder' => $cardHolder,
                                'card_type' => $cardType
                            ]);
                            
                            // Save card if requested
                            if (isset($_POST['save_card']) && $_POST['save_card'] == '1') {
                                $this->saveCard($userId, $cardNumber, $cardHolder, $expiryDate);
                            }
                        }
                    }
                    
                    // Update transaction with payment - use userId instead of uid
                    $stmt = $conn->prepare("
                        UPDATE transactions 
                        SET fineStatus = 'paid',
                            finePaymentDate = NOW(),
                            finePaymentMethod = ?,
                            finePaymentDetails = ?
                        WHERE tid = ? AND userId = ?
                    ");
                    $stmt->bind_param("ssss", $paymentMethod, $paymentDetails, $borrowId, $userId);
                    $stmt->execute();
                    $stmt->close();
                    
                    $conn->commit();
                    $_SESSION['success'] = 'Payment successful! Fine has been cleared.';
                } catch (\Exception $e) {
                    $conn->rollback();
                    error_log("Payment error: " . $e->getMessage());
                    $_SESSION['error'] = 'Payment failed: ' . $e->getMessage();
                }
                
                $this->redirect('faculty/fines');
                return;
            }
        }

        // Get all fines (both paid and unpaid) using transactionModel
        $fines = $this->transactionModel->getFinesByUserId($userId);
        
        $this->data['fines'] = $fines;
        $this->view('faculty/fines', $this->data);
    }

    /**
     * Detect card type from card number
     */
    private function detectCardType($cardNumber)
    {
        $cardNumber = str_replace(' ', '', $cardNumber);
        
        if (preg_match('/^4/', $cardNumber)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'Amex';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'Discover';
        }
        
        return 'Unknown';
    }
    
    /**
     * Save card details for future use
     */
    private function saveCard($userId, $cardNumber, $cardHolder, $expiry)
    {
        global $conn;
        
        $cardNumber = str_replace(' ', '', $cardNumber);
        $cardLast4 = substr($cardNumber, -4);
        $cardType = $this->detectCardType($cardNumber);
        
        // Parse expiry (MM/YY format)
        list($month, $year) = explode('/', $expiry);
        $year = '20' . $year;
        
        // Check if card already exists
        $stmt = $conn->prepare("
            SELECT id FROM saved_cards 
            WHERE userid = ? AND cardLastFour = ? AND expiryMonth = ? AND expiryYear = ?
        ");
        $stmt->bind_param("ssss", $userId, $cardLast4, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return;
        }
        $stmt->close();
        
        // Insert new card
        $stmt = $conn->prepare("
            INSERT INTO saved_cards 
            (userid, cardType, cardLastFour, cardHolderName, expiryMonth, expiryYear, createdAt)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssssss", $userId, $cardType, $cardLast4, $cardHolder, $month, $year);
        $stmt->execute();
        $stmt->close();
    }

    public function returnBook()
    {
        $this->requireLogin(['Faculty']);
        
        // Only show books that have been returned by this user
        $userId = $_SESSION['userId'];
        // Fetch only returned books (returnDate is not null)
        $returnedBooks = $this->borrowModel->getBorrowHistory($userId);
        $returnedBooks = array_filter($returnedBooks, function($book) {
            return !empty($book['returnDate']);
        });
        $this->data['returnedBooks'] = $returnedBooks;
        $this->view('faculty/return', $this->data);
    }

    public function borrowHistory()
    {
        $this->requireLogin(['Faculty']);
        $userId = $_SESSION['userId'];
        $history = $this->borrowModel->getBorrowHistory($userId);

        // Add renewal info for active borrows
        foreach ($history as &$item) {
            if (empty($item['returnDate'])) {
                $item['renewalInfo'] = $this->borrowModel->getRenewalInfo($item['id'] ?? 0, $userId);
            }
        }
        unset($item);

        $this->data['history'] = $history;
        $this->view('faculty/borrow-history', $this->data);
    }

    /**
     * Renew (extend) a borrowed book's due date
     */
    public function renew()
    {
        $this->requireLogin(['Faculty']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('faculty/borrow-history');
            return;
        }

        $borrowId = intval($_POST['borrow_id'] ?? 0);
        $userId = $_SESSION['userId'];

        if ($borrowId <= 0) {
            $_SESSION['error'] = 'Invalid borrow record.';
            $this->redirect('faculty/borrow-history');
            return;
        }

        $result = $this->borrowModel->renewBook($borrowId, $userId);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        $this->redirect('faculty/borrow-history');
    }

    public function profile()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_profile'])) {
                // Handle profile image upload
                $profileImagePath = null;
                if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = APP_ROOT . '/public/assets/images/users/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileInfo = pathinfo($_FILES['profileImage']['name']);
                    $extension = strtolower($fileInfo['extension']);
                    
                    // Validate file type
                    $allowedTypes = ['jpg', 'jpeg', 'png'];
                    if (!in_array($extension, $allowedTypes)) {
                        $_SESSION['error'] = 'Only JPG and PNG images are allowed';
                        $this->redirect('faculty/profile');
                        return;
                    }
                    
                    // Validate file size (2MB max)
                    if ($_FILES['profileImage']['size'] > 2 * 1024 * 1024) {
                        $_SESSION['error'] = 'File size must be less than 2 MB';
                        $this->redirect('faculty/profile');
                        return;
                    }
                    
                    // Delete old profile images for this user
                    $patterns = [$userId . '.jpg', $userId . '.jpeg', $userId . '.png'];
                    foreach ($patterns as $pattern) {
                        $oldFile = $uploadDir . $pattern;
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    
                    // Save new image
                    $newFileName = $userId . '.' . $extension;
                    $targetPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetPath)) {
                        $profileImagePath = 'assets/images/users/' . $newFileName;
                    } else {
                        $_SESSION['error'] = 'Failed to upload profile image';
                        $this->redirect('faculty/profile');
                        return;
                    }
                }
                
                // Validate using ValidationHelper
                $errors = ValidationHelper::validateProfileUpdate($_POST);
                
                if (!empty($errors)) {
                    $_SESSION['validation_errors'] = $errors;
                    ValidationHelper::setFormData($_POST);
                    $_SESSION['error'] = 'Please fix the validation errors below';
                    header('Location: ' . BASE_URL . 'faculty/profile');
                    exit;
                }
                
                ValidationHelper::clearValidation();
                
                $data = [
                    'username' => $_POST['name'] ?? '',
                    'emailId' => $_POST['email'] ?? '',
                    'gender' => $_POST['gender'] ?? '',
                    'dob' => $_POST['dob'] ?? '',
                    'phoneNumber' => $_POST['phoneNumber'] ?? '',
                    'address' => $_POST['address'] ?? '',
                ];
                
                // Add profile image path if uploaded
                if ($profileImagePath) {
                    $data['profileImage'] = $profileImagePath;
                }
                
                if ($this->userModel->updateProfile($userId, $data)) {
                    $_SESSION['success'] = 'Profile updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update profile';
                }
            } elseif (isset($_POST['change_password'])) {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = 'New password and confirmation do not match.';
                } else {
                    if ($this->userModel->changePassword($userId, $currentPassword, $newPassword)) {
                        $_SESSION['success'] = 'Password changed successfully.';
                    } else {
                        $_SESSION['error'] = $this->userModel->getLastError() ?? 'Failed to change password.';
                    }
                }
            }
            
            $this->redirect('faculty/profile');
            return;
        }
        
        $user = $this->userModel->findById($userId);
        
        $this->data['user'] = $user;
        $this->view('faculty/profile', $this->data);
    }

    public function search()
    {
        $this->requireLogin(['Faculty']);
        $searchTerm = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        $books = $this->bookModel->advancedSearch($searchTerm, $category);
        $this->data['books'] = $books;
        $this->data['searchTerm'] = $searchTerm;
        $this->data['category'] = $category;
        $this->view('faculty/search', $this->data);
    }

    public function feedback()
    {
        $this->requireLogin(['Faculty']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle feedback submission
            $feedback = $_POST['feedback'] ?? '';
            $bookId = $_POST['book_id'] ?? 0;
            // Save feedback logic here
            $_SESSION['success'] = 'Feedback submitted successfully';
        }
        $this->view('faculty/feedback', $this->data);
    }

    public function bookRequest()
    {
        $this->requireLogin(['Faculty']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle book request
            $bookTitle = $_POST['book_title'] ?? '';
            $author = $_POST['author'] ?? '';
            $reason = $_POST['reason'] ?? '';
            // Save book request logic here
            $_SESSION['success'] = 'Book request submitted successfully';
        }
        $this->view('faculty/book-request', $this->data);
    }

    public function notifications()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        // Handle mark as read
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
            $notificationId = $_POST['notification_id'] ?? 0;
            if ($notificationId) {
                global $mysqli;
                $sql = "UPDATE notifications SET isRead = 1 WHERE id = ? AND userId = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('is', $notificationId, $userId);
                $stmt->execute();
                $stmt->close();
            }
            $this->redirect('faculty/notifications');
            return;
        }
        
        // Get notifications
        global $mysqli;
        $sql = "SELECT 
                    n.id,
                    n.userId,
                    n.title,
                    n.message,
                    n.type,
                    n.priority,
                    n.isRead,
                    n.relatedId,
                    n.createdAt,
                    u.userType,
                    u.username,
                    u.emailId
                FROM notifications n
                LEFT JOIN users u ON n.userId = u.userId
                WHERE n.userId = ? OR n.userId IS NULL
                ORDER BY n.isRead ASC, n.createdAt DESC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
        
        $this->data['notifications'] = $notifications;
        $this->data['userType'] = 'Faculty';
        $this->view('faculty/notifications', $this->data);
    }

    public function borrowedBooks()
    {
        $this->requireLogin(['Faculty']);
        
        $userId = $_SESSION['userId'];
        
        // Get all currently borrowed books (not returned)
        $borrowedBooks = $this->borrowModel->getAllBorrowedBooks($userId);
        
        // Get filter if provided
        $status = $_GET['status'] ?? 'all';
        
        if ($status === 'overdue') {
            $borrowedBooks = array_filter($borrowedBooks, function($book) {
                return !empty($book['isOverdue']);
            });
        }
        
        $this->data['borrowedBooks'] = $borrowedBooks;
        $this->data['status'] = $status;
        
        $this->view('faculty/borrowed-books', $this->data);
    }

    /**
     * Show payment form for faculty fines
     */
    public function showPaymentForm()
    {
        // Use consistent session check
        $this->requireLogin(['Faculty']);

        global $conn;
        
        // Use the correct session variable name
        $userId = $_SESSION['userId'];
        
        // Get transaction details from URL parameters
        $borrowId = $_GET['borrow_id'] ?? '';
        $amount = $_GET['amount'] ?? 0;
        $bookName = $_GET['book_name'] ?? '';
        
        // Fetch transaction details from database
        $transaction = ['tid' => $borrowId, 'fineAmount' => $amount, 'bookName' => $bookName];
        
        if (!empty($borrowId)) {
            $stmt = $conn->prepare("
                SELECT 
                    t.*,
                    b.bookName,
                    b.isbn,
                    t.fineAmount
                FROM transactions t
                JOIN books b ON t.isbn = b.isbn
                WHERE t.tid = ? AND t.userId = ?
            ");
            $stmt->bind_param("ss", $borrowId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $transaction = $result->fetch_assoc();
                $amount = $transaction['fineAmount'] ?? $amount;
                $bookName = $transaction['bookName'] ?? $bookName;
            }
            $stmt->close();
        }
        
        // Fetch saved cards for this user
        $savedCards = [];
        $stmt = $conn->prepare("
            SELECT * FROM saved_cards 
            WHERE userid = ? 
            ORDER BY createdAt DESC
        ");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $savedCards[] = $row;
        }
        $stmt->close();
        
        // Include the view
        extract([
            'transaction' => $transaction,
            'amount' => $amount,
            'savedCards' => $savedCards,
            'pay_all' => false
        ]);
        
        include APP_ROOT . '/views/faculty/payment-form.php';
    }

    /**
     * Process fine payment for faculty
     */
    public function payFine()
    {
        // This method is called when form is submitted to /faculty/fines with pay_online
        // Redirect to fines method which handles both cash and online payments
        $this->fines();
    }
} // End of FacultyController class