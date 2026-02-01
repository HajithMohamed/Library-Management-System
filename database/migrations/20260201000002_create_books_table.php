<?php

use Phinx\Migration\AbstractMigration;

class CreateBooksTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('books', ['id' => false, 'primary_key' => 'isbn']);
        $table->addColumn('isbn', 'string', ['limit' => 13, 'null' => false])
            ->addColumn('barcode', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('bookName', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('authorName', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('publisherName', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('category', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('publicationYear', 'integer', ['limit' => 4, 'null' => true])
            ->addColumn('totalCopies', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('available', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('borrowed', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('bookImage', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('isTrending', 'boolean', ['default' => 0])
            ->addColumn('isSpecial', 'boolean', ['default' => 0])
            ->addColumn('specialBadge', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addIndex(['authorName'], ['name' => 'idx_author'])
            ->addIndex(['publisherName'], ['name' => 'idx_publisher'])
            ->addIndex(['category'], ['name' => 'idx_category'])
            ->addIndex(['available'], ['name' => 'idx_available'])
            ->addIndex(['isTrending'], ['name' => 'idx_trending'])
            ->addIndex(['category', 'isTrending', 'available'], ['name' => 'idx_books_search'])
            ->addIndex(['publicationYear', 'category'], ['name' => 'idx_books_sort'])
            ->create();
    }
}
