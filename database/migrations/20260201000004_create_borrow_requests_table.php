<?php

use Phinx\Migration\AbstractMigration;

class CreateBorrowRequestsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('borrow_requests');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('isbn', 'string', ['limit' => 13, 'null' => false])
            ->addColumn('requestDate', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('status', 'enum', ['values' => ['Pending', 'Approved', 'Rejected'], 'default' => 'Pending'])
            ->addColumn('approvedBy', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('dueDate', 'date', ['null' => true])
            ->addColumn('rejectionReason', 'text', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['isbn'], ['name' => 'idx_isbn'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['requestDate'], ['name' => 'idx_requestDate'])
            ->addIndex(['approvedBy'], ['name' => 'idx_approvedBy'])
            ->addIndex(['userId', 'status', 'requestDate'], ['name' => 'idx_user_status_date'])
            ->addIndex(['isbn', 'status'], ['name' => 'idx_isbn_status'])
            ->addForeignKey('userId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('isbn', 'books', 'isbn', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('approvedBy', 'users', 'userId', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
