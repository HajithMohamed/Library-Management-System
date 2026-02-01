<?php

use Phinx\Migration\AbstractMigration;

class CreateBookReviewsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('book_reviews');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('isbn', 'string', ['limit' => 13, 'null' => false])
            ->addColumn('rating', 'integer', ['limit' => 1, 'null' => false])
            ->addColumn('reviewText', 'text', ['null' => true])
            ->addColumn('isApproved', 'boolean', ['default' => 0])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['isbn'], ['name' => 'idx_isbn'])
            ->addIndex(['rating'], ['name' => 'idx_rating'])
            ->addIndex(['isbn', 'isApproved', 'rating'], ['name' => 'idx_book_approved_rating'])
            ->addForeignKey('userId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('isbn', 'books', 'isbn', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
