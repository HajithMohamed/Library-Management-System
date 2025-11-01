<?php
include_once('config.php');
include_once('dbConnection.php');

echo "<h1>Merging Borrow Records</h1>";

try {
    // Start transaction
    $conn->begin_transaction();

    // 1. Get all records from books_borrowed that don't exist in transactions
    $sql = "SELECT bb.*, 
            COALESCE(bb.borrowDate, bb.createdAt) as borrowDate,
            COALESCE(bb.returnDate, NULL) as returnDate,
            CASE 
                WHEN bb.status = 'Returned' THEN bb.returnDate
                WHEN bb.status = 'Overdue' THEN NULL
                ELSE NULL
            END as returnDate,
            0.00 as fineAmount,
            'pending' as fineStatus,
            NULL as finePaymentDate,
            NULL as finePaymentMethod
            FROM books_borrowed bb
            LEFT JOIN transactions t ON 
                bb.userId = t.userId AND 
                bb.isbn = t.isbn AND 
                bb.borrowDate = t.borrowDate
            WHERE t.tid IS NULL";

    $result = $conn->query($sql);
    $mergedCount = 0;

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Generate transaction ID
            $tid = 'TXN' . time() . rand(100, 999);
            
            // Insert into transactions
            $stmt = $conn->prepare("INSERT INTO transactions (
                tid, userId, isbn, borrowDate, returnDate, 
                fineAmount, fineStatus, finePaymentDate, finePaymentMethod
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("sssssdsss", 
                $tid,
                $row['userId'],
                $row['isbn'],
                $row['borrowDate'],
                $row['returnDate'],
                $row['fineAmount'],
                $row['fineStatus'],
                $row['finePaymentDate'],
                $row['finePaymentMethod']
            );

            if ($stmt->execute()) {
                $mergedCount++;
                echo "<p style='color:green'>✓ Merged record for user {$row['userId']}, book {$row['isbn']}</p>";
            } else {
                echo "<p style='color:red'>✗ Failed to merge record: " . $stmt->error . "</p>";
            }
        }
    }

    // 2. Update any missing fields in transactions from books_borrowed
    $sql = "UPDATE transactions t
            JOIN books_borrowed bb ON 
                t.userId = bb.userId AND 
                t.isbn = bb.isbn AND 
                t.borrowDate = bb.borrowDate
            SET 
                t.returnDate = COALESCE(t.returnDate, bb.returnDate),
                t.fineAmount = COALESCE(t.fineAmount, 0.00),
                t.fineStatus = COALESCE(t.fineStatus, 'pending')
            WHERE t.returnDate IS NULL AND bb.returnDate IS NOT NULL";

    $conn->query($sql);

    // 3. Commit transaction
    $conn->commit();

    echo "<h2>Migration Complete!</h2>";
    echo "<p>Successfully merged $mergedCount records from books_borrowed to transactions.</p>";
    echo "<p>You can now safely remove the books_borrowed table if needed.</p>";

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "<h2 style='color:red'>Error During Migration</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>