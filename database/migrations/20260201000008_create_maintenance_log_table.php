<?php

use Phinx\Migration\AbstractMigration;

class CreateMaintenanceLogTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('maintenance_log');
        $table->addColumn('action', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('performedBy', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('status', 'enum', ['values' => ['success', 'failed', 'warning'], 'default' => 'success'])
            ->addColumn('details', 'text', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['createdAt'], ['name' => 'idx_createdAt'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->create();
    }
}
