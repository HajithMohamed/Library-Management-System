<?php
use Phinx\Migration\AbstractMigration;

class CreateAuditLogsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('audit_logs');
        $table->addColumn('user_id', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('action', 'string', ['limit' => 255])
            ->addColumn('details', 'text', ['null' => true])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'userId', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
