<?php

use Phinx\Migration\AbstractMigration;

class CreateEResourcesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('e_resources', ['id' => false, 'primary_key' => 'esourceId']);
        $table->addColumn('esourceId', 'integer', ['identity' => true, 'null' => false])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('fileUrl', 'string', ['limit' => 500, 'null' => false])
            ->addColumn('publicId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('uploadedBy', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('status', 'enum', ['values' => ['pending', 'approved', 'rejected'], 'default' => 'pending'])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['uploadedBy'], ['name' => 'idx_uploadedBy'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addForeignKey('uploadedBy', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
