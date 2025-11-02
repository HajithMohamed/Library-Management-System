<?php

namespace App\Services;

class AdminService
{
    private $db;

    public function __construct()
    {
        global $mysqli;
        $this->db = $mysqli;
    }

    /**
     * Generate a report based on type and date range.
     *
     * @param string $reportType
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function generateReport(string $reportType, string $startDate, string $endDate): array
    {
        switch ($reportType) {
            case 'borrowing':
                return $this->generateBorrowingReport($startDate, $endDate);
            case 'fines':
                return $this->generateFinesReport($startDate, $endDate);
            case 'users':
                return $this->generateUsersReport($startDate, $endDate);
            case 'books':
                return $this->generateBooksReport($startDate, $endDate);
            case 'overview':
            default:
                return $this->generateOverviewReport($startDate, $endDate);
        }
    }

    private function generateOverviewReport(string $startDate, string $endDate): array
    {
        $query = "SELECT 
            (SELECT COUNT(*) FROM books_borrowed WHERE borrowDate BETWEEN ? AND ?) as total_transactions,
            (SELECT COUNT(*) FROM users WHERE createdAt BETWEEN ? AND ?) as total_users,
            (SELECT COUNT(*) FROM books) as total_books,
            (SELECT SUM(fineAmount) FROM fines WHERE fineDate BETWEEN ? AND ?) as total_fines,
            (SELECT COUNT(*) FROM books_borrowed WHERE status = 'Active' AND borrowDate BETWEEN ? AND ?) as active_borrowings,
            (SELECT COUNT(*) FROM books_borrowed WHERE status = 'Overdue' AND borrowDate BETWEEN ? AND ?) as overdue_books
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssssssssss", $startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?? [];
    }

    private function generateBorrowingReport(string $startDate, string $endDate): array
    {
        $query = "SELECT 
            COUNT(*) as total_borrowings,
            SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as returned_books,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_loans
            FROM books_borrowed 
            WHERE borrowDate BETWEEN ? AND ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?? [];
    }
    
    private function generateFinesReport(string $startDate, string $endDate): array
    {
        $query = "SELECT 
            SUM(fineAmount) as total_fines,
            SUM(CASE WHEN status = 'Paid' THEN fineAmount ELSE 0 END) as collected_fines,
            SUM(CASE WHEN status = 'Unpaid' THEN fineAmount ELSE 0 END) as pending_fines,
            (SELECT COUNT(DISTINCT borrowId) FROM fines WHERE fineDate BETWEEN ? AND ?) as overdue_books
            FROM fines 
            WHERE fineDate BETWEEN ? AND ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssss", $startDate, $endDate, $startDate, $endDate);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc() ?? [];
        $report['period'] = "$startDate to $endDate";
        return $report;
    }

    private function generateUsersReport(string $startDate, string $endDate): array
    {
        $query = "SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM users WHERE isVerified = 1) as active_users,
            (SELECT COUNT(*) FROM users WHERE createdAt BETWEEN ? AND ?) as new_users
            ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc() ?? [];
        $report['period'] = "$startDate to $endDate";
        return $report;
    }

    private function generateBooksReport(string $startDate, string $endDate): array
    {
        $report = [];
        $report['total_books'] = $this->db->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
        $report['available_books'] = $this->db->query("SELECT SUM(available) as c FROM books")->fetch_assoc()['c'];
        $report['borrowed_books'] = $this->db->query("SELECT SUM(borrowed) as c FROM books")->fetch_assoc()['c'];
        $report['period'] = "$startDate to $endDate";

        $pop_query = "SELECT b.bookName, b.authorName, COUNT(bb.id) as borrow_count 
            FROM books_borrowed bb
            JOIN books b ON bb.isbn = b.isbn
            WHERE bb.borrowDate BETWEEN ? AND ?
            GROUP BY b.bookName, b.authorName
            ORDER BY borrow_count DESC
            LIMIT 10";
        $stmt = $this->db->prepare($pop_query);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $report['popular_books'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return $report;
    }
}
