<?php
require_once APP_ROOT . '/config/config.php';

class AdminAnalyticsController {
    private $conn;
    
    public function __construct() {
        $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        mysqli_set_charset($this->conn, 'utf8mb4');
    }
    
    public function index() {
        // Check if user is admin
        if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'Admin') {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        // Get analytics data
        $stats = $this->getOverallStats();
        $borrowTrends = $this->getBorrowTrends();
        $topBooks = $this->getTopBooks();
        $categoryDistribution = $this->getCategoryDistribution();
        $userActivity = $this->getUserActivity();
        $fineStats = $this->getFineStats();
        $monthlyStats = $this->getMonthlyStats();
        $recentActivities = $this->getRecentActivities();
        
        // Load the view
        include APP_ROOT . '/views/admin/analytics.php';
    }
    
    private function getOverallStats() {
        $stats = [];
        
        // Total books
        $result = $this->conn->query("SELECT COUNT(*) as total, SUM(totalCopies) as copies, SUM(available) as available, SUM(borrowed) as borrowed FROM books");
        $stats['books'] = $result->fetch_assoc();
        
        // Total users
        $result = $this->conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN userType='Student' THEN 1 ELSE 0 END) as students, SUM(CASE WHEN userType='Faculty' THEN 1 ELSE 0 END) as faculty FROM users WHERE userType != 'Admin'");
        $stats['users'] = $result->fetch_assoc();
        
        // Active transactions
        $result = $this->conn->query("SELECT COUNT(*) as total FROM transactions WHERE status='Issued'");
        $stats['activeTransactions'] = $result->fetch_assoc()['total'];
        
        // Pending requests
        $result = $this->conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE status='Pending'");
        $stats['pendingRequests'] = $result->fetch_assoc()['total'];
        
        // Total fines
        $result = $this->conn->query("SELECT SUM(fineAmount) as total, SUM(CASE WHEN fineStatus='pending' THEN fineAmount ELSE 0 END) as pending, SUM(CASE WHEN fineStatus='paid' THEN fineAmount ELSE 0 END) as paid FROM transactions WHERE fineAmount > 0");
        $stats['fines'] = $result->fetch_assoc();
        
        // Overdue books
        $result = $this->conn->query("SELECT COUNT(*) as total FROM transactions WHERE status='Issued' AND dueDate < CURDATE()");
        $stats['overdue'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
    
    private function getBorrowTrends() {
        $sql = "SELECT DATE(issueDate) as date, COUNT(*) as count 
                FROM transactions 
                WHERE issueDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(issueDate)
                ORDER BY date ASC";
        
        $result = $this->conn->query($sql);
        $trends = [];
        
        while ($row = $result->fetch_assoc()) {
            $trends[] = $row;
        }
        
        return $trends;
    }
    
    private function getTopBooks() {
        $sql = "SELECT b.bookName, b.authorName, b.isbn, COUNT(t.id) as borrowCount
                FROM books b
                LEFT JOIN transactions t ON b.isbn = t.isbn
                WHERE t.issueDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                GROUP BY b.isbn
                ORDER BY borrowCount DESC
                LIMIT 10";
        
        $result = $this->conn->query($sql);
        $books = [];
        
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
        
        return $books;
    }
    
    private function getCategoryDistribution() {
        $sql = "SELECT category, COUNT(*) as count, SUM(totalCopies) as copies
                FROM books
                GROUP BY category
                ORDER BY count DESC";
        
        $result = $this->conn->query($sql);
        $distribution = [];
        
        while ($row = $result->fetch_assoc()) {
            $distribution[] = $row;
        }
        
        return $distribution;
    }
    
    private function getUserActivity() {
        $sql = "SELECT u.userType, COUNT(DISTINCT t.userId) as activeUsers, COUNT(t.id) as transactions
                FROM transactions t
                JOIN users u ON t.userId = u.userId
                WHERE t.issueDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY u.userType";
        
        $result = $this->conn->query($sql);
        $activity = [];
        
        while ($row = $result->fetch_assoc()) {
            $activity[] = $row;
        }
        
        return $activity;
    }
    
    private function getFineStats() {
        $sql = "SELECT 
                    DATE_FORMAT(issueDate, '%Y-%m') as month,
                    SUM(fineAmount) as totalFines,
                    SUM(CASE WHEN fineStatus='paid' THEN fineAmount ELSE 0 END) as paidFines,
                    SUM(CASE WHEN fineStatus='pending' THEN fineAmount ELSE 0 END) as pendingFines
                FROM transactions
                WHERE fineAmount > 0 AND issueDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month ASC";
        
        $result = $this->conn->query($sql);
        $stats = [];
        
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
    
    private function getMonthlyStats() {
        $sql = "SELECT 
                    DATE_FORMAT(issueDate, '%Y-%m') as month,
                    COUNT(*) as issues,
                    SUM(CASE WHEN status='Returned' THEN 1 ELSE 0 END) as returns
                FROM transactions
                WHERE issueDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month ASC";
        
        $result = $this->conn->query($sql);
        $stats = [];
        
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
    
    private function getRecentActivities() {
        $sql = "SELECT 
                    'Transaction' as type,
                    CONCAT(u.emailId, ' borrowed ', b.bookName) as description,
                    t.issueDate as timestamp
                FROM transactions t
                JOIN users u ON t.userId = u.userId
                JOIN books b ON t.isbn = b.isbn
                WHERE t.issueDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                
                UNION ALL
                
                SELECT 
                    'Request' as type,
                    CONCAT(u.emailId, ' requested ', b.bookName) as description,
                    br.requestDate as timestamp
                FROM borrow_requests br
                JOIN users u ON br.userId = u.userId
                JOIN books b ON br.isbn = b.isbn
                WHERE br.requestDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                
                ORDER BY timestamp DESC
                LIMIT 20";
        
        $result = $this->conn->query($sql);
        $activities = [];
        
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        return $activities;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
