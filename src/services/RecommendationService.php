<?php

namespace App\Services;

use PDO;
use PDOException;

class RecommendationService
{
    
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getTeaserRecs($categories = []) {
        try {
            // Track browsed categories in session
            if (!isset($_SESSION['browsed_categories'])) {
                $_SESSION['browsed_categories'] = [];
            }
            
            // Merge new categories
            $_SESSION['browsed_categories'] = array_unique(array_merge($_SESSION['browsed_categories'], $categories));
            
            if (empty($_SESSION['browsed_categories'])) {
                // Default: show trending books
                $stmt = $this->pdo->prepare("
                    SELECT b.*, COUNT(t.tid) as borrow_count 
                    FROM books b
                    LEFT JOIN transactions t ON b.isbn = t.isbn AND t.borrowDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    WHERE b.available > 0
                    GROUP BY b.isbn
                    ORDER BY borrow_count DESC
                    LIMIT 3
                ");
                $stmt->execute();
            } else {
                // Recommendations based on browsed categories
                $placeholders = str_repeat('?,', count($_SESSION['browsed_categories']) - 1) . '?';
                
                $stmt = $this->pdo->prepare("
                    SELECT b.*, COUNT(t.tid) as borrow_count 
                    FROM books b
                    LEFT JOIN transactions t ON b.isbn = t.isbn AND t.borrowDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    WHERE b.category IN ($placeholders) AND b.available > 0
                    GROUP BY b.isbn
                    ORDER BY borrow_count DESC
                    LIMIT 3
                ");
                $stmt->execute($_SESSION['browsed_categories']);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Recommendation error: " . $e->getMessage());
            return [];
        }
    }
}
