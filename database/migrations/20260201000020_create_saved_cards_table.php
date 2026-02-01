<?php

use Phinx\Migration\AbstractMigration;

class CreateSavedCardsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('saved_cards');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('cardNickname', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('cardLastFour', 'string', ['limit' => 4, 'null' => false])
            ->addColumn('cardType', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('cardHolderName', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('expiryMonth', 'string', ['limit' => 2, 'null' => false])
            ->addColumn('expiryYear', 'string', ['limit' => 4, 'null' => false])
            ->addColumn('isDefault', 'boolean', ['default' => 0])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addForeignKey('userId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
