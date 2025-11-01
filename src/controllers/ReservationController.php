<?php

namespace App\Controllers;

class ReservationController
{
    /**
     * Handle book reservation
     */
    public function reserve()
    {
        // Check if user is logged in
        if (!isset($_SESSION['userId'])) {
            header('Location: /login');
            exit();
        }

        $isbn = $_GET['isbn'] ?? '';
        $userId = $_SESSION['userId'];

        if (empty($isbn)) {
            $_SESSION['error'] = 'Invalid book ISBN';
            header('Location: /faculty/books');
            exit();
        }

        global $pdo;

        if (!$pdo) {
            $_SESSION['error'] = 'Database connection failed';
            header('Location: /faculty/books');
            exit();
        }

        try {
            // Check if book exists
            $bookStmt = $pdo->prepare("SELECT * FROM books WHERE isbn = ?");
            $bookStmt->execute([$isbn]);
            $book = $bookStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$book) {
                $_SESSION['error'] = 'Book not found';
                header('Location: /faculty/books');
                exit();
            }

            // Check if user already has an active reservation
            $checkStmt = $pdo->prepare("
                SELECT * FROM book_reservations 
                WHERE userId = ? AND isbn = ? AND reservationStatus IN ('Active', 'Notified')
            ");
            $checkStmt->execute([$userId, $isbn]);

            if ($checkStmt->fetch()) {
                $_SESSION['error'] = 'You already have an active reservation for this book';
                header('Location: /faculty/book-request');
                exit();
            }

            // Create reservation
            $insertStmt = $pdo->prepare("
                INSERT INTO book_reservations (userId, isbn, reservationStatus, createdAt)
                VALUES (?, ?, 'Active', NOW())
            ");

            if ($insertStmt->execute([$userId, $isbn])) {
                $_SESSION['success'] = 'Book reserved successfully! You will be notified when it becomes available.';
                header('Location: /faculty/book-request');
            } else {
                $_SESSION['error'] = 'Failed to reserve book. Please try again.';
                header('Location: /faculty/books');
            }

        } catch (\PDOException $e) {
            error_log("Reservation error: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while reserving the book';
            header('Location: /faculty/books');
        }

        exit();
    }
}
