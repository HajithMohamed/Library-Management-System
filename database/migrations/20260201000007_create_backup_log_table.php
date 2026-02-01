<?php

use Phinx\Migration\AbstractMigration;

class CreateBackupLogTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('backup_log');
        $table->addColumn('filename', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('filepath', 'string', ['limit' => 500, 'null' => false])
            ->addColumn('filesize', 'biginteger', ['null' => false])
            ->addColumn('backupType', 'enum', ['values' => ['manual', 'scheduled', 'system'], 'default' => 'manual'])
            ->addColumn('status', 'enum', ['values' => ['success', 'failed', 'in_progress'], 'default' => 'success'])
            ->addColumn('createdBy', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['createdAt'], ['name' => 'idx_createdAt'])
            ->addIndex(['backupType'], ['name' => 'idx_backupType'])
            ->create();
    }
}
