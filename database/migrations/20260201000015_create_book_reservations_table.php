<?php

use Phinx\Migration\AbstractMigration;

class CreateBookReservationsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('book_reservations');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('isbn', 'string', ['limit' => 13, 'null' => false])
            ->addColumn('reservationStatus', 'enum', ['values' => ['Active', 'Notified', 'Expired', 'Cancelled'], 'default' => 'Active'])
            ->addColumn('notifiedDate', 'datetime', ['null' => true])
            ->addColumn('expiryDate', 'date', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['isbn'], ['name' => 'idx_isbn'])
            ->addIndex(['reservationStatus'], ['name' => 'idx_status'])
            ->addForeignKey('userId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('isbn', 'books', 'isbn', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
