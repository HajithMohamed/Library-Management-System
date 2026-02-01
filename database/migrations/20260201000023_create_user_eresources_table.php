<?php

use Phinx\Migration\AbstractMigration;

class CreateUserEresourcesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('user_eresources');
        $table->addColumn('user_id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('esource_id', 'integer', ['null' => false])
            ->addColumn('obtained_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id', 'esource_id'], ['unique' => true, 'name' => 'unique_user_resource'])
            ->addIndex(['user_id'], ['name' => 'idx_user'])
            ->addIndex(['esource_id'], ['name' => 'idx_resource'])
            ->addForeignKey('user_id', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('esource_id', 'e_resources', 'esourceId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
