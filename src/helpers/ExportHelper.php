<?php

namespace App\Helpers;

use PDO;
use PDOException;

class ExportHelper
{
    public static function exportFavorites($userId)
    {
        try {
            require_once __DIR__ . '/../config/dbConnection.php';
            
            $stmt = $pdo->prepare("
                SELECT b.isbn, b.bookName, b.authorName, b.category, 
                       f.notes, f.createdAt as added_on
                FROM favorites f
                JOIN books b ON f.isbn = b.isbn
                WHERE f.userId = ?
                ORDER BY f.createdAt DESC
            ");
            $stmt->execute([$userId]);
            $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=my-reading-list-' . date('Y-m-d') . '.csv');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, ['ISBN', 'Book Name', 'Author', 'Category', 'Notes', 'Added On']);
            
            // Data rows
            foreach ($favorites as $fav) {
                fputcsv($output, [
                    $fav['isbn'],
                    $fav['bookName'],
                    $fav['authorName'],
                    $fav['category'],
                    $fav['notes'] ?? '',
                    $fav['added_on']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (PDOException $e) {
            error_log("Export error: " . $e->getMessage());
            header("Location: /index.php?route=favorites&error=export_failed");
            exit;
        }
    }
}
