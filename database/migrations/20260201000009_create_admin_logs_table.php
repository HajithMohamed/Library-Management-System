<?php

use Phinx\Migration\AbstractMigration;

class CreateAdminLogsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('admin_logs');
        $table->addColumn('adminId', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('action', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('entityType', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('entityId', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('oldValues', 'json', ['null' => true])
            ->addColumn('newValues', 'json', ['null' => true])
            ->addColumn('ipAddress', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('userAgent', 'text', ['null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['adminId'], ['name' => 'idx_adminId'])
            ->addIndex(['createdAt'], ['name' => 'idx_createdAt'])
            ->addIndex(['action'], ['name' => 'idx_action'])
            ->addIndex(['adminId', 'createdAt'], ['name' => 'idx_admin_time'])
            ->addIndex(['action', 'entityType'], ['name' => 'idx_action_entity'])
            ->addForeignKey('adminId', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
