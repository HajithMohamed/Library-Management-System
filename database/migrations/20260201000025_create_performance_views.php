<?php

use Phinx\Migration\AbstractMigration;

class CreatePerformanceViews extends AbstractMigration
{
    public function up()
    {
        // View for active borrows
        $this->execute("
            CREATE OR REPLACE VIEW vw_active_borrows AS
            SELECT 
                t.tid, 
                u.username, 
                u.emailId, 
                b.bookName, 
                b.isbn, 
                t.borrowDate, 
                t.returnDate,
                t.fineAmount,
                t.fineStatus
            FROM transactions t
            JOIN users u ON t.userId = u.userId
            JOIN books b ON t.isbn = b.isbn
            WHERE t.returnDate IS NULL
        ");

        // View for overdue books
        $this->execute("
            CREATE OR REPLACE VIEW vw_overdue_books AS
            SELECT 
                t.tid, 
                u.username, 
                b.bookName, 
                t.borrowDate,
                DATEDIFF(NOW(), t.borrowDate) as daysBorrowed
            FROM transactions t
            JOIN users u ON t.userId = u.userId
            JOIN books b ON t.isbn = b.isbn
            WHERE t.returnDate IS NULL 
            AND DATEDIFF(NOW(), t.borrowDate) > 14
        ");
    }

    public function down()
    {
        $this->execute("DROP VIEW IF EXISTS vw_active_borrows");
        $this->execute("DROP VIEW IF EXISTS vw_overdue_books");
    }
}
