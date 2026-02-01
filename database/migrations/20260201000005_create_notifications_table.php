<?php

use Phinx\Migration\AbstractMigration;

class CreateNotificationsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('notifications');
        $table->addColumn('userId', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('title', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('message', 'text', ['null' => false])
            ->addColumn('type', 'enum', ['values' => ['overdue', 'fine_paid', 'out_of_stock', 'system', 'reminder', 'approval'], 'default' => 'system'])
            ->addColumn('priority', 'enum', ['values' => ['low', 'medium', 'high'], 'default' => 'medium'])
            ->addColumn('isRead', 'boolean', ['default' => 0])
            ->addColumn('relatedId', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['userId'], ['name' => 'idx_userId'])
            ->addIndex(['type'], ['name' => 'idx_type'])
            ->addIndex(['isRead'], ['name' => 'idx_isRead'])
            ->addIndex(['createdAt'], ['name' => 'idx_createdAt'])
            ->addIndex(['userId', 'isRead', 'createdAt'], ['name' => 'idx_user_unread'])
            ->addIndex(['type', 'priority', 'isRead'], ['name' => 'idx_type_priority_read'])
            ->create();
    }
}
