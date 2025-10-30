<?php
// ...existing code...

$query = "SELECT 
    transactions.transaction_id,
    transactions.user_id,
    transactions.book_id,
    transactions.borrow_date,
    transactions.due_date,
    transactions.return_date,
    transactions.status,
    users.name as user_name,
    users.email as user_email,
    books.title as book_title,
    books.author as book_author
FROM transactions
INNER JOIN users ON transactions.user_id = users.user_id
INNER JOIN books ON transactions.book_id = books.book_id
WHERE transactions.status IN ('borrowed', 'overdue')
ORDER BY transactions.borrow_date DESC";

$result = $db->query($query);
?>