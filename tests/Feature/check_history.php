<?php
/**
 * Feature Test: Borrow History
 * Tests the retrieval of user borrow history with transaction details
 */

// Load configuration from the project root
require_once __DIR__ . '/../../src/config/config.php';

// Use the database connection from config
$mysqli = $GLOBALS['mysqli'];

$userId = 'STU2025002';

$sql = "SELECT DISTINCT 
        t.tid,
        t.userId,
        t.isbn,
        t.borrowDate,
        t.returnDate,
        t.fineAmount,
        t.fineStatus,
        COALESCE(b.bookName, CONCAT('Book (ISBN: ', t.isbn, ')')) as bookName,
        COALESCE(b.authorName, 'Unknown Author') as authorName,
        DATE_ADD(t.borrowDate, INTERVAL 14 DAY) as dueDate,
        CASE 
            WHEN t.returnDate IS NOT NULL THEN 'Returned'
            WHEN CURDATE() > DATE_ADD(t.borrowDate, INTERVAL 14 DAY) THEN 'Overdue'
            ELSE 'Active'
        END as status
        FROM transactions t
        LEFT JOIN books b ON t.isbn = b.isbn
        WHERE t.userId = ?
        ORDER BY t.borrowDate DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $userId);
$stmt->execute();
$result = $stmt->get_result();

echo "Borrow History for User: $userId\n";
echo "----------------------------------------\n";
while ($record = $result->fetch_assoc()) {
    echo "Transaction ID: " . $record['tid'] . "\n";
    echo "Book: " . $record['bookName'] . "\n";
    echo "ISBN: " . $record['isbn'] . "\n";
    echo "Borrow Date: " . $record['borrowDate'] . "\n";
    echo "Return Date: " . ($record['returnDate'] ?? 'Not Returned') . "\n";
    echo "Status: " . $record['status'] . "\n";
    echo "Fine Amount: $" . number_format($record['fineAmount'], 2) . "\n";
    echo "Fine Status: " . $record['fineStatus'] . "\n";
    echo "----------------------------------------\n";
}

$stmt->close();
