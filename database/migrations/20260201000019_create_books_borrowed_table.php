<?php

use Phinx\Migration\AbstractMigration;

class CreateBooksBorrowedTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('books_borrowed');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('isbn', 'string', ['limit' => 13, 'null' => false])
            ->addColumn('borrowDate', 'date', ['null' => false])
            ->addColumn('dueDate', 'date', ['null' => false])
            ->addColumn('returnDate', 'date', ['null' => true])
            ->addColumn('status', 'enum', ['values' => ['Active', 'Returned', 'Overdue'], 'default' => 'Active'])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('addedBy', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['isbn'], ['name' => 'idx_isbn'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['borrowDate'], ['name' => 'idx_borrowDate'])
            ->addIndex(['dueDate'], ['name' => 'idx_dueDate'])
            ->addIndex(['userId', 'status', 'dueDate'], ['name' => 'idx_user_active_borrows'])
            ->addForeignKey('userId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('isbn', 'books', 'isbn', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('addedBy', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
