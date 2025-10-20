<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Book;

class UserService
{
    private $userModel;
    private $transactionModel;
    private $bookModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->transactionModel = new Transaction();
        $this->bookModel = new Book();
    }

    /**
     * Calculate fine for overdue books
     */
    public function calculateFine($borrowDate, $returnDate = null)
    {
        $returnDate = $returnDate ?: date('Y-m-d');
        $borrowTimestamp = strtotime($borrowDate);
        $returnTimestamp = strtotime($returnDate);
        
        $daysDiff = ($returnTimestamp - $borrowTimestamp) / (60 * 60 * 24);
        $maxDays = 14; // 2 weeks borrowing period
        
        if ($daysDiff > $maxDays) {
            $overdueDays = $daysDiff - $maxDays;
            return $overdueDays * 5; // 5 rupees per day fine
        }
        
        return 0;
    }

    /**
     * Get user borrowing statistics
     */
    public function getUserBorrowingStats($userId)
    {
        $borrowedBooks = $this->transactionModel->getBorrowedBooks($userId);
        $transactionHistory = $this->transactionModel->getUserTransactionHistory($userId);
        
        $stats = [
            'total_borrowed' => count($borrowedBooks),
            'overdue_books' => 0,
            'total_fines' => 0,
            'books_returned' => 0,
            'favorite_authors' => [],
            'favorite_publishers' => []
        ];
        
        // Calculate overdue books and fines
        foreach ($borrowedBooks as $book) {
            $fine = $this->calculateFine($book['borrowDate']);
            if ($fine > 0) {
                $stats['overdue_books']++;
                $stats['total_fines'] += $fine;
            }
        }
        
        // Count returned books
        $stats['books_returned'] = count(array_filter($transactionHistory, function($transaction) {
            return !empty($transaction['returnDate']);
        }));
        
        // Get favorite authors and publishers
        $authors = [];
        $publishers = [];
        foreach ($transactionHistory as $transaction) {
            if (!empty($transaction['authorName'])) {
                $authors[$transaction['authorName']] = ($authors[$transaction['authorName']] ?? 0) + 1;
            }
            if (!empty($transaction['publisherName'])) {
                $publishers[$transaction['publisherName']] = ($publishers[$transaction['publisherName']] ?? 0) + 1;
            }
        }
        
        arsort($authors);
        arsort($publishers);
        
        $stats['favorite_authors'] = array_slice(array_keys($authors), 0, 5);
        $stats['favorite_publishers'] = array_slice(array_keys($publishers), 0, 5);
        
        return $stats;
    }

    /**
     * Get user reading history
     */
    public function getUserReadingHistory($userId, $limit = 20)
    {
        return $this->transactionModel->getUserTransactionHistory($userId, $limit);
    }

    /**
     * Get recommended books for user
     */
    public function getRecommendedBooks($userId, $limit = 10)
    {
        $userStats = $this->getUserBorrowingStats($userId);
        $recommendedBooks = [];
        
        // Get books from favorite authors
        if (!empty($userStats['favorite_authors'])) {
            $authorBooks = $this->bookModel->searchBooks('');
            foreach ($authorBooks as $book) {
                if (in_array($book['authorName'], $userStats['favorite_authors']) && $book['available'] > 0) {
                    $recommendedBooks[] = $book;
                }
            }
        }
        
        // Get popular books if no author recommendations
        if (empty($recommendedBooks)) {
            $recommendedBooks = $this->bookModel->getPopularBooks($limit);
        }
        
        return array_slice($recommendedBooks, 0, $limit);
    }

    /**
     * Check if user can borrow more books
     */
    public function canBorrowMoreBooks($userId, $userType)
    {
        $borrowedCount = $this->transactionModel->getActiveBorrowingCount($userId);
        $maxBooks = ($userType === 'Faculty') ? 5 : 3;
        
        return $borrowedCount < $maxBooks;
    }

    /**
     * Get user's borrowing limit
     */
    public function getBorrowingLimit($userType)
    {
        return ($userType === 'Faculty') ? 5 : 3;
    }

    /**
     * Get user's current borrowing status
     */
    public function getBorrowingStatus($userId)
    {
        $borrowedBooks = $this->transactionModel->getBorrowedBooks($userId);
        $user = $this->userModel->getUserById($userId);
        
        $status = [
            'can_borrow' => $this->canBorrowMoreBooks($userId, $user['userType']),
            'borrowed_count' => count($borrowedBooks),
            'max_books' => $this->getBorrowingLimit($user['userType']),
            'overdue_count' => 0,
            'total_fines' => 0
        ];
        
        foreach ($borrowedBooks as $book) {
            $fine = $this->calculateFine($book['borrowDate']);
            if ($fine > 0) {
                $status['overdue_count']++;
                $status['total_fines'] += $fine;
            }
        }
        
        return $status;
    }

    /**
     * Update user's last login time
     */
    public function updateLastLogin($userId)
    {
        // This would typically update a last_login field in the users table
        // For now, we'll just log the activity
        error_log("User {$userId} logged in at " . date('Y-m-d H:i:s'));
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        $endDate = date('Y-m-d');
        
        $transactions = $this->transactionModel->getTransactionsByDateRange($startDate, $endDate);
        $userTransactions = array_filter($transactions, function($transaction) use ($userId) {
            return $transaction['userId'] === $userId;
        });
        
        $summary = [
            'total_activities' => count($userTransactions),
            'books_borrowed' => 0,
            'books_returned' => 0,
            'fines_paid' => 0,
            'activity_by_day' => []
        ];
        
        foreach ($userTransactions as $transaction) {
            $date = $transaction['borrowDate'];
            if (!isset($summary['activity_by_day'][$date])) {
                $summary['activity_by_day'][$date] = 0;
            }
            $summary['activity_by_day'][$date]++;
            
            if (empty($transaction['returnDate'])) {
                $summary['books_borrowed']++;
            } else {
                $summary['books_returned']++;
            }
            
            if ($transaction['fine'] > 0) {
                $summary['fines_paid'] += $transaction['fine'];
            }
        }
        
        return $summary;
    }

    /**
     * Validate user profile data
     */
    public function validateProfileData($data, $userId = null)
    {
        $errors = [];
        
        if (empty($data['emailId'])) {
            $errors[] = 'Email address is required';
        } elseif (!filter_var($data['emailId'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif ($this->userModel->emailExists($data['emailId'], $userId)) {
            $errors[] = 'Email address is already taken';
        }
        
        if (empty($data['phoneNumber'])) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^\d{10}$/', $data['phoneNumber'])) {
            $errors[] = 'Phone number must be 10 digits';
        }
        
        if (empty($data['gender'])) {
            $errors[] = 'Gender is required';
        } elseif (!in_array($data['gender'], ['Male', 'Female', 'Other'])) {
            $errors[] = 'Invalid gender selection';
        }
        
        if (empty($data['dob'])) {
            $errors[] = 'Date of birth is required';
        } elseif (strtotime($data['dob']) > strtotime('-13 years')) {
            $errors[] = 'You must be at least 13 years old';
        }
        
        if (empty($data['address'])) {
            $errors[] = 'Address is required';
        } elseif (strlen($data['address']) < 10) {
            $errors[] = 'Address must be at least 10 characters long';
        }
        
        return $errors;
    }
}
?>
