<?php

use Phinx\Migration\AbstractMigration;

class CreateFavoritesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('favorites');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('isbn', 'string', ['limit' => 13, 'null' => false])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId', 'isbn'], ['unique' => true, 'name' => 'unique_user_book'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['isbn'], ['name' => 'idx_isbn'])
            ->addForeignKey('userId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('isbn', 'books', 'isbn', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
