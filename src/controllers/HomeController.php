<?php

namespace App\Controllers;

class HomeController
{
    /**
     * Display home page with real statistics
     */
    public function index()
    {
        global $mysqli;
        
        // Fetch total books
        $totalBooksResult = $mysqli->query("SELECT COUNT(*) as count FROM books");
        $totalBooks = $totalBooksResult->fetch_assoc()['count'];
        
        // Fetch total active users (verified users)
        $activeUsersResult = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE isVerified = 1");
        $activeUsers = $activeUsersResult->fetch_assoc()['count'];
        
        // Fetch unique categories/publishers
        $categoriesResult = $mysqli->query("SELECT COUNT(DISTINCT publisherName) as count FROM books WHERE publisherName IS NOT NULL AND publisherName != ''");
        $categories = $categoriesResult->fetch_assoc()['count'];
        
        // Fetch total transactions
        $transactionsResult = $mysqli->query("SELECT COUNT(*) as count FROM transactions");
        $totalTransactions = $transactionsResult->fetch_assoc()['count'];
        
        // Fetch available books count
        $availableBooksResult = $mysqli->query("SELECT SUM(available) as count FROM books");
        $availableBooks = $availableBooksResult->fetch_assoc()['count'] ?? 0;
        
        // Fetch borrowed books count
        $borrowedBooksResult = $mysqli->query("SELECT COUNT(*) as count FROM transactions WHERE returnDate IS NULL");
        $activeBorrowings = $borrowedBooksResult->fetch_assoc()['count'];
        
        // Pass data to view
        $pageTitle = 'Home - Library Management System';
        $stats = [
            'totalBooks' => $totalBooks,
            'activeUsers' => $activeUsers,
            'categories' => $categories,
            'totalTransactions' => $totalTransactions,
            'availableBooks' => $availableBooks,
            'activeBorrowings' => $activeBorrowings
        ];
        
        include APP_ROOT . '/views/home/index.php';
    }

    /**
     * Display about page
     */
    public function about()
    {
        $pageTitle = 'About Us';
        include APP_ROOT . '/views/home/about.php';
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        $pageTitle = 'Contact Us';
        include APP_ROOT . '/views/home/contact.php';
    }

    /**
     * Display library page
     */
    public function library()
    {
        global $mysqli;
        
        // Fetch all books for public browsing
        $sql = "SELECT 
                    isbn,
                    bookName,
                    authorName,
                    publisherName,
                    available,
                    totalCopies,
                    bookImage
                FROM books
                WHERE available > 0
                ORDER BY bookName ASC";
        
        $result = $mysqli->query($sql);
        
        $books = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
        }
        
        $pageTitle = 'Browse Library';
        include APP_ROOT . '/views/home/library.php';
    }

    /**
     * Video debug page
     */
    public function videoDebug()
    {
        $pageTitle = 'Video Debug';
        include APP_ROOT . '/views/home/video-debug.php';
    }
}
?>
