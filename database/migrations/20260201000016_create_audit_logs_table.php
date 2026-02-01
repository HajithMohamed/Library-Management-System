<?php

use Phinx\Migration\AbstractMigration;

class CreateAuditLogsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('audit_logs');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('action', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('entityType', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('entityId', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('changes', 'json', ['null' => true])
            ->addColumn('ipAddress', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('userAgent', 'text', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['action'], ['name' => 'idx_action'])
            ->addIndex(['createdAt'], ['name' => 'idx_createdAt'])
            ->addIndex(['entityType'], ['name' => 'idx_entityType'])
            ->create();
    }
}
