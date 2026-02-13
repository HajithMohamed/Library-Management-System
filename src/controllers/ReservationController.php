<?php

namespace App\Controllers;

use App\Helpers\NotificationHelper;

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
            NotificationHelper::error('Invalid book ISBN');
            header('Location: /faculty/books');
            exit();
        }

        global $mysqli;

        if (!$mysqli) {
            NotificationHelper::error('Database connection failed');
            header('Location: /faculty/books');
            exit();
        }

        try {
            // Check if book exists
            $bookStmt = $mysqli->prepare("SELECT * FROM books WHERE isbn = ?");
            $bookStmt->bind_param("s", $isbn);
            $bookStmt->execute();
            $result = $bookStmt->get_result();
            $book = $result->fetch_assoc();
            $bookStmt->close();

            if (!$book) {
                NotificationHelper::error('Book not found');
                header('Location: /faculty/books');
                exit();
            }

            // Check if user already has an active reservation
            $checkStmt = $mysqli->prepare("
                SELECT * FROM book_reservations 
                WHERE userId = ? AND isbn = ? AND reservationStatus IN ('Active', 'Notified')
            ");
            $checkStmt->bind_param("ss", $userId, $isbn);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->fetch_assoc()) {
                NotificationHelper::error('You already have an active reservation for this book');
                $checkStmt->close();
                header('Location: /faculty/book-request');
                exit();
            }
            $checkStmt->close();

            // Create reservation
            $insertStmt = $mysqli->prepare("
                INSERT INTO book_reservations (userId, isbn, reservationStatus, createdAt)
                VALUES (?, ?, 'Active', NOW())
            ");
            $insertStmt->bind_param("ss", $userId, $isbn);

            if ($insertStmt->execute()) {
                NotificationHelper::success('Book reserved successfully! You will be notified when it becomes available.');
                $insertStmt->close();
                header('Location: /faculty/book-request');
            } else {
                $insertStmt->close();
                NotificationHelper::error('Failed to reserve book. Please try again.');
                header('Location: /faculty/books');
            }

        } catch (\Exception $e) {
            error_log("Reservation error: " . $e->getMessage());
            NotificationHelper::error('An error occurred while reserving the book');
            header('Location: /faculty/books');
        }

        exit();
    }
}
