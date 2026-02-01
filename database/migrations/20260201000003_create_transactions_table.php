<?php

use Phinx\Migration\AbstractMigration;

class CreateTransactionsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('transactions', ['id' => false, 'primary_key' => 'tid']);
        $table->addColumn('tid', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('isbn', 'string', ['limit' => 13, 'null' => false])
            ->addColumn('borrowDate', 'date', ['null' => false])
            ->addColumn('returnDate', 'date', ['null' => true])
            ->addColumn('lastFinePaymentDate', 'date', ['null' => true])
            ->addColumn('fineAmount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('fineStatus', 'enum', ['values' => ['pending', 'paid', 'waived'], 'default' => 'pending'])
            ->addColumn('finePaymentDate', 'date', ['null' => true])
            ->addColumn('finePaymentMethod', 'enum', ['values' => ['cash', 'online', 'card', 'credit_card', 'debit_card', 'upi'], 'null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['isbn'], ['name' => 'idx_isbn'])
            ->addIndex(['borrowDate'], ['name' => 'idx_borrowDate'])
            ->addIndex(['returnDate'], ['name' => 'idx_returnDate'])
            ->addIndex(['fineStatus'], ['name' => 'idx_fineStatus'])
            ->addIndex(['userId', 'borrowDate'], ['name' => 'idx_user_borrow'])
            ->addIndex(['borrowDate', 'returnDate'], ['name' => 'idx_date_range'])
            ->addIndex(['userId', 'fineStatus', 'returnDate'], ['name' => 'idx_user_fine_status'])
            ->addForeignKey('userId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('isbn', 'books', 'isbn', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
